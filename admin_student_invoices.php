<?php include('includes/dbconnect.php'); ?>
<?php
session_start();
if (@$_SESSION['user_type'] != 1 && @$_SESSION['user_type'] != 2) {
    header('Location: index.php');
    exit;
}

// Ensure table exists
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS student_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrolment_db_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    student_name VARCHAR(200),
    student_id VARCHAR(100),
    email_address VARCHAR(200),
    course_enrolled TEXT,
    enrolment_ref VARCHAR(100),
    branch VARCHAR(100),
    issue_date DATE,
    due_date DATE,
    payment_terms VARCHAR(50),
    invoice_type VARCHAR(100),
    payment_method VARCHAR(100),
    funding_type VARCHAR(100),
    currency VARCHAR(10) DEFAULT 'AUD',
    line_items JSON,
    subtotal DECIMAL(10,2) DEFAULT 0,
    gst_amount DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    total_due DECIMAL(10,2) DEFAULT 0,
    paid_amount DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(30) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Fetch all invoices
$invoices = [];
$res = mysqli_query($connection, "SELECT * FROM student_invoices ORDER BY id DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $invoices[] = $row;
    }
}

// Summary counts
$total_count   = count($invoices);
$paid_count    = count(array_filter($invoices, fn($i) => $i['status'] === 'paid'));
$pending_count = count(array_filter($invoices, fn($i) => $i['status'] === 'pending'));
$overdue_count = count(array_filter($invoices, function($i) {
    return $i['status'] !== 'paid' && !empty($i['due_date']) && strtotime($i['due_date']) < time();
}));
$total_revenue = array_sum(array_column(
    array_filter($invoices, fn($i) => $i['status'] === 'paid'), 'total_due'
));
$total_outstanding = array_sum(array_map(function($i) {
    $bal = floatval($i['total_due']) - floatval($i['paid_amount'] ?? 0);
    return $i['status'] !== 'paid' ? max(0, $bal) : 0;
}, $invoices));

function avatarInfo($name) {
    $parts = array_filter(explode(' ', trim($name)));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $p) $initials .= strtoupper($p[0]);
    $colours = ['#3b7dd8','#e04f5f','#f5a623','#2ecc71','#9b59b6','#1abc9c','#e67e22','#e74c3c'];
    $idx = abs(crc32($name)) % count($colours);
    return ['initials' => $initials ?: '?', 'color' => $colours[$idx]];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student Invoices – Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        /* Summary cards */
        .summary-card { border-radius: 10px; padding: 18px 20px; color: #fff; }
        .summary-card .label { font-size: 0.8rem; opacity: .85; margin-bottom: 4px; }
        .summary-card .value { font-size: 1.6rem; font-weight: 700; line-height: 1; }
        .summary-card .icon  { font-size: 2rem; opacity: .3; }
        .sc-blue   { background: linear-gradient(135deg,#1e3a5f,#3b7dd8); }
        .sc-green  { background: linear-gradient(135deg,#155724,#2ecc71); }
        .sc-orange { background: linear-gradient(135deg,#856404,#f5a623); }
        .sc-red    { background: linear-gradient(135deg,#721c24,#e04f5f); }

        /* Status badges */
        .badge-paid    { background:#d4edda; color:#155724; font-size:.75rem; padding:4px 10px; border-radius:20px; }
        .badge-pending { background:#fff3cd; color:#856404; font-size:.75rem; padding:4px 10px; border-radius:20px; }
        .badge-overdue { background:#f8d7da; color:#721c24; font-size:.75rem; padding:4px 10px; border-radius:20px; }
        .badge-sent    { background:#cce5ff; color:#004085; font-size:.75rem; padding:4px 10px; border-radius:20px; }

        /* Table */
        .inv-table th {
            font-size:.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.05em; color:#6c757d; background:#f8f9fa;
            white-space:nowrap; padding:12px 14px;
        }
        .inv-table td { padding:13px 14px; vertical-align:middle; }
        .inv-table tbody tr:hover { background:#f8f9fa; }

        /* Avatar */
        .av { width:36px; height:36px; border-radius:50%; display:flex; align-items:center;
              justify-content:center; font-weight:700; font-size:.78rem; color:#fff; flex-shrink:0; }

        /* Progress */
        .paid-bar { min-width:80px; }
        .paid-bar .progress { height:5px; border-radius:4px; margin-bottom:2px; }
        .paid-pct { font-size:.72rem; color:#6c757d; }

        /* Filter bar */
        .filter-bar .form-control, .filter-bar .form-select { font-size:.85rem; }
        .filter-bar .input-group-text { background:#fff; border-right:none; }
        .filter-bar .search-input { border-left:none; }

        .inv-num { font-weight:700; color:#1e3a5f; font-size:.875rem; }
        .amount-due { color:#dc3545; font-weight:600; }

        /* Checkbox column */
        .inv-table th:first-child, .inv-table td:first-child { width:42px; text-align:center; }
        .inv-check { width:16px; height:16px; cursor:pointer; accent-color:#1e3a5f; }

        /* Bulk action bar */
        #bulkBar {
            display:none; align-items:center; gap:10px;
            background:#1e3a5f; color:#fff;
            padding:10px 18px; border-radius:0;
            border-bottom:1px solid #dee2e6;
        }
        #bulkBar.show { display:flex; }
        #bulkBar .bulk-count { font-weight:600; font-size:.9rem; }
        #bulkBar .btn-bulk {
            font-size:.82rem; padding:5px 14px; border-radius:6px;
            border:1px solid rgba(255,255,255,.4); color:#fff;
            background:rgba(255,255,255,.12); cursor:pointer;
            display:inline-flex; align-items:center; gap:5px;
        }
        #bulkBar .btn-bulk:hover { background:rgba(255,255,255,.25); }
        #bulkBar .btn-bulk-danger { border-color:rgba(220,53,69,.6); background:rgba(220,53,69,.2); }
        #bulkBar .btn-bulk-danger:hover { background:rgba(220,53,69,.4); }
    </style>
</head>
<body>
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">

                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Student Invoices</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item">Invoices</li>
                                    <li class="breadcrumb-item active">Student Invoices</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="summary-card sc-blue d-flex justify-content-between align-items-center">
                            <div>
                                <div class="label">Total Invoices</div>
                                <div class="value"><?php echo $total_count; ?></div>
                            </div>
                            <i class="ti ti-file-invoice icon"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="summary-card sc-green d-flex justify-content-between align-items-center">
                            <div>
                                <div class="label">Revenue Collected</div>
                                <div class="value">$<?php echo number_format($total_revenue, 0); ?></div>
                            </div>
                            <i class="ti ti-circle-check icon"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="summary-card sc-orange d-flex justify-content-between align-items-center">
                            <div>
                                <div class="label">Outstanding</div>
                                <div class="value">$<?php echo number_format($total_outstanding, 0); ?></div>
                            </div>
                            <i class="ti ti-clock icon"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="summary-card sc-red d-flex justify-content-between align-items-center">
                            <div>
                                <div class="label">Overdue</div>
                                <div class="value"><?php echo $overdue_count; ?></div>
                            </div>
                            <i class="ti ti-alert-circle icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Table card -->
                <div class="card">
                    <div class="card-body p-0">

                        <!-- Filter bar -->
                        <div class="filter-bar d-flex flex-wrap gap-2 p-3 border-bottom align-items-center">
                            <div class="input-group" style="max-width:280px;">
                                <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" class="form-control search-input" id="searchInput"
                                       placeholder="Search name, invoice #, course…">
                            </div>
                            <select class="form-select" id="filterStatus" style="max-width:150px;">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                                <option value="sent">Sent</option>
                            </select>
                            <select class="form-select" id="filterBranch" style="max-width:150px;">
                                <option value="">All Branches</option>
                                <?php
                                $branches = array_unique(array_filter(array_column($invoices, 'branch')));
                                foreach ($branches as $b): ?>
                                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="form-select" id="filterType" style="max-width:160px;">
                                <option value="">All Types</option>
                                <?php
                                $types = array_unique(array_filter(array_column($invoices, 'invoice_type')));
                                foreach ($types as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="ms-auto d-flex gap-2 align-items-center">
                                <span class="text-muted small" id="rowCount"><?php echo $total_count; ?> invoices</span>
                            </div>
                        </div>

                        <!-- Bulk action bar (hidden until rows selected) -->
                        <div id="bulkBar">
                            <span class="bulk-count"><span id="selCount">0</span> selected</span>
                            <button class="btn-bulk" onclick="bulkPrint()">
                                <i class="ti ti-printer"></i> Print
                            </button>
                            <button class="btn-bulk" onclick="bulkExportCSV()">
                                <i class="ti ti-table-export"></i> Export CSV
                            </button>
                            <button class="btn-bulk" onclick="bulkDownloadPDF()">
                                <i class="ti ti-file-download"></i> Download PDF
                            </button>
                            <button class="btn-bulk btn-bulk-danger ms-auto" onclick="clearSelection()">
                                <i class="ti ti-x"></i> Clear
                            </button>
                        </div>

                        <?php if (empty($invoices)): ?>
                        <div class="text-center py-5">
                            <i class="ti ti-file-invoice fs-1 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">No student invoices yet</h5>
                            <p class="text-muted">Invoices submitted by students will appear here.</p>
                        </div>
                        <?php else: ?>

                        <div class="table-responsive">
                            <table class="table inv-table mb-0">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="inv-check" id="selectAll" title="Select all"></th>
                                        <th>Invoice #</th>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="invoiceBody">
                                <?php foreach ($invoices as $idx => $inv):
                                    $total      = floatval($inv['total_due']);
                                    $paid       = floatval($inv['paid_amount'] ?? 0);
                                    $balance    = max(0, $total - $paid);
                                    $pct        = $total > 0 ? min(100, round(($paid / $total) * 100)) : 0;
                                    $is_paid    = $inv['status'] === 'paid';
                                    $is_overdue = $inv['status'] !== 'paid' && !empty($inv['due_date']) && strtotime($inv['due_date']) < time();
                                    $status_key = $is_overdue ? 'overdue' : $inv['status'];
                                    $av = avatarInfo($inv['student_name'] ?? '');
                                    $due_cls = $is_overdue ? 'text-danger fw-bold' : '';
                                ?>
                                <tr class="inv-row"
                                    data-id="<?php echo $inv['id']; ?>"
                                    data-search="<?php echo strtolower(htmlspecialchars(($inv['invoice_number'] ?? '') . ' ' . ($inv['student_name'] ?? '') . ' ' . ($inv['course_enrolled'] ?? ''))); ?>"
                                    data-status="<?php echo htmlspecialchars($inv['status']); ?>"
                                    data-overdue="<?php echo $is_overdue ? '1' : '0'; ?>"
                                    data-branch="<?php echo htmlspecialchars($inv['branch'] ?? ''); ?>"
                                    data-type="<?php echo htmlspecialchars($inv['invoice_type'] ?? ''); ?>">

                                    <td><input type="checkbox" class="inv-check row-check" data-id="<?php echo $inv['id']; ?>"></td>
                                    <td><span class="inv-num"><?php echo htmlspecialchars($inv['invoice_number']); ?></span></td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="av" style="background:<?php echo $av['color']; ?>"><?php echo $av['initials']; ?></div>
                                            <div>
                                                <div class="fw-semibold" style="font-size:.875rem;"><?php echo htmlspecialchars($inv['student_name'] ?? '—'); ?></div>
                                                <div class="text-muted" style="font-size:.75rem;"><?php echo htmlspecialchars($inv['email_address'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td style="max-width:150px;font-size:.82rem;color:#6c757d;">
                                        <?php echo htmlspecialchars($inv['course_enrolled'] ?? '—'); ?>
                                    </td>

                                    <td style="white-space:nowrap;font-size:.875rem;">
                                        <?php echo $inv['issue_date'] ? date('d M Y', strtotime($inv['issue_date'])) : '—'; ?>
                                    </td>

                                    <td class="<?php echo $due_cls; ?>" style="white-space:nowrap;font-size:.875rem;">
                                        <?php echo $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '—'; ?>
                                    </td>

                                    <td class="fw-semibold" style="white-space:nowrap;">
                                        $<?php echo number_format($total, 2); ?>
                                    </td>

                                    <td>
                                        <div class="paid-bar">
                                            <div class="progress">
                                                <div class="progress-bar <?php echo $is_paid ? 'bg-success' : 'bg-primary'; ?>"
                                                     style="width:<?php echo $pct; ?>%"></div>
                                            </div>
                                            <span class="paid-pct"><?php echo $pct; ?>%</span>
                                        </div>
                                    </td>

                                    <td class="<?php echo $balance > 0 ? 'amount-due' : 'text-muted'; ?>" style="white-space:nowrap;">
                                        <?php echo $balance > 0 ? '$' . number_format($balance, 2) : '—'; ?>
                                    </td>

                                    <td>
                                        <?php
                                        $badge_map = [
                                            'paid'    => '<span class="badge-paid"><i class="ti ti-circle-check me-1"></i>Paid</span>',
                                            'pending' => '<span class="badge-pending"><i class="ti ti-clock me-1"></i>Pending</span>',
                                            'sent'    => '<span class="badge-sent"><i class="ti ti-send me-1"></i>Sent</span>',
                                            'overdue' => '<span class="badge-overdue"><i class="ti ti-alert-circle me-1"></i>Overdue</span>',
                                        ];
                                        echo $badge_map[$status_key] ?? '<span class="badge-pending">' . htmlspecialchars($inv['status']) . '</span>';
                                        ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewInvoice(<?php echo $inv['id']; ?>)" title="View">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="markPaid(<?php echo $inv['id']; ?>, this)" title="Mark as Paid"
                                                <?php echo $is_paid ? 'disabled' : ''; ?>>
                                                <i class="ti ti-circle-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(<?php echo $inv['id']; ?>, this)" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- View Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e3a5f;color:#fff;">
                <h5 class="modal-title"><i class="ti ti-file-invoice me-2"></i>Invoice Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceModalBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-outline-success" onclick="downloadInvoice()"><i class="ti ti-download me-1"></i>Download</button>
                <button class="btn btn-primary" onclick="printInvoice()"><i class="ti ti-printer me-1"></i>Print</button>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer_includes.php'); ?>
<script>
var allInvoices      = <?php echo json_encode($invoices); ?>;
var currentInvoiceId = null;

// ── Filter ───────────────────────────────────────────────────────
function filterTable() {
    var search  = document.getElementById('searchInput').value.toLowerCase();
    var status  = document.getElementById('filterStatus').value;
    var branch  = document.getElementById('filterBranch').value;
    var type    = document.getElementById('filterType').value;
    var visible = 0;

    document.querySelectorAll('#invoiceBody .inv-row').forEach(function(row) {
        var matchSearch  = !search || row.dataset.search.includes(search);
        var rowStatus    = row.dataset.status;
        var rowOverdue   = row.dataset.overdue === '1';
        var matchStatus  = !status
            || rowStatus === status
            || (status === 'overdue' && rowOverdue);
        var matchBranch  = !branch || row.dataset.branch === branch;
        var matchType    = !type   || row.dataset.type   === type;
        var show = matchSearch && matchStatus && matchBranch && matchType;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('rowCount').textContent = visible + ' invoice' + (visible !== 1 ? 's' : '');
}
['searchInput','filterStatus','filterBranch','filterType'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', filterTable);
    document.getElementById(id).addEventListener('change', filterTable);
});

// ── Mark Paid ────────────────────────────────────────────────────
function markPaid(id, btn) {
    if (!confirm('Mark this invoice as Paid?')) return;
    btn.disabled = true;
    var fd = new FormData();
    fd.append('action',     'admin_mark_invoice_paid');
    fd.append('invoice_id', id);
    fetch('includes/datacontrol.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                Toastify({ text: 'Invoice marked as paid.', duration: 3000, backgroundColor: '#28a745' }).showToast();
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                alert(res.message || 'Failed.');
                btn.disabled = false;
            }
        });
}

// ── Delete ───────────────────────────────────────────────────────
function deleteInvoice(id, btn) {
    if (!confirm('Delete this invoice? This cannot be undone.')) return;
    btn.disabled = true;
    var fd = new FormData();
    fd.append('action',     'admin_delete_invoice');
    fd.append('invoice_id', id);
    fetch('includes/datacontrol.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                btn.closest('tr').remove();
                Toastify({ text: 'Invoice deleted.', duration: 3000, backgroundColor: '#dc3545' }).showToast();
            } else {
                alert(res.message || 'Failed.');
                btn.disabled = false;
            }
        });
}

// ── View modal ───────────────────────────────────────────────────
function viewInvoice(id) {
    currentInvoiceId = id;
    var inv = allInvoices.find(function(i) { return parseInt(i.id) === id; });
    if (!inv) return;

    var lineItems = [];
    try { lineItems = JSON.parse(inv.line_items || '[]'); } catch(e) {}

    var total   = parseFloat(inv.total_due   || 0);
    var paid    = parseFloat(inv.paid_amount || 0);
    var balance = Math.max(0, total - paid);

    var rows = lineItems.map(function(li) {
        return '<tr><td>' + esc(li.description) + '</td><td>' + esc(li.unit) + '</td>' +
               '<td>' + li.qty + '</td><td>$' + parseFloat(li.unit_price).toFixed(2) + '</td>' +
               '<td>' + esc(li.gst) + '</td><td class="text-end">$' + parseFloat(li.amount).toFixed(2) + '</td></tr>';
    }).join('');

    var html =
        '<div class="row g-3 mb-3">' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Invoice #</div><div class="fw-bold">'      + esc(inv.invoice_number)   + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Student</div><div>'                        + esc(inv.student_name || '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Student ID</div><div>'                     + esc(inv.student_id   || '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Email</div><div>'                          + esc(inv.email_address || '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Course</div><div style="font-size:.85rem;">' + esc(inv.course_enrolled || '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Branch</div><div>'                         + esc(inv.branch || '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Issue Date</div><div>'   + (inv.issue_date ? fmtDate(inv.issue_date) : '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Due Date</div><div>'     + (inv.due_date  ? fmtDate(inv.due_date)  : '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small mb-1">Payment Method</div><div>' + esc(inv.payment_method || '—') + '</div></div>' +
        '</div><hr>' +
        '<div class="table-responsive"><table class="table table-sm table-bordered mb-3">' +
        '<thead class="table-light"><tr><th>Description</th><th>Unit</th><th>QTY</th><th>Unit Price</th><th>GST?</th><th class="text-end">Amount</th></tr></thead>' +
        '<tbody>' + (rows || '<tr><td colspan="6" class="text-center text-muted py-3">No line items</td></tr>') + '</tbody></table></div>' +
        '<div class="d-flex justify-content-end"><div style="min-width:230px;">' +
        '<div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Subtotal</span><span>$' + parseFloat(inv.subtotal||0).toFixed(2) + '</span></div>' +
        '<div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">GST (10%)</span><span>$' + parseFloat(inv.gst_amount||0).toFixed(2) + '</span></div>' +
        '<div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Discount</span><span>$' + parseFloat(inv.discount||0).toFixed(2) + '</span></div>' +
        '<div class="d-flex justify-content-between py-2"><strong>Total Due</strong><strong class="text-primary fs-5">$' + total.toFixed(2) + '</strong></div>' +
        '<div class="d-flex justify-content-between py-1"><span class="text-muted">Balance</span><span class="' + (balance > 0 ? 'text-danger fw-bold' : 'text-success') + '">$' + balance.toFixed(2) + '</span></div>' +
        '</div></div>';

    document.getElementById('invoiceModalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('invoiceModal')).show();
}

// ── Download PDF ─────────────────────────────────────────────────
function downloadInvoice() {
    var inv = allInvoices.find(function(i) { return parseInt(i.id) === currentInvoiceId; });
    if (!inv) return;
    var lineItems = [];
    try { lineItems = JSON.parse(inv.line_items || '[]'); } catch(e) {}

    var { jsPDF } = window.jspdf;
    var doc = new jsPDF();

    doc.setFillColor(30, 58, 95);
    doc.rect(0, 0, 210, 28, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(15); doc.setFont('helvetica', 'bold');
    doc.text('National College Australia', 14, 12);
    doc.setFontSize(9);  doc.setFont('helvetica', 'normal');
    doc.text('Tax Invoice', 14, 20);
    doc.setFontSize(11); doc.setFont('helvetica', 'bold');
    doc.text(esc(inv.invoice_number), 196, 14, { align: 'right' });
    doc.setFontSize(8);  doc.setFont('helvetica', 'normal');
    doc.text('Status: ' + (inv.status || 'pending').toUpperCase(), 196, 20, { align: 'right' });
    doc.setTextColor(0,0,0);

    var y = 36, ry = 36;
    var leftFields = [
        ['Student',      esc(inv.student_name   || '—')],
        ['Student ID',   esc(inv.student_id     || '—')],
        ['Email',        esc(inv.email_address  || '—')],
        ['Course',       esc(inv.course_enrolled|| '—')],
        ['Enrolment Ref',esc(inv.enrolment_ref  || '—')],
        ['Branch',       esc(inv.branch         || '—')],
    ];
    leftFields.forEach(function(f) {
        doc.setFont('helvetica','bold');   doc.setFontSize(8.5); doc.text(f[0]+':', 14, y);
        doc.setFont('helvetica','normal'); doc.text(f[1],         55, y);
        y += 5.5;
    });
    var rightFields = [
        ['Issue Date',    inv.issue_date ? fmtDate(inv.issue_date) : '—'],
        ['Due Date',      inv.due_date   ? fmtDate(inv.due_date)   : '—'],
        ['Payment Terms', esc(inv.payment_terms  || '—')],
        ['Invoice Type',  esc(inv.invoice_type   || '—')],
        ['Payment Method',esc(inv.payment_method || '—')],
        ['Funding Type',  esc(inv.funding_type   || '—')],
    ];
    rightFields.forEach(function(f) {
        doc.setFont('helvetica','bold');   doc.setFontSize(8.5); doc.text(f[0]+':', 112, ry);
        doc.setFont('helvetica','normal'); doc.text(f[1],         155, ry);
        ry += 5.5;
    });

    y = Math.max(y, ry) + 4;
    doc.setDrawColor(200,200,200); doc.line(14, y, 196, y); y += 6;

    doc.autoTable({
        startY: y,
        head: [['Description','Unit','QTY','Unit Price','GST?','Amount']],
        body: lineItems.length ? lineItems.map(function(li) {
            return [esc(li.description), esc(li.unit), li.qty,
                    '$'+parseFloat(li.unit_price).toFixed(2), li.gst,
                    '$'+parseFloat(li.amount).toFixed(2)];
        }) : [['No line items','','','','','']],
        styles: { fontSize: 8, cellPadding: 3 },
        headStyles: { fillColor: [30,58,95], textColor: 255 },
        columnStyles: { 0: { cellWidth: 70 }, 5: { halign: 'right' } },
        margin: { left: 14, right: 14 },
    });

    var fy = doc.lastAutoTable.finalY + 6;
    [['Subtotal','$'+parseFloat(inv.subtotal||0).toFixed(2)],
     ['GST (10%)','$'+parseFloat(inv.gst_amount||0).toFixed(2)],
     ['Discount','$'+parseFloat(inv.discount||0).toFixed(2)],
     ['Total Due','$'+parseFloat(inv.total_due||0).toFixed(2)]
    ].forEach(function(t, i) {
        var bold = i === 3;
        doc.setFont('helvetica', bold ? 'bold' : 'normal');
        doc.setFontSize(bold ? 10 : 8.5);
        if (bold) doc.setTextColor(13,110,253);
        doc.text(t[0], 150, fy, { align: 'right' });
        doc.text(t[1], 196, fy, { align: 'right' });
        fy += bold ? 7 : 5.5;
        doc.setTextColor(0,0,0);
    });

    doc.save(inv.invoice_number + '.pdf');
}

function printInvoice() {
    var content = document.getElementById('invoiceModalBody').innerHTML;
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Invoice</title>' +
        '<link rel="stylesheet" href="crm/html/template/assets/css/bootstrap.min.css">' +
        '</head><body class="p-4">' + content + '</body></html>');
    win.document.close(); win.focus();
    setTimeout(function() { win.print(); win.close(); }, 500);
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function fmtDate(d) {
    var dt = new Date(d);
    var m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return dt.getDate() + ' ' + m[dt.getMonth()] + ' ' + dt.getFullYear();
}

// ── Checkbox selection ────────────────────────────────────────────
function getSelectedIds() {
    return Array.from(document.querySelectorAll('.row-check:checked')).map(function(cb) {
        return parseInt(cb.dataset.id);
    });
}

function getSelectedInvoices() {
    return getSelectedIds().map(function(id) {
        return allInvoices.find(function(i) { return parseInt(i.id) === id; });
    }).filter(Boolean);
}

function updateBulkBar() {
    var count = getSelectedIds().length;
    document.getElementById('selCount').textContent = count;
    var bar = document.getElementById('bulkBar');
    if (count > 0) bar.classList.add('show'); else bar.classList.remove('show');
    // Update select-all state
    var all  = document.querySelectorAll('.row-check:not([style*="display:none"])');
    var selectAll = document.getElementById('selectAll');
    var visibleChecked = Array.from(all).filter(function(cb) {
        return cb.closest('tr').style.display !== 'none' && cb.checked;
    });
    var visibleAll = Array.from(all).filter(function(cb) {
        return cb.closest('tr').style.display !== 'none';
    });
    selectAll.indeterminate = visibleChecked.length > 0 && visibleChecked.length < visibleAll.length;
    selectAll.checked = visibleAll.length > 0 && visibleChecked.length === visibleAll.length;
}

function clearSelection() {
    document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = false; });
    document.getElementById('selectAll').checked = false;
    updateBulkBar();
}

// Select-all toggle
document.getElementById('selectAll').addEventListener('change', function() {
    var checked = this.checked;
    document.querySelectorAll('#invoiceBody .inv-row').forEach(function(row) {
        if (row.style.display !== 'none') {
            row.querySelector('.row-check').checked = checked;
        }
    });
    updateBulkBar();
});

// Per-row checkboxes
document.getElementById('invoiceBody').addEventListener('change', function(e) {
    if (e.target.classList.contains('row-check')) updateBulkBar();
});

// Highlight selected row
document.getElementById('invoiceBody').addEventListener('change', function(e) {
    if (e.target.classList.contains('row-check')) {
        e.target.closest('tr').style.background = e.target.checked ? '#eef4ff' : '';
    }
});

// ── Bulk Print ────────────────────────────────────────────────────
function bulkPrint() {
    var selected = getSelectedInvoices();
    if (!selected.length) { alert('Select at least one invoice.'); return; }

    var html = '<html><head><title>Invoices</title>' +
        '<link rel="stylesheet" href="crm/html/template/assets/css/bootstrap.min.css">' +
        '<style>body{padding:20px;font-size:13px;} .inv-block{border:1px solid #dee2e6;border-radius:6px;padding:20px;margin-bottom:30px;page-break-inside:avoid;} hr{margin:12px 0;} @media print{.inv-block{page-break-after:always;}}</style>' +
        '</head><body>';

    selected.forEach(function(inv) {
        var lineItems = [];
        try { lineItems = JSON.parse(inv.line_items || '[]'); } catch(e) {}
        var rows = lineItems.map(function(li) {
            return '<tr><td>' + esc(li.description) + '</td><td>' + esc(li.unit) + '</td>' +
                   '<td>' + li.qty + '</td><td>$' + parseFloat(li.unit_price).toFixed(2) + '</td>' +
                   '<td>' + esc(li.gst) + '</td><td class="text-end">$' + parseFloat(li.amount).toFixed(2) + '</td></tr>';
        }).join('');

        html += '<div class="inv-block">' +
            '<div class="d-flex justify-content-between align-items-start mb-3">' +
            '<div><h5 class="mb-1">National College Australia</h5><small class="text-muted">Tax Invoice</small></div>' +
            '<div class="text-end"><strong>' + esc(inv.invoice_number) + '</strong><br>' +
            '<span class="badge bg-secondary">' + (inv.status || 'pending').toUpperCase() + '</span></div></div>' +
            '<div class="row mb-3"><div class="col-6">' +
            '<small class="text-muted d-block">Student</small><strong>' + esc(inv.student_name || '—') + '</strong><br>' +
            '<small>' + esc(inv.email_address || '') + '</small><br>' +
            '<small class="text-muted">' + esc(inv.course_enrolled || '—') + '</small>' +
            '</div><div class="col-6 text-end">' +
            '<small class="text-muted">Issue Date:</small> ' + (inv.issue_date ? fmtDate(inv.issue_date) : '—') + '<br>' +
            '<small class="text-muted">Due Date:</small> ' + (inv.due_date ? fmtDate(inv.due_date) : '—') + '<br>' +
            '<small class="text-muted">Payment Method:</small> ' + esc(inv.payment_method || '—') +
            '</div></div><hr>' +
            '<table class="table table-sm table-bordered mb-3">' +
            '<thead class="table-dark"><tr><th>Description</th><th>Unit</th><th>QTY</th><th>Unit Price</th><th>GST?</th><th class="text-end">Amount</th></tr></thead>' +
            '<tbody>' + (rows || '<tr><td colspan="6" class="text-center text-muted">No items</td></tr>') + '</tbody></table>' +
            '<div class="d-flex justify-content-end"><div style="min-width:200px;font-size:.85rem;">' +
            '<div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>$' + parseFloat(inv.subtotal||0).toFixed(2) + '</span></div>' +
            '<div class="d-flex justify-content-between"><span class="text-muted">GST (10%)</span><span>$' + parseFloat(inv.gst_amount||0).toFixed(2) + '</span></div>' +
            '<div class="d-flex justify-content-between"><span class="text-muted">Discount</span><span>$' + parseFloat(inv.discount||0).toFixed(2) + '</span></div>' +
            '<hr class="my-1">' +
            '<div class="d-flex justify-content-between"><strong>Total Due</strong><strong class="text-primary">$' + parseFloat(inv.total_due||0).toFixed(2) + '</strong></div>' +
            '</div></div></div>';
    });

    html += '</body></html>';
    var win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); }, 600);
}

// ── Bulk Export CSV ───────────────────────────────────────────────
function bulkExportCSV() {
    var selected = getSelectedInvoices();
    if (!selected.length) { alert('Select at least one invoice.'); return; }

    var headers = ['Invoice #','Student','Email','Course','Branch','Issue Date','Due Date',
                   'Payment Terms','Invoice Type','Payment Method','Funding Type','Currency',
                   'Subtotal','GST','Discount','Total Due','Status'];
    var rows = selected.map(function(inv) {
        return [
            inv.invoice_number, inv.student_name, inv.email_address, inv.course_enrolled,
            inv.branch, inv.issue_date, inv.due_date, inv.payment_terms,
            inv.invoice_type, inv.payment_method, inv.funding_type, inv.currency,
            parseFloat(inv.subtotal||0).toFixed(2),
            parseFloat(inv.gst_amount||0).toFixed(2),
            parseFloat(inv.discount||0).toFixed(2),
            parseFloat(inv.total_due||0).toFixed(2),
            inv.status
        ].map(function(v) {
            v = (v === null || v === undefined) ? '' : String(v);
            return '"' + v.replace(/"/g, '""') + '"';
        }).join(',');
    });

    var csv = [headers.map(function(h) { return '"' + h + '"'; }).join(',')]
        .concat(rows).join('\r\n');

    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href   = url;
    a.download = 'student_invoices_' + new Date().toISOString().slice(0,10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ── Bulk Download PDF ─────────────────────────────────────────────
function bulkDownloadPDF() {
    var selected = getSelectedInvoices();
    if (!selected.length) { alert('Select at least one invoice.'); return; }

    var { jsPDF } = window.jspdf;
    var doc = new jsPDF();
    var pageH = doc.internal.pageSize.height;

    selected.forEach(function(inv, invIdx) {
        if (invIdx > 0) doc.addPage();

        var lineItems = [];
        try { lineItems = JSON.parse(inv.line_items || '[]'); } catch(e) {}

        // Header bar
        doc.setFillColor(30, 58, 95);
        doc.rect(0, 0, 210, 26, 'F');
        doc.setTextColor(255,255,255);
        doc.setFontSize(14); doc.setFont('helvetica','bold');
        doc.text('National College Australia', 14, 11);
        doc.setFontSize(8);  doc.setFont('helvetica','normal');
        doc.text('Tax Invoice', 14, 19);
        doc.setFontSize(11); doc.setFont('helvetica','bold');
        doc.text(esc(inv.invoice_number), 196, 11, { align:'right' });
        doc.setFontSize(8);  doc.setFont('helvetica','normal');
        doc.text((inv.status||'pending').toUpperCase(), 196, 19, { align:'right' });
        doc.setTextColor(0,0,0);

        // Left / Right details
        var y = 34, ry = 34;
        [['Student', esc(inv.student_name||'—')],['Email', esc(inv.email_address||'—')],
         ['Course', esc(inv.course_enrolled||'—')],['Branch', esc(inv.branch||'—')],
         ['Student ID', esc(inv.student_id||'—')]
        ].forEach(function(f) {
            doc.setFont('helvetica','bold');   doc.setFontSize(8); doc.text(f[0]+':', 14, y);
            doc.setFont('helvetica','normal'); doc.text(String(f[1]).substring(0,45), 48, y);
            y += 5.5;
        });
        [['Issue Date', inv.issue_date ? fmtDate(inv.issue_date):'—'],
         ['Due Date',   inv.due_date   ? fmtDate(inv.due_date)  :'—'],
         ['Payment Terms',  esc(inv.payment_terms ||'—')],
         ['Invoice Type',   esc(inv.invoice_type  ||'—')],
         ['Payment Method', esc(inv.payment_method||'—')]
        ].forEach(function(f) {
            doc.setFont('helvetica','bold');   doc.setFontSize(8); doc.text(f[0]+':', 112, ry);
            doc.setFont('helvetica','normal'); doc.text(String(f[1]), 152, ry);
            ry += 5.5;
        });

        var startY = Math.max(y, ry) + 4;
        doc.setDrawColor(200,200,200); doc.line(14, startY, 196, startY);

        doc.autoTable({
            startY: startY + 4,
            head: [['Description','Unit','QTY','Unit Price','GST?','Amount']],
            body: lineItems.length ? lineItems.map(function(li) {
                return [esc(li.description), esc(li.unit), li.qty,
                        '$'+parseFloat(li.unit_price).toFixed(2), li.gst,
                        '$'+parseFloat(li.amount).toFixed(2)];
            }) : [['No items','','','','','']],
            styles: { fontSize: 7.5, cellPadding: 2.5 },
            headStyles: { fillColor:[30,58,95], textColor:255 },
            columnStyles: { 0:{cellWidth:68}, 5:{halign:'right'} },
            margin: { left:14, right:14 },
        });

        var fy = doc.lastAutoTable.finalY + 6;
        [['Subtotal',  '$'+parseFloat(inv.subtotal   ||0).toFixed(2)],
         ['GST (10%)','$'+parseFloat(inv.gst_amount  ||0).toFixed(2)],
         ['Discount',  '$'+parseFloat(inv.discount   ||0).toFixed(2)],
         ['Total Due', '$'+parseFloat(inv.total_due  ||0).toFixed(2)]
        ].forEach(function(t, i) {
            var bold = i === 3;
            doc.setFont('helvetica', bold?'bold':'normal');
            doc.setFontSize(bold ? 10 : 8);
            if (bold) doc.setTextColor(13,110,253);
            doc.text(t[0], 150, fy, {align:'right'});
            doc.text(t[1], 196, fy, {align:'right'});
            fy += bold ? 7 : 5;
            doc.setTextColor(0,0,0);
        });
    });

    var filename = selected.length === 1
        ? selected[0].invoice_number + '.pdf'
        : 'invoices_' + new Date().toISOString().slice(0,10) + '_(' + selected.length + ').pdf';
    doc.save(filename);
}
</script>
</body>
</html>

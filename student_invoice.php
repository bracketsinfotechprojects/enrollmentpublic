<?php
include('includes/dbconnect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: student_login.php');
    exit;
}
$ut = @$_SESSION['user_type'];
if ($ut !== 0 && $ut !== 'student') {
    header('Location: dashboard.php');
    exit;
}

$student_user_id = intval($_SESSION['user_id']);

// Get student email
$student_email = '';
$student_name  = $_SESSION['user_name'] ?? '';
$suRes = mysqli_query($connection,
    "SELECT email, full_name FROM student_users WHERE id = $student_user_id LIMIT 1"
);
if ($suRes && mysqli_num_rows($suRes) > 0) {
    $su = mysqli_fetch_assoc($suRes);
    $student_email = $su['email'];
    $student_name  = $su['full_name'] ?: $student_name;
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

// Fetch invoices for this student
$invoices = [];
if ($student_email !== '') {
    $esc_email = mysqli_real_escape_string($connection, $student_email);
    $invRes = mysqli_query($connection,
        "SELECT * FROM student_invoices
         WHERE email_address = '$esc_email'
         ORDER BY id DESC"
    );
    if ($invRes) {
        while ($row = mysqli_fetch_assoc($invRes)) {
            $invoices[] = $row;
        }
    }
}

// Status badge helper
function statusBadge($status, $due_date) {
    if ($status === 'paid') {
        return '<span class="badge badge-paid"><i class="ti ti-circle-check me-1"></i>Paid</span>';
    }
    if ($status === 'overdue' || ($status !== 'paid' && $due_date && strtotime($due_date) < time())) {
        return '<span class="badge badge-overdue"><i class="ti ti-alert-circle me-1"></i>Overdue</span>';
    }
    if ($status === 'sent') {
        return '<span class="badge badge-sent"><i class="ti ti-send me-1"></i>Sent</span>';
    }
    return '<span class="badge badge-pending"><i class="ti ti-clock me-1"></i>Pending</span>';
}

// Avatar initials + colour
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
    <title>My Invoices – National College Australia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        /* Tab nav */
        .tab-nav { border-bottom: 2px solid #dee2e6; margin-bottom: 24px; }
        .tab-nav .nav-link {
            color: #495057; border: none; padding: 10px 18px;
            font-size: 0.875rem; font-weight: 500;
            border-bottom: 3px solid transparent; margin-bottom: -2px;
        }
        .tab-nav .nav-link.active { color: #0d6efd; border-bottom-color: #0d6efd; background: transparent; }
        .tab-nav .nav-link i { margin-right: 5px; }

        /* Status badges */
        .badge { font-size: 0.75rem; padding: 5px 10px; border-radius: 20px; font-weight: 500; }
        .badge-paid    { background:#d4edda; color:#155724; }
        .badge-pending { background:#fff3cd; color:#856404; }
        .badge-overdue { background:#f8d7da; color:#721c24; }
        .badge-sent    { background:#cce5ff; color:#004085; }

        /* Table */
        .invoice-table th {
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: #6c757d; background: #f8f9fa;
            white-space: nowrap; padding: 12px 14px;
        }
        .invoice-table td { padding: 14px; vertical-align: middle; }
        .invoice-table tbody tr:hover { background: #f8f9fa; }

        /* Avatar */
        .avatar-circle {
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: #fff;
            flex-shrink: 0;
        }

        /* Progress bar */
        .paid-progress { min-width: 90px; }
        .paid-progress .progress { height: 6px; border-radius: 4px; margin-bottom: 2px; }
        .paid-pct { font-size: 0.72rem; color: #6c757d; }

        /* Amount colours */
        .amount-due   { color: #dc3545; font-weight: 600; }
        .amount-clear { color: #6c757d; }

        /* Invoice # */
        .inv-num { font-weight: 700; font-size: 0.875rem; color: #1e3a5f; }

        /* Search / filter bar */
        .filter-bar .form-control, .filter-bar .form-select {
            font-size: 0.85rem; border-radius: 6px;
        }
        .filter-bar .input-group-text { background: #fff; border-right: none; }
        .filter-bar .search-input { border-left: none; }

        /* Empty state */
        .empty-state { padding: 60px 0; text-align: center; }
        .empty-state i { font-size: 3rem; color: #dee2e6; }
    </style>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">

                <!-- Page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">My Invoices</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item active">Invoices</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab nav -->
                <ul class="nav tab-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="student_invoice.php">
                            <i class="ti ti-layout-list"></i>All Invoices
                            <span class="badge bg-primary ms-1" style="font-size:0.7rem;border-radius:10px;padding:2px 7px;"><?php echo count($invoices); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_payment.php">
                            <i class="ti ti-plus"></i>Make Payment
                        </a>
                    </li>
                </ul>

                <div class="card">
                    <div class="card-body p-0">

                        <!-- Filter bar -->
                        <div class="filter-bar d-flex flex-wrap gap-2 p-3 border-bottom align-items-center">
                            <div class="input-group" style="max-width:280px;">
                                <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" class="form-control search-input" id="searchInput"
                                       placeholder="Search invoice #, course…">
                            </div>
                            <select class="form-select" id="filterStatus" style="max-width:150px;">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                                <option value="sent">Sent</option>
                            </select>
                            <select class="form-select" id="filterBranch" style="max-width:160px;">
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
                        </div>

                        <?php if (empty($invoices)): ?>
                        <div class="empty-state">
                            <i class="ti ti-file-invoice d-block mb-3"></i>
                            <h5 class="text-muted">No invoices yet</h5>
                            <p class="text-muted mb-4">Submit a payment request and your invoices will appear here.</p>
                            <a href="student_payment.php" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Make Payment
                            </a>
                        </div>
                        <?php else: ?>

                        <!-- Invoice table -->
                        <div class="table-responsive">
                            <table class="table invoice-table mb-0" id="invoiceTable">
                                <thead>
                                    <tr>
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
                                <?php foreach ($invoices as $inv):
                                    $total    = floatval($inv['total_due']);
                                    $paid     = floatval($inv['paid_amount'] ?? 0);
                                    $balance  = max(0, $total - $paid);
                                    $pct      = $total > 0 ? min(100, round(($paid / $total) * 100)) : 0;
                                    $is_paid  = $inv['status'] === 'paid' || $pct >= 100;
                                    $is_overdue = ($inv['status'] !== 'paid' && !empty($inv['due_date']) && strtotime($inv['due_date']) < time());
                                    $av = avatarInfo($inv['student_name'] ?? $student_name);
                                    $due_class = $is_overdue ? 'text-danger fw-bold' : '';
                                ?>
                                <tr class="inv-row"
                                    data-inv="<?php echo strtolower(htmlspecialchars($inv['invoice_number'])); ?>"
                                    data-course="<?php echo strtolower(htmlspecialchars($inv['course_enrolled'] ?? '')); ?>"
                                    data-status="<?php echo htmlspecialchars($inv['status']); ?>"
                                    data-overdue="<?php echo $is_overdue ? 'overdue' : ''; ?>"
                                    data-branch="<?php echo htmlspecialchars($inv['branch'] ?? ''); ?>"
                                    data-type="<?php echo htmlspecialchars($inv['invoice_type'] ?? ''); ?>">

                                    <td><span class="inv-num"><?php echo htmlspecialchars($inv['invoice_number']); ?></span></td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle" style="background:<?php echo $av['color']; ?>">
                                                <?php echo htmlspecialchars($av['initials']); ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold" style="font-size:0.875rem;"><?php echo htmlspecialchars($inv['student_name'] ?? $student_name); ?></div>
                                                <div class="text-muted" style="font-size:0.75rem;"><?php echo htmlspecialchars($inv['email_address'] ?? $student_email); ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td style="max-width:160px;font-size:0.82rem;color:#6c757d;">
                                        <?php echo htmlspecialchars($inv['course_enrolled'] ?? '—'); ?>
                                    </td>

                                    <td style="white-space:nowrap;font-size:0.875rem;">
                                        <?php echo $inv['issue_date'] ? date('d M Y', strtotime($inv['issue_date'])) : '—'; ?>
                                    </td>

                                    <td style="white-space:nowrap;font-size:0.875rem;" class="<?php echo $due_class; ?>">
                                        <?php echo $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '—'; ?>
                                    </td>

                                    <td class="fw-semibold" style="white-space:nowrap;">
                                        $<?php echo number_format($total, 2); ?>
                                    </td>

                                    <td>
                                        <div class="paid-progress">
                                            <div class="progress">
                                                <div class="progress-bar <?php echo $is_paid ? 'bg-success' : 'bg-primary'; ?>"
                                                     style="width:<?php echo $pct; ?>%"></div>
                                            </div>
                                            <span class="paid-pct"><?php echo $pct; ?>%</span>
                                        </div>
                                    </td>

                                    <td class="<?php echo $balance > 0 ? 'amount-due' : 'amount-clear'; ?>" style="white-space:nowrap;">
                                        <?php echo $balance > 0 ? '$' . number_format($balance, 2) : '—'; ?>
                                    </td>

                                    <td><?php echo statusBadge($inv['status'], $inv['due_date']); ?></td>

                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewInvoice(<?php echo $inv['id']; ?>)" title="View">
                                            <i class="ti ti-eye"></i>
                                        </button>
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
// ── Search & filter ──────────────────────────────────────────────
function filterTable() {
    var search  = document.getElementById('searchInput').value.toLowerCase();
    var status  = document.getElementById('filterStatus').value.toLowerCase();
    var branch  = document.getElementById('filterBranch').value;
    var type    = document.getElementById('filterType').value;

    document.querySelectorAll('#invoiceBody .inv-row').forEach(function(row) {
        var inv     = row.dataset.inv     || '';
        var course  = row.dataset.course  || '';
        var rowSt   = row.dataset.status  || '';
        var rowOv   = row.dataset.overdue || '';
        var rowBr   = row.dataset.branch  || '';
        var rowTy   = row.dataset.type    || '';

        var matchSearch = !search  || inv.includes(search) || course.includes(search);
        var matchStatus = !status  || rowSt === status || (status === 'overdue' && rowOv === 'overdue');
        var matchBranch = !branch  || rowBr === branch;
        var matchType   = !type    || rowTy === type;

        row.style.display = (matchSearch && matchStatus && matchBranch && matchType) ? '' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('filterStatus').addEventListener('change', filterTable);
document.getElementById('filterBranch').addEventListener('change', filterTable);
document.getElementById('filterType').addEventListener('change', filterTable);

// ── View invoice modal ───────────────────────────────────────────
var allInvoices = <?php echo json_encode($invoices); ?>;

function viewInvoice(id) {
    currentInvoiceId = id;
    var inv = allInvoices.find(function(i) { return parseInt(i.id) === id; });
    if (!inv) return;

    var total   = parseFloat(inv.total_due   || 0);
    var paid    = parseFloat(inv.paid_amount || 0);
    var balance = Math.max(0, total - paid);
    var lineItems = [];
    try { lineItems = JSON.parse(inv.line_items || '[]'); } catch(e) {}

    var rows = lineItems.map(function(li) {
        return '<tr><td>' + esc(li.description) + '</td><td>' + esc(li.unit) + '</td>' +
               '<td>' + li.qty + '</td><td>$' + parseFloat(li.unit_price).toFixed(2) + '</td>' +
               '<td>' + esc(li.gst) + '</td><td>$' + parseFloat(li.amount).toFixed(2) + '</td></tr>';
    }).join('');

    var html = '<div class="row g-3 mb-3">' +
        '<div class="col-sm-4"><div class="text-muted small">Invoice #</div><div class="fw-bold">' + esc(inv.invoice_number) + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small">Issue Date</div><div>' + (inv.issue_date ? formatDate(inv.issue_date) : '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small">Due Date</div><div>' + (inv.due_date ? formatDate(inv.due_date) : '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small">Invoice Type</div><div>' + esc(inv.invoice_type || '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small">Payment Method</div><div>' + esc(inv.payment_method || '—') + '</div></div>' +
        '<div class="col-sm-4"><div class="text-muted small">Payment Terms</div><div>' + esc(inv.payment_terms || '—') + '</div></div>' +
        '</div>' +
        '<hr>' +
        '<table class="table table-sm table-bordered mb-3">' +
        '<thead class="table-light"><tr><th>Description</th><th>Unit</th><th>QTY</th><th>Unit Price</th><th>GST?</th><th>Amount</th></tr></thead>' +
        '<tbody>' + (rows || '<tr><td colspan="6" class="text-center text-muted">No line items</td></tr>') + '</tbody></table>' +
        '<div class="d-flex justify-content-end"><div style="min-width:220px;">' +
        '<div class="d-flex justify-content-between py-1"><span class="text-muted">Subtotal</span><span>$' + parseFloat(inv.subtotal||0).toFixed(2) + '</span></div>' +
        '<div class="d-flex justify-content-between py-1"><span class="text-muted">GST (10%)</span><span>$' + parseFloat(inv.gst_amount||0).toFixed(2) + '</span></div>' +
        '<div class="d-flex justify-content-between py-1"><span class="text-muted">Discount</span><span>$' + parseFloat(inv.discount||0).toFixed(2) + '</span></div>' +
        '<hr class="my-1">' +
        '<div class="d-flex justify-content-between py-1"><strong>Total Due</strong><strong class="text-primary">$' + total.toFixed(2) + '</strong></div>' +
        '<div class="d-flex justify-content-between py-1"><span class="text-muted">Balance</span><span class="' + (balance > 0 ? 'text-danger fw-bold' : 'text-success') + '">$' + balance.toFixed(2) + '</span></div>' +
        '</div></div>';

    document.getElementById('invoiceModalBody').innerHTML = html;
    var modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
    modal.show();
}

function downloadInvoice() {
    var inv = allInvoices.find(function(i) {
        return parseInt(i.id) === currentInvoiceId;
    });
    if (!inv) return;

    var lineItems = [];
    try { lineItems = JSON.parse(inv.line_items || '[]'); } catch(e) {}

    var { jsPDF } = window.jspdf;
    var doc = new jsPDF();

    // Header bar
    doc.setFillColor(30, 58, 95);
    doc.rect(0, 0, 210, 28, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.text('National College Australia', 14, 12);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.text('Tax Invoice', 14, 20);
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text(esc(inv.invoice_number), 196, 14, { align: 'right' });
    doc.setFontSize(8);
    doc.setFont('helvetica', 'normal');
    doc.text('Status: ' + (inv.status || 'pending').toUpperCase(), 196, 20, { align: 'right' });

    doc.setTextColor(0, 0, 0);

    // Student details
    var y = 36;
    doc.setFontSize(9);
    doc.setFont('helvetica', 'bold');
    doc.text('STUDENT DETAILS', 14, y);
    doc.setFont('helvetica', 'normal');
    y += 6;
    var details = [
        ['Student Name', esc(inv.student_name || '')],
        ['Student ID',   esc(inv.student_id   || '—')],
        ['Email',        esc(inv.email_address || '')],
        ['Course',       esc(inv.course_enrolled || '—')],
        ['Enrolment Ref',esc(inv.enrolment_ref || '—')],
        ['Branch',       esc(inv.branch || '—')],
    ];
    details.forEach(function(d) {
        doc.setFont('helvetica', 'bold');   doc.text(d[0] + ':', 14, y);
        doc.setFont('helvetica', 'normal'); doc.text(d[1],       60, y);
        y += 5.5;
    });

    // Invoice settings (right column)
    var ry = 42;
    var settings = [
        ['Issue Date',      inv.issue_date ? formatDate(inv.issue_date) : '—'],
        ['Due Date',        inv.due_date   ? formatDate(inv.due_date)   : '—'],
        ['Payment Terms',   esc(inv.payment_terms  || '—')],
        ['Invoice Type',    esc(inv.invoice_type   || '—')],
        ['Payment Method',  esc(inv.payment_method || '—')],
        ['Funding Type',    esc(inv.funding_type   || '—')],
    ];
    settings.forEach(function(s) {
        doc.setFont('helvetica', 'bold');   doc.text(s[0] + ':', 115, ry);
        doc.setFont('helvetica', 'normal'); doc.text(s[1],        162, ry);
        ry += 5.5;
    });

    // Divider
    y = Math.max(y, ry) + 4;
    doc.setDrawColor(200, 200, 200);
    doc.line(14, y, 196, y);
    y += 6;

    // Line items table
    var tableRows = lineItems.map(function(li) {
        return [
            esc(li.description),
            esc(li.unit),
            li.qty,
            '$' + parseFloat(li.unit_price).toFixed(2),
            li.gst,
            '$' + parseFloat(li.amount).toFixed(2)
        ];
    });

    doc.autoTable({
        startY: y,
        head: [['Description', 'Unit', 'QTY', 'Unit Price', 'GST?', 'Amount']],
        body: tableRows.length ? tableRows : [['No line items', '', '', '', '', '']],
        styles: { fontSize: 8, cellPadding: 3 },
        headStyles: { fillColor: [30, 58, 95], textColor: 255, fontStyle: 'bold' },
        columnStyles: { 0: { cellWidth: 70 }, 5: { halign: 'right' } },
        margin: { left: 14, right: 14 },
    });

    // Totals
    var finalY = doc.lastAutoTable.finalY + 6;
    var totals = [
        ['Subtotal',   '$' + parseFloat(inv.subtotal   || 0).toFixed(2)],
        ['GST (10%)',  '$' + parseFloat(inv.gst_amount || 0).toFixed(2)],
        ['Discount',   '$' + parseFloat(inv.discount   || 0).toFixed(2)],
        ['Total Due',  '$' + parseFloat(inv.total_due  || 0).toFixed(2)],
    ];
    totals.forEach(function(t, idx) {
        var isBold = idx === totals.length - 1;
        doc.setFont('helvetica', isBold ? 'bold' : 'normal');
        doc.setFontSize(isBold ? 10 : 8.5);
        if (isBold) doc.setTextColor(13, 110, 253);
        doc.text(t[0], 150, finalY, { align: 'right' });
        doc.text(t[1], 196, finalY, { align: 'right' });
        finalY += isBold ? 7 : 5.5;
        doc.setTextColor(0, 0, 0);
    });

    doc.save(inv.invoice_number + '.pdf');
}

var currentInvoiceId = null;

function printInvoice() {
    var content = document.getElementById('invoiceModalBody').innerHTML;
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Invoice</title>' +
        '<link rel="stylesheet" href="crm/html/template/assets/css/bootstrap.min.css">' +
        '</head><body class="p-4">' + content + '</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); win.close(); }, 500);
}

function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function formatDate(d) {
    var dt = new Date(d);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return dt.getDate() + ' ' + months[dt.getMonth()] + ' ' + dt.getFullYear();
}
</script>
</body>
</html>

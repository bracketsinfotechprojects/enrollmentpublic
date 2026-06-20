<?php
include('includes/dbconnect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' || ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    header('Location: student_login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header('Location: invoices_list.php'); exit; }

// Fetch invoice header
$res = mysqli_query($connection, "SELECT * FROM enrolment_invoices WHERE id = $id LIMIT 1");
if (!$res || mysqli_num_rows($res) === 0) { header('Location: invoices_list.php'); exit; }
$inv = mysqli_fetch_assoc($res);

// Fetch installments with course name
$inst_res = mysqli_query($connection,
    "SELECT eis.*, c.course_sname, c.course_name
     FROM enrolment_invoice_installments eis
     LEFT JOIN courses c ON c.course_id = eis.course_id
     WHERE eis.invoice_id = $id
     ORDER BY eis.id ASC"
);
$installments = [];
$total_amount = 0;
$total_gst    = 0;
while ($row = mysqli_fetch_assoc($inst_res)) {
    $installments[] = $row;
    $total_amount  += $row['amount'];
    $total_gst     += $row['gst_amount'];
}

function val($v, $fallback = '—') {
    return (isset($v) && $v !== '' && $v !== null) ? htmlspecialchars($v) : $fallback;
}

$status_map = ['pending' => 'warning', 'paid' => 'success', 'overdue' => 'danger'];
$badge = $status_map[$inv['status']] ?? 'secondary';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice <?php echo val($inv['invoice_number']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .field-label  { font-size: 0.75rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
        .field-value  { font-size: 0.92rem; color: #212529; }

        .inst-section-header  { background: #1e293b; color: #fff; border-radius: 10px 10px 0 0; padding: 14px 22px; }
        .inst-section-header h6 { margin: 0; font-size: .95rem; }
        .inst-section-header p  { margin: 2px 0 0; font-size: .78rem; color: #94a3b8; }

        .inst-card            { border: 1px solid #e5e7eb; border-top: none; background: #fff; padding: 18px 22px; }
        .inst-card:last-child { border-radius: 0 0 10px 10px; }

        .badge-overdue { background: #fee2e2; color: #dc2626; font-size: .72rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
        .badge-paid    { background: #d1fae5; color: #059669; font-size: .72rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #d97706; font-size: .72rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }

        .timeline            { margin: 14px 0 0; padding: 0; list-style: none; }
        .timeline li         { display: flex; gap: 14px; align-items: flex-start; padding: 9px 0; border-bottom: 1px solid #f3f4f6; font-size: .83rem; }
        .timeline li:last-child { border-bottom: none; }
        .tl-icon             { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .82rem; margin-top: 1px; }
        .tl-icon.done        { background: #d1fae5; color: #059669; }
        .tl-icon.warn        { background: #fee2e2; color: #dc2626; }
        .tl-icon.info        { background: #dbeafe; color: #2563eb; }
        .tl-icon.idle        { background: #f3f4f6; color: #9ca3af; border: 2px dashed #d1d5db; }
        .tl-date             { font-size: .75rem; color: #9ca3af; }
        .tl-note             { font-size: .78rem; color: #6b7280; margin-top: 2px; }

        .grand-total-bar { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 22px; margin-top: 18px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content">
            <div class="container-fluid">

                <!-- Page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Invoice Detail</h4>
                            <div class="page-title-right d-flex gap-2 align-items-center no-print">
                                <a href="invoices_list.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="mdi mdi-arrow-left me-1"></i>Back to List
                                </a>
                                <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                                    <i class="mdi mdi-printer me-1"></i>Print
                                </button>
                                <ol class="breadcrumb m-0 ms-2">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="invoices_list.php">Invoices</a></li>
                                    <li class="breadcrumb-item active">View</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice header card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h4 class="fw-bold mb-1"><?php echo val($inv['invoice_number']); ?></h4>
                                <span class="badge bg-<?php echo $badge; ?> fs-12 mb-2">
                                    <?php echo ucfirst($inv['status']); ?>
                                </span>
                                <p class="mb-0 text-muted" style="font-size:0.82rem;">
                                    Created on <?php echo date('d M Y, h:i A', strtotime($inv['created_at'])); ?>
                                </p>
                            </div>
                            <img src="assets/images/logo-dark.webp" alt="NCA" height="50" onerror="this.style.display='none'">
                        </div>
                    </div>
                </div>

                <!-- Student info -->
                <div class="card mb-4">
                    <div class="card-header"><h6 class="mb-0 fw-semibold">Student Information</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="field-label">Student Name</div>
                                <div class="field-value"><?php echo val($inv['student_name']); ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-label">Student ID</div>
                                <div class="field-value"><?php echo val($inv['student_id']); ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-label">Email Address</div>
                                <div class="field-value"><?php echo val($inv['email_address']); ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-label">Enrolment ID</div>
                                <div class="field-value"><?php echo val($inv['enrolment_id']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Installments -->
                <?php if (!empty($installments)): ?>

                <div class="inst-section-header">
                    <h6><i class="ti ti-layout-list me-2"></i>Invoice Installments</h6>
                    <p><?php echo count($installments); ?> installment(s) · Grand Total: $<?php echo number_format($total_amount + $total_gst, 2); ?></p>
                </div>

                <?php
                $today = date('Y-m-d');
                foreach ($installments as $i => $inst):
                    $course_label = ($inst['course_sname'] && $inst['course_name'])
                        ? $inst['course_sname'] . ' – ' . $inst['course_name'] : '—';
                    $inst_total   = floatval($inst['amount']) + floatval($inst['gst_amount']);
                    $is_paid      = $inst['status'] === 'paid';
                    $is_overdue   = !$is_paid && !empty($inst['due_date']) && $inst['due_date'] < $today;
                    $overdue_days = $is_overdue ? max(0, (int)((strtotime($today) - strtotime($inst['due_date'])) / 86400)) : 0;
                    $is_last      = $i === count($installments) - 1;
                ?>
                <div class="inst-card <?php echo $is_last ? 'mb-0' : ''; ?>">

                    <!-- Installment header row -->
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold" style="font-size:.95rem;">Installment #<?php echo $i + 1; ?></span>
                                <?php if ($is_paid): ?>
                                <span class="badge-paid"><i class="ti ti-circle-check me-1"></i>Paid</span>
                                <?php elseif ($is_overdue): ?>
                                <span class="badge-overdue"><i class="ti ti-clock me-1"></i>Overdue <?php echo $overdue_days; ?> days</span>
                                <?php else: ?>
                                <span class="badge-pending"><i class="ti ti-clock me-1"></i>Pending</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.82rem;">
                                <?php echo htmlspecialchars($course_label); ?>
                                <?php if (!empty($inst['invoice_type'])): ?> &middot; <?php echo htmlspecialchars($inst['invoice_type']); ?><?php endif; ?>
                            </div>
                            <div class="mt-1">
                                <span class="<?php echo $is_overdue ? 'text-danger' : 'text-dark'; ?> fw-semibold" style="font-size:.83rem;">
                                    Balance: $<?php echo number_format($inst_total, 2); ?>
                                </span>
                                <span class="text-muted ms-2" style="font-size:.78rem;">
                                    (Amount: $<?php echo number_format($inst['amount'], 2); ?> + Tax: $<?php echo number_format($inst['gst_amount'], 2); ?>)
                                </span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap no-print">
                            <?php if (!$is_paid): ?>
                            <button class="btn btn-sm btn-success">
                                <i class="ti ti-check me-1"></i>Mark Paid
                            </button>
                            <?php else: ?>
                            <button class="btn btn-sm btn-success" disabled>
                                <i class="ti ti-circle-check me-1"></i>Paid
                            </button>
                            <?php
                            $pdf_href = (!empty($inst['pdf_path']) && file_exists(__DIR__ . '/' . $inst['pdf_path']))
                                ? htmlspecialchars($inst['pdf_path'])
                                : 'installment_pdf_serve.php?id=' . intval($inst['id']) . '&action=view';
                            ?>
                            <a href="<?php echo $pdf_href; ?>"
                               target="_blank"
                               class="btn btn-sm btn-outline-danger"
                               title="View stored invoice PDF">
                                <i class="ti ti-file-type-pdf me-1"></i>View PDF
                            </a>
                            <?php endif; ?>
                            <a href="installment_view.php?id=<?php echo intval($inst['id']); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye me-1"></i>View
                            </a>
                            <?php if (!$is_paid): ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="payOffline(<?php echo intval($inst['id']); ?>)">
                                <i class="ti ti-cash me-1"></i>Pay Offline
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="sendInstallmentReminder(<?php echo intval($inst['id']); ?>, this)">
                                <i class="ti ti-bell me-1"></i>Send Reminder
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <ul class="timeline">
                        <li>
                            <div class="tl-icon done"><i class="ti ti-file-check"></i></div>
                            <div>
                                <div class="fw-semibold">Installment Created</div>
                                <?php if ($inst['created_at']): ?>
                                <div class="tl-date"><?php echo date('d M Y', strtotime($inst['created_at'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php if (!empty($inst['issue_date'])): ?>
                        <li>
                            <div class="tl-icon done"><i class="ti ti-calendar"></i></div>
                            <div>
                                <div class="fw-semibold">Issue Date</div>
                                <div class="tl-date"><?php echo date('d M Y', strtotime($inst['issue_date'])); ?></div>
                            </div>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($inst['due_date'])): ?>
                        <li>
                            <div class="tl-icon <?php echo $is_overdue ? 'warn' : ($is_paid ? 'done' : 'info'); ?>">
                                <i class="ti <?php echo $is_overdue ? 'ti-calendar-off' : ($is_paid ? 'ti-circle-check' : 'ti-calendar-due'); ?>"></i>
                            </div>
                            <div>
                                <div class="fw-semibold <?php echo $is_overdue ? 'text-danger' : ''; ?>">
                                    Due Date<?php echo $is_overdue ? ' — Passed' : ''; ?>
                                </div>
                                <div class="tl-date"><?php echo date('d M Y', strtotime($inst['due_date'])); ?></div>
                                <?php if ($is_overdue): ?>
                                <div class="tl-note">No payment received by due date.</div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endif; ?>
                        <?php if ($is_paid): ?>
                        <li>
                            <div class="tl-icon done"><i class="ti ti-currency-dollar"></i></div>
                            <div>
                                <div class="fw-semibold text-success">Payment Received</div>
                                <div class="tl-note">$<?php echo number_format($inst_total, 2); ?> paid in full.</div>
                            </div>
                        </li>
                        <?php elseif ($is_overdue): ?>
                        <li>
                            <div class="tl-icon idle"><i class="ti ti-bell"></i></div>
                            <div>
                                <div class="fw-semibold text-muted">Reminder — Pending</div>
                                <div class="tl-note">Use "Mark Paid" once payment is received.</div>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>

                </div>
                <?php endforeach; ?>

                <!-- Grand total bar -->
                <div class="grand-total-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted" style="font-size:.85rem;">Grand Total</span>
                    <div class="d-flex gap-4">
                        <div class="text-center">
                            <div style="font-size:.72rem;color:#6b7280;text-transform:uppercase;">Amount</div>
                            <div class="fw-semibold">$<?php echo number_format($total_amount, 2); ?></div>
                        </div>
                        <div class="text-center">
                            <div style="font-size:.72rem;color:#6b7280;text-transform:uppercase;">Tax</div>
                            <div class="fw-semibold">$<?php echo number_format($total_gst, 2); ?></div>
                        </div>
                        <div class="text-center">
                            <div style="font-size:.72rem;color:#6b7280;text-transform:uppercase;">Total (inc. Tax)</div>
                            <div class="fw-bold text-primary fs-12">$<?php echo number_format($total_amount + $total_gst, 2); ?></div>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5 text-muted">No installments found for this invoice.</div>
                </div>
                <?php endif; ?>

            </div> <!-- container-fluid -->
        </div> <!-- content -->
    </div> <!-- page-wrapper -->
</div> <!-- main-wrapper -->

<!-- Pay Offline Modal -->
<div class="modal fade" id="payOfflineModal" tabindex="-1" aria-labelledby="payOfflineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payOfflineModalLabel">
                    <i class="ti ti-cash me-2 text-secondary"></i>Record Offline Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="offlineInstallmentId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                    <select class="form-select" id="offlinePaymentMethod">
                        <option value="">— Select —</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="eftpos">EFTPOS</option>
                        <option value="money_order">Money Order</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="offlinePaymentDate" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Receiver's Name <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" class="form-control" id="offlineReceiverName" placeholder="Name of person who received payment">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Proof <span class="text-muted fw-normal">(optional — screenshot, receipt, or bank slip)</span></label>
                    <input type="file" class="form-control" id="offlineProofImage" accept="image/jpeg,image/png,image/gif,image/webp,.pdf" onchange="previewProof(this)">
                    <small class="text-muted">Accepted: JPG, PNG, GIF, PDF &nbsp;|&nbsp; Max 2 MB</small>
                    <div id="offlineProofPreview" class="mt-2 d-none">
                        <img id="offlineProofImg" src="" alt="Proof preview"
                             class="img-thumbnail"
                             style="max-height:120px;max-width:100%;cursor:zoom-in;"
                             onclick="document.getElementById('offlineProofLightbox').classList.remove('d-none')">
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearProof()">
                            <i class="ti ti-x me-1"></i>Remove
                        </button>
                    </div>
                    <!-- inline lightbox -->
                    <div id="offlineProofLightbox" class="d-none text-center mt-2">
                        <img id="offlineProofLightboxImg" src="" alt="Proof full"
                             style="max-width:100%;border-radius:6px;box-shadow:0 4px 16px rgba(0,0,0,.2);cursor:zoom-out;"
                             onclick="document.getElementById('offlineProofLightbox').classList.add('d-none')">
                        <div><small class="text-muted">Click image to close</small></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Description <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea class="form-control" id="offlineNotes" rows="3" placeholder="Describe the payment — transaction ID, bank name, branch, amount paid, or any other relevant details…"></textarea>
                </div>
                <div id="offlineModalError" class="alert alert-danger mt-3 d-none py-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="offlineConfirmBtn" onclick="submitOfflinePayment()">
                    <i class="ti ti-check me-1"></i>Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
<script>
function markInstallmentPaid(installmentId, btn) {
    if (!confirm('Mark this installment as Paid?')) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    fetch('invoice_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=mark_installment_paid&installment_id=' + installmentId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { location.reload(); }
        else {
            alert(data.message || 'Failed to update.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check me-1"></i>Mark Paid';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i>Mark Paid';
    });
}

function previewProof(input) {
    const file    = input.files[0];
    const preview = document.getElementById('offlineProofPreview');
    const img     = document.getElementById('offlineProofImg');
    const lbImg   = document.getElementById('offlineProofLightboxImg');
    const lb      = document.getElementById('offlineProofLightbox');
    if (!file) { preview.classList.add('d-none'); lb.classList.add('d-none'); return; }
    if (file.size > 2 * 1024 * 1024) {
        document.getElementById('offlineModalError').textContent = 'Proof image must be 2 MB or smaller.';
        document.getElementById('offlineModalError').classList.remove('d-none');
        input.value = '';
        preview.classList.add('d-none');
        return;
    }
    if (file.type === 'application/pdf') {
        img.src = ''; img.alt = file.name;
        img.style.display = 'none';
        preview.querySelector('button').insertAdjacentHTML('beforebegin',
            `<span class="badge bg-secondary me-2"><i class="ti ti-file-type-pdf me-1"></i>${file.name}</span>`);
    } else {
        const url = URL.createObjectURL(file);
        img.src = url; img.style.display = '';
        lbImg.src = url;
    }
    preview.classList.remove('d-none');
    lb.classList.add('d-none');
}

function clearProof() {
    const input = document.getElementById('offlineProofImage');
    input.value = '';
    document.getElementById('offlineProofPreview').classList.add('d-none');
    document.getElementById('offlineProofLightbox').classList.add('d-none');
    document.getElementById('offlineProofImg').src = '';
}

function sendInstallmentReminder(installmentId, btn) {
    if (!confirm('Send a payment reminder email for this installment?')) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending…';
    const body = new URLSearchParams({ action: 'send_installment_reminder', installment_id: installmentId });
    fetch('invoice_action.php', { method: 'POST', body })
    .then(r => r.text())
    .then(text => {
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error(text.replace(/<[^>]+>/g,'').trim().substring(0,200)); }
        if (data.success) {
            btn.innerHTML = '<i class="ti ti-check me-1"></i>Reminder Sent';
            btn.classList.replace('btn-outline-warning', 'btn-outline-success');
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-bell me-1"></i>Send Reminder';
                btn.classList.replace('btn-outline-success', 'btn-outline-warning');
            }, 3000);
        } else {
            alert(data.message || 'Failed to send reminder.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-bell me-1"></i>Send Reminder';
        }
    })
    .catch(err => {
        alert(err.message || 'Request failed.');
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-bell me-1"></i>Send Reminder';
    });
}

function payOffline(installmentId) {
    document.getElementById('offlineInstallmentId').value  = installmentId;
    document.getElementById('offlinePaymentMethod').value  = '';
    document.getElementById('offlinePaymentDate').value    = new Date().toISOString().split('T')[0];
    document.getElementById('offlineReceiverName').value   = '';
    document.getElementById('offlineNotes').value          = '';
    document.getElementById('offlineModalError').classList.add('d-none');
    document.getElementById('offlineConfirmBtn').disabled  = false;
    document.getElementById('offlineConfirmBtn').innerHTML = '<i class="ti ti-check me-1"></i>Confirm Payment';
    clearProof();
    new bootstrap.Modal(document.getElementById('payOfflineModal')).show();
}

// Show post-reload feedback toast
(function(){
    const msg = sessionStorage.getItem('_inv_toast');
    if (!msg) return;
    sessionStorage.removeItem('_inv_toast');
    const isOk = msg.startsWith('Payment recorded and invoice');
    const div = document.createElement('div');
    div.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;padding:12px 20px;border-radius:8px;font-size:13.5px;font-weight:500;box-shadow:0 4px 18px rgba(0,0,0,.15);max-width:360px;display:flex;align-items:center;gap:10px;color:#fff;background:' + (isOk ? '#059669' : '#d97706');
    div.innerHTML = '<i class="ti ' + (isOk ? 'ti-circle-check' : 'ti-alert-triangle') + '" style="font-size:18px;flex-shrink:0"></i><span>' + msg + '</span>';
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 5000);
})();

function submitOfflinePayment() {
    const method       = document.getElementById('offlinePaymentMethod').value.trim();
    const date         = document.getElementById('offlinePaymentDate').value.trim();
    const receiverName = document.getElementById('offlineReceiverName').value.trim();
    const notes        = document.getElementById('offlineNotes').value.trim();
    const proofFile    = document.getElementById('offlineProofImage').files[0];
    const instId       = document.getElementById('offlineInstallmentId').value;
    const errEl        = document.getElementById('offlineModalError');
    const btn          = document.getElementById('offlineConfirmBtn');

    errEl.classList.add('d-none');

    if (!method) { errEl.textContent = 'Please select a payment method.'; errEl.classList.remove('d-none'); return; }
    if (!date)   { errEl.textContent = 'Please enter the payment date.';   errEl.classList.remove('d-none'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

    const body = new FormData();
    body.append('action',           'pay_offline');
    body.append('installment_id',   instId);
    body.append('payment_method',   method);
    body.append('payment_date',     date);
    body.append('receiver_name',    receiverName);
    body.append('notes',            notes);
    if (proofFile) body.append('proof_image', proofFile);

    fetch('invoice_action.php', { method: 'POST', body })
    .then(r => r.text())
    .then(text => {
        let data;
        try { data = JSON.parse(text); } catch(e) {
            throw new Error('Server error: ' + text.replace(/<[^>]+>/g, '').trim().substring(0, 300));
        }
        if (data.success) {
            const mi = bootstrap.Modal.getInstance(document.getElementById('payOfflineModal'));
            if (mi) mi.hide();
            if (data.email_sent) {
                sessionStorage.setItem('_inv_toast', 'Payment recorded and invoice PDF emailed to student.');
            } else if (data.pdf_generated) {
                sessionStorage.setItem('_inv_toast', 'Payment recorded. PDF saved but email could not be sent.');
            } else {
                sessionStorage.setItem('_inv_toast', 'Payment recorded.' + (data.pdf_error ? ' PDF: ' + data.pdf_error : ''));
            }
            location.reload();
        } else {
            errEl.textContent = data.message || 'Failed to record offline payment.';
            errEl.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check me-1"></i>Confirm Payment';
        }
    })
    .catch(err => {
        errEl.textContent = err.message || 'Request failed. Please try again.';
        errEl.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i>Confirm Payment';
    });
}
</script>
</body>
</html>

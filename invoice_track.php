<?php
include('includes/dbconnect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' || ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    header('Location: student_login.php');
    exit;
}

$today = date('Y-m-d');

// Summary counts
$pending_res = mysqli_query($connection,
    "SELECT ei.id, ei.invoice_number, ei.student_name, ei.email_address,
            COALESCE(SUM(eis.amount + eis.gst_amount), 0) AS total
     FROM enrolment_invoices ei
     LEFT JOIN enrolment_invoice_installments eis ON eis.invoice_id = ei.id
     WHERE ei.status = 'pending'
     GROUP BY ei.id"
);
$pending_invoices = [];
$pending_total = 0;
while ($r = mysqli_fetch_assoc($pending_res)) {
    $pending_invoices[] = $r;
    $pending_total += $r['total'];
}

$overdue_res = mysqli_query($connection,
    "SELECT ei.id, ei.invoice_number, ei.student_name, ei.email_address,
            COALESCE(SUM(eis.amount + eis.gst_amount), 0) AS total,
            MIN(eis.due_date) AS earliest_due
     FROM enrolment_invoices ei
     LEFT JOIN enrolment_invoice_installments eis ON eis.invoice_id = ei.id
     WHERE ei.status = 'overdue'
     GROUP BY ei.id"
);
$overdue_invoices = [];
$overdue_total = 0;
while ($r = mysqli_fetch_assoc($overdue_res)) {
    $overdue_invoices[] = $r;
    $overdue_total += $r['total'];
}

// Partial paid: invoices where some installments are paid but not all
$partial_res = mysqli_query($connection,
    "SELECT ei.id, ei.invoice_number, ei.student_name, ei.email_address,
            COALESCE(SUM(CASE WHEN eis.status='paid' THEN eis.amount+eis.gst_amount ELSE 0 END), 0) AS paid_total,
            COALESCE(SUM(eis.amount + eis.gst_amount), 0) AS grand_total
     FROM enrolment_invoices ei
     LEFT JOIN enrolment_invoice_installments eis ON eis.invoice_id = ei.id
     WHERE ei.status != 'paid'
     GROUP BY ei.id
     HAVING paid_total > 0 AND paid_total < grand_total"
);
$partial_invoices = [];
$partial_remaining = 0;
while ($r = mysqli_fetch_assoc($partial_res)) {
    $partial_invoices[] = $r;
    $partial_remaining += ($r['grand_total'] - $r['paid_total']);
}

// Detailed overdue invoices with installments
$overdue_detail = [];
$overdue_ids_res = mysqli_query($connection,
    "SELECT DISTINCT ei.id, ei.invoice_number, ei.student_name, ei.email_address, ei.status, ei.created_at,
            efn.mobile_num, efn.given_name, efn.surname,
            COALESCE(SUM(eis.amount + eis.gst_amount), 0) AS balance_due,
            MIN(eis.due_date) AS earliest_due
     FROM enrolment_invoices ei
     LEFT JOIN enrolment_invoice_installments eis ON eis.invoice_id = ei.id AND eis.status = 'pending'
     LEFT JOIN enrolment_form_new efn ON efn.id = ei.enrolment_id
     WHERE ei.status = 'overdue'
     GROUP BY ei.id
     ORDER BY earliest_due ASC"
);
while ($inv = mysqli_fetch_assoc($overdue_ids_res)) {
    // Fetch installments for this invoice
    $inst_res = mysqli_query($connection,
        "SELECT eis.*, c.course_sname, c.course_name
         FROM enrolment_invoice_installments eis
         LEFT JOIN courses c ON c.course_id = eis.course_id
         WHERE eis.invoice_id = " . intval($inv['id']) . "
         ORDER BY eis.id ASC"
    );
    $insts = [];
    while ($inst = mysqli_fetch_assoc($inst_res)) $insts[] = $inst;
    $inv['installments'] = $insts;

    // Calculate overdue days
    $due = $inv['earliest_due'] ?? $today;
    $inv['overdue_days'] = max(0, (int)((strtotime($today) - strtotime($due)) / 86400));

    // Build course label from first installment with a course
    $course_label = '—';
    foreach ($insts as $inst) {
        if (!empty($inst['course_name'])) {
            $course_label = $inst['course_sname'] . ' ' . $inst['course_name'];
            break;
        }
    }
    $inv['course_label'] = $course_label;
    $overdue_detail[] = $inv;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Track & Follow-up – Invoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .summary-card        { border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; padding: 20px 22px; height: 100%; }
        .summary-card .count { font-size: 2rem; font-weight: 700; line-height: 1; }
        .summary-card .label { font-size: .8rem; color: #6b7280; margin-top: 2px; }
        .summary-card .total { font-size: .85rem; font-weight: 600; margin-top: 6px; }
        .inv-item            { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f3f4f6; font-size: .83rem; }
        .inv-item:last-child { border-bottom: none; }
        .more-link           { font-size: .78rem; color: #6b7280; text-align: center; padding-top: 6px; cursor: pointer; }

        .overdue-section     { background: #1e293b; color: #fff; border-radius: 10px 10px 0 0; padding: 14px 22px; }
        .overdue-section h5  { margin: 0; font-size: 1rem; }
        .overdue-section p   { margin: 2px 0 0; font-size: .8rem; color: #94a3b8; }

        .overdue-card        { border: 1px solid #e5e7eb; border-top: none; background: #fff; padding: 18px 22px; margin-bottom: 2px; }
        .overdue-card:last-child { border-radius: 0 0 10px 10px; margin-bottom: 20px; }

        .timeline            { margin: 14px 0 0; padding: 0; list-style: none; }
        .timeline li         { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 0; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: .83rem; }
        .timeline li:last-child { border-bottom: none; }
        .tl-icon             { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .85rem; margin-top: 1px; }
        .tl-icon.done        { background: #d1fae5; color: #059669; }
        .tl-icon.warn        { background: #fee2e2; color: #dc2626; }
        .tl-icon.info        { background: #dbeafe; color: #2563eb; }
        .tl-icon.idle        { background: #f3f4f6; color: #9ca3af; border: 2px dashed #d1d5db; }
        .tl-date             { font-size: .75rem; color: #9ca3af; }
        .tl-note             { font-size: .78rem; color: #6b7280; margin-top: 2px; }

        .badge-overdue       { background: #fee2e2; color: #dc2626; font-size: .72rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
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
                            <div>
                                <h4 class="mb-1">Track &amp; Follow-up</h4>
                                <p class="text-muted mb-0" style="font-size:.85rem;">Monitor outstanding invoices and send automated reminders</p>
                            </div>
                            <div class="page-title-right d-flex gap-2">
                                <a href="invoices_list.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-list me-1"></i>All Invoices
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="sendAllReminders()">
                                    <i class="ti ti-send me-1"></i>Send All Reminders
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-3 mb-4">

                    <!-- Pending -->
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="count text-dark"><?php echo count($pending_invoices); ?></div>
                                <div>
                                    <div class="label">Pending Invoices</div>
                                    <div class="total text-secondary">$<?php echo number_format($pending_total, 2); ?></div>
                                </div>
                            </div>
                            <?php foreach (array_slice($pending_invoices, 0, 3) as $pi): ?>
                            <div class="inv-item">
                                <div>
                                    <a href="invoice_view.php?id=<?php echo $pi['id']; ?>" class="fw-semibold text-dark text-decoration-none" style="font-size:.83rem;"><?php echo htmlspecialchars($pi['invoice_number']); ?></a>
                                    <div class="text-muted" style="font-size:.75rem;"><?php echo htmlspecialchars($pi['student_name']); ?></div>
                                </div>
                                <span class="text-secondary fw-semibold" style="font-size:.83rem;">$<?php echo number_format($pi['total'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($pending_invoices) > 3): ?>
                            <div class="more-link">+ <?php echo count($pending_invoices) - 3; ?> more</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Overdue -->
                    <div class="col-md-4">
                        <div class="summary-card" style="border-color:#fecaca;">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="count text-danger"><?php echo count($overdue_invoices); ?></div>
                                <div>
                                    <div class="label" style="color:#dc2626;">Overdue</div>
                                    <div class="total text-danger">$<?php echo number_format($overdue_total, 2); ?></div>
                                </div>
                            </div>
                            <?php foreach (array_slice($overdue_invoices, 0, 3) as $oi): ?>
                            <div class="inv-item">
                                <div>
                                    <a href="invoice_view.php?id=<?php echo $oi['id']; ?>" class="fw-semibold text-dark text-decoration-none" style="font-size:.83rem;"><?php echo htmlspecialchars($oi['invoice_number']); ?></a>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        <?php echo htmlspecialchars($oi['student_name']); ?>
                                        <?php if ($oi['earliest_due']): ?>
                                        · <?php echo max(0, (int)((strtotime($today) - strtotime($oi['earliest_due'])) / 86400)); ?> days
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="text-danger fw-semibold" style="font-size:.83rem;">$<?php echo number_format($oi['total'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($overdue_invoices) > 3): ?>
                            <div class="more-link">+ <?php echo count($overdue_invoices) - 3; ?> more</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Partial Payment -->
                    <div class="col-md-4">
                        <div class="summary-card" style="border-color:#bfdbfe;">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="count text-primary"><?php echo count($partial_invoices); ?></div>
                                <div>
                                    <div class="label" style="color:#2563eb;">Partial Payment</div>
                                    <div class="total text-primary">$<?php echo number_format($partial_remaining, 2); ?> rem.</div>
                                </div>
                            </div>
                            <?php foreach (array_slice($partial_invoices, 0, 3) as $pp): ?>
                            <div class="inv-item">
                                <div>
                                    <a href="invoice_view.php?id=<?php echo $pp['id']; ?>" class="fw-semibold text-dark text-decoration-none" style="font-size:.83rem;"><?php echo htmlspecialchars($pp['invoice_number']); ?></a>
                                    <div class="text-muted" style="font-size:.75rem;"><?php echo htmlspecialchars($pp['student_name']); ?> · $<?php echo number_format($pp['paid_total'], 2); ?> paid</div>
                                </div>
                                <span class="text-primary fw-semibold" style="font-size:.83rem;">$<?php echo number_format($pp['grand_total'] - $pp['paid_total'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($partial_invoices) > 3): ?>
                            <div class="more-link">+ <?php echo count($partial_invoices) - 3; ?> more</div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Overdue Action Required -->
                <?php if (!empty($overdue_detail)): ?>
                <div class="overdue-section">
                    <h5><i class="ti ti-alert-triangle me-2"></i>Overdue — Action Required</h5>
                    <p>These invoices are past their due date</p>
                </div>

                <?php foreach ($overdue_detail as $idx => $ov): ?>
                <div class="overdue-card <?php echo $idx === count($overdue_detail) - 1 ? 'rounded-bottom' : ''; ?>">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="invoice_view.php?id=<?php echo $ov['id']; ?>" class="fw-bold text-dark text-decoration-none fs-6">
                                    <?php echo htmlspecialchars($ov['invoice_number']); ?>
                                </a>
                                <?php if ($ov['overdue_days'] > 0): ?>
                                <span class="badge-overdue"><i class="ti ti-clock me-1"></i>Overdue <?php echo $ov['overdue_days']; ?> days</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.82rem;">
                                <?php echo htmlspecialchars($ov['student_name']); ?>
                                <?php if ($ov['email_address']): ?> &middot; <?php echo htmlspecialchars($ov['email_address']); ?><?php endif; ?>
                                <?php if ($ov['course_label'] !== '—'): ?> &middot; <?php echo htmlspecialchars($ov['course_label']); ?><?php endif; ?>
                            </div>
                            <div class="mt-1" style="font-size:.83rem;">
                                <span class="text-danger fw-semibold">Balance Due: $<?php echo number_format($ov['balance_due'], 2); ?></span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if (!empty($ov['mobile_num'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($ov['mobile_num']); ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="ti ti-phone me-1"></i>Call
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($ov['email_address'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($ov['email_address']); ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="ti ti-message me-1"></i>SMS
                            </a>
                            <button class="btn btn-sm btn-danger" onclick="sendReminder(<?php echo $ov['id']; ?>, '<?php echo htmlspecialchars($ov['invoice_number']); ?>', this)">
                                <i class="ti ti-send me-1"></i>Send Reminder
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-success" onclick="markInvoicePaid(<?php echo $ov['id']; ?>, this)">
                                <i class="ti ti-check me-1"></i>Mark Paid
                            </button>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <ul class="timeline">
                        <?php
                        $issued_date  = $ov['created_at'] ? date('d M Y', strtotime($ov['created_at'])) : null;
                        $overdue_date = null;
                        foreach ($ov['installments'] as $inst) {
                            if (!empty($inst['due_date']) && $inst['due_date'] < $today) {
                                $overdue_date = $overdue_date
                                    ? min($overdue_date, $inst['due_date'])
                                    : $inst['due_date'];
                            }
                        }
                        ?>
                        <li>
                            <div class="tl-icon done"><i class="ti ti-file-check"></i></div>
                            <div>
                                <div class="fw-semibold">Invoice Issued</div>
                                <?php if ($issued_date): ?><div class="tl-date"><?php echo $issued_date; ?></div><?php endif; ?>
                            </div>
                        </li>
                        <li>
                            <div class="tl-icon done"><i class="ti ti-mail"></i></div>
                            <div>
                                <div class="fw-semibold">Invoice Sent via Email</div>
                                <?php if ($issued_date): ?><div class="tl-date"><?php echo $issued_date; ?> &middot; <?php echo htmlspecialchars($ov['email_address']); ?></div><?php endif; ?>
                            </div>
                        </li>
                        <li>
                            <div class="tl-icon warn"><i class="ti ti-calendar-off"></i></div>
                            <div>
                                <div class="fw-semibold text-danger">Due Date Passed — No Payment</div>
                                <?php if ($overdue_date): ?><div class="tl-date"><?php echo date('d M Y', strtotime($overdue_date)); ?></div><?php endif; ?>
                                <div class="tl-note">No payment received by due date.</div>
                            </div>
                        </li>
                        <?php
                        // Show installment payment status in timeline
                        $paid_count = count(array_filter($ov['installments'], fn($i) => $i['status'] === 'paid'));
                        $total_inst = count($ov['installments']);
                        if ($paid_count > 0): ?>
                        <li>
                            <div class="tl-icon info"><i class="ti ti-currency-dollar"></i></div>
                            <div>
                                <div class="fw-semibold"><?php echo $paid_count; ?> of <?php echo $total_inst; ?> Installment(s) Paid</div>
                                <div class="tl-note">Partial payment received.</div>
                            </div>
                        </li>
                        <?php endif; ?>
                        <li>
                            <div class="tl-icon idle"><i class="ti ti-bell"></i></div>
                            <div>
                                <div class="fw-semibold text-muted">Reminder — Pending</div>
                                <div class="tl-note">Use "Send Reminder" button to notify the student.</div>
                            </div>
                        </li>
                    </ul>
                </div>
                <?php endforeach; ?>

                <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-circle-check text-success" style="font-size:3rem;"></i>
                        <h5 class="mt-3">No Overdue Invoices</h5>
                        <p class="text-muted mb-0">All invoices are up to date. Nothing requires follow-up right now.</p>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Mark Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Mark Invoice as Paid</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Mark this entire invoice as <strong>Paid</strong>? All installments will be updated.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="confirmMarkPaid">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>

<script>
var _markPaidInvoiceId  = null;
var _markPaidBtn        = null;

function markInvoicePaid(invoiceId, btn) {
    _markPaidInvoiceId = invoiceId;
    _markPaidBtn       = btn;
    var modal = new bootstrap.Modal(document.getElementById('markPaidModal'));
    modal.show();
}

document.getElementById('confirmMarkPaid').addEventListener('click', function () {
    if (!_markPaidInvoiceId) return;
    var btn = _markPaidBtn;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch('invoice_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=mark_paid&invoice_id=' + _markPaidInvoiceId
    })
    .then(r => r.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('markPaidModal')).hide();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check me-1"></i>Mark Paid';
        }
    })
    .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="ti ti-check me-1"></i>Mark Paid'; });
});

function sendReminder(invoiceId, invoiceNumber, btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch('invoice_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=send_reminder&invoice_id=' + invoiceId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="ti ti-check me-1"></i>Reminder Sent';
            btn.classList.replace('btn-danger', 'btn-secondary');
        } else {
            alert(data.message || 'Failed to send reminder.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-send me-1"></i>Send Reminder';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-send me-1"></i>Send Reminder';
    });
}

function sendAllReminders() {
    if (!confirm('Send payment reminders to all overdue students?')) return;
    document.querySelectorAll('[onclick^="sendReminder"]').forEach(function(btn) {
        if (!btn.disabled) btn.click();
    });
}
</script>
</body>
</html>

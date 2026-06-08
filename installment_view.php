<?php
include('includes/dbconnect.php');
include('includes/stripe_config.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: student_login.php');
    exit;
}
$ut = @$_SESSION['user_type'];
$is_admin   = ($ut == 1 || $ut == 2);
$is_student = ($ut === 0 || $ut === 'student');

if (!$is_admin && !$is_student) {
    header('Location: student_login.php');
    exit;
}

$inst_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($inst_id <= 0) {
    header($is_admin ? 'Location: invoices_list.php' : 'Location: student_invoice.php');
    exit;
}

// Fetch installment with invoice + course details
$res = mysqli_query($connection,
    "SELECT eis.*,
            ei.invoice_number, ei.student_name, ei.email_address, ei.student_id,
            ei.enrolment_id,   ei.status AS invoice_status,
            c.course_sname,    c.course_name
     FROM enrolment_invoice_installments eis
     LEFT JOIN enrolment_invoices ei ON ei.id = eis.invoice_id
     LEFT JOIN courses c ON c.course_id = eis.course_id
     WHERE eis.id = $inst_id
     LIMIT 1"
);
if (!$res || mysqli_num_rows($res) === 0) {
    header($is_admin ? 'Location: invoices_list.php' : 'Location: student_invoice.php');
    exit;
}
$inst = mysqli_fetch_assoc($res);

$inst_total   = floatval($inst['amount']) + floatval($inst['gst_amount']);
$is_paid      = $inst['status'] === 'paid';
$today        = date('Y-m-d');
$is_overdue   = !$is_paid && !empty($inst['due_date']) && $inst['due_date'] < $today;
$overdue_days = $is_overdue ? max(0, (int)((strtotime($today) - strtotime($inst['due_date'])) / 86400)) : 0;

$course_label = ($inst['course_sname'] && $inst['course_name'])
    ? $inst['course_sname'] . ' – ' . $inst['course_name'] : '—';

$back_url = $is_admin
    ? 'invoice_view.php?id=' . intval($inst['invoice_id'])
    : 'student_invoice.php';

// Flash message
$flash = $_SESSION['stripe_flash'] ?? null;
unset($_SESSION['stripe_flash']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Installment – <?php echo htmlspecialchars($inst['invoice_number']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .detail-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; color: #6b7280; margin-bottom: 2px; }
        .detail-value { font-size: .93rem; color: #212529; }
        .amount-box   { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 22px 28px; }
        .stripe-btn   { background: #635bff; color: #fff; border: none; border-radius: 8px; padding: 13px 32px;
                        font-size: 1rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 10px;
                        transition: background .2s; width: 100%; justify-content: center; }
        .stripe-btn:hover { background: #4f46e5; }
        .stripe-btn:disabled { background: #9ca3af; cursor: not-allowed; }
        .stripe-logo  { height: 22px; filter: brightness(0) invert(1); }
        .paid-badge   { background: #d1fae5; color: #065f46; border-radius: 8px; padding: 14px 20px; font-weight: 600; font-size: .95rem; }
        .overdue-badge { background: #fee2e2; color: #991b1b; border-radius: 8px; padding: 10px 16px; font-size: .83rem; font-weight: 600; }
        .tl-icon       { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .85rem; }
        .tl-icon.done  { background: #d1fae5; color: #059669; }
        .tl-icon.warn  { background: #fee2e2; color: #dc2626; }
        .tl-icon.info  { background: #dbeafe; color: #2563eb; }
        .tl-icon.idle  { background: #f3f4f6; color: #9ca3af; border: 2px dashed #d1d5db; }
        .timeline li   { display: flex; gap: 14px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: .84rem; }
        .timeline li:last-child { border-bottom: none; }
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
                            <h4 class="mb-sm-0">Installment Details</h4>
                            <div class="page-title-right d-flex gap-2 align-items-center">
                                <a href="<?php echo $back_url; ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-arrow-left me-1"></i>Back
                                </a>
                                <ol class="breadcrumb m-0 ms-2">
                                    <li class="breadcrumb-item"><a href="<?php echo $is_admin ? 'dashboard.php' : 'student_docs.php'; ?>">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="<?php echo $back_url; ?>"><?php echo htmlspecialchars($inst['invoice_number']); ?></a></li>
                                    <li class="breadcrumb-item active">Installment</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($flash): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-dismissible">
                    <?php echo htmlspecialchars($flash['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row g-4">

                    <!-- Left: details -->
                    <div class="col-lg-7">

                        <!-- Invoice ref card -->
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-semibold"><i class="ti ti-file-invoice me-2"></i><?php echo htmlspecialchars($inst['invoice_number']); ?></h6>
                                <?php if ($is_paid): ?>
                                <span class="badge bg-success">Paid</span>
                                <?php elseif ($is_overdue): ?>
                                <span class="badge bg-danger">Overdue <?php echo $overdue_days; ?> days</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="detail-label">Student Name</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($inst['student_name'] ?: '—'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="detail-label">Student ID</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($inst['student_id'] ?: '—'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="detail-label">Email</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($inst['email_address'] ?: '—'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="detail-label">Course</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($course_label); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="detail-label">Issue Date</div>
                                        <div class="detail-value"><?php echo $inst['issue_date'] ? date('d M Y', strtotime($inst['issue_date'])) : '—'; ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="detail-label">Due Date</div>
                                        <div class="detail-value <?php echo $is_overdue ? 'text-danger fw-semibold' : ''; ?>">
                                            <?php echo $inst['due_date'] ? date('d M Y', strtotime($inst['due_date'])) : '—'; ?>
                                            <?php if ($is_overdue): ?><small class="ms-1">(<?php echo $overdue_days; ?> days overdue)</small><?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($inst['invoice_type'])): ?>
                                    <div class="col-sm-6">
                                        <div class="detail-label">Invoice Type</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($inst['invoice_type']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($inst['funding_type'])): ?>
                                    <div class="col-sm-6">
                                        <div class="detail-label">Funding Type</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($inst['funding_type']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="card">
                            <div class="card-header bg-light"><h6 class="mb-0 fw-semibold"><i class="ti ti-list-check me-2"></i>Payment Timeline</h6></div>
                            <div class="card-body">
                                <ul class="timeline list-unstyled mb-0">
                                    <li>
                                        <div class="tl-icon done"><i class="ti ti-file-check"></i></div>
                                        <div>
                                            <div class="fw-semibold">Installment Created</div>
                                            <div class="text-muted" style="font-size:.76rem;"><?php echo date('d M Y', strtotime($inst['created_at'])); ?></div>
                                        </div>
                                    </li>
                                    <?php if (!empty($inst['issue_date'])): ?>
                                    <li>
                                        <div class="tl-icon done"><i class="ti ti-calendar"></i></div>
                                        <div>
                                            <div class="fw-semibold">Invoice Issued</div>
                                            <div class="text-muted" style="font-size:.76rem;"><?php echo date('d M Y', strtotime($inst['issue_date'])); ?></div>
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
                                                Payment Due<?php echo $is_overdue ? ' — Overdue' : ''; ?>
                                            </div>
                                            <div class="text-muted" style="font-size:.76rem;"><?php echo date('d M Y', strtotime($inst['due_date'])); ?></div>
                                            <?php if ($is_overdue): ?>
                                            <div style="font-size:.78rem;color:#dc2626;">No payment received — <?php echo $overdue_days; ?> days past due.</div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($is_paid): ?>
                                    <li>
                                        <div class="tl-icon done"><i class="ti ti-circle-check"></i></div>
                                        <div>
                                            <div class="fw-semibold text-success">Payment Completed</div>
                                            <div style="font-size:.78rem;color:#059669;">$<?php echo number_format($inst_total, 2); ?> paid in full.</div>
                                        </div>
                                    </li>
                                    <?php else: ?>
                                    <li>
                                        <div class="tl-icon idle"><i class="ti ti-credit-card"></i></div>
                                        <div>
                                            <div class="fw-semibold text-muted">Awaiting Payment</div>
                                            <div style="font-size:.78rem;color:#9ca3af;">$<?php echo number_format($inst_total, 2); ?> outstanding.</div>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <!-- Right: amount + pay -->
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-semibold"><i class="ti ti-receipt me-2"></i>Payment Summary</h6>
                            </div>
                            <div class="card-body">

                                <div class="amount-box mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted" style="font-size:.85rem;">Amount</span>
                                        <span class="fw-semibold">$<?php echo number_format($inst['amount'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted" style="font-size:.85rem;">Tax </span>
                                        <span class="fw-semibold">$<?php echo number_format($inst['gst_amount'], 2); ?></span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total Due</span>
                                        <span class="fw-bold text-primary fs-5">$<?php echo number_format($inst_total, 2); ?></span>
                                    </div>
                                    <div class="text-muted mt-1" style="font-size:.75rem;">
                                        <?php echo htmlspecialchars($inst['currency'] ?: 'AUD'); ?> · includes Tax
                                    </div>
                                </div>

                                <?php if ($is_paid): ?>
                                <div class="paid-badge text-center">
                                    <i class="ti ti-circle-check me-2 fs-5"></i>Payment Received — Thank You!
                                </div>

                                <?php else: ?>

                                <?php if ($is_overdue): ?>
                                <div class="overdue-badge mb-3">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    This installment is <?php echo $overdue_days; ?> days overdue. Please pay immediately.
                                </div>
                                <?php endif; ?>

                                <form action="stripe_create_session.php" method="POST" id="stripe-form">
                                    <input type="hidden" name="installment_id" value="<?php echo $inst_id; ?>">

                                    <button type="submit" class="stripe-btn" id="pay-btn">
                                        
                                        Pay $<?php echo number_format($inst_total, 2); ?>
                                    </button>
                                </form>

                                <div class="text-center mt-3" style="font-size:.75rem; color:#9ca3af;">
                                    <i class="ti ti-lock me-1"></i>Secured by Stripe · SSL encrypted
                                </div>

                                <div class="d-flex justify-content-center gap-3 mt-3">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" height="20" alt="Visa">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" height="20" alt="Mastercard">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/American_Express_logo_%282018%29.svg" height="20" alt="Amex">
                                </div>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                </div><!-- /row -->

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
<script>
document.getElementById('stripe-form')?.addEventListener('submit', function() {
    var btn = document.getElementById('pay-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirecting to Stripe…';
});
</script>
</body>
</html>

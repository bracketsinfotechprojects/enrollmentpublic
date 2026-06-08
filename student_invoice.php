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

$student_user_id = (int)$_SESSION['user_id'];

$student_email   = '';

if ($ut === 0 && $student_user_id) {
    $u = @mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT user_email FROM users WHERE user_id = $student_user_id LIMIT 1"
    ));
    if ($u && !empty($u['user_email'])) {
        $student_email = mysqli_real_escape_string($connection, $u['user_email']);
    }
} elseif ($ut === 'student' && !empty($_SESSION['student_email'])) {
    $student_email = mysqli_real_escape_string($connection, $_SESSION['student_email']);
}

// Fetch complete enrolment for this student
$invoices     = [];
$enrolment_id = 0;

$q1 = "SELECT efn.id, efn.student_user_id, efn.email_address
         FROM enrolment_form_new efn
        WHERE efn.student_user_id = $student_user_id
          AND efn.status = 'complete'
          LIMIT 1";

$q2 = $q3 = '';

if ($student_user_id > 0) {
    $enr_res = mysqli_query($connection, $q1);
    $enr     = $enr_res ? mysqli_fetch_assoc($enr_res) : null;

    if ($enr) {
        $enrolment_id    = intval($enr['id']);
        $student_user_id = intval($enr['student_user_id']);

        $q2 = "SELECT ei.*,
                    COALESCE(SUM(eis.amount), 0)     AS total_amount,
                    COALESCE(SUM(eis.gst_amount), 0) AS total_gst
             FROM enrolment_invoices ei
             LEFT JOIN enrolment_invoice_installments eis ON eis.invoice_id = ei.id
             WHERE ei.enrolment_id = $enrolment_id
               AND ei.student_user_id = $student_user_id
             GROUP BY ei.id
             ORDER BY ei.created_at DESC";

        $inv_res = mysqli_query($connection, $q2);
        if ($inv_res) {
            while ($row = mysqli_fetch_assoc($inv_res)) $invoices[] = $row;
        }

        if (!empty($invoices)) {
            $inv_id = intval($invoices[0]['id']);
            $q3 = "SELECT eis.*, c.course_sname, c.course_name
                     FROM enrolment_invoice_installments eis
                     LEFT JOIN courses c ON c.course_id = eis.course_id
                     WHERE eis.invoice_id = $inv_id
                     ORDER BY eis.id ASC";
        }
    }
}


$status_badge = ['pending' => 'warning', 'paid' => 'success', 'overdue' => 'danger'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .inv-card-header { background: #f8f9fa; border-bottom: 1px solid #dee2e6; }
        .total-row td    { font-weight: 700; background: #f1f3f5; }
        .field-label     { font-size: 0.75rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
        .field-value     { font-size: 0.93rem; color: #212529; }
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
                            <h4 class="mb-sm-0">My Invoice</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="student_docs.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">My Invoice</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($invoices)): ?>
                <!-- No invoice state -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-file-invoice text-muted" style="font-size:3rem;"></i>
                        <p class="mt-3 mb-0 text-muted fs-15">No invoice has been generated for your enrolment yet.</p>
                    </div>
                </div>

                <?php else: ?>

                <?php foreach ($invoices as $inv):
                    $badge         = $status_badge[$inv['status']] ?? 'secondary';
                    $total_inc_gst = floatval($inv['total_amount']) + floatval($inv['total_gst']);

                    // Fetch installments
                    $inst_res = mysqli_query($connection,
                        "SELECT eis.*, c.course_sname, c.course_name
                         FROM enrolment_invoice_installments eis
                         LEFT JOIN courses c ON c.course_id = eis.course_id
                         WHERE eis.invoice_id = " . intval($inv['id']) . "
                         ORDER BY eis.id ASC"
                    );
                    $installments = [];
                    while ($row = mysqli_fetch_assoc($inst_res)) $installments[] = $row;
                ?>

                <div class="card mb-4">
                    <!-- Invoice header -->
                    <div class="card-body inv-card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($inv['invoice_number']); ?></h5>
                                <span class="badge bg-<?php echo $badge; ?> me-2"><?php echo ucfirst($inv['status']); ?></span>
                                <span class="text-muted" style="font-size:0.82rem;">
                                    Issued on <?php echo date('d M Y', strtotime($inv['created_at'])); ?>
                                </span>
                            </div>
                            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                <div class="field-label">Grand Total (inc. GST)</div>
                                <div class="field-value fs-4 fw-bold text-primary">
                                    $<?php echo number_format($total_inc_gst, 2); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student info row -->
                    <div class="card-body border-bottom py-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="field-label">Student Name</div>
                                <div class="field-value"><?php echo htmlspecialchars($inv['student_name']); ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-label">Student ID</div>
                                <div class="field-value"><?php echo htmlspecialchars($inv['student_id'] ?: '—'); ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-label">Email</div>
                                <div class="field-value"><?php echo htmlspecialchars($inv['email_address']); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Installments table -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Course</th>
                                        <th>Amount</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($installments)): ?>
                                    <tr><td colspan="9" class="text-center text-muted py-3">No installments found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($installments as $i => $inst):
                                        $course_label = ($inst['course_sname'] && $inst['course_name'])
                                            ? htmlspecialchars($inst['course_sname'] . ' – ' . $inst['course_name'])
                                            : '—';
                                        $inst_total = floatval($inst['amount']) + floatval($inst['gst_amount']);
                                        $ibadge     = $inst['status'] === 'paid' ? 'success' : 'warning';
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo $course_label; ?></td>
                                        <td>$<?php echo number_format($inst['amount'], 2); ?></td>
                                        <td>$<?php echo number_format($inst['gst_amount'], 2); ?></td>
                                        <td>$<?php echo number_format($inst_total, 2); ?></td>
                                        <td><?php echo $inst['issue_date'] ? date('d M Y', strtotime($inst['issue_date'])) : '—'; ?></td>
                                        <td><?php echo $inst['due_date']   ? date('d M Y', strtotime($inst['due_date']))   : '—'; ?></td>
                                        <td><span class="badge bg-<?php echo $ibadge; ?>"><?php echo ucfirst($inst['status']); ?></span></td>
                                        <td>
                                            <a href="installment_view.php?id=<?php echo intval($inst['id']); ?>" class="btn btn-sm btn-primary">
                                                <i class="ti ti-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="total-row">
                                        <td colspan="2" class="text-end">Grand Total</td>
                                        <td>$<?php echo number_format($inv['total_amount'], 2); ?></td>
                                        <td>$<?php echo number_format($inv['total_gst'], 2); ?></td>
                                        <td>$<?php echo number_format($total_inc_gst, 2); ?></td>
                                        <td colspan="4"></td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
</body>
</html>

<?php
include('includes/dbconnect.php');
session_start();

// Check authentication - Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' || ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    header('Location: student_login.php');
    exit;
}

// Fetch students with complete enrolment status
$students = [];
$query = mysqli_query($connection,
    "SELECT efn.id as enrolment_id, efn.office_student_id, efn.given_name, efn.surname,
            efn.email_address, efn.status, efn.courses, efn.created_at,
            su.id as user_id, su.full_name
     FROM enrolment_form_new efn
     LEFT JOIN student_users su ON su.email = efn.email_address
     WHERE efn.status = 'complete'
     ORDER BY efn.created_at DESC"
);
// Build a map of enrolment_id => invoice for quick lookup
$invoiced_map = [];
$inv_check = mysqli_query($connection, "SELECT id, enrolment_id, invoice_number FROM enrolment_invoices");
if ($inv_check) {
    while ($inv_row = mysqli_fetch_assoc($inv_check)) {
        $invoiced_map[intval($inv_row['enrolment_id'])] = $inv_row;
    }
}

if ($query && mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        $course_name = '';
        if (!empty($row['courses'])) {
            $course_ids = array_filter(array_map('intval', json_decode($row['courses'], true) ?: []));
            if ($course_ids) {
                $cids = implode(',', $course_ids);
                $cRes = mysqli_query($connection, "SELECT course_sname, course_name FROM courses WHERE course_id IN ($cids) LIMIT 1");
                if ($cRes && mysqli_num_rows($cRes) > 0) {
                    $cr = mysqli_fetch_assoc($cRes);
                    $course_name = $cr['course_sname'] . ' – ' . $cr['course_name'];
                }
            }
        }
        $row['course_name'] = $course_name;
        $row['invoice']     = $invoiced_map[intval($row['enrolment_id'])] ?? null;
        $students[] = $row;
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Create Invoice – Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <?php include('includes/app_includes.php'); ?>
    </head>

    <body data-topbar="colored">

        <!-- Begin page -->
        <div class="main-wrapper">
            <?php include('includes/header.php'); ?>
            <?php include('includes/sidebar.php'); ?>

            <div class="page-wrapper">
                <div class="content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Create Invoice</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Create Invoice</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Select Student</h5>
                                        <p class="text-muted mb-3">Click <strong>Select</strong> on a student to create their invoice.</p>
                                        
                                        <?php if (count($students) > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Student Name</th>
                                                            <th>Student ID</th>
                                                            <th>Email</th>
                                                            <th>Course</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $i => $student): ?>
                                                        <tr class="student-row" id="row-<?php echo $i; ?>">
                                                            <td><?php echo $i + 1; ?></td>
                                                            <td><?php echo htmlspecialchars($student['given_name'] . ' ' . $student['surname']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['office_student_id'] ?? '—'); ?></td>
                                                            <td><?php echo htmlspecialchars($student['email_address']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['course_name'] ?: 'N/A'); ?></td>
                                                            <td><span class="badge bg-success">Complete</span></td>
                                                            <td>
                                                                <?php if ($student['invoice']): ?>
                                                                    <a href="invoice_view.php?id=<?php echo $student['invoice']['id']; ?>" class="btn btn-sm btn-success">
                                                                        <i class="mdi mdi-check-circle-outline"></i>
                                                                        <?php echo htmlspecialchars($student['invoice']['invoice_number']); ?>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <a href="invoice_new.php?id=<?php echo $student['enrolment_id']; ?>" class="btn btn-sm btn-primary">
                                                                        <i class="mdi mdi-file-document-outline"></i> Select
                                                                    </a>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information"></i> No students with complete enrolment found.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div> <!-- content -->
            </div> <!-- page-wrapper -->
        </div> <!-- main-wrapper -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>

    </body>
</html>

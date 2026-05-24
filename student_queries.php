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

// Get student's email from student_users
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

// Find their enrolment record by email (most recent)
$enrolment_id  = 0;
$enrolment_row = null;
if ($student_email !== '') {
    $esc_email = mysqli_real_escape_string($connection, $student_email);
    $enrRes = mysqli_query($connection,
        "SELECT id, office_student_id, given_name, surname, status, updated_status
         FROM enrolment_form_new
         WHERE email_address = '$esc_email'
         ORDER BY id DESC LIMIT 1"
    );
    if ($enrRes && mysqli_num_rows($enrRes) > 0) {
        $enrolment_row = mysqli_fetch_assoc($enrRes);
        $enrolment_id  = intval($enrolment_row['id']);
    }
}

// Fetch all queries for this enrolment
$queries = [];
if ($enrolment_id > 0) {
    $qRes = mysqli_query($connection,
        "SELECT * FROM enrolment_queries WHERE enrolment_id = $enrolment_id ORDER BY created_at DESC"
    );
    if ($qRes) {
        while ($q = mysqli_fetch_assoc($qRes)) $queries[] = $q;
    }
}

$display_name = $enrolment_row
    ? trim(($enrolment_row['given_name'] ?? '') . ' ' . ($enrolment_row['surname'] ?? ''))
    : $student_name;
$display_name = $display_name ?: '—';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Queries – National College Australia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">My Queries</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item active">My Queries</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($enrolment_id === 0): ?>
                <!-- No enrolment found -->
                <div class="row">
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="ti ti-file-off fs-1 text-muted mb-3 d-block"></i>
                            <h5>No Enrolment Found</h5>
                            <p class="text-muted">You don't have a submitted enrolment form yet. Queries will appear here once your enrolment is on file.</p>
                            <a href="student_enrolment_form.php" class="btn btn-primary mt-2">
                                <i class="ti ti-forms me-1"></i>Go to Enrolment Form
                            </a>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- Enrolment info bar -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <div>
                                        <div class="text-muted small mb-1">Student Name</div>
                                        <strong><?php echo htmlspecialchars($display_name); ?></strong>
                                    </div>
                                    <div>
                                        <div class="text-muted small mb-1">Student ID</div>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($enrolment_row['office_student_id'] ?: '—'); ?></span>
                                    </div>
                                    <div>
                                        <div class="text-muted small mb-1">Enrolment Status</div>
                                        <?php if ($enrolment_row['status'] === 'complete'): ?>
                                        <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Complete</span>
                                        <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                                        <?php if (count($queries) > 0): ?>
                                        <span class="badge bg-danger fs-12 px-3 py-2">
                                            <i class="ti ti-message-report me-1"></i>
                                            <?php echo count($queries); ?> Open Quer<?php echo count($queries) !== 1 ? 'ies' : 'y'; ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="badge bg-success fs-12 px-3 py-2">
                                            <i class="ti ti-circle-check me-1"></i>No Queries
                                        </span>
                                        <?php endif; ?>
                                        <?php if (($enrolment_row['status'] ?? '') !== 'complete'): ?>
                                        <a href="student_enrolment_form.php?edit=1" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit me-1"></i>Edit Enrolment Form
                                        </a>
                                        <?php else: ?>
                                        <span class="badge bg-success fs-6 px-3 py-2">
                                            <i class="ti ti-circle-check me-1"></i>Enrolment Completed
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (count($queries) === 0): ?>
                <!-- No queries -->
                <div class="row">
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="ti ti-message-off fs-1 text-muted mb-3 d-block"></i>
                            <h5>No Queries Raised</h5>
                            <p class="text-muted">There are no queries on your enrolment. Everything looks good!</p>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- Queries list -->
                <div class="row">
                    <?php foreach ($queries as $idx => $q): ?>
                    <div class="col-12 mb-3">
                        <div class="card border <?php echo $q['status'] === 'resolved' ? 'border-success' : 'border-warning'; ?>">
                            <div class="card-header d-flex align-items-center justify-content-between py-2
                                <?php echo $q['status'] === 'resolved' ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10'; ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-message-report fs-5 <?php echo $q['status'] === 'resolved' ? 'text-success' : 'text-warning'; ?>"></i>
                                    <strong><?php echo htmlspecialchars($q['subject']); ?></strong>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($q['status'] === 'resolved'): ?>
                                    <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Resolved</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Open</span>
                                    <?php endif; ?>
                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($q['created_at'])); ?></small>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-3" style="white-space:pre-wrap;"><?php echo htmlspecialchars($q['message']); ?></p>
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3 text-muted small">
                                        <span><i class="ti ti-user me-1"></i>Raised by: <?php echo htmlspecialchars($q['raised_by'] ?: 'Admin'); ?></span>
                                        <span><i class="ti ti-calendar me-1"></i><?php echo date('d M Y, h:i A', strtotime($q['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
</body>
</html>

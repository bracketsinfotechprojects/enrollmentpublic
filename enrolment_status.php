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

// Resolve student email
$student_email = '';
if ($ut === 0 && $student_user_id) {
    $u = @mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT user_email FROM users WHERE user_id = $student_user_id LIMIT 1"
    ));
    if ($u && !empty($u['user_email']))
        $student_email = mysqli_real_escape_string($connection, $u['user_email']);
} elseif ($ut === 'student' && !empty($_SESSION['student_email'])) {
    $student_email = mysqli_real_escape_string($connection, $_SESSION['student_email']);
}

// Fetch enrolment record
$enrolment = null;
if ($student_email !== '') {
    $res = mysqli_query($connection,
        "SELECT efn.*
         FROM enrolment_form_new efn
         WHERE efn.email_address = '$student_email'
         ORDER BY efn.id DESC LIMIT 1"
    );
    if ($res && mysqli_num_rows($res) > 0)
        $enrolment = mysqli_fetch_assoc($res);
}

// Fetch open queries count
$query_count = 0;
if ($enrolment) {
    $qc = mysqli_query($connection,
        "SELECT COUNT(*) AS cnt FROM enrolment_queries
         WHERE enrolment_id = " . intval($enrolment['id']) . " AND status != 'resolved'"
    );
    if ($qc) $query_count = intval(mysqli_fetch_assoc($qc)['cnt']);
}

// Resolve enrolled course names
$course_names = [];
if ($enrolment && !empty($enrolment['courses'])) {
    $cids = array_filter(array_map('intval', json_decode($enrolment['courses'], true) ?: []));
    if ($cids) {
        $cids_str = implode(',', $cids);
        $cres = mysqli_query($connection,
            "SELECT course_sname, course_name FROM courses WHERE course_id IN ($cids_str)"
        );
        while ($cr = mysqli_fetch_assoc($cres))
            $course_names[] = $cr['course_sname'] . ' – ' . $cr['course_name'];
    }
}

$status        = $enrolment['status'] ?? '';
$upd_status    = $enrolment['updated_status'] ?? '';
$is_complete   = $status === 'complete';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enrolment Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .status-step { display: flex; align-items: flex-start; gap: 16px; padding: 14px 0; border-bottom: 1px solid #f0f0f0; }
        .status-step:last-child { border-bottom: none; }
        .step-icon { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem; }
        .step-icon.done  { background: #d1fae5; color: #059669; }
        .step-icon.wait  { background: #fef3c7; color: #d97706; }
        .step-icon.idle  { background: #f3f4f6; color: #9ca3af; }
        .step-label { font-weight: 600; font-size: .93rem; margin-bottom: 2px; }
        .step-sub   { font-size: .82rem; color: #6c757d; }
        .info-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; color: #6c757d; margin-bottom: 2px; }
        .info-value { font-size: .93rem; color: #212529; }
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
                            <h4 class="mb-sm-0">Enrolment Status</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="student_docs.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Enrolment Status</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!$enrolment): ?>
                <!-- No enrolment found -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-file-off text-muted" style="font-size:3rem;"></i>
                        <h5 class="mt-3">No Enrolment Found</h5>
                        <p class="text-muted mb-4">You haven't submitted an enrolment form yet.</p>
                        <a href="student_enrolment_form.php" class="btn btn-primary">
                            <i class="ti ti-forms me-1"></i>Go to Enrolment Form
                        </a>
                    </div>
                </div>

                <?php else: ?>

                <!-- Status banner -->
                <div class="row mb-4">
                    <div class="col-12">
                        <?php if ($is_complete): ?>
                        <div class="alert alert-success d-flex align-items-center gap-3 mb-0" role="alert">
                            <i class="ti ti-circle-check fs-3"></i>
                            <div>
                                <strong>Enrolment Complete</strong><br>
                                <span class="small">Your enrolment has been verified and is complete. Welcome aboard!</span>
                            </div>
                        </div>
                        <?php elseif ($query_count > 0): ?>
                        <div class="alert alert-warning d-flex align-items-center gap-3 mb-0" role="alert">
                            <i class="ti ti-message-report fs-3"></i>
                            <div>
                                <strong><?php echo $query_count; ?> Open Quer<?php echo $query_count !== 1 ? 'ies' : 'y'; ?></strong><br>
                                <span class="small">Admin has raised queries on your form. Please review and update.</span>
                            </div>
                            <a href="student_queries.php" class="btn btn-sm btn-warning ms-auto">View Queries</a>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info d-flex align-items-center gap-3 mb-0" role="alert">
                            <i class="ti ti-clock fs-3"></i>
                            <div>
                                <strong>Under Review</strong><br>
                                <span class="small">Your enrolment has been submitted and is being reviewed by our team.</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-4">

                    <!-- Student details card -->
                    <div class="col-lg-5">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-semibold"><i class="ti ti-user me-2"></i>Student Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="info-label">Given Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($enrolment['given_name'] ?: '—'); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Surname</div>
                                        <div class="info-value"><?php echo htmlspecialchars($enrolment['surname'] ?: '—'); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Student ID</div>
                                        <div class="info-value">
                                            <?php if ($enrolment['office_student_id']): ?>
                                            <span class="badge bg-primary fs-12"><?php echo htmlspecialchars($enrolment['office_student_id']); ?></span>
                                            <?php else: ?>—<?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Email</div>
                                        <div class="info-value"><?php echo htmlspecialchars($enrolment['email_address'] ?: '—'); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Mobile</div>
                                        <div class="info-value"><?php echo htmlspecialchars($enrolment['mobile_num'] ?: '—'); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Date of Birth</div>
                                        <div class="info-value"><?php echo $enrolment['dob'] ? date('d M Y', strtotime($enrolment['dob'])) : '—'; ?></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-label">Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars(trim(($enrolment['street_details'] ?? '') . ', ' . ($enrolment['sub_urb'] ?? '') . ', ' . ($enrolment['stu_state'] ?? '') . ' ' . ($enrolment['post_code'] ?? ''), ', ')); ?></div>
                                    </div>
                                    <?php if (!empty($course_names)): ?>
                                    <div class="col-12">
                                        <div class="info-label">Enrolled Course(s)</div>
                                        <?php foreach ($course_names as $cn): ?>
                                        <div class="info-value"><?php echo htmlspecialchars($cn); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-6">
                                        <div class="info-label">Submitted On</div>
                                        <div class="info-value"><?php echo $enrolment['created_at'] ? date('d M Y', strtotime($enrolment['created_at'])) : '—'; ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php if (!$is_complete): ?>
                            <div class="card-footer bg-transparent">
                                <a href="student_enrolment_form.php?edit=1" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-edit me-1"></i>Edit Enrolment Form
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Progress / Status steps -->
                    <div class="col-lg-7">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-semibold"><i class="ti ti-list-check me-2"></i>Enrolment Progress</h6>
                            </div>
                            <div class="card-body">

                                <?php
                                $steps = [
                                    [
                                        'label' => 'Form Submitted',
                                        'sub'   => 'Your enrolment form has been received.',
                                        'done'  => true,
                                        'icon'  => 'ti-file-check',
                                    ],
                                    [
                                        'label' => 'Under Review',
                                        'sub'   => 'Our team is reviewing your submitted details.',
                                        'done'  => in_array($upd_status, ['completed', 'resolve_query']) || $is_complete,
                                        'icon'  => 'ti-eye',
                                    ],
                                    [
                                        'label' => 'Queries Resolved',
                                        'sub'   => $query_count > 0
                                            ? "$query_count open quer" . ($query_count !== 1 ? 'ies' : 'y') . " pending."
                                            : 'No outstanding queries.',
                                        'done'  => $query_count === 0 && (in_array($upd_status, ['completed', 'resolve_query']) || $is_complete),
                                        'icon'  => 'ti-message-check',
                                    ],
                                    [
                                        'label' => 'LMS Access Granted',
                                        'sub'   => 'Access to the Learning Management System.',
                                        'done'  => !empty($enrolment['office_lms_access']),
                                        'icon'  => 'ti-device-laptop',
                                    ],
                                    [
                                        'label' => 'Resources Provided',
                                        'sub'   => 'Course materials and resources made available.',
                                        'done'  => !empty($enrolment['office_resources_access']),
                                        'icon'  => 'ti-books',
                                    ],
                                    [
                                        'label' => 'Welcome Pack Sent',
                                        'sub'   => 'Welcome pack dispatched to you.',
                                        'done'  => !empty($enrolment['office_welcome_pack_sent']),
                                        'icon'  => 'ti-mail',
                                    ],
                                    [
                                        'label' => 'Enrolment Complete',
                                        'sub'   => 'Your enrolment is fully verified and active.',
                                        'done'  => $is_complete,
                                        'icon'  => 'ti-circle-check',
                                    ],
                                ];
                                // Determine current active step
                                $active_idx = 0;
                                foreach ($steps as $i => $s) {
                                    if ($s['done']) $active_idx = $i;
                                }
                                ?>

                                <?php foreach ($steps as $i => $step):
                                    if ($step['done']) {
                                        $cls = 'done'; $ico = 'ti-check';
                                    } elseif ($i === $active_idx + 1) {
                                        $cls = 'wait'; $ico = 'ti-clock';
                                    } else {
                                        $cls = 'idle'; $ico = 'ti-circle';
                                    }
                                ?>
                                <div class="status-step">
                                    <div class="step-icon <?php echo $cls; ?>">
                                        <i class="ti <?php echo $ico; ?>"></i>
                                    </div>
                                    <div>
                                        <div class="step-label <?php echo $step['done'] ? 'text-success' : ($cls === 'wait' ? 'text-warning' : 'text-muted'); ?>">
                                            <?php echo htmlspecialchars($step['label']); ?>
                                        </div>
                                        <div class="step-sub"><?php echo htmlspecialchars($step['sub']); ?></div>
                                    </div>
                                    <?php if ($step['done']): ?>
                                    <span class="badge bg-success ms-auto align-self-center">Done</span>
                                    <?php elseif ($cls === 'wait'): ?>
                                    <span class="badge bg-warning text-dark ms-auto align-self-center">In Progress</span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>

                </div><!-- /row -->
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
</body>
</html>

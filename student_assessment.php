<?php include('includes/dbconnect.php'); ?>
<?php 

session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
    header('Location: student_login.php');
    exit;
}

$ut = @$_SESSION['user_type'];
if($ut !== 0 && $ut !== 'student'){
    header('Location: dashboard.php');
    exit;
}
$student_user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error_msg = isset($_GET['error']) ? $_GET['error'] : '';

$student_assessments = array();
if($student_user_id > 0){
    $query = "SELECT
    aa.id,
    aa.assessment_id,
    aa.student_enrol_id,
    aa.status AS assignment_status,
    aa.created_at AS assignment_date,
    aa.feedback,
    aa.attempt_count,
    a.assessment_name,
    a.marks,
    a.duration,
    a.status AS assessment_status,
    asub.status AS submission_status
FROM assessment_assignments aa
INNER JOIN assessment a
    ON aa.assessment_id = a.assessment_id
LEFT JOIN assessment_submissions asub
    ON aa.assessment_id = asub.assessment_id
    AND aa.student_enrol_id = asub.student_enrol_id
WHERE aa.assign_status = 'active'
  AND aa.student_enrol_id = " . $student_user_id . "
ORDER BY aa.created_at DESC";
            
    $result = mysqli_query($connection, $query);
    if($result && mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            $student_assessments[] = $row;
        }
    }
}
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>My Assessment</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
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
                                <?php if($error_msg): ?>
                                <div class="alert alert-danger">
                                    <i class="ti ti-alert-circle me-1"></i>
                                    <?php if($error_msg == 'not_assigned'): ?>
                                    You are not assigned to this assessment.
                                    <?php elseif($error_msg == 'already_submitted'): ?>
                                    You have already submitted this assessment.
                                    <?php else: ?>
                                    Error: <?php echo htmlspecialchars($error_msg); ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if(isset($_GET['success'])): ?>
                                <div class="alert alert-success">
                                    <i class="ti ti-check-circle me-1"></i>
                                    Assessment submitted successfully!
                                </div>
                                <?php endif; ?>
                                
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">My Assessment</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item active">My Assessment</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if(count($student_assessments) > 0): ?>
                        <div class="row">
                            <?php foreach($student_assessments as $assessment): ?>
                            <div class="col-lg-6 mb-3">
                                <div class="card border">
                                    <div class="card-header bg-transparent border-bottom">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($assessment['assessment_name']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <strong><i class="ti ti-star me-1"></i>Marks:</strong> 
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($assessment['marks']); ?></span>
                                        </div>
                                        <div class="mb-2">
                                            <strong><i class="ti ti-clock me-1"></i>Duration:</strong> 
                                            <span><?php echo htmlspecialchars($assessment['duration']); ?></span>
                                        </div>
                                        <div class="mb-2">
                                            <strong><i class="ti ti-info-circle me-1"></i>Status:</strong>
                                            <?php
                                            $subStatus = $assessment['submission_status'] ?? null;
                                            if($subStatus === null || $subStatus == 0): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif($subStatus == 1): ?>
                                                <span class="badge bg-info">Submitted</span>
                                            <?php elseif($subStatus == 2): ?>
                                                <span class="badge bg-primary">Graded</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo $subStatus; ?></span>
                                            <?php endif; ?>
                                            <?php if(intval($assessment['attempt_count']) > 0): ?>
                                                <span class="badge bg-secondary ms-1">Attempt <?php echo intval($assessment['attempt_count']) + 1; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if(!empty($assessment['feedback'])): ?>
                                        <div class="alert alert-warning py-2 px-3 mb-2 d-flex gap-2" style="font-size:0.875rem;">
                                            <i class="ti ti-message-report flex-shrink-0 mt-1"></i>
                                            <div>
                                                <strong>Feedback from Admin:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($assessment['feedback'])); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php 
                                            $subStatus = $assessment['submission_status'] ?? null;
                                           
                                            if($subStatus === null || $subStatus == 0):
                                        ?>

                                        <div class="mt-3">
                                            <a href="take_assessment.php?id=<?php echo $assessment['assessment_id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="ti ti-pencil me-1"></i>Take Assessment
                                            </a>
                                        </div>
                                        <?php elseif($subStatus == 1 || $subStatus == 2): ?>
                                        <div class="mt-3">
                                            <span class="btn btn-success btn-sm disabled">
                                                <i class="ti ti-check me-1"></i>Completed
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="ti ti-file-off fs-1 text-muted mb-3"></i>
                                    <h5>No Assessments Assigned</h5>
                                    <p class="text-muted">You don't have any assessments assigned yet.</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>
    </body>
</html>
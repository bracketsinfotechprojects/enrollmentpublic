<?php 
include('includes/dbconnect.php');

session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
    header('Location: student_login.php');
    exit;
}

$user_type = @$_SESSION['user_type'];
// Allow admin (1) and staff (2) to access
if($user_type !== 1 && $user_type !== 2){
    header("Location: dashboard.php");
    exit;
}

$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch assessment if ID provided
$assessment = null;
if($assessment_id > 0){
    $res = mysqli_query($connection, "SELECT * FROM assessment WHERE assessment_id = $assessment_id LIMIT 1");
    if($res && mysqli_num_rows($res) > 0){
        $assessment = mysqli_fetch_assoc($res);
    } else {
        header("Location: assessment_list.php");
        exit;
    }
}

// Build query based on filters
$where_clause = "";
$params = [];
$param_types = "";

if($assessment_id > 0){
    $where_clause = "WHERE s.assessment_id = $assessment_id";
} else {
    $where_clause = "WHERE 1=1";
}

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
if($filter_status !== ''){
    $where_clause .= " AND s.status = '$filter_status'";
}

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
if($search_query !== ''){
    $search_like = "%$search_query%";
    $where_clause .= " AND (se.st_name LIKE ? OR se.st_surname LIKE ? OR se.st_email LIKE ? OR cd.st_enquiry_id LIKE ?)";
    $params = array_fill(0, 4, $search_like);
    $param_types = str_repeat('s', 4);
}

$submissions = array();
$query = "SELECT
            s.submission_id,
            s.assessment_id,
            s.student_enrol_id,
            s.total_marks,
            s.obtained_marks,
            s.percentage,
            s.status as submission_status,
            s.started_at,
            s.submitted_at,
            a.assessment_name,
            a.marks as assessment_total_marks,
            a.passing_marks,
            a.duration,
            a.assessment_unique_id,
            COALESCE(se.st_name, '') as st_given_name,
            COALESCE(se.st_surname, '') as st_surname,
            COALESCE(cd.st_enquiry_id, '') as st_unique_id,
            COALESCE(se.st_email, '') as st_email,
            (SELECT COUNT(*) FROM assessment_assignments aa
             WHERE aa.assessment_id = s.assessment_id
               AND aa.student_enrol_id = s.student_enrol_id) as assessment_count
          FROM assessment_submissions s
          INNER JOIN assessment a ON s.assessment_id = a.assessment_id
          LEFT JOIN counseling_details cd ON cd.student_user_id = s.student_enrol_id
          LEFT JOIN student_enquiry se ON se.st_enquiry_id = cd.st_enquiry_id
          $where_clause
          ORDER BY a.assessment_name ASC, s.submitted_at DESC";

if($param_types){
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($connection, $query);
}

if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $submissions[] = $row;
    }
}

$all_assessments = [];
$assessments_query = "SELECT assessment_id, assessment_unique_id, assessment_name FROM assessment WHERE status = 0 ORDER BY assessment_name";
$assessments_result = mysqli_query($connection, $assessments_query);
if($assessments_result){
    while($row = mysqli_fetch_assoc($assessments_result)){
        $all_assessments[] = $row;
    }
}

$total_submissions = count($submissions);
$graded_count = 0;
$submitted_count = 0;
$inprogress_count = 0;
$total_marks = 0;
$total_obtained = 0;

foreach($submissions as $sub){
    switch(intval($sub['submission_status'])){
        case 0: $inprogress_count++; break;
        case 1: $submitted_count++; break;
        case 2: $graded_count++; break;
    }
    $total_marks += intval($sub['total_marks']);
    $total_obtained += intval($sub['obtained_marks']);
}
$avg_percentage = $total_submissions > 0 ? ($total_obtained/$total_marks)*100 : 0;

function getStatusBadge($status){
    switch($status){
        case 0: return ['<span class="badge bg-info">In Progress</span>', 'status-inprogress'];
        case 1: return ['<span class="badge bg-warning text-dark">Submitted</span>', 'status-submitted'];
        case 2: return ['<span class="badge bg-success">Graded</span>', 'status-graded'];
        default: return ['<span class="badge bg-secondary">Unknown</span>', ''];
    }
}

function getPercentageClass($perc){
    if($perc < 50) return 'bg-poor';
    elseif($perc < 65) return 'bg-average';
    elseif($perc < 80) return 'bg-good';
    else return 'bg-excellent';
}
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?php echo $assessment ? 'Results: ' . htmlspecialchars($assessment['assessment_name']) : 'All Assessment Results'; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <style>
            .result-card { transition: transform 0.2s, box-shadow 0.2s; border-left: 4px solid transparent; }
            .result-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
            .status-submitted { border-left-color: #ffc107; }
            .status-graded { border-left-color: #198754; }
            .status-inprogress { border-left-color: #0dcaf0; }
            .percentage-circle { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; }
            .bg-excellent { background-color: #198754; color: white; }
            .bg-good { background-color: #0dcaf0; color: #000; }
            .bg-average { background-color: #ffc107; color: #000; }
            .bg-poor { background-color: #dc3545; color: white; }
            .filter-section { background-color: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
            .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.02); }
            .mark-cell { font-weight: bold; font-size: 1.1em; }
            .mark-pass { color: #198754; }
            .mark-fail { color: #dc3545; }
        </style>
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
                                    <h4 class="mb-sm-0">
                                        <?php if($assessment): ?>
                                        Results: <?php echo htmlspecialchars($assessment['assessment_name']); ?>
                                        <?php else: ?>
                                        All Assessment Results
                                        <?php endif; ?>
                                    </h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                            <?php if($assessment): ?>
                                            <li class="breadcrumb-item"><a href="assessment_list.php">Assessments</a></li>
                                            <li class="breadcrumb-item active">Results</li>
                                            <?php else: ?>
                                            <li class="breadcrumb-item active">All Results</li>
                                            <?php endif; ?>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="filter-section">
                            <form method="GET" class="row g-3 align-items-end">
                                <?php if($assessment): ?>
                                <input type="hidden" name="id" value="<?php echo $assessment_id; ?>">
                                <?php endif; ?>
                                <div class="col-md-4">
                                    <label class="form-label">Search Student</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search by name, ID or email..." value="<?php echo htmlspecialchars($search_query); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Filter by Assessment</label>
                                    <select name="id" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Assessments</option>
                                        <?php foreach($all_assessments as $asm): ?>
                                        <option value="<?php echo $asm['assessment_id']; ?>" <?php echo $assessment_id == $asm['assessment_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($asm['assessment_unique_id'] ?: 'ID-'.$asm['assessment_id']); ?> - 
                                            <?php echo htmlspecialchars($asm['assessment_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="0" <?php echo $filter_status === '0' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="1" <?php echo $filter_status === '1' ? 'selected' : ''; ?>>Submitted</option>
                                        <option value="2" <?php echo $filter_status === '2' ? 'selected' : ''; ?>>Graded</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>Filter
                                    </button>
                                    <?php if($assessment_id || $filter_status !== '' || $search_query !== ''): ?>
                                    <a href="assessment_result.php" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>Clear
                                    </a>
                                    <?php endif; ?>
                                    <?php if($assessment): ?>
                                    <a href="assessment_result.php?id=<?php echo $assessment_id; ?>&export=1" class="btn btn-outline-success">
                                        <i class="ti ti-download me-1"></i>Export
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <?php if($total_submissions > 0): ?>
                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card stats-card border-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3 me-3">
                                                <i class="ti ti-users text-primary fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1">Students</h6>
                                                <h4 class="mb-0"><?php echo $total_submissions; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stats-card border-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3 me-3">
                                                <i class="ti ti-check text-success fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1">Graded</h6>
                                                <h4 class="mb-0"><?php echo $graded_count; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stats-card border-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded p-3 me-3">
                                                <i class="ti ti-clock text-warning fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1">Pending</h6>
                                                <h4 class="mb-0"><?php echo $inprogress_count + $submitted_count; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stats-card border-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-3 me-3">
                                                <i class="ti ti-percentage text-info fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1">Avg Score</h6>
                                                <h4 class="mb-0"><?php echo number_format($avg_percentage, 1); ?>%</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Results Table -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead>
                                                    <tr>
                                                        <?php if(!$assessment): ?>
                                                        <th>Assessment</th>
                                                        <?php endif; ?>
                                                        <th>Student</th>
                                                        <th>Student ID</th>
                                                        <?php if(!$assessment): ?>
                                                        <th>Assessment ID</th>
                                                        <?php endif; ?>
                                                        <th class="text-center">Attempt</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Total</th>
                                                        <th class="text-center">Passing</th>
                                                        <th class="text-center">Obtained</th>
                                                        <!-- <th class="text-center">Submitted</th> -->
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($submissions as $sub): 
                                                        $perc = floatval($sub['percentage']);
                                                        $percClass = getPercentageClass($perc);
                                                        list($statusBadge, $rowClass) = getStatusBadge(intval($sub['submission_status']));
                                                        
                                                         $studentName = trim(($sub['st_given_name'] ?? '') . ' ' . ($sub['st_surname'] ?? '')) ?: 'N/A';
                                                         $studentEmail = $sub['st_email'] ?: 'N/A';
                                                         $studentId = $sub['st_unique_id'] ?: 'N/A';
                                                         $submittedAt = !empty($sub['submitted_at']) ? date('d/m/Y h:i A', strtotime($sub['submitted_at'])) : 'N/A';
                                                    ?>
                                                    <tr class="<?php echo $rowClass; ?>">
                                                        <?php if(!$assessment): ?>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($sub['assessment_name']); ?></strong>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($sub['assessment_unique_id'] ?? 'N/A'); ?></small>
                                                        </td>
                                                        <?php endif; ?>
                                                        <td>
                                                            <?php echo htmlspecialchars($studentName ?: 'N/A'); ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($sub['st_email'] ?? 'N/A'); ?></small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($studentId); ?></td>
                                                        <?php if(!$assessment): ?>
                                                        <td><?php echo htmlspecialchars($sub['assessment_unique_id'] ?? 'N/A'); ?></td>
                                                        <?php endif; ?>
                                                        <td class="text-center"><?php echo intval($sub['assessment_count']); ?></td>
                                                        <td class="text-center"><?php echo $statusBadge; ?></td>
                                                        <td class="text-center mark-cell"><?php echo $sub['total_marks']; ?></td>
                                                        <td class="text-center mark-cell"><?php echo $sub['passing_marks'] ?? 'N/A'; ?></td>
                                                        <td class="text-center">
                                                            <span class="mark-cell <?php echo $perc >= 50 ? 'mark-pass' : 'mark-fail'; ?>">
                                                                <?php echo $sub['obtained_marks']; ?>
                                                            </span>
                                                        </td>
                                                        <!-- <td class="text-center"><small><?//php echo $submittedAt; ?></small></td> -->
                                                        <td class="text-center">
                                                            <a href="view_result_details.php?submission_id=<?php echo $sub['submission_id']; ?>&assessment_id=<?php echo $sub['assessment_id']; ?>"
                                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                            <?php if(intval($sub['obtained_marks']) >= intval($sub['passing_marks']) ): ?>
                                                            <button type="button"
                                                                class="btn btn-sm btn-success ms-1"
                                                                title="Pass & Proceed to Enrolment"
                                                                onclick="handleAction('pass', <?php echo $sub['submission_id']; ?>, <?php echo $sub['assessment_id']; ?>, <?php echo $sub['student_enrol_id']; ?>)">
                                                                 Pass & Proceed to Enrolment
                                                            </button>
                                                            <?php elseif(intval($sub['obtained_marks']) < intval($sub['passing_marks']) && intval($sub['assessment_count']) < 3): ?>
                                                            <button type="button"
                                                                class="btn btn-sm btn-warning ms-1"
                                                                title="Reassign"
                                                                onclick="handleAction('reassign', <?php echo $sub['submission_id']; ?>, <?php echo $sub['assessment_id']; ?>, <?php echo $sub['student_enrol_id']; ?>)">
                                                                <i class="ti ti-refresh"></i> Reassign
                                                            </button>
                                                           
                                                            <?php elseif(intval($sub['obtained_marks']) < intval($sub['passing_marks']) ): ?>
                                                            
                                                             <button type="button"
                                                                class="btn btn-sm btn-danger ms-1"
                                                                title="Manual Pass"
                                                                onclick="handleAction('manual_pass', <?php echo $sub['submission_id']; ?>, <?php echo $sub['assessment_id']; ?>, <?php echo $sub['student_enrol_id']; ?>)">
                                                                <i class="ti ti-award"></i> Manual Pass
                                                            </button>
                                                            <?php endif; ?>
                                                            


                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($total_submissions > 0 && $assessment): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <h6 class="text-muted mb-1">Total Submissions</h6>
                                                <h3 class="mb-0"><?php echo $total_submissions; ?></h3>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="text-muted mb-1">Graded</h6>
                                                <h3 class="mb-0"><?php echo $graded_count; ?></h3>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="text-muted mb-1">Avg Score</h6>
                                                <h3 class="mb-0"><?php echo number_format($avg_percentage, 2); ?>%</h3>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="text-muted mb-1">Pass Rate</h6>
                                                <h3 class="mb-0">
                                                    <?php 
                                                    $pass_count = 0;
                                                    foreach($submissions as $sub){
                                                        if(floatval($sub['percentage']) >= 50) $pass_count++;
                                                    }
                                                    echo $total_submissions > 0 ? number_format(($pass_count/$total_submissions)*100, 1) : 0; ?>%
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="ti ti-chart-bar-off fs-1 text-muted mb-3"></i>
                                    <h5>No Results Found</h5>
                                    <p class="text-muted">
                                        <?php if($assessment): ?>
                                        No students have submitted this assessment yet.
                                        <?php else: ?>
                                        No assessment submissions found in the system.
                                        <?php endif; ?>
                                    </p>
                                    <?php if($assessment): ?>
                                    <a href="assign_student.php?id=<?php echo $assessment_id; ?>" class="btn btn-primary">
                                        <i class="ti ti-user-plus me-1"></i>Assign Students
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Reassign Modal -->
        <div class="modal fade" id="reassignModal" tabindex="-1" aria-labelledby="reassignModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reassignModalLabel"><i class="ti ti-refresh me-2 text-warning"></i>Reassign Assessment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Provide feedback explaining why the assessment is being reassigned. This will be recorded against the assignment.</p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Feedback to Student <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reassign_feedback" rows="4"
                                placeholder="e.g. Your answers were incomplete. Please review sections 2 and 3 and resubmit..."></textarea>
                            <div class="invalid-feedback" id="reassign_feedback_err">Please enter feedback before reassigning.</div>
                        </div>
                        <div id="reassign_status"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" id="reassign_confirm_btn">
                            <i class="ti ti-refresh me-1"></i>Reassign
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Pass Modal -->
        <div class="modal fade" id="manualPassModal" tabindex="-1" aria-labelledby="manualPassModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="manualPassModalLabel"><i class="ti ti-award me-2 text-danger"></i>Manual Pass</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Please provide a reason for granting a manual pass. This will be recorded against the assignment record.</p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reason for Manual Pass <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="manual_pass_reason" rows="4"
                                placeholder="e.g. Student demonstrated competency through practical assessment..."></textarea>
                            <div class="invalid-feedback" id="manual_pass_reason_err">Please enter a reason before applying manual pass.</div>
                        </div>
                        <div id="manual_pass_status"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="manual_pass_confirm_btn">
                            <i class="ti ti-award me-1"></i>Apply Manual Pass
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>

        <script>
        var _reassign = {};
        var _manualPass = {};

        function handleAction(action, submissionId, assessmentId, studentEnrolId) {
            if (action === 'reassign') {
                _reassign = { submissionId: submissionId, assessmentId: assessmentId, studentEnrolId: studentEnrolId };
                $('#reassign_feedback').val('').removeClass('is-invalid');
                $('#reassign_feedback_err').hide();
                $('#reassign_status').html('');
                $('#reassign_confirm_btn').prop('disabled', false).html('<i class="ti ti-refresh me-1"></i>Reassign');
                new bootstrap.Modal(document.getElementById('reassignModal')).show();
                return;
            }

            if (action === 'manual_pass') {
                _manualPass = { submissionId: submissionId, assessmentId: assessmentId, studentEnrolId: studentEnrolId };
                $('#manual_pass_reason').val('').removeClass('is-invalid');
                $('#manual_pass_reason_err').hide();
                $('#manual_pass_status').html('');
                $('#manual_pass_confirm_btn').prop('disabled', false).html('<i class="ti ti-award me-1"></i>Apply Manual Pass');
                new bootstrap.Modal(document.getElementById('manualPassModal')).show();
                return;
            }

            if (!confirm('Are you sure you want to mark this student as Passed?')) return;

            $.ajax({
                url: 'includes/datacontrol.php',
                method: 'POST',
                data: {
                    action: 'assessment_' + action,
                    submission_id: submissionId,
                    assessment_id: assessmentId,
                    student_enrol_id: studentEnrolId
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        location.reload();
                    } else {
                        alert(res.message || 'Action failed.');
                    }
                },
                error: function() { alert('Server error. Please try again.'); }
            });
        }

        $('#manual_pass_confirm_btn').on('click', function(){
            var reason = $('#manual_pass_reason').val().trim();
            if (!reason) {
                $('#manual_pass_reason').addClass('is-invalid');
                $('#manual_pass_reason_err').show();
                return;
            }
            $('#manual_pass_reason').removeClass('is-invalid');
            var $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Applying…');
            $('#manual_pass_status').html('');

            $.ajax({
                url: 'includes/datacontrol.php',
                method: 'POST',
                data: {
                    action: 'assessment_manual_pass',
                    submission_id: _manualPass.submissionId,
                    assessment_id: _manualPass.assessmentId,
                    student_enrol_id: _manualPass.studentEnrolId,
                    manual_pass_reason: reason
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('manualPassModal')).hide();
                        setTimeout(function(){ location.reload(); }, 400);
                    } else {
                        $('#manual_pass_status').html('<div class="alert alert-danger py-2 mb-0">' + (res.message || 'Failed.') + '</div>');
                        $btn.prop('disabled', false).html('<i class="ti ti-award me-1"></i>Apply Manual Pass');
                    }
                },
                error: function() {
                    $('#manual_pass_status').html('<div class="alert alert-danger py-2 mb-0">Server error. Please try again.</div>');
                    $btn.prop('disabled', false).html('<i class="ti ti-award me-1"></i>Apply Manual Pass');
                }
            });
        });

        $('#reassign_confirm_btn').on('click', function(){
            var feedback = $('#reassign_feedback').val().trim();
            if (!feedback) {
                $('#reassign_feedback').addClass('is-invalid');
                $('#reassign_feedback_err').show();
                return;
            }
            $('#reassign_feedback').removeClass('is-invalid');
            var $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Reassigning…');
            $('#reassign_status').html('');

            $.ajax({
                url: 'includes/datacontrol.php',
                method: 'POST',
                data: {
                    action: 'assessment_reassign',
                    submission_id: _reassign.submissionId,
                    assessment_id: _reassign.assessmentId,
                    student_enrol_id: _reassign.studentEnrolId,
                    feedback: feedback
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('reassignModal')).hide();
                        setTimeout(function(){ location.reload(); }, 400);
                    } else {
                        $('#reassign_status').html('<div class="alert alert-danger py-2 mb-0">' + (res.message || 'Reassign failed.') + '</div>');
                        $btn.prop('disabled', false).html('<i class="ti ti-refresh me-1"></i>Reassign');
                    }
                },
                error: function() {
                    $('#reassign_status').html('<div class="alert alert-danger py-2 mb-0">Server error. Please try again.</div>');
                    $btn.prop('disabled', false).html('<i class="ti ti-refresh me-1"></i>Reassign');
                }
            });
        });
        </script>
    </body>
</html>
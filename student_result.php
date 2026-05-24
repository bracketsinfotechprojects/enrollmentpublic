<?php 
include('includes/dbconnect.php');

session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
    header('Location: student_login.php');
    exit;
}

$user_type = @$_SESSION['user_type'];
$is_student = ($user_type === 0 || $user_type === 'student');
$student_enrol_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Filters for admin/staff
$filter_student = isset($_GET['student']) ? intval($_GET['student']) : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query based on user role
$where_clause = "";
$join_clause = "";
$params = [];
$param_types = "";

if($is_student){
    // Students can only see their own results
    $where_clause = "WHERE s.student_enrol_id = $student_enrol_id";
} else {
    // Admin/Staff can filter
    if($filter_student > 0){
        $where_clause = "WHERE s.student_enrol_id = $filter_student";
    }
    if($filter_status !== ''){
        $where_clause .= $where_clause ? " AND s.status = '$filter_status'" : "WHERE s.status = '$filter_status'";
    }
    if($search_query !== ''){
        $search_like = "%$search_query%";
        $where_clause .= $where_clause ? " AND (se.st_given_name LIKE ? OR se.st_surname LIKE ? OR se.st_email LIKE ?)" : "WHERE (se.st_given_name LIKE ? OR se.st_surname LIKE ? OR se.st_email LIKE ?)";
        $params = array_fill(0, 3, $search_like);
        $param_types = str_repeat('s', 3);
    }
}

// Fetch submissions with student details
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
            a.duration,
            se.st_given_name,
            se.st_surname,
            se.st_unique_id
          FROM assessment_submissions s
          INNER JOIN assessment a ON s.assessment_id = a.assessment_id
          LEFT JOIN student_enrolments se ON s.student_enrol_id = se.st_enrol_id
          $where_clause
          ORDER BY s.submitted_at DESC, s.submission_id DESC";

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

// Fetch all students for filter dropdown (admin/staff only)
$all_students = [];
if(!$is_student){
    $students_query = "SELECT st_enrol_id, st_unique_id, st_given_name, st_surname FROM student_enrolments WHERE st_status != 1 ORDER BY st_surname, st_given_name";
    $students_result = mysqli_query($connection, $students_query);
    if($students_result){
        while($row = mysqli_fetch_assoc($students_result)){
            $all_students[] = $row;
        }
    }
}

// Calculate summary stats
$total_submissions = count($submissions);
$total_attempted = 0;
$total_obtained = 0;
foreach($submissions as $sub){
    $total_attempted += intval($sub['total_marks']);
    $total_obtained += intval($sub['obtained_marks']);
}
$overall_percentage = $total_attempted > 0 ? ($total_obtained/$total_attempted)*100 : 0;

function getStudentName($row){
    if(isset($row['st_given_name']) && isset($row['st_surname'])){
        return trim($row['st_given_name'] . ' ' . $row['st_surname']);
    }
    return 'N/A';
}
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?php echo $is_student ? 'My' : 'All'; ?> Results</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <style>
            .result-card {
                transition: transform 0.2s, box-shadow 0.2s;
                border-left: 4px solid transparent;
            }
            .result-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .status-submitted { border-left-color: #ffc107; }
            .status-graded { border-left-color: #198754; }
            .status-inprogress { border-left-color: #0dcaf0; }
            .percentage-circle {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 14px;
            }
            .bg-excellent { background-color: #198754; color: white; }
            .bg-good { background-color: #0dcaf0; color: #000; }
            .bg-average { background-color: #ffc107; color: #000; }
            .bg-poor { background-color: #dc3545; color: white; }
            .filter-section {
                background-color: #f8f9fa;
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
            }
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
                                    <h4 class="mb-sm-0"><?php echo $is_student ? 'My' : 'All'; ?> Results</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <?php if($is_student): ?>
                                            <li class="breadcrumb-item"><a href="student_assessment.php">Assessments</a></li>
                                            <li class="breadcrumb-item active">My Results</li>
                                            <?php else: ?>
                                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Assessment Results</li>
                                            <?php endif; ?>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if(!$is_student): ?>
                        <!-- Filter Section for Admin/Staff -->
                        <div class="filter-section">
                            <form method="GET" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Search Student</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search_query); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Filter by Student</label>
                                    <select name="student" class="form-select">
                                        <option value="">All Students</option>
                                        <?php foreach($all_students as $std): ?>
                                        <option value="<?php echo $std['st_enrol_id']; ?>" <?php echo $filter_student == $std['st_enrol_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(trim($std['st_given_name'] . ' ' . $std['st_surname'])); ?> 
                                            (<?php echo htmlspecialchars($std['st_unique_id']); ?>)
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
                                    <?php if($filter_student || $filter_status !== '' || $search_query !== ''): ?>
                                    <a href="student_result.php" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>Clear
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>

                        <?php if($total_submissions > 0): ?>
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="text-muted mb-0">
                                        Showing <?php echo $total_submissions; ?> submission<?php echo $total_submissions > 1 ? 's' : ''; ?>
                                        <?php if(!$is_student && $filter_student): ?>
                                        for selected student
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <?php 
                            foreach($submissions as $sub): 
                                $total_attempted += intval($sub['total_marks']);
                                $total_obtained += intval($sub['obtained_marks']);
                                
                                // Determine badge class based on percentage
                                $perc = floatval($sub['percentage']);
                                $percClass = 'bg-excellent';
                                $badgeText = 'Excellent';
                                if($perc < 50){
                                    $percClass = 'bg-poor';
                                    $badgeText = 'Needs Improvement';
                                } elseif($perc < 65){
                                    $percClass = 'bg-average';
                                    $badgeText = 'Average';
                                } elseif($perc < 80){
                                    $percClass = 'bg-good';
                                    $badgeText = 'Good';
                                }
                                
                                // Status badge and card class
                                $status = intval($sub['submission_status']);
                                $statusBadge = '';
                                $cardClass = 'result-card';
                                switch($status){
                                    case 0:
                                        $statusBadge = '<span class="badge bg-info">In Progress</span>';
                                        $cardClass .= ' status-inprogress';
                                        break;
                                    case 1:
                                        $statusBadge = '<span class="badge bg-warning text-dark">Submitted</span>';
                                        $cardClass .= ' status-submitted';
                                        break;
                                    case 2:
                                        $statusBadge = '<span class="badge bg-success">Graded</span>';
                                        $cardClass .= ' status-graded';
                                        break;
                                    default:
                                        $statusBadge = '<span class="badge bg-secondary">Unknown</span>';
                                }
                                
                                // Format dates
                                $submittedAt = !empty($sub['submitted_at']) ? date('d M Y, h:i A', strtotime($sub['submitted_at'])) : 'Not submitted';
                                
                                // Student name display
                                $studentName = $is_student ? 'You' : getStudentName($sub);
                            ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card <?php echo $cardClass; ?>">
                                    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($sub['assessment_name']); ?></h5>
                                        <?php echo $statusBadge; ?>
                                    </div>
                                    <div class="card-body">
                                        <?php if(!$is_student): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <span class="text-muted"><i class="ti ti-user me-1"></i>Student:</span>
                                            <strong><?php echo htmlspecialchars($studentName); ?></strong>
                                            <?php if(!empty($sub['st_unique_id'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($sub['st_unique_id']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="mb-3">
                                                    <span class="text-muted"><i class="ti ti-clock me-1"></i>Duration:</span>
                                                    <span><?php echo htmlspecialchars($sub['duration']); ?></span>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="text-muted"><i class="ti ti-calendar me-1"></i>Submitted:</span>
                                                    <span><?php echo $submittedAt; ?></span>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="mb-2">
                                                            <small class="text-muted">Total Marks</small>
                                                            <div class="fs-5 fw-bold"><?php echo $sub['total_marks']; ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="mb-2">
                                                            <small class="text-muted">Obtained</small>
                                                            <div class="fs-5 fw-bold"><?php echo $sub['obtained_marks']; ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-muted">Percentage</span>
                                                        <span class="fw-bold"><?php echo number_format($perc, 2); ?>%</span>
                                                    </div>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar <?php echo $percClass; ?>" role="progressbar" style="width: <?php echo $perc; ?>%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 d-flex flex-column align-items-center justify-content-center">
                                                <div class="percentage-circle <?php echo $percClass; ?>">
                                                    <?php echo number_format($perc, 0); ?>%
                                                </div>
                                                <?php if($status == 2): ?>
                                                <a href="view_result_details.php?submission_id=<?php echo $sub['submission_id']; ?>&assessment_id=<?php echo $sub['assessment_id']; ?>" class="btn btn-sm btn-outline-primary mt-3">
                                                    <i class="ti ti-eye me-1"></i>View Details
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if($total_attempted > 0): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-4">
                                                <h6 class="text-muted mb-1">Total Submissions</h6>
                                                <h3 class="mb-0"><?php echo $total_submissions; ?></h3>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="text-muted mb-1">Cumulative Percentage</h6>
                                                <h3 class="mb-0"><?php echo number_format($overall_percentage, 2); ?>%</h3>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="text-muted mb-1">Overall Grade</h6>
                                                <h3 class="mb-0">
                                                    <?php 
                                                    if($overall_percentage >= 80) echo '<span class="badge bg-success">A</span>';
                                                    elseif($overall_percentage >= 65) echo '<span class="badge bg-info">B</span>';
                                                    elseif($overall_percentage >= 50) echo '<span class="badge bg-warning text-dark">C</span>';
                                                    else echo '<span class="badge bg-danger">D</span>';
                                                    ?>
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
                                        <?php if($is_student): ?>
                                        You haven't completed any assessments yet.
                                        <?php else: ?>
                                        No submissions match the current filters.
                                        <?php endif; ?>
                                    </p>
                                    <?php if($is_student): ?>
                                    <a href="student_assessment.php" class="btn btn-primary">
                                        <i class="ti ti-list me-1"></i>Go to Assessments
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

        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>
    </body>
</html>

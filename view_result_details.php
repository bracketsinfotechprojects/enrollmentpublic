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

$submission_id = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : 0;
$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;

// Validate submission exists
$submission = null;
if($submission_id > 0){
    if($is_student){
        // Students can only view their own submissions
        $sub_check = mysqli_query($connection, "SELECT 
            s.*, 
            a.assessment_name,
            a.marks as assessment_total_marks,
            a.duration
            FROM assessment_submissions s
            INNER JOIN assessment a ON s.assessment_id = a.assessment_id
            WHERE s.submission_id = $submission_id 
            AND s.student_enrol_id = $student_enrol_id
            LIMIT 1");
    } else {
        // Admin/Staff can view any submission
        $sub_check = mysqli_query($connection, "SELECT 
            s.*, 
            a.assessment_name,
            a.marks as assessment_total_marks,
            a.duration,
            se.st_given_name,
            se.st_surname,
            se.st_unique_id
            FROM assessment_submissions s
            INNER JOIN assessment a ON s.assessment_id = a.assessment_id
            LEFT JOIN student_enrolments se ON s.student_enrol_id = se.st_enrol_id
            WHERE s.submission_id = $submission_id
            LIMIT 1");
    }
    
    if($sub_check && mysqli_num_rows($sub_check) > 0){
        $submission = mysqli_fetch_assoc($sub_check);
    } else {
        header('Location: student_result.php');
        exit;
    }
    
    // For admin/staff, verify assessment_id matches if provided
    if(!$is_student && $assessment_id > 0 && intval($submission['assessment_id']) !== $assessment_id){
        // Assessment ID mismatch, but we'll still show the submission (it's valid)
    }
} else {
    header('Location: student_result.php');
    exit;
}

// Fetch all answers for this assessment and student
$answers = array();
$ans_query = "SELECT 
    q.question_id,
    q.question_text,
    q.question_type,
    q.option_1,
    q.option_2,
    q.option_3,
    q.option_4,
    q.correct_option,
    q.correct_options_multi,
    q.marks as question_marks,
    a.answer_option,
    a.is_correct,
    a.marks_obtained,
    a.answered_at
    FROM assessment_answers a
    INNER JOIN assessment_questions q ON a.question_id = q.question_id
    WHERE a.assessment_id = {$submission['assessment_id']} 
    AND a.student_enrol_id = {$submission['student_enrol_id']}
    ORDER BY q.question_id";

$ans_result = mysqli_query($connection, $ans_query);
if($ans_result){
    while($row = mysqli_fetch_assoc($ans_result)){
        $answers[] = $row;
    }
}

function getQuestionTypeName($type) {
    $types = [
        1 => 'Single Choice',
        2 => 'True/False',
        3 => 'Multiple Choice',
        4 => 'Text Answer',
        5 => 'Textarea (Code/Essay)'
    ];
    return $types[$type] ?? 'Unknown';
}

function getOptionLabel($num) {
    $labels = ['A', 'B', 'C', 'D'];
    return $labels[$num-1] ?? '';
}
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>Result Details - <?php echo htmlspecialchars($submission['assessment_name']); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <style>
            .answer-card {
                border-left: 4px solid #ccc;
                margin-bottom: 1rem;
            }
            .answer-correct { border-left-color: #198754; }
            .answer-incorrect { border-left-color: #dc3545; }
            .answer-partial { border-left-color: #ffc107; }
            .marked-answer {
                background-color: #d1e7dd;
                border: 1px solid #badbcc;
                border-radius: 4px;
                padding: 2px 6px;
                font-size: 0.9em;
            }
            .correct-answer {
                background-color: #d1e7dd;
                border: 1px solid #badbcc;
                border-radius: 4px;
                padding: 2px 6px;
                font-size: 0.9em;
            }
            .user-answer {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                padding: 2px 6px;
                font-size: 0.9em;
            }
            .text-answer {
                background-color: #fff3cd;
                border: 1px solid #ffeeba;
                border-radius: 4px;
                padding: 4px 8px;
                font-size: 0.9em;
                font-family: monospace;
            }
            .question-marker {
                display: inline-block;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                text-align: center;
                line-height: 24px;
                font-weight: bold;
                font-size: 14px;
            }
            .mark-correct { background-color: #198754; color: white; }
            .mark-incorrect { background-color: #dc3545; color: white; }
            .mark-partial { background-color: #ffc107; color: #000; }
            .student-info {
                background-color: #f8f9fa;
                padding: 0.75rem 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
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
                                    <div>
                                        <h4 class="mb-sm-0"><?php echo htmlspecialchars($submission['assessment_name']); ?></h4>
                                        <small class="text-muted">
                                            <?php if($is_student): ?>
                                            Your Result Details
                                            <?php else: ?>
                                            Result Details - 
                                            <?php 
                                            $studentName = trim($submission['st_given_name'] . ' ' . $submission['st_surname']);
                                            echo htmlspecialchars($studentName ?: 'Unknown Student');
                                            if(!empty($submission['st_unique_id'])) {
                                                echo ' (' . htmlspecialchars($submission['st_unique_id']) . ')';
                                            }
                                            ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="page-title-right">
                                        <a href="student_result.php" class="btn btn-sm btn-outline-secondary">
                                            <i class="ti ti-arrow-left me-1"></i> Back to Results
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if(!$is_student): ?>
                        <!-- Student Info Card (Admin/Staff Only) -->
                        <div class="row mb-3">
                            <div class="col-lg-8 offset-lg-2">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="ti ti-user fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h5 class="mb-0"><?php echo htmlspecialchars(trim($submission['st_given_name'] . ' ' . $submission['st_surname'])); ?></h5>
                                                <p class="text-muted mb-0">
                                                    <?php if(!empty($submission['st_unique_id'])): ?>
                                                    ID: <?php echo htmlspecialchars($submission['st_unique_id']); ?> |
                                                    <?php endif; ?>
                                                    Submitted: <?php echo date('d M Y, h:i A', strtotime($submission['submitted_at'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Summary Card -->
                        <div class="row mb-4">
                            <div class="col-lg-8 offset-lg-2">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <?php 
                                            $perc = floatval($submission['percentage']);
                                            $percClass = 'bg-excellent';
                                            if($perc < 50) $percClass = 'bg-poor';
                                            elseif($perc < 65) $percClass = 'bg-average';
                                            elseif($perc < 80) $percClass = 'bg-good';
                                            ?>
                                            <div class="percentage-circle <?php echo $percClass; ?>" style="width: 100px; height: 100px; font-size: 24px; margin: 0 auto;">
                                                <?php echo number_format($perc, 1); ?>%
                                            </div>
                                        </div>
                                        <h4>
                                            Obtained: <?php echo $submission['obtained_marks']; ?> / <?php echo $submission['total_marks']; ?> marks
                                        </h4>
                                        <p class="text-muted mb-0">
                                            Status: 
                                            <?php 
                                            $status = intval($submission['submission_status']);
                                            if($status == 0) echo '<span class="badge bg-info">In Progress</span>';
                                            elseif($status == 1) echo '<span class="badge bg-warning text-dark">Submitted</span>';
                                            elseif($status == 2) echo '<span class="badge bg-success">Graded</span>';
                                            else echo '<span class="badge bg-secondary">Unknown</span>';
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Answers Detail -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3"><i class="ti ti-list-checks me-2"></i>Answer Review</h5>
                                
                                <?php 
                                $qNum = 1;
                                foreach($answers as $answer): 
                                    $qt = intval($answer['question_type']);
                                    $isCorrect = intval($answer['is_correct']) === 1;
                                    $obtMarks = intval($answer['marks_obtained']);
                                    $qMarks = intval($answer['question_marks']);
                                    
                                    $cardClass = 'answer-card ';
                                    $markClass = $isCorrect ? 'mark-correct' : 'mark-incorrect';
                                    if($obtMarks > 0 && $obtMarks < $qMarks) {
                                        $cardClass .= 'answer-partial ';
                                        $markClass = 'mark-partial';
                                    } elseif($isCorrect) {
                                        $cardClass .= 'answer-correct';
                                    } else {
                                        $cardClass .= 'answer-incorrect';
                                    }
                                    
                                    // Decode correct options for multiple choice
                                    $correctOptions = $answer['correct_options_multi'] ? json_decode($answer['correct_options_multi'], true) : null;
                                    if(!$correctOptions && $answer['correct_option']){
                                        $correctOptions = [$answer['correct_option']];
                                    }
                                ?>
                                <div class="card <?php echo $cardClass; ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="question-marker <?php echo $markClass; ?> me-3 flex-shrink-0">
                                                <?php echo $qNum++; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span class="badge bg-secondary me-2"><?php echo getQuestionTypeName($qt); ?></span>
                                                        <strong class="fs-6"><?php echo htmlspecialchars($answer['question_text']); ?></strong>
                                                    </div>
                                                    <span class="badge <?php echo $isCorrect ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $obtMarks; ?>/<?php echo $qMarks; ?> marks
                                                    </span>
                                                </div>
                                                
                                                <div class="mt-3 row">
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block mb-1">Your Answer:</small>
                                                        <?php
                                                        $userAnswer = $answer['answer_option'];
                                                        if($qt == 1 || $qt == 2){
                                                            // Single choice or True/False
                                                            $optNum = intval($userAnswer);
                                                            echo '<span class="marked-answer">' . getOptionLabel($optNum) . '. ' . htmlspecialchars($answer['option_' . $userAnswer] ?? 'N/A') . '</span>';
                                                        } elseif($qt == 3){
                                                            // Multiple choice
                                                            $selected = json_decode($userAnswer, true);
                                                            if(is_array($selected) && count($selected) > 0){
                                                                $opts = [];
                                                                foreach($selected as $sel){
                                                                    $opts[] = getOptionLabel(intval($sel)) . '. ' . htmlspecialchars($answer['option_' . $sel] ?? 'N/A');
                                                                }
                                                                echo '<span class="marked-answer">' . implode(', ', $opts) . '</span>';
                                                            } else {
                                                                echo '<span class="text-muted">No answer</span>';
                                                            }
                                                        } else {
                                                            // Text answer
                                                            echo '<div class="text-answer">' . nl2br(htmlspecialchars($userAnswer)) . '</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block mb-1">Correct Answer<?php echo $qt == 3 ? 's' : ''; ?>:</small>
                                                        <?php
                                                        if($qt == 1 || $qt == 2){
                                                            $correctOpt = intval($answer['correct_option']);
                                                            echo '<span class="correct-answer">' . getOptionLabel($correctOpt) . '. ' . htmlspecialchars($answer['option_' . $correctOpt] ?? 'N/A') . '</span>';
                                                        } elseif($qt == 3){
                                                            if(is_array($correctOptions) && count($correctOptions) > 0){
                                                                $corrOpts = [];
                                                                foreach($correctOptions as $corr){
                                                                    $corrOpts[] = getOptionLabel(intval($corr)) . '. ' . htmlspecialchars($answer['option_' . $corr] ?? 'N/A');
                                                                }
                                                                echo '<span class="correct-answer">' . implode(', ', $corrOpts) . '</span>';
                                                            }
                                                        } else {
                                                            $correctText = $answer['correct_option'] ?: 'As per evaluator';
                                                            echo '<div class="text-answer">' . nl2br(htmlspecialchars($correctText)) . '</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>
    </body>
</html>

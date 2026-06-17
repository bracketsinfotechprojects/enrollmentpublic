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
            se.st_unique_id,
            efn.given_name as efn_given_name,
            efn.surname as efn_surname
            FROM assessment_submissions s
            INNER JOIN assessment a ON s.assessment_id = a.assessment_id
            LEFT JOIN student_enrolments se ON s.student_enrol_id = se.st_enrol_id
            LEFT JOIN enrolment_form_new efn ON efn.student_user_id = s.student_enrol_id
            WHERE s.submission_id = $submission_id
            LIMIT 1");
    }
    
    if($sub_check && mysqli_num_rows($sub_check) > 0){
        $submission = mysqli_fetch_assoc($sub_check);
    } else {
        header('Location: student_result.php');
        exit;
    }

    $studentName = '';
    if(!$is_student){
        $studentName = trim(($submission['efn_given_name'] ?? '') . ' ' . ($submission['efn_surname'] ?? ''));
        if(!$studentName) $studentName = trim(($submission['st_given_name'] ?? '') . ' ' . ($submission['st_surname'] ?? ''));
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
    q.correct_option_text,
    q.`min` as q_min,
    q.`max` as q_max,
    q.marks as question_marks,
    a.answer_id,
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
        1  => 'Single Choice',
        2  => 'True/False',
        3  => 'Multiple Choice',
        4  => 'Text Answer',
        5  => 'Image Based',
        6  => 'Text',
        7  => 'Number',
        8  => 'URL',
        9  => 'Textarea',
        10 => 'Dropdown',
        11 => 'Radio',
        12 => 'Checkbox',
        13 => 'Date',
        14 => 'Time',
        15 => 'Date & Time',
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
                                                <h5 class="mb-0"><?php echo htmlspecialchars($studentName ?: 'Unknown Student'); ?></h5>
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
                                            <?php if(!empty($submission['submitted_at'])): ?>
                                            &nbsp;| Submitted: <?php echo date('d M Y, h:i A', strtotime($submission['submitted_at'])); ?>
                                            <?php endif; ?>
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
                                                        <strong class=""><?php echo htmlspecialchars($answer['question_text']); ?></strong>
                                                    </div>
                                                    <span class="badge <?php echo $isCorrect ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $obtMarks; ?>/<?php echo $qMarks; ?> marks
                                                    </span>
                                                </div>
                                                
                                                <?php if($qt == 5 && !empty($answer['correct_options_multi'])): ?>
                                                <div class="mt-2 mb-2 text-center">
                                                    <img src="uploads/question_images/<?php echo htmlspecialchars($answer['correct_options_multi']); ?>"
                                                         alt="Question Image" class="img-fluid rounded border"
                                                         style="max-height:200px;">
                                                </div>
                                                <?php endif; ?>
                                                <div class="mt-3 row">
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block mb-1">Your Answer:</small>
                                                        <?php
                                                        $userAnswer = $answer['answer_option'];
                                                        if($qt == 1 || $qt == 5){
                                                            $optNum = intval($userAnswer);
                                                            if($optNum >= 1 && $optNum <= 4 && !empty($answer['option_'.$optNum])){
                                                                echo '<span class="marked-answer">'.getOptionLabel($optNum).'. '.htmlspecialchars($answer['option_'.$optNum]).'</span>';
                                                            } elseif($userAnswer){
                                                                echo '<span class="marked-answer">'.htmlspecialchars($userAnswer).'</span>';
                                                            } else {
                                                                echo '<span class="text-muted">No answer</span>';
                                                            }
                                                        } elseif($qt == 2){
                                                            if($userAnswer == '1' || strtolower((string)$userAnswer) == 'true'){
                                                                echo '<span class="marked-answer">True</span>';
                                                            } elseif($userAnswer == '2' || strtolower((string)$userAnswer) == 'false'){
                                                                echo '<span class="marked-answer">False</span>';
                                                            } else {
                                                                echo '<span class="text-muted">No answer</span>';
                                                            }
                                                        } elseif($qt == 3){
                                                            $selected = json_decode($userAnswer, true);
                                                            if(is_array($selected) && count($selected) > 0){
                                                                $opts = [];
                                                                foreach($selected as $sel){
                                                                    $opts[] = getOptionLabel(intval($sel)).'. '.htmlspecialchars($answer['option_'.$sel] ?? 'N/A');
                                                                }
                                                                echo '<span class="marked-answer">'.implode(', ', $opts).'</span>';
                                                            } else {
                                                                echo '<span class="text-muted">No answer</span>';
                                                            }
                                                        } elseif($qt == 4 || $qt == 6){
                                                            echo $userAnswer ? '<div class="text-answer">'.nl2br(htmlspecialchars($userAnswer)).'</div>' : '<span class="text-muted">No answer</span>';
                                                        } elseif($qt == 7){
                                                            echo $userAnswer !== '' && $userAnswer !== null ? '<span class="marked-answer">'.htmlspecialchars($userAnswer).'</span>' : '<span class="text-muted">No answer</span>';
                                                        } elseif($qt == 8){
                                                            if($userAnswer){
                                                                $safeUrl = htmlspecialchars($userAnswer);
                                                                echo '<a href="'.$safeUrl.'" target="_blank" rel="noopener" class="marked-answer text-break">'.$safeUrl.'</a>';
                                                            } else {
                                                                echo '<span class="text-muted">No answer</span>';
                                                            }
                                                        } elseif($qt == 9){
                                                            echo $userAnswer ? '<div class="text-answer">'.nl2br(htmlspecialchars($userAnswer)).'</div>' : '<span class="text-muted">No answer</span>';
                                                        } elseif($qt == 10 || $qt == 11){
                                                            echo $userAnswer ? '<span class="marked-answer">'.htmlspecialchars($userAnswer).'</span>' : '<span class="text-muted">No answer</span>';
                                                        } elseif($qt == 12){
                                                            $selArr = json_decode($userAnswer, true);
                                                            if(is_array($selArr) && count($selArr) > 0){
                                                                echo '<div class="text-answer">'.implode('<br>', array_map('htmlspecialchars', $selArr)).'</div>';
                                                            } else {
                                                                echo '<span class="text-muted">No answer</span>';
                                                            }
                                                        } elseif($qt == 13){
                                                            $ts = $userAnswer ? strtotime($userAnswer) : false;
                                                            echo $ts ? '<span class="marked-answer">'.date('d M Y', $ts).'</span>' : ($userAnswer ? '<span class="marked-answer">'.htmlspecialchars($userAnswer).'</span>' : '<span class="text-muted">No answer</span>');
                                                        } elseif($qt == 14){
                                                            $ts = $userAnswer ? strtotime($userAnswer) : false;
                                                            echo $ts ? '<span class="marked-answer">'.date('h:i A', $ts).'</span>' : ($userAnswer ? '<span class="marked-answer">'.htmlspecialchars($userAnswer).'</span>' : '<span class="text-muted">No answer</span>');
                                                        } elseif($qt == 15){
                                                            $ts = $userAnswer ? strtotime($userAnswer) : false;
                                                            echo $ts ? '<span class="marked-answer">'.date('d M Y, h:i A', $ts).'</span>' : ($userAnswer ? '<span class="marked-answer">'.htmlspecialchars($userAnswer).'</span>' : '<span class="text-muted">No answer</span>');
                                                        } else {
                                                            echo $userAnswer ? '<div class="text-answer">'.nl2br(htmlspecialchars($userAnswer)).'</div>' : '<span class="text-muted">No answer</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block mb-1">
                                                        <?php
                                                        if(in_array($qt, [4, 9])) echo 'Model Answer:';
                                                        elseif(in_array($qt, [3, 12])) echo 'Correct Answers:';
                                                        else echo 'Correct Answer:';
                                                        ?>
                                                        </small>
                                                        <?php
                                                        if($qt == 1 || $qt == 5){
                                                            $correctOpt = intval($answer['correct_option']);
                                                            if($correctOpt >= 1 && $correctOpt <= 4 && !empty($answer['option_'.$correctOpt])){
                                                                echo '<span class="correct-answer">'.getOptionLabel($correctOpt).'. '.htmlspecialchars($answer['option_'.$correctOpt]).'</span>';
                                                            } elseif($answer['correct_option']){
                                                                echo '<span class="correct-answer">'.htmlspecialchars($answer['correct_option']).'</span>';
                                                            } else {
                                                                echo '<span class="text-muted">Not set</span>';
                                                            }
                                                        } elseif($qt == 2){
                                                            $co = (string)($answer['correct_option'] ?? '');
                                                            if($co === '1' || strtolower($co) === 'true') $coDisplay = 'True';
                                                            elseif($co === '0' || $co === '2' || strtolower($co) === 'false') $coDisplay = 'False';
                                                            elseif($co !== '') $coDisplay = $co;
                                                            else $coDisplay = 'Not set';
                                                            echo '<span class="correct-answer">'.htmlspecialchars($coDisplay).'</span>';
                                                        } elseif($qt == 3){
                                                            $corrArr = json_decode($answer['correct_options_multi'] ?? '[]', true);
                                                            if(is_array($corrArr) && count($corrArr) > 0){
                                                                $corrItems = [];
                                                                foreach($corrArr as $corr){
                                                                    $corrItems[] = getOptionLabel(intval($corr)).'. '.htmlspecialchars($answer['option_'.$corr] ?? 'N/A');
                                                                }
                                                                echo '<span class="correct-answer">'.implode(', ', $corrItems).'</span>';
                                                            } else {
                                                                echo '<span class="text-muted">Not set</span>';
                                                            }
                                                        } elseif($qt == 4){
                                                            $ct = $answer['correct_options_multi'] ?: ($answer['correct_option'] ?? '');
                                                            echo $ct ? '<div class="text-answer">'.nl2br(htmlspecialchars($ct)).'</div>' : '<span class="text-muted">Manual grading required</span>';
                                                        } elseif($qt == 6){
                                                            $ct = $answer['correct_option_text'] ?? '';
                                                            echo $ct ? '<div class="text-answer">'.nl2br(htmlspecialchars($ct)).'</div>' : '<span class="text-muted">—</span>';
                                                        } elseif($qt == 7){
                                                            $expNum = $answer['correct_option_text'] ?? '';
                                                            $minV   = $answer['q_min'] ?? '';
                                                            $maxV   = $answer['q_max'] ?? '';
                                                            if($expNum !== '') echo '<span class="correct-answer">'.htmlspecialchars($expNum).'</span>';
                                                            if($minV !== '' || $maxV !== '') echo '<small class="text-muted d-block mt-1">Range: '.htmlspecialchars($minV !== '' ? $minV : '—').' – '.htmlspecialchars($maxV !== '' ? $maxV : '—').'</small>';
                                                            if($expNum === '' && $minV === '' && $maxV === '') echo '<span class="text-muted">Not set</span>';
                                                        } elseif($qt == 8){
                                                            $expUrl = $answer['correct_option_text'] ?? '';
                                                            if($expUrl){
                                                                $safeUrl = htmlspecialchars($expUrl);
                                                                echo '<a href="'.$safeUrl.'" target="_blank" rel="noopener" class="correct-answer text-break">'.$safeUrl.'</a>';
                                                            } else {
                                                                echo '<span class="text-muted">Not set</span>';
                                                            }
                                                        } elseif($qt == 9){
                                                            $ct = $answer['correct_option_text'] ?? '';
                                                            echo $ct ? '<div class="text-answer">'.nl2br(htmlspecialchars($ct)).'</div>' : '<span class="text-muted">Manual grading required</span>';
                                                        } elseif($qt == 10){
                                                            $allOpts = json_decode($answer['option_1'] ?? '[]', true) ?: [];
                                                            $corrIdx = json_decode($answer['correct_options_multi'] ?? '[]', true) ?: [];
                                                            $displayVals = [];
                                                            foreach($corrIdx as $cv){
                                                                $idx = intval($cv) - 1; // stored as 1-based index
                                                                if(isset($allOpts[$idx]) && $allOpts[$idx] !== ''){
                                                                    $displayVals[] = $allOpts[$idx];
                                                                } elseif($cv !== ''){
                                                                    $displayVals[] = $cv;
                                                                }
                                                            }
                                                            echo count($displayVals) > 0
                                                                ? '<span class="correct-answer">'.htmlspecialchars(implode(', ', $displayVals)).'</span>'
                                                                : '<span class="text-muted">Not set</span>';
                                                        } elseif($qt == 11){
                                                            $allOpts = json_decode($answer['option_1'] ?? '[]', true) ?: [];
                                                            $co = $answer['correct_option'] ?? '';
                                                            $idx = intval($co) - 1; // stored as 1-based index
                                                            if(isset($allOpts[$idx]) && $allOpts[$idx] !== ''){
                                                                echo '<span class="correct-answer">'.htmlspecialchars($allOpts[$idx]).'</span>';
                                                            } elseif($co !== ''){
                                                                echo '<span class="correct-answer">'.htmlspecialchars($co).'</span>';
                                                            } else {
                                                                echo '<span class="text-muted">Not set</span>';
                                                            }
                                                        } elseif($qt == 12){
                                                            $allOpts = json_decode($answer['option_1'] ?? '[]', true) ?: [];
                                                            $corrIdx = json_decode($answer['correct_options_multi'] ?? '[]', true) ?: [];
                                                            $displayVals = [];
                                                            foreach($corrIdx as $cv){
                                                                $idx = intval($cv) - 1; // stored as 1-based index
                                                                if(isset($allOpts[$idx]) && $allOpts[$idx] !== ''){
                                                                    $displayVals[] = $allOpts[$idx];
                                                                } elseif($cv !== ''){
                                                                    $displayVals[] = $cv;
                                                                }
                                                            }
                                                            echo count($displayVals) > 0
                                                                ? '<div class="correct-answer">'.implode('<br>', array_map('htmlspecialchars', $displayVals)).'</div>'
                                                                : '<span class="text-muted">Not set</span>';
                                                        } elseif($qt == 13){
                                                            $ct = $answer['correct_option_text'] ?? '';
                                                            $ts = $ct ? strtotime($ct) : false;
                                                            echo $ts ? '<span class="correct-answer">'.date('d M Y', $ts).'</span>' : ($ct ? '<span class="correct-answer">'.htmlspecialchars($ct).'</span>' : '<span class="text-muted">Not set</span>');
                                                        } elseif($qt == 14){
                                                            $ct = $answer['correct_option_text'] ?? '';
                                                            $ts = $ct ? strtotime($ct) : false;
                                                            echo $ts ? '<span class="correct-answer">'.date('h:i A', $ts).'</span>' : ($ct ? '<span class="correct-answer">'.htmlspecialchars($ct).'</span>' : '<span class="text-muted">Not set</span>');
                                                        } elseif($qt == 15){
                                                            $ct = $answer['correct_option_text'] ?? '';
                                                            $ts = $ct ? strtotime($ct) : false;
                                                            echo $ts ? '<span class="correct-answer">'.date('d M Y, h:i A', $ts).'</span>' : ($ct ? '<span class="correct-answer">'.htmlspecialchars($ct).'</span>' : '<span class="text-muted">Not set</span>');
                                                        } else {
                                                            $co = $answer['correct_option'] ?? '';
                                                            echo $co ? '<div class="text-answer">'.nl2br(htmlspecialchars($co)).'</div>' : '<span class="text-muted">Not set</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php if(in_array($qt, [4, 6, 9]) && !$is_student): ?>
                                                <div class="mt-3 pt-3 border-top d-flex align-items-end gap-3 flex-wrap">
                                                    <div>
                                                        <label class="form-label mb-1 fw-semibold small">Mark as Correct</label>
                                                        <div class="form-check form-switch mt-1">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="correct_<?php echo $answer['question_id']; ?>"
                                                                <?php echo $isCorrect ? 'checked' : ''; ?>
                                                                onchange="toggleTextGrade(this,<?php echo $answer['question_id']; ?>,<?php echo $qMarks; ?>)">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="form-label mb-1 fw-semibold small">Marks <span class="text-muted">(max <?php echo $qMarks; ?>)</span></label>
                                                        <input type="number" class="form-control form-control-sm"
                                                            id="marks_<?php echo $answer['question_id']; ?>"
                                                            min="0" max="<?php echo $qMarks; ?>"
                                                            value="<?php echo $obtMarks; ?>"
                                                            style="width:90px">
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-primary"
                                                            onclick="saveTextGrade(<?php echo intval($answer['answer_id']); ?>,<?php echo $submission_id; ?>,<?php echo $answer['question_id']; ?>,<?php echo $qMarks; ?>)">
                                                            Save Grade
                                                        </button>
                                                        <span id="grade_status_<?php echo $answer['question_id']; ?>" class="ms-2 small"></span>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
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
        <script>
        function toggleTextGrade(cb, qid, maxMarks){
            var marksInput = document.getElementById('marks_'+qid);
            if(cb.checked){
                marksInput.value = maxMarks;
            } else {
                marksInput.value = 0;
            }
        }
        function saveTextGrade(answerId, submissionId, questionId, maxMarks){
            var cb = document.getElementById('correct_'+questionId);
            var marksInput = document.getElementById('marks_'+questionId);
            var statusEl = document.getElementById('grade_status_'+questionId);
            var isCorrect = cb.checked ? 1 : 0;
            var rawVal = parseInt(marksInput.value) || 0;
            var marks = Math.min(Math.max(rawVal, 0), maxMarks);
            marksInput.value = marks;
            statusEl.textContent = 'Saving...';
            statusEl.className = 'ms-2 small text-muted';
            var fd = new FormData();
            fd.append('formName', 'save_text_grade');
            fd.append('answer_id', answerId);
            fd.append('submission_id', submissionId);
            fd.append('question_id', questionId);
            fd.append('is_correct', isCorrect);
            fd.append('marks_obtained', marks);
            fetch('includes/datacontrol.php', { method:'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if(data.success){
                        statusEl.textContent = 'Saved';
                        statusEl.className = 'ms-2 small text-success';
                    } else {
                        statusEl.textContent = data.message || 'Error';
                        statusEl.className = 'ms-2 small text-danger';
                    }
                })
                .catch(function(){
                    statusEl.textContent = 'Request failed';
                    statusEl.className = 'ms-2 small text-danger';
                });
        }
        </script>
    </body>
</html>

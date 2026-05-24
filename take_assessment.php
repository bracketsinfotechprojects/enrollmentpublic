<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!='' && @$_SESSION['user_type']=='student'){
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student_enrol_id = $_SESSION['user_id'] ?? 0;

$assessment = null;
if($assessment_id > 0 && $student_enrol_id > 0){
    // Check if student is assigned
    $assign_check = mysqli_query($connection, "SELECT * FROM assessment_assignments WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id LIMIT 1");
    if(!$assign_check || mysqli_num_rows($assign_check) == 0){
        header("Location: student_assessment.php?error=not_assigned");
        exit;
    }
    
    // Check if already submitted
    $sub_check = mysqli_query($connection, "SELECT * FROM assessment_submissions WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id AND status = 1 LIMIT 1");
    if($sub_check && mysqli_num_rows($sub_check) > 0){
        header("Location: student_assessment.php?error=already_submitted");
        exit;
    }
    
    $res = mysqli_query($connection, "SELECT * FROM assessment WHERE assessment_id = $assessment_id LIMIT 1");
    if($res && mysqli_num_rows($res) > 0){
        $assessment = mysqli_fetch_assoc($res);
    }
    
    // Get questions
    $questions_result = mysqli_query($connection, "SELECT * FROM assessment_questions WHERE assessment_id = $assessment_id ORDER BY question_id");
    $questions = [];
    if($questions_result){
        while($row = mysqli_fetch_assoc($questions_result)){
            $questions[] = $row;
        }
    }
}

if(!$assessment){
    header("Location: student_assessment.php");
    exit;
}

function getQuestionTypeName($type) {
    $types = [
        1 => 'Single Choice',
        2 => 'True/False',
        3 => 'Multiple Choice',
        4 => 'Text Answer',
        5 => 'Image-based'
    ];
    return $types[$type] ?? 'Unknown';
}
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>Take Assessment - <?php echo htmlspecialchars($assessment['assessment_name']); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <style>
            :root {
                --theme-primary: #0d6efd;
                --theme-primary-hover: #0b5ed7;
                --theme-primary-light: #e7f1ff;
            }
            .question-card { border-left: 4px solid var(--theme-primary); }
            .option-item { transition: all 0.2s; cursor: pointer; }
            .option-item:hover { background-color: #f8f9fa; }
            .option-item.selected { background-color: var(--theme-primary-light); border-color: var(--theme-primary); }
            .checkbox-item { transition: all 0.2s; cursor: pointer; }
            .checkbox-item:hover { background-color: #f8f9fa; }
            .checkbox-item.checked { background-color: var(--theme-primary-light); border-color: var(--theme-primary); }
            .radio-custom, .checkbox-custom { transform: scale(1.2); }
            .answered { border-color: var(--theme-primary); background-color: var(--theme-primary-light); }
            .btn-primary { background-color: var(--theme-primary); border-color: var(--theme-primary); }
            .btn-primary:hover { background-color: var(--theme-primary-hover); border-color: var(--theme-primary-hover); }
            .progress-bar { background-color: var(--theme-primary); }
            .question-img { box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        </style>
    </head>

    <body>
        <div class="main-wrapper">
            <?php include('includes/header.php'); ?>
            
            <div class="page-wrapper">
                <div class="content pb-0">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="mb-sm-0"><?php echo htmlspecialchars($assessment['assessment_name']); ?></h4>
                                        
                                    </div>
                                    <div>
                                        <span class="badge bg-warning fs-4 mt-1" id="timerDisplay">
                                            <i class="ti ti-clock me-1"></i><span id="timer">--:--</span>
                                        </span>
                                    </div>
                                    <div class="page-title-right">
                                        <a href="student_assessment.php" class="btn btn-sm btn-outline-secondary">
                                            <i class="ti ti-arrow-left me-1"></i> Back
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card question-card">
                                    <div class="card-header" style="background-color: var(--theme-primary); color: white;">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h5 class="card-title mb-0">
                                                    <i class="ti ti-clipboard-check me-1"></i> Answer the Questions
                                                </h5>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <span class="badge bg-light text-dark">
                                                    Question <span id="currentQ">1</span> of <?php echo count($questions); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="progress mb-4" style="height: 8px;">
                                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%;"></div>
                                        </div>
                                        
                                        <form id="assessmentForm">
                                            <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
                                            <input type="hidden" name="student_enrol_id" value="<?php echo $student_enrol_id; ?>">
                                            
                                            <?php foreach($questions as $index => $q): ?>
                                            <div class="question-section" id="question_<?php echo $q['question_id']; ?>" data-question-id="<?php echo $q['question_id']; ?>" data-question-type="<?php echo $q['question_type'] ?? 1; ?>" style="display: <?php echo $index == 0 ? 'block' : 'none'; ?>;">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">
                                                        <span class="badge bg-primary me-2">Q<?php echo $index + 1; ?></span>
                                                        <?php echo htmlspecialchars($q['question_text']); ?>
                                                        <span class="text-muted ms-2">(<?php echo $q['marks']; ?> marks)</span>
                                                    </label>
                                                </div>
                                                
                                                <?php 
                                                $qt = $q['question_type'] ?? 1;
                                                if($qt == 1): // Single Choice
                                                ?>
                                                <div class="mb-3 options-container" data-question-id="<?php echo $q['question_id']; ?>">
                                                    <?php 
                                                    $opts = [
                                                        1 => $q['option_1'],
                                                        2 => $q['option_2'],
                                                        3 => $q['option_3'],
                                                        4 => $q['option_4']
                                                    ];
                                                    foreach($opts as $optNum => $optVal):
                                                    if($optVal): ?>
                                                    <div class="form-check mb-2 option-item" onclick="selectOption(<?php echo $q['question_id']; ?>, <?php echo $optNum; ?>, 1)">
                                                        <input class="form-check-input" type="radio" name="answer_<?php echo $q['question_id']; ?>" id="q<?php echo $q['question_id']; ?>_opt<?php echo $optNum; ?>" value="<?php echo $optNum; ?>">
                                                        <label class="form-check-label" for="q<?php echo $q['question_id']; ?>_opt<?php echo $optNum; ?>"><?php echo htmlspecialchars($optVal); ?></label>
                                                    </div>
                                                    <?php endif; endforeach; ?>
                                                </div>
                                                <?php elseif($qt == 2): // True/False ?>
                                                <div class="mb-3 options-container" data-question-id="<?php echo $q['question_id']; ?>">
                                                    <div class="form-check mb-2 option-item" onclick="selectOption(<?php echo $q['question_id']; ?>, 'True', 2)">
                                                        <input class="form-check-input" type="radio" name="answer_<?php echo $q['question_id']; ?>" id="q<?php echo $q['question_id']; ?>_true" value="True">
                                                        <label class="form-check-label" for="q<?php echo $q['question_id']; ?>_true">True</label>
                                                    </div>
                                                    <div class="form-check mb-2 option-item" onclick="selectOption(<?php echo $q['question_id']; ?>, 'False', 2)">
                                                        <input class="form-check-input" type="radio" name="answer_<?php echo $q['question_id']; ?>" id="q<?php echo $q['question_id']; ?>_false" value="False">
                                                        <label class="form-check-label" for="q<?php echo $q['question_id']; ?>_false">False</label>
                                                    </div>
                                                </div>
                                                <?php elseif($qt == 3): // Multiple Choice ?>
                                                <div class="mb-3 options-container" data-question-id="<?php echo $q['question_id']; ?>">
                                                    <?php 
                                                    $opts = [
                                                        1 => $q['option_1'],
                                                        2 => $q['option_2'],
                                                        3 => $q['option_3'],
                                                        4 => $q['option_4']
                                                    ];
                                                    foreach($opts as $optNum => $optVal):
                                                    if($optVal): ?>
                                                    <div class="form-check mb-2 checkbox-item" onclick="selectMultiOption(<?php echo $q['question_id']; ?>, <?php echo $optNum; ?>, this)">
                                                        <input class="form-check-input" type="checkbox" name="multi_<?php echo $q['question_id']; ?>[]" id="q<?php echo $q['question_id']; ?>_multi<?php echo $optNum; ?>" value="<?php echo $optNum; ?>">
                                                        <label class="form-check-label" for="q<?php echo $q['question_id']; ?>_multi<?php echo $optNum; ?>"><?php echo htmlspecialchars($optVal); ?></label>
                                                    </div>
                                                    <?php endif; endforeach; ?>
                                                </div>
                                                <?php elseif($qt == 4): // Text Answer ?>
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" name="text_answer_<?php echo $q['question_id']; ?>" id="text_<?php echo $q['question_id']; ?>" placeholder="Type your answer here..." onchange="saveTextAnswer(<?php echo $q['question_id']; ?>, this.value)">
                                                </div>
                                                <?php elseif($qt == 5): // Image-based
                                                $img_file = $q['correct_options_multi'] ?? '';
                                                $img_opts = [
                                                    1 => $q['option_1'] ?? '',
                                                    2 => $q['option_2'] ?? '',
                                                    3 => $q['option_3'] ?? '',
                                                    4 => $q['option_4'] ?? ''
                                                ];
                                                ?>
                                                <?php if($img_file): ?>
                                                <div class="mb-3 text-center">
                                                    <img src="uploads/question_images/<?php echo htmlspecialchars($img_file); ?>"
                                                         alt="Question Image"
                                                         class="question-img"
                                                         style="max-width:100%;max-height:340px;border-radius:8px;border:1px solid #dee2e6;">
                                                </div>
                                                <?php endif; ?>
                                                <div class="mb-3 options-container" data-question-id="<?php echo $q['question_id']; ?>">
                                                    <?php foreach($img_opts as $optNum => $optVal): if($optVal): ?>
                                                    <div class="form-check mb-2 option-item" onclick="selectOption(<?php echo $q['question_id']; ?>, <?php echo $optNum; ?>, 5)">
                                                        <input class="form-check-input radio-custom" type="radio"
                                                               name="answer_<?php echo $q['question_id']; ?>"
                                                               id="q<?php echo $q['question_id']; ?>_opt<?php echo $optNum; ?>"
                                                               value="<?php echo $optNum; ?>">
                                                        <label class="form-check-label" for="q<?php echo $q['question_id']; ?>_opt<?php echo $optNum; ?>">
                                                            <?php echo htmlspecialchars($optVal); ?>
                                                        </label>
                                                    </div>
                                                    <?php endif; endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                            
                                            <div class="d-flex justify-content-between mt-4">
                                                <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="prevQuestion()">
                                                    <i class="ti ti-arrow-left me-1"></i> Previous
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" id="nextBtn" onclick="nextQuestion()">
                                                    Next <i class="ti ti-arrow-right ms-1"></i>
                                                </button>
                                                <button type="button" class="btn btn-success" id="submitBtn" onclick="submitAssessment()" style="display: none;">
                                                    <i class="ti ti-check me-1"></i> Submit Assessment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Up Modal -->
        <div id="timeUpModal" class="modal" tabindex="-1" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 400px; margin: 200px auto;">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="ti ti-alert-circle me-2"></i>Time's Up!</h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="spinner-border text-danger mb-3" role="status"></div>
                        <h4>Submitting your assessment...</h4>
                        <p class="text-muted">Please wait while we save your answers.</p>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/footer_includes.php'); ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

        <script>
            var currentQuestion = 0;
            var questions = <?php echo json_encode(array_map(function($q){ return $q['question_id']; }, $questions)); ?>;
            var totalQuestions = <?php echo count($questions); ?>;
            var answeredQuestions = new Set();
            
            // Timer
            var durationMinutes = <?php echo intval($assessment['duration'] ?? 60); ?>;
            var timeRemaining = durationMinutes * 60;
            var timerInterval = setInterval(function() {
                var minutes = Math.floor(timeRemaining / 60);
                var seconds = timeRemaining % 60;
                document.getElementById('timer').textContent = 
                    (minutes < 10 ? '0' : '') + minutes + ':' + 
                    (seconds < 10 ? '0' : '') + seconds;
                 if(timeRemaining == 300) {
                     showToast('5 Minutes left ! Hurry up... ', 'error');
                 }
                if(timeRemaining <= 300) {
                    document.getElementById('timerDisplay').classList.remove('bg-warning');
                   
                    document.getElementById('timerDisplay').classList.add('bg-danger');
                }
                
                if(timeRemaining == 0) {
                    clearInterval(timerInterval);
                    showToast('Time is up! Submitting assessment...', 'error');
                    submitAssessment();
                }
                timeRemaining--;
            }, 1000);
            
            function showToast(message, type) {
                var bgColor = type === 'success' ? '#28a745' : '#dc3545';
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    backgroundColor: bgColor
                }).showToast();
            }
            
            function updateProgress() {
                var progress = ((currentQuestion + 1) / totalQuestions) * 100;
                document.getElementById('progressBar').style.width = progress + '%';
                document.getElementById('currentQ').textContent = currentQuestion + 1;
                
                document.getElementById('prevBtn').style.display = currentQuestion === 0 ? 'none' : 'inline-block';
                
                if(currentQuestion >= totalQuestions - 1){
                    document.getElementById('nextBtn').style.display = 'none';
                    document.getElementById('submitBtn').style.display = 'inline-block';
                } else {
                    document.getElementById('nextBtn').style.display = 'inline-block';
                    document.getElementById('submitBtn').style.display = 'none';
                }
            }
            
            function showQuestion(index) {
                questions.forEach(function(qid, i) {
                    document.getElementById('question_' + qid).style.display = i === index ? 'block' : 'none';
                });
                currentQuestion = index;
                updateProgress();
            }
            
            function nextQuestion() {
                if(currentQuestion < totalQuestions - 1){
                    showQuestion(currentQuestion + 1);
                }
            }
            
            function prevQuestion() {
                if(currentQuestion > 0){
                    showQuestion(currentQuestion - 1);
                }
            }
            
            function selectOption(questionId, optionValue, type) {
                var qId = 'q' + questionId + '_opt' + optionValue;
                var el = document.getElementById(qId);
                if(el) el.checked = true;
                
                // Visual selection
                var container = document.querySelector('[data-question-id="' + questionId + '"]');
                container.querySelectorAll('.option-item').forEach(function(item) {
                    item.classList.remove('selected');
                });
                var selected = container.querySelector('input[value="' + optionValue + '"]');
                if(selected) selected.closest('.option-item').classList.add('selected');
                
                saveAnswer(questionId, optionValue);
                answeredQuestions.add(questionId);
            }
            
            function selectMultiOption(questionId, optionValue, element) {
                element.classList.toggle('checked');
                if(element.querySelector('input').checked){
                    element.classList.add('checked');
                } else {
                    element.classList.remove('checked');
                }
                
                var selected = [];
                var container = element.closest('.options-container');
                container.querySelectorAll('input:checked').forEach(function(el) {
                    selected.push(el.value);
                });
                
                saveAnswer(questionId, JSON.stringify(selected));
                answeredQuestions.add(questionId);
            }
            
            function saveTextAnswer(questionId, text) {
                if(text.trim()){
                    saveAnswer(questionId, text);
                    answeredQuestions.add(questionId);
                }
            }
            
            function saveAnswer(questionId, answer) {
                var assessmentId = <?php echo $assessment_id; ?>;
                var studentId = <?php echo $student_enrol_id; ?>;
                
                $.ajax({
                    url: 'includes/datacontrol.php',
                    type: 'POST',
                    data: {
                        formName: 'save_answer',
                        assessment_id: assessmentId,
                        student_enrol_id: studentId,
                        question_id: questionId,
                        answer_option: answer
                    },
                    success: function(response) {
                        console.log('Answer saved:', response.trim());
                    }
                });
            }
            
            function submitAssessment() {
            if(timeRemaining > 0){
                if(answeredQuestions.size < totalQuestions){
                    var unanswered = totalQuestions - answeredQuestions.size;
                    if(!confirm('You have ' + unanswered + ' unanswered question(s). Are you sure you want to submit?')){
                        return;
                    }
                }
            } 
            
                $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Submitting...');
                
                $.ajax({
                    url: 'includes/datacontrol.php',
                    type: 'POST',
                    data: {
                        formName: 'submit_assessment',
                        assessment_id: <?php echo $assessment_id; ?>,
                        student_enrol_id: <?php echo $student_enrol_id; ?>
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success){
                            var msg = 'Assessment submitted successfully!';
                            if(response.attempted_questions !== undefined){
                                msg += '\nAttempted: ' + response.attempted_questions + ' questions';
                                if(response.pending_questions > 0){
                                    msg += '\nPending: ' + response.pending_questions + ' questions';
                                }
                            }
                            showToast(msg, 'success');
                            setTimeout(function(){
                                window.location.href = 'student_assessment.php?success=1';
                            }, 3000);
                        } else {
                            showToast('Error: ' + response, 'error');
                            $('#submitBtn').prop('disabled', false).html('<i class="ti ti-check me-1"></i> Submit Assessment');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error: ' + status + ' - ' + error + ' - ' + xhr.responseText);
                        showToast('Error submitting assessment', 'error');
                        $('#submitBtn').prop('disabled', false).html('<i class="ti ti-check me-1"></i> Submit Assessment');
                    }
                });
            }
            
            $(document).ready(function() {
                showQuestion(0);
            });
        </script>
    </body>
</html>
<?php 
} else {
    header("Location: index.php");
}
?>
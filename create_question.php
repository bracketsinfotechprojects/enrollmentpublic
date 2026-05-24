<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!='' && @$_SESSION['user_type']==1){
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$assessment = null;
if($assessment_id > 0){
    $res = mysqli_query($connection, "SELECT * FROM assessment WHERE assessment_id = $assessment_id LIMIT 1");
    if($res && mysqli_num_rows($res) > 0){
        $assessment = mysqli_fetch_assoc($res);
    }
    
    $questions_result = mysqli_query($connection, "SELECT * FROM assessment_questions WHERE assessment_id = $assessment_id ORDER BY question_id DESC");
    $questions = [];
    if($questions_result){
        while($row = mysqli_fetch_assoc($questions_result)){
            $questions[] = $row;
        }
    }
}
$totalQuestionsMarks = array_sum(array_column($questions, 'marks'));

if(!$assessment){
    header("Location: assessment_list.php");
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
        <title>Add Questions - <?php echo htmlspecialchars($assessment['assessment_name']); ?></title>
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
            .asterisk { color: var(--theme-primary); }
            .form-label { font-weight: 500; }
            .correct-answer { border-color: #28a745 !important; background-color: #d4edda; }
            .question-card { border-left: 4px solid var(--theme-primary); }
            .option-item { transition: all 0.2s; }
            .option-item:hover { background-color: #f8f9fa; }
            .radio-custom, .checkbox-custom { transform: scale(1.2); }
            .type-single-choice, .type-multiple-choice { display: none; }
            .type-true-false { display: none; }
            .type-text-answer, .type-textarea { display: none; }
            .type-image-based { display: none; }
            #image_preview_container img, #current_image_container img { border: 2px dashed #dee2e6; padding: 4px; border-radius: 8px; }
            .img-opt-item.correct-answer { border-color: #28a745 !important; background-color: #d4edda; }
            .show-section { display: block; }
            .checkbox-item { transition: all 0.2s; }
            .checkbox-item:hover { background-color: #f8f9fa; }
            .checkbox-item.checked { background-color: #d4edda; border-color: #28a745; }
            .question-item { cursor: pointer; transition: all 0.2s; }
            .question-item:hover { background-color: #f8f9fa; }
            .question-item.active { background-color: var(--theme-primary-light); border-left: 3px solid var(--theme-primary); }
            .list-group-item { border-left: none; border-right: none; }
            .list-group-item:first-child { border-top: none; }
            .list-group-item:last-child { border-bottom: none; }
            .type-select-btn { border-radius: 8px !important; border: 1px solid var(--theme-primary) !important; color: var(--theme-primary) !important; }
            .type-select-btn.active { background-color: var(--theme-primary) !important; color: white !important; border-color: var(--theme-primary) !important; }
            .type-select-btn:hover { background-color: var(--theme-primary-light) !important; transform: translateX(5px); }
            .btn-primary { background-color: var(--theme-primary); border-color: var(--theme-primary); }
            .btn-primary:hover { background-color: var(--theme-primary-hover); border-color: var(--theme-primary-hover); }
            .card-header.bg-primary { background-color: var(--theme-primary) !important; }
            .badge.bg-primary { background-color: var(--theme-primary) !important; }
            .btn-outline-primary { color: var(--theme-primary) !important; border-color: var(--theme-primary) !important; }
            .btn-outline-primary:hover { background-color: var(--theme-primary) !important; color: white !important; }
            .bg-primary { background-color: var(--theme-primary) !important; }
            .text-primary { color: var(--theme-primary) !important; }
        </style>
    </head>

    <body>
        <div id="loader-container" style="display:none;">
            <div class="loader"></div>
        </div>
        <div class="main-wrapper">
            <?php include('includes/header.php'); ?>
            <?php include('includes/sidebar.php'); ?>
            
            <div class="page-wrapper">
                <div class="content pb-0">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Add Questions: <?php echo htmlspecialchars($assessment['assessment_name']); ?></h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Assessment</a></li>
                                            <li class="breadcrumb-item"><a href="assessment_list.php">Assessment List</a></li>
                                            <li class="breadcrumb-item active">Add Questions</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="marks_banner_row" style="display:none;">
                            <div class="col-12">
                                <div id="marks_limit_banner" class="alert alert-danger d-flex align-items-center gap-2 mb-3">
                                    <i class="ti ti-lock fs-5"></i>
                                    <div><strong>Marks limit reached.</strong> All <span id="banner_total"></span> marks have been allocated. Delete or edit an existing question to free up marks before adding a new one.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Left Side: Question Type Selection -->
                            <div class="col-lg-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header" style="background-color: var(--theme-primary); color: white;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">
                                                <i class="ti ti-apps me-1"></i> Question Types
                                            </h5>
                                            <a href="assessment_list.php" class="btn btn-sm btn-outline-light">
                                                <i class="ti ti-arrow-left me-1"></i> Back
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-primary text-start p-3 type-select-btn" data-type="1" onclick="selectQuestionType(1)">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3"><i class="ti ti-circle-check fs-4"></i></div>
                                                    <div>
                                                        <strong>Single Choice</strong>
                                                        <small class="d-block text-muted">Select one correct option from list</small>
                                                    </div>
                                                </div>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary text-start p-3 type-select-btn" data-type="2" onclick="selectQuestionType(2)">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3"><i class="ti ti-toggle-left fs-4"></i></div>
                                                    <div>
                                                        <strong>True / False</strong>
                                                        <small class="d-block text-muted">Simple True or False answer</small>
                                                    </div>
                                                </div>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary text-start p-3 type-select-btn" data-type="3" onclick="selectQuestionType(3)">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3"><i class="ti ti-checks fs-4"></i></div>
                                                    <div>
                                                        <strong>Multiple Choice</strong>
                                                        <small class="d-block text-muted">Select multiple correct options</small>
                                                    </div>
                                                </div>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary text-start p-3 type-select-btn" data-type="4" onclick="selectQuestionType(4)">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3"><i class="ti ti-forms fs-4"></i></div>
                                                    <div>
                                                        <strong>Text Answer</strong>
                                                        <small class="d-block text-muted">Short text response</small>
                                                    </div>
                                                </div>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary text-start p-3 type-select-btn" data-type="5" onclick="selectQuestionType(5)">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3"><i class="ti ti-photo fs-4"></i></div>
                                                    <div>
                                                        <strong>Image-based</strong>
                                                        <small class="d-block text-muted">Upload image, choose correct option</small>
                                                    </div>
                                                </div>
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Question Form -->
                            <div class="col-lg-8">
                                <div class="card question-card shadow-sm">
                                    <div class="card-header" style="background-color: var(--theme-primary); color: white;">
                                        <h5 class="card-title mb-0" id="formTitle">
                                            <i class="ti ti-plus me-1"></i> Add New Question
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-primary mb-3 py-2" style="background-color: var(--theme-primary-light); border-color: var(--theme-primary); color: var(--theme-primary);">
                                            <div class="row text-center">
                                                <div class="col">
                                                    <strong>Total Marks:</strong> <?php echo htmlspecialchars($assessment['marks']); ?>
                                                </div>
                                                <div class="col">
                                                    <strong>Duration:</strong> <?php echo htmlspecialchars($assessment['duration']); ?>
                                                </div>
                                                <div class="col">
                                                    <strong>Questions:</strong> <?php echo count($questions); ?>
                                                </div>
                                                <div class="col">
                                                    <strong>Added Marks:</strong>
                                                    <span id="added_marks_display" class="fw-bold">
                                                        <?php echo intval($totalQuestionsMarks); ?>
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <strong>Remaining Marks:</strong>
                                                    <?php $remaining = intval($assessment['marks']) - intval($totalQuestionsMarks); ?>
                                                    <span id="remaining_marks_display"
                                                          class="fw-bold <?php echo $remaining <= 0 ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo $remaining; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <form id="questionForm" method="post">
                                            <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
                                            <input type="hidden" name="question_type" id="question_type" value="1">
                                            
                                            <div id="image_error_alert" class="alert alert-danger py-2 px-3 mb-2" style="display:none;font-size:0.875rem;"></div>

                                            <div class="mb-3">
                                                <label class="form-label">Question <span class="asterisk">*</span></label>
                                                <textarea class="form-control" name="question_text" id="question_text" rows="3" placeholder="Enter your question here..." required></textarea>
                                            </div>
                                            
                                            <!-- Single Choice Options -->
                                            <div class="mb-3 type-single-choice show-section" id="section_single">
                                                <label class="form-label">Options <span class="asterisk">*</span></label>
                                                <p class="text-muted small mb-2">Select the radio button next to the correct answer</p>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-2 option-item" id="option-item-1">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="radio" name="correct_option" value="1" class="radio-custom">
                                                            </div>
                                                            <input type="text" class="form-control" name="option_1" id="option_1" placeholder="Option 1">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 option-item" id="option-item-2">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="radio" name="correct_option" value="2" class="radio-custom">
                                                            </div>
                                                            <input type="text" class="form-control" name="option_2" id="option_2" placeholder="Option 2">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 option-item" id="option-item-3">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="radio" name="correct_option" value="3" class="radio-custom">
                                                            </div>
                                                            <input type="text" class="form-control" name="option_3" id="option_3" placeholder="Option 3">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 option-item" id="option-item-4">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="radio" name="correct_option" value="4" class="radio-custom">
                                                            </div>
                                                            <input type="text" class="form-control" name="option_4" id="option_4" placeholder="Option 4">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- True/False Options -->
                                            <div class="mb-3 type-true-false" id="section_tf">
                                                <label class="form-label">Correct Answer <span class="asterisk">*</span></label>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="correct_option_tf" id="tf_true" value="True">
                                                            <label class="form-check-label" for="tf_true">True</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="correct_option_tf" id="tf_false" value="False">
                                                            <label class="form-check-label" for="tf_false">False</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Multiple Choice Options -->
                                            <div class="mb-3 type-multiple-choice" id="section_multi">
                                                <label class="form-label">Options <span class="asterisk">*</span></label>
                                                <p class="text-muted small mb-2">Check all that apply - correct answer</p>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-2 checkbox-item" id="checkbox-item-1">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="checkbox" name="multi_correct[]" value="1" class="checkbox-custom">
                                                            </div>
                                                            <input type="text" class="form-control" name="option_multi_1" id="option_multi_1" placeholder="Option 1">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 checkbox-item" id="checkbox-item-2">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="checkbox" name="multi_correct[]" value="2" class="checkbox-custom">
                                                            </div>
                                                            <input type="text" class="form-control" name="option_multi_2" id="option_multi_2" placeholder="Option 2">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 checkbox-item" id="checkbox-item-3">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="checkbox" name="multi_correct[]" value="3" class="checkbox-custom">
                                                            </div>
                                                            <input type="text" class="form-control" name="option_multi_3" id="option_multi_3" placeholder="Option 3">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 checkbox-item" id="checkbox-item-4">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="checkbox" name="multi_correct[]" value="4" class="checkbox-custom">
                                                            </div>
                                                            <input type="text" class="form-control" name="option_multi_4" id="option_multi_4" placeholder="Option 4">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                           <!-- Text Answer -->
                                            <div class="mb-3 type-text-answer" id="section_text">
                                                <label class="form-label">Expected Answer <span class="asterisk">*</span></label>
                                                <input type="text" class="form-control" name="text_answer" id="text_answer" placeholder="Enter the expected answer (case-sensitive)">
                                                <small class="text-muted">Student will need to type this answer exactly</small>
                                            </div>

                                            <!-- Image-based Question -->
                                            <div class="mb-3 type-image-based" id="section_image">
                                                <label class="form-label">Question Image <span class="asterisk">*</span></label>
                                                <input type="file" class="form-control mb-1" id="question_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                                <small class="text-muted">JPG, PNG, GIF or WEBP &mdash; max 2 MB</small>

                                                <!-- New image preview -->
                                                <div id="image_preview_container" class="mt-2" style="display:none;">
                                                    <p class="text-muted small mb-1">Preview:</p>
                                                    <img id="image_preview_new" src="" alt="Preview" style="max-width:100%;max-height:220px;">
                                                </div>

                                                <!-- Existing image (edit mode) -->
                                                <div id="current_image_container" class="mt-2" style="display:none;">
                                                    <p class="text-muted small mb-1">Current image (leave file blank to keep):</p>
                                                    <img id="current_image_display" src="" alt="Current" style="max-width:100%;max-height:200px;">
                                                    <input type="hidden" id="current_image_path" value="">
                                                </div>

                                                <label class="form-label mt-3">Answer Options <span class="asterisk">*</span></label>
                                                <p class="text-muted small mb-2">Select the radio button next to the correct answer</p>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2 img-opt-item option-item" id="img-option-item-1">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="radio" name="img_correct_option" value="1" class="radio-custom">
                                                            </div>
                                                            <input type="text" class="form-control" id="option_img_1" placeholder="Option 1">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 img-opt-item option-item" id="img-option-item-2">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="radio" name="img_correct_option" value="2" class="radio-custom">
                                                            </div>
                                                            <input type="text" class="form-control" id="option_img_2" placeholder="Option 2">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 img-opt-item option-item" id="img-option-item-3">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="radio" name="img_correct_option" value="3" class="radio-custom">
                                                            </div>
                                                            <input type="text" class="form-control" id="option_img_3" placeholder="Option 3">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2 img-opt-item option-item" id="img-option-item-4">
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <input type="radio" name="img_correct_option" value="4" class="radio-custom">
                                                            </div>
                                                            <input type="text" class="form-control" id="option_img_4" placeholder="Option 4">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Marks <span class="asterisk">*</span></label>
                                                <input type="number" class="form-control" name="question_marks" id="question_marks" placeholder="Enter marks for this question" min="1" required>
                                            </div>
                                            
                                            <div class="text-end">
                                                <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                                                <button type="submit" class="btn btn-primary" id="submitBtn">Add Question</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Section: Questions List -->
                            <div class="col-lg-12 mt-4">
                                <div class="card shadow-sm">
                                    <div class="card-header" style="background-color: var(--theme-primary); color: white;">
                                        <h5 class="card-title mb-0">
                                            <i class="ti ti-list me-1"></i> All Questions (<?php echo count($questions); ?>)
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if(count($questions) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Type</th>
                                                        <th>Question</th>
                                                        <th>Correct Answer</th>
                                                        <th>Marks</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($questions as $index => $q): ?>
                                                    <tr>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td><span class="badge" style="background-color: var(--theme-primary);"><?php echo getQuestionTypeName($q['question_type'] ?? 1); ?></span></td>
                                                        <td class="text-wrap" style="width: 60%;"><?php echo htmlspecialchars($q['question_text']); ?></td>
                                                        <td class="text-wrap" style="width: 20%;">
                                                            <?php 
                                                            $qt = $q['question_type'] ?? 1;
                                                            if($qt == 1) {
                                                                $opts = [$q['option_1'], $q['option_2'], $q['option_3'], $q['option_4']];
                                                                $idx = intval($q['correct_option']) - 1;
                                                                echo 'Option ' . $q['correct_option'] . ': ' . htmlspecialchars($opts[$idx] ?? '');
                                                            } elseif($qt == 2) {
                                                                if($q['correct_option'] == '1')
                                                                {
                                                                    $q['correct_option']='True';
                                                                }
                                                                else
                                                                {
                                                                    $q['correct_option']='False';
                                                                }
                                                                echo htmlspecialchars($q['correct_option']);
                                                            } elseif($qt == 3) {
                                                                $multi = json_decode($q['correct_options_multi'] ?? '[]', true);
                                                                echo 'Options: ' . implode(', ', $multi);
                                                            } elseif($qt == 4) {
                                                                echo htmlspecialchars($q['correct_options_multi']);
                                                            } elseif($qt == 5) {
                                                                $img_file = htmlspecialchars($q['correct_options_multi'] ?? '');
                                                                $correct_idx = intval($q['correct_option']) - 1;
                                                                $opts5 = [$q['option_1'] ?? '', $q['option_2'] ?? '', $q['option_3'] ?? '', $q['option_4'] ?? ''];
                                                                if($img_file) {
                                                                    echo '<img src="uploads/question_images/' . $img_file . '" style="max-height:38px;border-radius:4px;vertical-align:middle;" class="me-2">';
                                                                }
                                                                echo 'Option ' . $q['correct_option'] . ': ' . htmlspecialchars($opts5[$correct_idx] ?? '');
                                                            } else {
                                                                echo htmlspecialchars(substr($q['correct_options_multi'] ?? '', 0, 50)) . (strlen($q['correct_options_multi'] ?? '') > 50 ? '...' : '');
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($q['marks']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary me-1" onclick="editQuestion(<?php echo $q['question_id']; ?>, '<?php echo htmlspecialchars(addslashes($q['question_text'])); ?>', <?php echo $q['question_type'] ?? 1; ?>, '<?php echo htmlspecialchars(addslashes($q['option_1'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($q['option_2'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($q['option_3'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($q['option_4'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($q['correct_option'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($q['correct_options_multi'] ?? '')); ?>', <?php echo $q['marks']; ?>)" title="Edit Question"><i class="ti ti-edit"></i></button>
                                                            <button class="btn btn-sm btn-danger" onclick="deleteQuestion(<?php echo $q['question_id']; ?>)" title="Delete Question"><i class="ti ti-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="ti ti-inbox fs-1 text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No questions added yet</p>
                                            <small class="text-muted">Use the form above to add questions</small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/footer_includes.php'); ?>

        <script>
            var assessmentId = <?php echo $assessment_id; ?>;
            var assessmentTotalMarks = <?php echo intval($assessment['marks'] ?? 0); ?>;
            var currentTotalMarks = <?php echo intval($totalQuestionsMarks ?? 0); ?>;
            var editingQuestionId = null;
            var currentEditType = 1;
            
            function showToast(message, type) {
                var bgColor = type === 'success' ? '#28a745' : '#dc3545';
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    backgroundColor: bgColor
                }).showToast();
            }
            
            function toggleQuestionType(type) {
                $('.type-single-choice, .type-true-false, .type-multiple-choice, .type-text-answer, .type-textarea, .type-image-based').hide().removeClass('show-section');

                if(type == 1) {
                    $('#section_single').show().addClass('show-section');
                } else if(type == 2) {
                    $('#section_tf').show().addClass('show-section');
                } else if(type == 3) {
                    $('#section_multi').show().addClass('show-section');
                } else if(type == 4) {
                    $('#section_text').show().addClass('show-section');
                } else if(type == 5) {
                    $('#section_image').show().addClass('show-section');
                }
            }
            
            function selectQuestionType(type) {
                $('.type-select-btn').removeClass('active');
                $('.type-select-btn[data-type="' + type + '"]').addClass('active');
                $('#question_type').val(type);
                toggleQuestionType(type);
                currentEditType = type;
                $('html, body').animate({ scrollTop: $('#questionForm').offset().top - 100 }, 300);
            }
            
            function deleteQuestion(questionId){
                Swal.fire({
                    title: 'Delete Question',
                    text: 'Are you sure you want to delete this question?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'includes/datacontrol.php',
                            type: 'POST',
                            data: { formName: 'delete_question', question_id: questionId },
                            success: function(response) {
                                if(response.trim() == '1'){
                                    showToast('Question deleted successfully!', 'success');
                                    setTimeout(function(){
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    showToast('Error: ' + response, 'error');
                                }
                            },
                            error: function() {
                                showToast('An error occurred.', 'error');
                            }
                        });
                    }
                });
            }
            
            function editQuestion(questionId, questionText, questionType, option1, option2, option3, option4, correctOption, correctOptionsMulti, marks){
                editingQuestionId = questionId;
                currentEditType = questionType;
                
                 $('#question_type').val(questionType);
                 $('#question_text').val(questionText);
                 $('#question_marks').val(marks).data('original-marks', marks);
                
                $('.type-select-btn').removeClass('active');
                $('.type-select-btn[data-type="' + questionType + '"]').addClass('active');
                toggleQuestionType(questionType);
                
                if(questionType == 1) {
                    $('#option_1').val(option1);
                    $('#option_2').val(option2);
                    $('#option_3').val(option3);
                    $('#option_4').val(option4);
                    $('input[name="correct_option"][value="' + correctOption + '"]').prop('checked', true);
                } else if(questionType == 2) {
                    $('input[name="correct_option_tf"][value="' + correctOption + '"]').prop('checked', true);
                } else if(questionType == 3) {
                    $('#option_multi_1').val(option1);
                    $('#option_multi_2').val(option2);
                    $('#option_multi_3').val(option3);
                    $('#option_multi_4').val(option4);
                    var multiOpts = correctOptionsMulti ? JSON.parse(correctOptionsMulti) : [];
                    $('input[name="multi_correct[]"]').each(function(i, el) {
                        el.checked = multiOpts.includes(i + 1);
                    });
                } else if(questionType == 4) {
                    $('#text_answer').val(correctOptionsMulti);
                } else if(questionType == 5) {
                    $('#option_img_1').val(option1);
                    $('#option_img_2').val(option2);
                    $('#option_img_3').val(option3);
                    $('#option_img_4').val(option4);
                    $('input[name="img_correct_option"][value="' + correctOption + '"]').prop('checked', true);
                    $('input[name="img_correct_option"]').trigger('change');
                    $('#current_image_path').val(correctOptionsMulti);
                    if(correctOptionsMulti) {
                        $('#current_image_display').attr('src', 'uploads/question_images/' + correctOptionsMulti);
                        $('#current_image_container').show();
                    }
                }
                
                $('#submitBtn').text('Update Question').removeClass('btn-primary').addClass('btn-success');
                $('#formTitle').html('<i class="ti ti-edit me-1"></i> Edit Question');
                updateMarksState();
                $('html, body').animate({ scrollTop: $('#questionForm').offset().top - 100 }, 500);
            }
            
            function resetForm(){
                 editingQuestionId = null;
                 currentEditType = 1;
                 $('#questionForm')[0].reset();
                 $('#question_marks').removeData('original-marks');
                 toggleQuestionType(1);
                 $('#question_marks').val('');
                 $('#submitBtn').text('Add Question').removeClass('btn-success').addClass('btn-primary');
                 $('#formTitle').html('<i class="ti ti-plus me-1"></i> Add New Question');
                 $('.type-select-btn').removeClass('active');
                 $('.type-select-btn[data-type="1"]').addClass('active');
                 // Reset image-based fields
                 $('#question_image').val('');
                 $('#image_error_alert').hide().text('');
                 $('#image_preview_container').hide();
                 $('#current_image_container').hide();
                 $('#current_image_path').val('');
                 $('#option_img_1, #option_img_2, #option_img_3, #option_img_4').val('');
                 $('input[name="img_correct_option"]').prop('checked', false);
                 $('.img-opt-item').removeClass('correct-answer');
                 updateMarksState();
             }

            // Central function: updates remaining display, input max, and form lock state
            function updateMarksState() {
                var isEdit       = editingQuestionId !== null;
                var originalMarks = isEdit ? (parseInt($('#question_marks').data('original-marks')) || 0) : 0;
                var base          = currentTotalMarks - originalMarks;           // marks already committed (excl. current question)
                var available     = assessmentTotalMarks - base;                  // max allowed for this question
                var entered       = parseInt($('#question_marks').val()) || 0;
                var remaining     = assessmentTotalMarks - base - entered;        // what will be left after saving

                // Added Marks = committed + what the user has typed (0 if empty)
                $('#added_marks_display').text(base + entered);

                // Remaining = available seats after accounting for typed value (show available when input is empty)
                var displayRemaining = entered > 0 ? remaining : available;
                var $disp = $('#remaining_marks_display');
                $disp.text(displayRemaining);
                $disp.removeClass('text-success text-danger text-warning');
                if      (displayRemaining < 0)  $disp.addClass('text-danger');
                else if (displayRemaining === 0) $disp.addClass('text-success');
                else                             $disp.addClass('text-warning');

                // Clamp the input max
                $('#question_marks').attr('max', available > 0 ? available : 0);

                // Lock / unlock the entire form
                if (available <= 0 && !isEdit) {
                    $('#banner_total').text(assessmentTotalMarks);
                    $('#marks_banner_row').show();
                    $('#questionForm input, #questionForm textarea, #questionForm select, #submitBtn, .type-select-btn')
                        .prop('disabled', true);
                } else {
                    $('#marks_banner_row').hide();
                    $('#questionForm input, #questionForm textarea, #questionForm select, #submitBtn, .type-select-btn')
                        .prop('disabled', false);
                }

                // Warn if entered value exceeds available
                var $marksInput = $('#question_marks');
                if (entered > available) {
                    $marksInput.addClass('is-invalid');
                    if (!$('#marks_input_error').length) {
                        $marksInput.after('<div id="marks_input_error" class="invalid-feedback">Only <strong>' + available + '</strong> mark(s) available for this question.</div>');
                    } else {
                        $('#marks_input_error').html('Only <strong>' + available + '</strong> mark(s) available for this question.');
                    }
                } else {
                    $marksInput.removeClass('is-invalid');
                    $('#marks_input_error').remove();
                }
            }
            
            $(document).ready(function() {
                
                $('#question_type').change(function() {
                    var type = $(this).val();
                    toggleQuestionType(type);
                    $('.type-select-btn').removeClass('active');
                    $('.type-select-btn[data-type="' + type + '"]').addClass('active');
                });
                
                selectQuestionType(1);
                toggleQuestionType(1);
                updateMarksState();   // run once on load

                $('#question_marks').on('input', function() { updateMarksState(); });
                
                $('input[name="correct_option"]').change(function(){
                    $('.option-item').removeClass('correct-answer');
                    $(this).closest('.option-item').addClass('correct-answer');
                });
                
                $('input[name="multi_correct[]"]').change(function() {
                    if($(this).is(':checked')) {
                        $(this).closest('.checkbox-item').addClass('checked');
                    } else {
                        $(this).closest('.checkbox-item').removeClass('checked');
                    }
                });

                // Image option correct-answer highlight
                $(document).on('change', 'input[name="img_correct_option"]', function() {
                    $('.img-opt-item').removeClass('correct-answer');
                    $(this).closest('.img-opt-item').addClass('correct-answer');
                });

                // Live preview for uploaded image (with size + type validation)
                $('#question_image').on('change', function() {
                    var file = this.files[0];
                    $('#image_error_alert').hide().text('');
                    $('#image_preview_container').hide();
                    if(!file) return;

                    var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    var maxBytes = 2 * 1024 * 1024;

                    if(allowedTypes.indexOf(file.type) === -1) {
                        $('#image_error_alert').text('Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.').show();
                        this.value = '';
                        return;
                    }
                    if(file.size > maxBytes) {
                        $('#image_error_alert').text('Image is too large (' + (file.size / 1024 / 1024).toFixed(2) + ' MB). Maximum allowed size is 2 MB.').show();
                        this.value = '';
                        return;
                    }

                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#image_preview_new').attr('src', e.target.result);
                        $('#image_preview_container').show();
                    };
                    reader.readAsDataURL(file);
                });
                
                $('#questionForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    var questionType = parseInt($('#question_type').val());
                    var questionText = $('#question_text').val();
                    var questionMarks = parseInt($('#question_marks').val());
                    var submitBtn = $('#submitBtn');
                    var isEdit = editingQuestionId !== null;
                    
                    // Marks validation
                    var originalMarks = isEdit ? (parseInt($('#question_marks').data('original-marks')) || 0) : 0;
                    var base          = currentTotalMarks - originalMarks;
                    var available     = assessmentTotalMarks - base;
                    var newTotalMarks = base + questionMarks;

                    if (questionMarks <= 0) {
                        showToast('Marks must be at least 1.', 'error');
                        return;
                    }
                    if (newTotalMarks > assessmentTotalMarks) {
                        showToast('Cannot save: would exceed assessment total of ' + assessmentTotalMarks + ' marks. Only ' + available + ' mark(s) available.', 'error');
                        updateMarksState();
                        return;
                    }
                    
                    var formData = {
                        formName: isEdit ? 'update_question' : 'create_question',
                        question_type: questionType,
                        question_text: questionText,
                        question_marks: questionMarks
                    };
                    
                    if(isEdit) {
                        formData.question_id = editingQuestionId;
                    }
                    formData.assessment_id = assessmentId;
                    
                    if(questionType == 1) {
                        var singleCorrect = $('input[name="correct_option"]:checked').val();
                        if(!singleCorrect) {
                            showToast('Please select the correct answer', 'error');
                            return;
                        }
                        formData.option_1 = $('#option_1').val();
                        formData.option_2 = $('#option_2').val();
                        formData.option_3 = $('#option_3').val();
                        formData.option_4 = $('#option_4').val();
                        if(!formData.option_1 || !formData.option_2 || !formData.option_3 || !formData.option_4) {
                            showToast('Please fill all 4 options', 'error');
                            return;
                        }
                        formData.correct_option = singleCorrect;
                    } else if(questionType == 2) {
                        var tfCorrect = $('input[name="correct_option_tf"]:checked').val();
                        // print(tfCorrect);
                        if(!tfCorrect) {
                            showToast('Please select True or False', 'error');
                            return;
                        }
                        formData.correct_option = tfCorrect;
                    } else if(questionType == 3) {
                        var multiCorrect = $('input[name="multi_correct[]"]:checked').map(function(){ return $(this).val(); }).get();
                        if(multiCorrect.length === 0) {
                            showToast('Please select at least one correct option', 'error');
                            return;
                        }
                        formData.option_1 = $('#option_multi_1').val();
                        formData.option_2 = $('#option_multi_2').val();
                        formData.option_3 = $('#option_multi_3').val();
                        formData.option_4 = $('#option_multi_4').val();
                        formData.correct_options_multi = JSON.stringify(multiCorrect);
                    } else if(questionType == 4) {
                        var textAns = $('#text_answer').val().trim();
                        if(!textAns) {
                            showToast('Please enter expected answer', 'error');
                            return;
                        }
                        formData.correct_option = textAns;
                    } else if(questionType == 5) {
                        var imgCorrect = $('input[name="img_correct_option"]:checked').val();
                        if(!imgCorrect) { showToast('Please select the correct answer', 'error'); return; }
                        var o1 = $('#option_img_1').val().trim(), o2 = $('#option_img_2').val().trim();
                        var o3 = $('#option_img_3').val().trim(), o4 = $('#option_img_4').val().trim();
                        if(!o1 || !o2 || !o3 || !o4) { showToast('Please fill all 4 options', 'error'); return; }
                        var imageFile = $('#question_image')[0].files[0];
                        var existingImg = $('#current_image_path').val();
                        if(!imageFile && !(isEdit && existingImg)) {
                            showToast('Please upload an image for this question', 'error'); return;
                        }
                        formData.option_1 = o1; formData.option_2 = o2;
                        formData.option_3 = o3; formData.option_4 = o4;
                        formData.correct_option = imgCorrect;

                        var fd = new FormData();
                        $.each(formData, function(k, v) { fd.append(k, v); });
                        if(imageFile) { fd.append('question_image', imageFile); }
                        else          { fd.append('existing_image', existingImg); }

                        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + (isEdit ? 'Updating...' : 'Adding...'));
                        $.ajax({
                            url: 'includes/datacontrol.php', type: 'POST',
                            data: fd, processData: false, contentType: false, timeout: 30000,
                            success: function(response) {
                                if(response.trim() == '1') {
                                    showToast(isEdit ? 'Question updated!' : 'Question added!', 'success');
                                    resetForm();
                                    setTimeout(function(){ window.location.reload(); }, 1000);
                                } else {
                                    submitBtn.prop('disabled', false).html(isEdit ? 'Update Question' : 'Add Question');
                                    var msg = response.trim() || 'Unknown server error';
                                    console.error('[Image Q save] Server response:', response);
                                    showToast('Error: ' + msg, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                submitBtn.prop('disabled', false).html(isEdit ? 'Update Question' : 'Add Question');
                                console.error('[Image Q save] AJAX error:', status, error, xhr.responseText);
                                showToast('Request failed: ' + (error || status || 'Network error'), 'error');
                            }
                        });
                        return; // skip the standard AJAX below
                    }

                    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + (isEdit ? 'Updating...' : 'Adding...'));

                    $.ajax({
                        url: 'includes/datacontrol.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'text',
                        timeout: 30000,
                        success: function(response) {
                            console.log('Response:', response);
                            if(response.trim() == '1'){
                                showToast(isEdit ? 'Question updated!' : 'Question added!', 'success');
                                resetForm();
                                setTimeout(function(){
                                    window.location.reload();
                                }, 1000);
                            } else {
                                submitBtn.prop('disabled', false).html(isEdit ? 'Update Question' : 'Add Question');
                                showToast('Error: ' + response, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            submitBtn.prop('disabled', false).html(isEdit ? 'Update Question' : 'Add Question');
                            showToast('Error: ' + (error || 'Failed'), 'error');
                        }
                    });
                });
            });
        </script>
    </body>
</html>
<?php 
} else {
    header("Location: index.php");
}
?>
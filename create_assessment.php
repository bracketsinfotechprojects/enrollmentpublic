<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!='' && @$_SESSION['user_type']==1){
$editMode = false;
$assessment = null;
if(isset($_GET['id']) && intval($_GET['id']) > 0){
    $editMode = true;
    $assessment_id = intval($_GET['id']);
    $res = mysqli_query($connection, "SELECT * FROM assessment WHERE assessment_id = $assessment_id LIMIT 1");
    if($res && mysqli_num_rows($res) > 0){
        $assessment = mysqli_fetch_assoc($res);
    }
}
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?php echo $editMode ? 'Edit Assessment' : 'Create Assessment'; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <style>
            :root {
                --theme-primary: #0d6efd;
                --theme-primary-hover: #0b5ed7;
            }
            .asterisk { color: var(--theme-primary); }
            .form-label { font-weight: 500; }
            .btn-primary { 
                background-color: var(--theme-primary); 
                border-color: var(--theme-primary); 
            }
            .btn-primary:hover { 
                background-color: var(--theme-primary-hover); 
                border-color: var(--theme-primary-hover); 
            }
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
                                    <h4 class="mb-sm-0"><?php echo $editMode ? 'Edit Assessment' : 'Create New Assessment'; ?></h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Assessment</a></li>
                                            <li class="breadcrumb-item active"><?php echo $editMode ? 'Edit Assessment' : 'Create Assessment'; ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-body">
                                        
                                        <form id="assessmentForm" method="post">
                                            <?php if($editMode): ?>
                                            <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
                                            <?php endif; ?>
                                            <div class="mb-3">
                                                <label class="form-label">Assessment Name <span class="asterisk">*</span></label>
                                                <input type="text" class="form-control" name="assessment_name" id="assessment_name" value="<?php echo $editMode ? htmlspecialchars($assessment['assessment_name']) : ''; ?>" required>
                                            </div>
                                             <div class="row">
<div class="col-md-6 mb-3">
                                                      <label class="form-label">Marks <span class="asterisk">*</span></label>
                                                      <input type="text" class="form-control" name="marks" id="marks" value="<?php echo $editMode ? htmlspecialchars($assessment['marks']) : ''; ?>" placeholder="e.g., 100" required>
                                                  </div>
                                                  <div class="col-md-6 mb-3">
                                                      <label class="form-label">Passing Marks <span class="asterisk">*</span></label>
                                                      <input type="text" class="form-control" name="passing_marks" id="passing_marks" value="<?php echo $editMode ? htmlspecialchars($assessment['passing_marks']) : ''; ?>" placeholder="e.g., 40" required>
                                                  </div>
                                                 <div class="col-md-6 mb-3">
                                                     <label class="form-label">Duration(in minutes) <span class="asterisk">*</span></label>
                                                     <input type="text" class="form-control" name="duration" id="duration" value="<?php echo $editMode ? htmlspecialchars($assessment['duration']) : ''; ?>" placeholder="e.g., 120" required>
                                                 </div>
                                             </div>
                                             <div class="mb-3">
                                                 <label class="form-label">Status</label>
                                                 <select class="form-select" name="status" id="status">
                                                     <option value="0" <?php echo $editMode && $assessment['status'] == '0' ? 'selected' : ''; ?>>Published</option>
                                                     <option value="1" <?php echo $editMode && $assessment['status'] == '1' ? 'selected' : ''; ?>>Draft</option>
                                                 </select>
                                                 <small class="text-muted">Only one assessment can be published at a time. Publishing will set all others to draft.</small>
                                             </div>
                                                <button type="submit" class="btn btn-primary" id="submitBtn"><?php echo $editMode ? 'Update Assessment' : 'Create Assessment'; ?></button>
                                                <a href="assessment_list.php" class="btn btn-secondary">Cancel</a>
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

        <?php include('includes/footer_includes.php'); ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
            var editMode = <?php echo $editMode ? 'true' : 'false'; ?>;
            var assessmentId = <?php echo $editMode ? $assessment_id : 'null'; ?>;
            
            $(document).ready(function() {
                $('#assessmentForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    var formData;
                    var submitBtn = $('#submitBtn');
                    
                     if(editMode){
formData = {
                              formName: 'update_assessment',
                              assessment_id: assessmentId,
                              assessment_name: $('#assessment_name').val(),
                              marks: $('#marks').val(),
                              passing_marks: $('#passing_marks').val(),
                              duration: $('#duration').val(),
                              status: $('#status').val()
                          };
                         submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');
                     } else {
formData = {
                              formName: 'create_assessment',
                              assessment_name: $('#assessment_name').val(),
                              marks: $('#marks').val(),
                              passing_marks: $('#passing_marks').val(),
                              duration: $('#duration').val(),
                              status: $('#status').val()
                          };
                         submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');
                     }

                    $.ajax({
                        url: 'includes/datacontrol.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'text',
                        timeout: 30000,
                        success: function(response) {
                            console.log('Response:', response);
                            if(response.trim() == '1'){
                                Toastify({
                                    text: editMode ? "Assessment updated successfully!" : "Assessment created successfully!",
                                    duration: 1500,
                                    close: true,
                                    backgroundColor: "#28a745"
                                }).showToast();
                                setTimeout(function(){
                                    window.location.replace('assessment_list.php');
                                }, 1500);
                            } else {
                                submitBtn.prop('disabled', false).html(editMode ? 'Update Assessment' : 'Create Assessment');
                                Toastify({
                                    text: "Error: " + response,
                                    duration: 3000,
                                    close: true,
                                    backgroundColor: "#dc3545"
                                }).showToast();
                            }
                        },
                        error: function(xhr, status, error) {
                            submitBtn.prop('disabled', false).html(editMode ? 'Update Assessment' : 'Create Assessment');
                            console.log('AJAX Error:', status, error);
                            Toastify({
                                text: "Error: " + (error || 'An error occurred'),
                                duration: 3000,
                                close: true,
                                backgroundColor: "#dc3545"
                            }).showToast();
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).html(editMode ? 'Update Assessment' : 'Create Assessment');
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
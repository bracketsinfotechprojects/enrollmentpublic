<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!='' && @$_SESSION['user_type']==1){
$assessment_id = 0;
if(isset($_GET['id'])){
    $assessment_id = intval($_GET['id']);
} elseif(isset($_GET['assessment_id'])){
    $assessment_id = intval($_GET['assessment_id']);
}
$assessment = null;
if($assessment_id > 0){
    $res = mysqli_query($connection, "SELECT * FROM assessment WHERE assessment_id = $assessment_id LIMIT 1");
    if($res && mysqli_num_rows($res) > 0){
        $assessment = mysqli_fetch_assoc($res);
    }
}
if(!$assessment){
    header("Location: assessment_list.php");
    exit;
}
$students = mysqli_query($connection, "SELECT DISTINCT cd.st_enquiry_id,
       cd.student_user_id AS st_enrol_id,
       cd.st_enquiry_id AS st_unique_id,
       e.st_name AS st_given_name,
       e.st_surname AS st_surname,
       e.st_phno,
       e.st_email
    FROM counseling_details cd
    LEFT JOIN student_enquiry e ON e.st_enquiry_id = cd.st_enquiry_id
    WHERE cd.counsil_enquiry_status = 0
      AND e.st_enquiry_status != 1
    ORDER BY e.st_name, e.st_surname");
$assigned_res = mysqli_query($connection, "SELECT student_enrol_id FROM assessment_assignments WHERE assessment_id = $assessment_id");
$assigned_ids = array();
if($assigned_res){
    while($row = mysqli_fetch_assoc($assigned_res)){
        $assigned_ids[] = $row['student_enrol_id'];
    }
}
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>Assign Students - <?php echo htmlspecialchars($assessment['assessment_name']); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
        <style>
            :root {
                --theme-primary: #0d6efd;
                --theme-primary-hover: #0b5ed7;
                --theme-primary-light: #e7f1ff;
            }
            .asterisk { color: var(--theme-primary); }
            .student-card { cursor: pointer; transition: all 0.2s; }
            .student-card:hover { background-color: #f8f9fa; }
            .student-card.selected { background-color: var(--theme-primary-light); border-color: var(--theme-primary); }
            .student-checkbox { width: 18px; height: 18px; cursor: pointer; }
            .btn-primary { background-color: var(--theme-primary); border-color: var(--theme-primary); }
            .btn-primary:hover { background-color: var(--theme-primary-hover); border-color: var(--theme-primary-hover); }
            .badge.bg-primary { background-color: var(--theme-primary) !important; }
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
                                    <h4 class="mb-sm-0">Assign Students - <?php echo htmlspecialchars($assessment['assessment_name']); ?></h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Assessment</a></li>
                                            <li class="breadcrumb-item"><a href="assessment_list.php">Assessment List</a></li>
                                            <li class="breadcrumb-item active">Assign Students</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="searchStudent" placeholder="Search counseling students...">
                                                    <button class="btn btn-primary" type="button"><i class="mdi mdi-magnify"></i></button>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <button class="btn btn-secondary me-2" id="selectAllBtn"><i class="mdi mdi-check-all me-1"></i> Select All</button>
                                                <button class="btn btn-info me-2" id="deselectAllBtn"><i class="mdi mdi-checkbox-multiple-blank-outline me-1"></i> Deselect All</button>
                                                <span class="badge bg-primary fs-6 me-2" id="selectedCount">0 selected</span>
                                            </div>
                                        </div>
                                        <div class="alert alert-info mb-3">
                                            Showing all students from counseling details. Enrollment status is displayed separately.
                                        </div>

                                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;"><input type="checkbox" id="selectAllCheckbox" class="student-checkbox"></th>
                                                        <!-- <th>Enquiry ID</th> -->
                                                        <th>Student ID</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Mobile</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentList">
                                                    <?php if($students && mysqli_num_rows($students) > 0): ?>
                                                        <?php while($student = mysqli_fetch_assoc($students)): ?>
                                                            <?php $canAssign = true; ?>
                                                            <?php $checkboxValue = $student['st_enrol_id'] > 0 ? $student['st_enrol_id'] : 'enquiry:' . $student['st_enquiry_id']; ?>
                                                            <tr class="student-row" data-id="<?php echo $student['st_enrol_id'] > 0 ? $student['st_enrol_id'] : htmlspecialchars($student['st_enquiry_id']); ?>">
                                                                <td>
                                                                    <input type="checkbox" class="student-checkbox student-select" 
                                                                           value="<?php echo htmlspecialchars($checkboxValue); ?>"
                                                                           <?php echo in_array($student['st_enrol_id'], $assigned_ids) ? 'checked' : ''; ?> >
                                                                </td>
                                                                <!-- <td><?//php echo htmlspecialchars($student['st_enquiry_id']); ?></td> -->
                                                                <td><?php echo htmlspecialchars($student['st_unique_id']); ?></td>
                                                                <td><?php echo htmlspecialchars($student['st_given_name'] . ' ' . $student['st_surname']); ?></td>
                                                                <td><?php echo htmlspecialchars($student['st_email']); ?></td>
                                                                <td><?php echo htmlspecialchars($student['st_phno']); ?></td>
                                                                <td>
                                                                    
                                                                    <?php if(in_array($student['st_enrol_id'], $assigned_ids)): ?>
                                                                        <span class="badge bg-success">Assigned</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary">Not Assigned</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">No counseling students found</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12 text-end">
                                                <button type="button" class="btn btn-success" id="assignBtn">
                                                    <i class="mdi mdi-user-plus me-1"></i> Assign Assessment
                                                </button>
                                                <a href="assessment_list.php" class="btn btn-secondary">Cancel</a>
                                            </div>
                                        </div>
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
            
            $(document).ready(function() {
                function updateSelectedCount() {
                    var count = $('.student-select:checked').length;
                    $('#selectedCount').text(count + ' selected');
                }
                
                function updateSelectAllCheckbox() {
                    var enabledCount = $('.student-select:not(:disabled)').length;
                    var checkedCount = $('.student-select:checked').length;
                    $('#selectAllCheckbox').prop('checked', enabledCount > 0 && enabledCount === checkedCount);
                }
                
                $('#searchStudent').on('keyup', function() {
                    var search = $(this).val().toLowerCase();
                    $('.student-row').each(function() {
                        var name = $(this).find('td:nth-child(4)').text().toLowerCase();
                        var email = $(this).find('td:nth-child(5)').text().toLowerCase();
                        var mobile = $(this).find('td:nth-child(6)').text().toLowerCase();
                        var id = $(this).find('td:nth-child(2)').text().toLowerCase();
                        if(name.indexOf(search) >= 0 || email.indexOf(search) >= 0 || mobile.indexOf(search) >= 0 || id.indexOf(search) >= 0) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });
                
                $('#selectAllCheckbox').on('change', function() {
                    var checked = $(this).prop('checked');
                    $('.student-select:not(:disabled)').prop('checked', checked);
                    updateSelectedCount();
                });
                
                $('#selectAllBtn').on('click', function() {
                    $('.student-select:not(:disabled)').prop('checked', true);
                    updateSelectAllCheckbox();
                    updateSelectedCount();
                });
                
                $('#deselectAllBtn').on('click', function() {
                    $('.student-select').prop('checked', false);
                    $('#selectAllCheckbox').prop('checked', false);
                    updateSelectedCount();
                });
                
                $('.student-select').on('change', function() {
                    updateSelectedCount();
                    updateSelectAllCheckbox();
                });
                
                $('#assignBtn').on('click', function() {
                    var selectedStudents = [];
                    $('.student-select:checked').each(function() {
                        selectedStudents.push($(this).val());
                    });
                    
                    if(selectedStudents.length === 0) {
                        Toastify({
                            text: "Please select at least one student",
                            duration: 3000,
                            close: true,
                            backgroundColor: "#dc3545"
                        }).showToast();
                        return;
                    }
                    
                    var btn = $('#assignBtn');
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Assigning...');
                    
                    $.ajax({
                        url: 'includes/datacontrol.php',
                        type: 'POST',
                        data: {
                            formName: 'assign_assessment',
                            assessment_id: assessmentId,
                            student_ids: JSON.stringify(selectedStudents)
                        },
                        success: function(response) {
                            response = response.trim();
                            if(response === '1') {
                                Toastify({
                                    text: "Assessment assigned successfully!",
                                    duration: 3000,
                                    close: true,
                                    backgroundColor: "#28a745"
                                }).showToast();
                                setTimeout(function() {
                                    window.location.href = 'assessment_list.php';
                                }, 1000);
                            } else {
                                Toastify({
                                    text: "Error: " + response,
                                    duration: 3000,
                                    close: true,
                                    backgroundColor: "#dc3545"
                                }).showToast();
                            }
                        },
                        error: function() {
                            Toastify({
                                text: "An error occurred. Please try again.",
                                duration: 3000,
                                close: true,
                                backgroundColor: "#dc3545"
                            }).showToast();
                        },
                        complete: function() {
                            btn.prop('disabled', false).html('<i class="mdi mdi-user-plus me-1"></i> Assign Assessment');
                        }
                    });
                });
                
                updateSelectedCount();
                updateSelectAllCheckbox();
            });
        </script>
    </body>
</html>
<?php 
} else {
    header("Location: index.php");
}
?>
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

// Pre-fetch student details
$student_info = [];
$infoRes = mysqli_query($connection,
    "SELECT se.st_name, se.st_surname, se.st_email, se.st_phno, cd.st_enquiry_id
     FROM counseling_details cd
     LEFT JOIN student_enquiry se ON se.st_enquiry_id = cd.st_enquiry_id
     WHERE cd.student_user_id = $student_user_id LIMIT 1"
);
if ($infoRes && mysqli_num_rows($infoRes) > 0) {
    $student_info = mysqli_fetch_assoc($infoRes);
}

$enquiry_id_val = $student_info['st_enquiry_id'] ?? '';

// Check already submitted in enrolment_form_new table
$already_submitted   = false;
$existing_student_id = '';
$enrolment_completed = false;
if ($enquiry_id_val !== '') {
    $esc_enq = mysqli_real_escape_string($connection, $enquiry_id_val);
    $existing = mysqli_query($connection,
        "SELECT office_student_id, status FROM enrolment_form_new WHERE enquiry_id = '$esc_enq' LIMIT 1"
    );
    if ($existing && mysqli_num_rows($existing) > 0) {
        $existing_row        = mysqli_fetch_assoc($existing);
        $already_submitted   = true;
        $existing_student_id = $existing_row['office_student_id'] ?? '';
        $enrolment_completed = ($existing_row['status'] ?? '') === 'complete';
    }
}

$edit_mode = isset($_GET['edit']) && $_GET['edit'] == '1' && !$enrolment_completed;
$ef = [];
$enrolment_id_edit = 0;

// In edit mode, fetch the existing record by student email
if ($edit_mode) {
    $su_res = mysqli_query($connection, "SELECT email FROM student_users WHERE id = $student_user_id LIMIT 1");
    $st_email_edit = ($su_res && mysqli_num_rows($su_res) > 0) ? mysqli_fetch_assoc($su_res)['email'] : '';
    if ($st_email_edit !== '') {
        $esc_e = mysqli_real_escape_string($connection, $st_email_edit);
        $ef_res = mysqli_query($connection,
            "SELECT * FROM enrolment_form_new WHERE email_address = '$esc_e' ORDER BY id DESC LIMIT 1"
        );
        if ($ef_res && mysqli_num_rows($ef_res) > 0) {
            $ef = mysqli_fetch_assoc($ef_res);
            $enrolment_id_edit = intval($ef['id']);
        }
    }
}

$courses = mysqli_query($connection, "SELECT * FROM courses WHERE course_status != 1");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enrolment Form – National College Australia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css">
    <style>
        .section-header {
            background-color: #d4edda;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 6px 10px;
            border: 1px solid #aed4b5;
            margin-bottom: 0;
        }
        .section-body {
            border: 1px solid #aed4b5;
            border-top: none;
            padding: 12px;
            margin-bottom: 16px;
            background: #fff;
        }
        .form-label { font-weight: 600; font-size: 0.875rem; margin-bottom: 3px; }
        .check-row label { font-weight: normal; margin-right: 16px; font-size: 0.875rem; }
        .note-text { font-size: 0.8rem; color: #555; font-style: italic; }
        .privacy-text { font-size: 0.82rem; line-height: 1.6; }
        .candidate-declaration li { margin-bottom: 6px; font-size: 0.875rem; }
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
                            <h4 class="mb-sm-0">Enrolment Form</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item active">Enrolment Form</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($already_submitted && !$edit_mode): ?>
                <div class="alert alert-success d-flex align-items-center">
                    <i class="ti ti-circle-check fs-4 me-2"></i>
                    <div>
                        You have already submitted your enrolment form.
                        <?php if ($existing_student_id): ?>
                        Your Student ID is: <strong><?php echo htmlspecialchars($existing_student_id); ?></strong>
                        <?php endif; ?>
                        <?php if (!$enrolment_completed): ?>
                        <a href="student_enrolment_form.php?edit=1" class="btn btn-sm btn-outline-primary ms-3">
                            <i class="ti ti-edit me-1"></i>Edit Enrolment
                        </a>
                        <?php else: ?>
                        <span class="badge bg-success ms-3 fs-6"><i class="ti ti-circle-check me-1"></i>Enrolment Completed</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>

                <?php if ($edit_mode): ?>
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <i class="ti ti-edit fs-4 me-2"></i>
                    <div>You are editing your previously submitted enrolment form. Changes will update your existing record.</div>
                </div>
                <?php endif; ?>

                <form id="student_enrolment_form">
                    <input type="hidden" name="form_source" value="student_portal">
                    <input type="hidden" name="enquiry_id" value="<?php echo htmlspecialchars($enquiry_id_val); ?>">
                    <input type="hidden" name="rto_name" value="National College Australia">
                    <input type="hidden" name="enrolment_id" value="<?php echo $enrolment_id_edit; ?>">
                    <input type="hidden" name="student_user_id" value="<?php echo htmlspecialchars($student_user_id ?? ''); ?>">

                    <!-- Form Header -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="fw-bold mb-1">ENROLMENT FORM</h3>
                                    <div class="mb-2">
                                        <span class="fw-bold">Qualification Code &amp; Title:</span>
                                        <input type="text" class="form-control d-inline-block ms-2" style="width:420px;" name="qualification_code_title" placeholder="e.g. CHC33015 Certificate III in Individual Support">
                                    </div>
                                    <small class="text-muted">National College Australia_RTO:91000 &nbsp;|&nbsp; Enrolment Form_V1.0_August 2025</small>
                                </div>
                                <img src="assets/images/logo-dark.webp" alt="NCA" height="60" onerror="this.style.display='none'">
                            </div>
                        </div>
                    </div>

                    <!-- STUDENT DETAILS -->
                    <div class="section-header">STUDENT DETAILS:</div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <p class="note-text mb-1"><strong>Unique Student Identifier (USI):</strong> <span class="text-danger">*</span> USI is a <u>10 Digit</u> unique identification. You must write your name exactly as written in your personal identity document.</p>
                                <input type="text" class="form-control" name="usi_id" maxlength="10" placeholder="Enter 10-digit USI" required>
                                <div class="invalid-feedback">Please enter your USI.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="given_name" value="<?php echo htmlspecialchars($student_info['st_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="surname" value="<?php echo htmlspecialchars($student_info['st_surname'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="note-text mb-2">NCA does not recommend enrolling students under 18 years of age. All information is collected as per Student Identifiers Act 2014 &amp; Privacy Act 1988.</p>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="age_declaration_18" id="age_declaration_18" value="1">
                                    <label class="form-check-label" for="age_declaration_18"><strong>Age Declaration:</strong> I am at least 18 years of age</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="dob" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="gender_check" value="1"> Male</label>
                                    <label><input type="radio" name="gender_check" value="2"> Female</label>
                                    <label><input type="radio" name="gender_check" value="3"> Other</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ADDRESS DETAILS -->
                    <div class="section-header">Address Details</div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">House and Street Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="street_details" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Post Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="post_code" maxlength="6" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Suburb <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="sub_urb" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">State <span class="text-danger">*</span></label>
                                <select class="form-control" name="stu_state" required>
                                    <option value="">-- Select --</option>
                                    <option>NSW</option><option>VIC</option><option>QLD</option>
                                    <option>WA</option><option>SA</option><option>TAS</option>
                                    <option>ACT</option><option>NT</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Postal Address (If Any)</label>
                                <div class="check-row mb-2">
                                    <label><input type="radio" name="postal_same_as_above" value="1" class="postal_same"> Same as Above</label>
                                    <label><input type="radio" name="postal_same_as_above" value="0" class="postal_same"> Enter postal address below</label>
                                </div>
                                <textarea class="form-control postal_address_field" name="postal_address" rows="2" placeholder="Postal address (if different)" style="display:none;"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Are you able to read, write, and understand English? <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="english_read_write" value="1"> Yes</label>
                                    <label><input type="radio" name="english_read_write" value="2"> No</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="mobile_num" value="<?php echo htmlspecialchars($student_info['st_phno'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Work Phone</label>
                                <input type="text" class="form-control" name="work_phone">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Home Phone</label>
                                <input type="text" class="form-control" name="home_phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="emailAddress" value="<?php echo htmlspecialchars($student_info['st_email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact (Name &amp; Relation)</label>
                                <input type="text" class="form-control mb-1" name="em_full_name" placeholder="Full Name">
                                <input type="text" class="form-control" name="em_relation" placeholder="Relationship">
                            </div>
                            <div class="col-md-6 mb-0">
                                <label class="form-label">Emergency Mobile Number</label>
                                <input type="text" class="form-control" name="em_mobile_num">
                            </div>
                        </div>
                    </div>

                    <!-- LANGUAGE AND CULTURAL DIVERSITY -->
                    <div class="section-header">Language and Cultural Diversity</div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country of Birth <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="birth_country" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City of Birth</label>
                                <input type="text" class="form-control" name="city_of_birth">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Do you speak a language other than English? <span class="text-danger">*</span></label>
                                <div class="check-row mb-2">
                                    <label><input type="radio" name="lan_spoken" value="2"> No</label>
                                    <label><input type="radio" name="lan_spoken" value="1"> Yes</label>
                                </div>
                                <div class="lan_spoken_other_wrap" style="display:none;">
                                    <label class="form-label">Language Spoken (at Home)</label>
                                    <input type="text" class="form-control" name="lan_spoken_other" placeholder="Specify language">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Are you Aboriginal and/or Torres Strait Islander? <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="origin" value="1"> No</label>
                                    <label><input type="radio" name="origin" value="2"> Yes, Aboriginal</label>
                                    <label><input type="radio" name="origin" value="3"> Yes, Torres Strait Islander</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DISABILITY -->
                    <div class="section-header">Disability</div>
                    <div class="section-body">
                        <p class="mb-2" style="font-size:0.875rem;">Do you live with any disability, impairment, or long-term condition that may affect your participation in the course?</p>
                        <div class="check-row mb-2">
                            <label><input type="radio" name="disability" value="1" class="disability_opt"> Yes (if yes, tick relevant)</label>
                            <label><input type="radio" name="disability" value="2" class="disability_opt"> No</label>
                        </div>
                        <div class="disability_types_wrap" style="display:none;">
                            <div class="row">
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="0"><label class="form-check-label">Hearing/Deaf</label></div></div>
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="1"><label class="form-check-label">Physical</label></div></div>
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="2"><label class="form-check-label">Intellectual</label></div></div>
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="3"><label class="form-check-label">Medical Condition</label></div></div>
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="4"><label class="form-check-label">Mental Illness</label></div></div>
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="5"><label class="form-check-label">Acquired brain impairment</label></div></div>
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="6"><label class="form-check-label">Learning</label></div></div>
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="7"><label class="form-check-label">Vision</label></div></div>
                                <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="st_disability_type[]" value="8" id="dis_other_chk"><label class="form-check-label">Other:</label></div></div>
                                <div class="col-md-9 disability_other_wrap" style="display:none;">
                                    <input type="text" class="form-control" name="disability_type_other" placeholder="Please specify">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EDUCATION AND TRAINING -->
                    <div class="section-header">Education and Training Details</div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-12 mb-1">
                                <label class="form-label">What is your highest school level COMPLETED? (tick one only) <span class="text-danger">*</span></label>
                                <p class="note-text mb-2">The highest school level completed refers to the highest level actually completed, not the level currently being undertaken.</p>
                                <div class="row check-row">
                                    <div class="col-md-4"><label><input type="radio" name="highest_school" value="1"> Year 12 or equivalent</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="highest_school" value="3"> Year 10 or equivalent</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="highest_school" value="5"> Year 8 or below</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="highest_school" value="2"> Year 11 or equivalent</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="highest_school" value="4"> Year 9 or equivalent</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="highest_school" value="6"> Never attended school</label></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3 mt-2">
                                <label class="form-label">Are you still enrolled in secondary or senior secondary education? <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="sec_school" value="1"> Yes</label>
                                    <label><input type="radio" name="sec_school" value="2"> No</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3 mt-2">
                                <label class="form-label">In which YEAR did you complete the above school level?</label>
                                <input type="text" class="form-control" name="year_completed_school" placeholder="e.g. 2020" maxlength="4">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Mode of Delivery <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="mode_delivery" value="Classroom"> Classroom</label>
                                    <label><input type="radio" name="mode_delivery" value="Online"> Online (Virtual)</label>
                                    <label><input type="radio" name="mode_delivery" value="Blended"> Blended</label>
                                    <label><input type="radio" name="mode_delivery" value="Workplace"> Workplace Based</label>
                                </div>
                            </div>
                            <div class="col-12 mb-0">
                                <label class="form-label">Choose A Qualification <span class="text-danger">*</span></label>
                                <select name="courses" id="courses" class="form-control selectpicker" data-live-search="true" title="-- Select course(s) --" multiple>
                                    <?php while ($c = mysqli_fetch_array($courses)): ?>
                                    <option value="<?php echo (int)$c['course_id']; ?>"><?php echo htmlspecialchars($c['course_sname'] . ' - ' . $c['course_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- QUALIFICATIONS COMPLETED -->
                    <div class="section-header">Have you successfully completed any of the following qualifications? (Tick most relevant)</div>
                    <div class="section-body">
                        <div class="row mb-2">
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_cert1" value="1"><label class="form-check-label">Certificate I</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_cert4" value="1"><label class="form-check-label">Certificate IV (or advanced certificate/technician)</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_bachelor" value="1"><label class="form-check-label">Bachelor's degree or Higher</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_cert2" value="1"><label class="form-check-label">Certificate II</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_diploma" value="1"><label class="form-check-label">Diploma (or associate diploma)</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_other" value="1"><label class="form-check-label">Other education (including overseas qualifications not listed above)</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_cert3" value="1"><label class="form-check-label">Certificate III (Trade Cert)</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_adv_diploma" value="1"><label class="form-check-label">Advanced Diploma/Associate Degree</label></div></div>
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_none" value="1"><label class="form-check-label">None</label></div></div>
                        </div>
                        <div class="check-row">
                            <label><input type="radio" name="qualification_attained" value="Australia"> Attained in Australia</label>
                            <label><input type="radio" name="qualification_attained" value="Equivalent"> Australian Equivalent</label>
                            <label><input type="radio" name="qualification_attained" value="International"> International</label>
                        </div>
                    </div>

                    <!-- EMPLOYMENT DETAILS -->
                    <div class="section-header">Employment Details <small class="fw-normal">(If your employment is not related to this course of study, tick most relevant)</small></div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Employment Status <span class="text-danger">*</span></label>
                                <div class="row check-row">
                                    <div class="col-md-4"><label><input type="radio" name="emp_status" value="3"> Self-employed - not employing others</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="emp_status" value="6"> Unemployed - seeking full-time work</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="emp_status" value="1"> Full-time employee</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="emp_status" value="4"> Self-employed - employing others</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="emp_status" value="7"> Unemployed - seeking part-time work</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="emp_status" value="2"> Part-time employee</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="emp_status" value="5"> Employed - unpaid worker in a family business</label></div>
                                    <div class="col-md-4"><label><input type="radio" name="emp_status" value="8"> Unemployed - not seeking employment</label></div>
                                </div>
                            </div>
                            <div class="col-12 mb-0">
                                <label class="form-label">Industry of Work <small class="text-muted fw-normal">(Refer ANZSCO codes online)</small></label>
                                <input type="text" class="form-control" name="industry_of_work" placeholder="Industry / ANZSCO code">
                            </div>
                        </div>
                    </div>

                    <!-- REASON FOR ENROLLING -->
                    <div class="section-header">Reason for Enrolling in this Course of Study</div>
                    <div class="section-body">
                        <p style="font-size:0.875rem;" class="mb-2">Of the following categories, which BEST describes your main reason for undertaking this course? <span class="text-danger">*</span></p>
                        <div class="row check-row mb-3">
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="1"> To get a job</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="2"> To get a better job or promotion</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="3"> It was a requirement for my job</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="4"> I wanted extra skills for my job</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="5"> To start my own business</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="6"> To get into another course of study</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="7"> To try for a different career</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="8"> To develop my existing business</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="9"> For personal interest or self-development</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="10"> To get skills for community/voluntary work</label></div>
                            <div class="col-md-4"><label><input type="radio" name="study_reason" value="11"> Other reasons</label></div>
                        </div>
                        <div class="study_reason_other_wrap" style="display:none;">
                            <label class="form-label">Please specify your reason</label>
                            <input type="text" class="form-control" name="study_reason_other">
                        </div>
                    </div>

                    <!-- COURSE ENROLMENT DETAILS -->
                    <div class="section-header">Course Enrolment Details: <small class="fw-normal">(See Course Outline for delivery mode and available durations)</small></div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label class="form-label">Do you want to apply for Credit Transfer (CT) / Recognise Prior Learning? <span class="text-danger">*</span></label>
                                <p class="note-text mb-2">A candidate is required to fill an additional form with details for CT/RPL application.</p>
                                <div class="check-row">
                                    <label><input type="radio" name="cred_tansf" value="1"> Yes</label>
                                    <label><input type="radio" name="cred_tansf" value="2"> No</label>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label">Which BEST describes your main reason for undertaking this course? Enter Text Below</label>
                                <textarea class="form-control" name="study_reason_text" rows="3" placeholder="Enter your reason here..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ADDITIONAL INFORMATION -->
                    <div class="section-header">Additional Information: <small class="fw-normal">(please answer all questions)</small></div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Do you have access to a computer and the internet? <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="computer_access" value="1"> Yes</label>
                                    <label><input type="radio" name="computer_access" value="2"> No</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">What level of computer literacy do you have? <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="computer_literacy" value="Excellent"> Excellent</label>
                                    <label><input type="radio" name="computer_literacy" value="Good"> Good</label>
                                    <label><input type="radio" name="computer_literacy" value="Basic"> Basic</label>
                                    <label><input type="radio" name="computer_literacy" value="Poor"> Poor</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">How do you rate your numeracy skills? <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="numeracy_skills" value="Excellent"> Excellent</label>
                                    <label><input type="radio" name="numeracy_skills" value="Good"> Good</label>
                                    <label><input type="radio" name="numeracy_skills" value="Basic"> Basic</label>
                                    <label><input type="radio" name="numeracy_skills" value="Poor"> Poor</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Do you require additional support? <span class="text-danger">*</span></label>
                                <div class="check-row">
                                    <label><input type="radio" name="additional_support" value="1"> No</label>
                                    <label><input type="radio" name="additional_support" value="2"> Yes (please specify below)</label>
                                </div>
                                <div class="additional_support_specify_wrap mt-2" style="display:none;">
                                    <input type="text" class="form-control" name="additional_support_specify" placeholder="Please specify">
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning py-2 mt-2 mb-0" style="font-size:0.82rem;">
                            <strong>IMPORTANT NOTE:</strong> National College Australia, RTO 91000 will provide access to additional support services where required. However, where a student is unable to meet minimum course entry requirements such as LLN Skills and/or Physical Fitness requirements, the college reserves the right to defer/terminate enrolment.
                        </div>
                    </div>

                    <!-- UNIQUE STUDENT IDENTIFIER -->
                    <div class="section-header">UNIQUE STUDENT IDENTIFIER</div>
                    <div class="section-body">
                        <p class="privacy-text">From 1 January 2015, National College Australia, RTO 91000 can be prevented from issuing you with a nationally recognised VET qualification if you do not have a USI. Apply at <a href="https://www.usi.gov.au/your-usi/create-usi" target="_blank">https://www.usi.gov.au/your-usi/create-usi</a>.</p>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="usi_declaration" id="usi_declaration" value="1" required>
                            <label class="form-check-label" for="usi_declaration">I understand that my results will be uploaded into USI records as per company policy. <strong>Yes, I understand and declare.</strong></label>
                        </div>
                    </div>

                    <!-- PRIVACY NOTICE -->
                    <div class="section-header">PRIVACY NOTICE</div>
                    <div class="section-body">
                        <p class="privacy-text">The NCVER will collect, hold, use, and disclose your personal information in accordance with the Privacy Act 1988 (Cth) and the NCVER Act.</p>
                        <p class="privacy-text"><strong>Why we collect your personal information:</strong> As a registered training organization (RTO), we collect your personal information so we can process and manage your enrolment in a vocational education and training (VET) course.</p>
                        <p class="privacy-text"><strong>How we disclose your personal information:</strong> We are required by law to disclose your information to the National VET Data Collection kept by NCVER. See <a href="https://www.ncver.edu.au/privacy" target="_blank">NCVER Privacy Policy</a>.</p>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="privacy_declaration" id="privacy_declaration" value="1" required>
                            <label class="form-check-label" for="privacy_declaration">I have read and understand the Privacy Notice. <strong>Yes, I understand and declare.</strong></label>
                        </div>
                    </div>

                    <!-- REFUND POLICY -->
                    <div class="section-header">REFUND POLICY</div>
                    <div class="section-body">
                        <p class="privacy-text">Details of the RTO Fees and Charges / Refund Policy and Student Handbook can be found on our website.</p>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="refund_declaration" id="refund_declaration" value="1" required>
                            <label class="form-check-label" for="refund_declaration">I have read the Refund Policy / Fees and Charges. <strong>Yes, I understand and declare.</strong></label>
                        </div>
                    </div>

                    <!-- CANDIDATE DECLARATION -->
                    <div class="section-header">Candidate Declaration:</div>
                    <div class="section-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="candidate_declaration" id="candidate_declaration" value="1" required>
                            <label class="form-check-label fw-bold" for="candidate_declaration">Yes, I understand and declare.</label>
                        </div>
                        <ul class="candidate-declaration mb-3">
                            <li>Have read the student Handbook including the Privacy Policy, Fee Administration and Refund Policy, and other policies and procedures prior to enrolling.</li>
                            <li>I agree to allow National College Australia to collect Language, Literacy, Numeracy test, progression, assessment status, and other course information on a periodic basis.</li>
                            <li>Give my consent to National College Australia to release my name, date of birth, contact details and statistical information, including my USI, to the relevant Federal government bodies.</li>
                            <li>Agree to participate in all mandatory course requirements satisfactorily which include assessments, work placement, practical workshops and be deemed competent before release of a final certificate.</li>
                            <li>May receive a student survey which may be run by a government department or an NCVER employee, agent, or third-party contractor.</li>
                            <li>By consent, Photographs may be requested during work placement or practical demonstrations for the purpose of administration of VET.</li>
                            <li>Confirm all the details provided in this form are true and are presented with the intention of attaining a qualification in accordance with the law, Privacy and NCVER ACT.</li>
                            <li>Assure that I have been informed about the training, assessment and support services to be provided and on my rights and obligations as a student at National College Australia.</li>
                        </ul>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name of the Candidate <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="candidate_full_name" value="<?php echo htmlspecialchars(trim(($student_info['st_name'] ?? '') . ' ' . ($student_info['st_surname'] ?? ''))); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="candidate_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Signature of the Candidate <small class="text-muted">(type full name)</small> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="candidate_signature" placeholder="Full name as signature" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Signature Image <span class="text-danger">*</span></label>
                                <?php if ($edit_mode && !empty($ef['signature_image'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">Current signature:</small>
                                    <img src="uploads/<?php echo htmlspecialchars($ef['signature_image']); ?>"
                                         alt="Signature"
                                         class="img-thumbnail"
                                         style="max-height:80px;"
                                         onerror="this.style.display='none'">
                                </div>
                                <input type="file" class="form-control" id="signature_image" name="signature_image" accept="image/jpeg,image/png,image/gif">
                                <small class="text-muted">Upload a new image to replace the existing one.</small>
                                <?php else: ?>
                                <input type="file" class="form-control" id="signature_image" name="signature_image" accept="image/jpeg,image/png,image/gif" required>
                                <?php endif; ?>
                                <div id="sig_img_error" class="text-danger mt-1" style="font-size:0.82rem;display:none;"></div>
                                <small class="text-muted d-block mt-1">
                                    Accepted: JPG, PNG, GIF &nbsp;|&nbsp; Max size: 500 KB &nbsp;|&nbsp; Dimensions: 300–800 px wide, 100–300 px tall
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- PHOTO UPLOAD -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="col-md-6">
                                <label class="form-label">Photo Upload</label>
                                <?php
                                $existing_photos = [];
                                if ($edit_mode && !empty($ef['photo_paths'])) {
                                    $decoded = json_decode($ef['photo_paths'], true);
                                    if (is_array($decoded)) $existing_photos = $decoded;
                                }
                                ?>
                                <?php if (!empty($existing_photos)): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">Previously uploaded photos:</small>
                                    <div class="d-flex flex-wrap gap-2" id="existing_photos_preview">
                                        <?php foreach ($existing_photos as $photo): ?>
                                        <div class="position-relative" style="width:90px;">
                                            <img src="uploads/<?php echo htmlspecialchars($photo); ?>"
                                                 alt="Photo"
                                                 class="img-thumbnail"
                                                 style="width:90px;height:90px;object-fit:cover;"
                                                 onerror="this.closest('.position-relative').style.display='none'">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="photo_upload" name="photo_upload[]" accept="image/*" multiple>
                                <?php if (!empty($existing_photos)): ?>
                                <small class="text-muted">Upload new photos to replace the existing ones, or leave empty to keep them.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-lg" id="enrolment_submit_btn">
                                <?php echo $edit_mode ? 'Update Enrolment Form' : 'Submit Enrolment Form'; ?>
                            </button>
                            <span class="ms-3" id="submit_status"></span>
                        </div>
                    </div>

                </form>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>
<script>
var isEditMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
<?php if ($edit_mode && !empty($ef)): ?>
var existingData = <?php echo json_encode($ef); ?>;
<?php endif; ?>

$(function(){
    $('.selectpicker').selectpicker('refresh');

    <?php if ($edit_mode && !empty($ef)): ?>
    // Pre-fill all fields from existing record
    $.each(existingData, function(key, val){
        if (val === null || val === '') return;
        var $el = $('[name="' + key + '"]');
        if ($el.is('input[type="text"], input[type="date"], input[type="email"], input[type="number"], textarea')) {
            $el.val(val);
        } else if ($el.is('select:not(.selectpicker)')) {
            $el.val(val);
        }
    });
    // Radio buttons
    $('input[type="radio"]').each(function(){
        var key = $(this).attr('name');
        if (existingData[key] !== undefined && existingData[key] !== null && $(this).val() == existingData[key]) {
            $(this).prop('checked', true);
        }
    });
    // Checkboxes (single value, not array)
    $('input[type="checkbox"]:not([name$="[]"])').each(function(){
        var key = $(this).attr('name');
        if (existingData[key] !== undefined) $(this).prop('checked', parseInt(existingData[key]) === 1);
    });
    // Disability types (comma-separated)
    if (existingData.st_disability_type) {
        var disTypes = String(existingData.st_disability_type).split(',');
        $('input[name="st_disability_type[]"]').each(function(){
            if (disTypes.indexOf($(this).val()) !== -1) $(this).prop('checked', true);
        });
    }
    // Courses multi-select
    try {
        var cArr = existingData.courses ? (typeof existingData.courses === 'string' ? JSON.parse(existingData.courses) : existingData.courses) : [];
        if (cArr.length) { $('#courses').selectpicker('val', cArr.map(String)); }
    } catch(e) {}
    // Show/hide dependent sections
    if (existingData.postal_same_as_above == 0) $('.postal_address_field').show();
    if (existingData.lan_spoken == 1) $('.lan_spoken_other_wrap').show();
    if (existingData.disability == 1) $('.disability_types_wrap').show();
    if (existingData.disability_type_other) { $('#dis_other_chk').prop('checked', true); $('.disability_other_wrap').show(); }
    if (existingData.study_reason == 11) $('.study_reason_other_wrap').show();
    if (existingData.additional_support == 2) $('.additional_support_specify_wrap').show();
    <?php endif; ?>

    $('.postal_same').on('change', function(){
        $('.postal_address_field').toggle($(this).val() === '0');
    });
    $('input[name="lan_spoken"]').on('change', function(){
        $('.lan_spoken_other_wrap').toggle($(this).val() === '1');
    });
    $('.disability_opt').on('change', function(){
        $('.disability_types_wrap').toggle($(this).val() === '1');
    });
    $('#dis_other_chk').on('change', function(){
        $('.disability_other_wrap').toggle(this.checked);
    });
    $('input[name="study_reason"]').on('change', function(){
        $('.study_reason_other_wrap').toggle($(this).val() === '11');
    });
    $('input[name="additional_support"]').on('change', function(){
        $('.additional_support_specify_wrap').toggle($(this).val() === '2');
    });

    // Email validation
    $('input[name="emailAddress"]').on('input', function(){
        $(this).removeClass('is-invalid')[0].setCustomValidity('');
    });

    // Validate signature image dimensions client-side
    function validateSignatureImage(file) {
        return new Promise(function(resolve, reject) {
            var SIG_MAX_BYTES = 500 * 1024;
            var SIG_MIN_W = 300, SIG_MAX_W = 800;
            var SIG_MIN_H = 100, SIG_MAX_H = 300;
            var allowed   = ['image/jpeg', 'image/png', 'image/gif'];

            if (!file) { resolve(); return; }

            if (allowed.indexOf(file.type) === -1) {
                reject('Signature image must be JPG, PNG, or GIF.'); return;
            }
            if (file.size > SIG_MAX_BYTES) {
                reject('Signature image must be 500 KB or smaller (current: ' + Math.round(file.size/1024) + ' KB).'); return;
            }

            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function() {
                URL.revokeObjectURL(url);
                if (img.width < SIG_MIN_W || img.width > SIG_MAX_W) {
                    reject('Signature image width must be between ' + SIG_MIN_W + ' and ' + SIG_MAX_W + ' px (current: ' + img.width + ' px).'); return;
                }
                if (img.height < SIG_MIN_H || img.height > SIG_MAX_H) {
                    reject('Signature image height must be between ' + SIG_MIN_H + ' and ' + SIG_MAX_H + ' px (current: ' + img.height + ' px).'); return;
                }
                resolve();
            };
            img.onerror = function() { URL.revokeObjectURL(url); reject('Could not read the signature image. Please try another file.'); };
            img.src = url;
        });
    }

    $('#student_enrolment_form').on('submit', function(e){
        e.preventDefault();
        var $form   = $(this);
        var $btn    = $('#enrolment_submit_btn');
        var $status = $('#submit_status');
        var $email  = $('input[name="emailAddress"]');
        var $sigErr = $('#sig_img_error');

        $email.removeClass('is-invalid');
        $sigErr.hide().text('');

        var emailVal = $email.val().trim();
        if (!emailVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
            $email.addClass('is-invalid');
            $email[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        var sigFile = $('#signature_image')[0].files[0];

        $btn.prop('disabled', true).text('Submitting…');
        $status.text('').removeClass('text-danger text-success');

        validateSignatureImage(sigFile).then(function() {
            var formData = new FormData($form[0]);
            formData.append('formName', isEditMode ? 'update_enrolment_form_new' : 'save_enrolment_form_new');

            var details = {};
            $form.find('input, select, textarea').each(function(){
                var $el = $(this), name = $el.attr('name');
                if (!name || name === 'photo_upload[]' || name === 'signature_image') return;
                if ($el.attr('type') === 'file') return;
                if ($el.attr('type') === 'radio'){
                    if ($el.is(':checked')) details[name] = $el.val();
                } else if ($el.attr('type') === 'checkbox'){
                    if (name.indexOf('[]') !== -1){
                        var base = name.replace('[]','');
                        if (!details[base]) details[base] = [];
                        if ($el.is(':checked')) details[base].push($el.val());
                    } else {
                        details[name] = $el.is(':checked') ? 1 : 0;
                    }
                } else {
                    details[name] = $el.val();
                }
            });
            details.courses = $('#courses').val() || [];
            formData.append('details', JSON.stringify(details));

            var files = $('#photo_upload')[0].files;
            for (var i = 0; i < files.length; i++) formData.append('image[]', files[i]);

            if (sigFile) formData.append('signature_image', sigFile);

            return formData;
        }).then(function(formData) {
            $.ajax({
                url: 'includes/datacontrol',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res){
                    try {
                        var data = typeof res === 'string' ? JSON.parse(res) : res;
                        if (data.success) {
                            if (isEditMode) {
                                window.location.href = 'student_queries.php';
                            } else {
                                window.location.href = 'enrolment_status.php';
                            }
                        } else {
                            $status.text(data.message || 'Error saving enrolment.').addClass('text-danger');
                            $btn.prop('disabled', false).text(isEditMode ? 'Update Enrolment Form' : 'Submit Enrolment Form');
                        }
                    } catch(err) {
                        $status.text(isEditMode ? 'Updated.' : 'Submitted.').addClass('text-success');
                        $btn.prop('disabled', false).text(isEditMode ? 'Update Enrolment Form' : 'Submit Enrolment Form');
                    }
                },
                error: function(xhr){
                    $status.text('Error: ' + (xhr.responseText || 'Request failed.')).addClass('text-danger');
                    $btn.prop('disabled', false).text(isEditMode ? 'Update Enrolment Form' : 'Submit Enrolment Form');
                }
            });
        }).catch(function(errMsg) {
            $sigErr.text(errMsg).show();
            $('#signature_image')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            $btn.prop('disabled', false).text(isEditMode ? 'Update Enrolment Form' : 'Submit Enrolment Form');
        });
    });
});
</script>
</body>
</html>

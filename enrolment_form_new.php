<?php
include('includes/dbconnect.php');
session_start();
if(@$_SESSION['user_type'] == ''){
    header('Location: index.php');
    exit;
}
$courses = mysqli_query($connection, "SELECT * FROM courses WHERE course_status != 1");
$Enquiries = mysqli_query($connection, "SELECT st_enquiry_id FROM student_enquiry WHERE st_enquiry_id NOT IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '') AND st_enquiry_status != 1");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Enrolment Form New – National College Australia</title>
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
        .field-row { border-bottom: 1px solid #dee2e6; padding: 6px 0; }
        .field-row:last-child { border-bottom: none; }
        .office-box { border: 2px solid #c00; padding: 12px; margin-bottom: 16px; }
        .office-box .section-header { background-color: #f8d7da; border-color: #c00; color: #c00; }
        .check-row label { font-weight: normal; margin-right: 16px; font-size: 0.875rem; }
        .note-text { font-size: 0.8rem; color: #555; font-style: italic; }
        .privacy-text { font-size: 0.82rem; line-height: 1.6; }
        .candidate-declaration li { margin-bottom: 6px; font-size: 0.875rem; }
        @media print { .no-print { display:none !important; } }
    </style>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">

                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Enrolment Form New – National College Australia</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Enrolment Form New</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="enrolment_new_form">
                    <input type="hidden" name="form_source" value="enrolment_form_new">

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
                                <p class="note-text mb-1"><strong>Unique Student Identifier (USI):</strong> <span class="text-danger">*</span> Unique Student Identifier (USI) is a <u>10 Digit</u> Unique identification allocated to each individual user. If you yet do not have a USI, please refer to the USI section of the form. <strong>You must write your name, exactly as written in your personal identity document you choose to use for applying for a USI.</strong></p>
                                <input type="text" class="form-control" name="usi_id" maxlength="10" placeholder="Enter 10-digit USI" required>
                                <div class="invalid-feedback">Please enter your USI.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="given_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="surname" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="note-text mb-2">NCA does not recommend enrolling students under 18 years of age. Please contact our admin staff if you have any questions. All information is collected as per Student Identifiers Act 2014 &amp; Privacy Act 1988.</p>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="age_declaration_18" id="age_declaration_18" value="1">
                                    <label class="form-check-label" for="age_declaration_18"><strong>Age Declaration:</strong> I am at least 18 years of age</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date of Birth (DD/MM/YYYY) <span class="text-danger">*</span></label>
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
                                <input type="text" class="form-control" name="mobile_num" required>
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
                                <input type="email" class="form-control" name="emailAddress" required>
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
                    <div class="section-header">Disability <small class="fw-normal">— Please see Disability Supplement section</small></div>
                    <div class="section-body">
                        <p class="mb-2" style="font-size:0.875rem;">Do you live with any disability, impairment, or long-term condition physical/mental disability that may affect your participation in the course?</p>
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
                                <p class="note-text mb-2">If you are currently enrolled under the secondary education, the Highest school level completed refers to the highest school level you have actually completed and not the level you are currently undertaking.</p>
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
                                <p class="note-text mb-1">(See Course Outline for delivery mode and available durations)</p>
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
                                    <?php while($c = mysqli_fetch_array($courses)): ?>
                                    <option value="<?php echo (int)$c['course_id']; ?>"><?php echo htmlspecialchars($c['course_sname'].' - '.$c['course_name']); ?></option>
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
                            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="qual_other" value="1"><label class="form-check-label">Other education (including certificates or overseas qualifications not listed above)</label></div></div>
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
                            <label class="form-label">Which BEST describes your main reason? Enter Text Below</label>
                            <input type="text" class="form-control" name="study_reason_other">
                        </div>
                    </div>

                    <!-- COURSE ENROLMENT DETAILS -->
                    <div class="section-header">Course Enrolment Details: <small class="fw-normal">(See Course Outline for delivery mode and available durations)</small></div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label class="form-label">Do you want to apply for Credit Transfer (CT) / Recognise Prior Learning? <span class="text-danger">*</span></label>
                                <p class="note-text mb-2">A candidate is required to fill additional form with details for CT/RPL application.</p>
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
                            <strong>IMPORTANT NOTE:</strong> National College Australia, RTO 91000 will provide access to additional support services where required. However, where a student is unable to meet minimum course entry requirements such as corresponding Learning, Literacy and Numeracy Skills and/or Physical Fitness requirements of a course, college reserves the right to defer/terminate enrolment.
                        </div>
                    </div>

                    <!-- UNIQUE STUDENT IDENTIFIER -->
                    <div class="section-header">UNIQUE STUDENT IDENTIFIER</div>
                    <div class="section-body">
                        <p class="privacy-text">From 1 January 2015, National College Australia, RTO 91000 can be prevented from issuing you with a nationally recognised VET qualification or statement of attainment when you complete your course if you do not have a Unique Student Identifier (USI). In addition, we are required to include your USI in the data we submit to NCVER. If you have not yet obtained a USI you can apply for it directly at <a href="https://www.usi.gov.au/your-usi/create-usi" target="_blank">https://www.usi.gov.au/your-usi/create-usi</a> on the computer or mobile device.</p>
                        <p class="privacy-text">If you don't have a USI number, you can apply for one by going to the USI website: <a href="https://www.usi.gov.au" target="_blank">www.usi.gov.au</a></p>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="usi_declaration" id="usi_declaration" value="1" required>
                            <label class="form-check-label" for="usi_declaration">I understand that my results will be uploaded into USI records as per company policy and information will be found online. <strong>Yes, I understand and declare.</strong></label>
                        </div>
                    </div>

                    <!-- PRIVACY NOTICE -->
                    <div class="section-header">PRIVACY NOTICE</div>
                    <div class="section-body">
                        <p class="privacy-text">The NCVER will collect, hold, use, and disclose your personal information in accordance with the law, including the Privacy Act 1988 (Cth) and the NCVER Act. Your personal information may be used and disclosed by NCVER for purposes that include populating authenticated VET transcripts; administration of VET; facilitation of statistics and research relating to education, including surveys and data linkage; and understanding the VET market.</p>
                        <p class="privacy-text"><strong>Why we collect your personal information:</strong> As a registered training organization (RTO), we collect your personal information so we can process and manage your enrolment in a vocational education and training (VET) course with us.</p>
                        <p class="privacy-text"><strong>How we use your personal information:</strong> We use your personal information to enable us to deliver VET courses to you, and otherwise, as needed, to comply with our obligations as an RTO.</p>
                        <p class="privacy-text"><strong>How we disclose your personal information:</strong> We are required by law (under the NVETR Act and NCVER Data Provision Requirements Instrument 2020) to disclose the personal information we collect about you to the National VET Data Collection kept by NCVER. For more information please refer to the <a href="https://www.ncver.edu.au/privacy" target="_blank">NCVER Privacy Policy</a> and the <a href="https://www.dese.gov.au/national-vet-data/vet-privacy-notice" target="_blank">DESE VET Privacy Notice</a>.</p>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="privacy_declaration" id="privacy_declaration" value="1" required>
                            <label class="form-check-label" for="privacy_declaration">I have read and understand the Privacy Notice (NCVER collection, use and disclosure). <strong>Yes, I understand and declare.</strong></label>
                        </div>
                    </div>

                    <!-- REFUND POLICY -->
                    <div class="section-header">REFUND POLICY</div>
                    <div class="section-body">
                        <p class="privacy-text">Details of the RTO Fees and Charges / Refund Policy and Refund Policy, Student Handbook can be found on our website.</p>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="refund_declaration" id="refund_declaration" value="1" required>
                            <label class="form-check-label" for="refund_declaration">I have read the Refund Policy / Fees and Charges. <strong>Yes, I understand and declare.</strong></label>
                        </div>
                    </div>

                    <!-- OFFICE USE ONLY -->
                    <div class="office-box mb-3">
                        <div class="section-header" style="background:#f8d7da;border-color:#c00;color:#c00;">Office Use Only:</div>
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Student ID #</label>
                                    <input type="text" class="form-control" id="office_student_id" name="office_student_id" readonly placeholder="Auto-generated">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Enrolment Coordinator/Admin Name</label>
                                    <input type="text" class="form-control" name="office_coordinator_name">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="office_invoice_provided" value="1"><label class="form-check-label">Invoice Provided</label></div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="office_receipt_collected" value="1"><label class="form-check-label">Receipt Collected</label></div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="office_lms_access" value="1"><label class="form-check-label">LMS Access Granted</label></div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="office_resources_access" value="1"><label class="form-check-label">Resources Access</label></div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="office_uploaded_sms" value="1"><label class="form-check-label">Uploaded into SMS</label></div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="office_welcome_pack_sent" value="1"><label class="form-check-label">Welcome Pack Sent</label></div>
                                    </div>
                                </div>
                            </div>
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
                            <li>By signing this enrolment application, I agree to allow and collect National College Australia for Language, Literacy (including digital), Numeracy test, progression, assessment status, and other course information on a periodic basis, during and/or after my enrolment period.</li>
                            <li>Give my consent to National College Australia to release my name, date of birth, contact details and statistical information, including my USI, to the relevant Federal government bodies for the purpose of auditing, regulation of training, obtaining feedback and as statistical information.</li>
                            <li>Agree to participate in all mandatory course requirements satisfactorily which include assessments, work placement, practical workshops and be deemed competent before release of a final certificate.</li>
                            <li>May receive a student survey which may be run by a government department or an NCVER employee, agent, third-party contractor or another authorised agency. Please note you may opt out of the survey at the time of being contacted.</li>
                            <li>By consent, Photographs may be requested during work placement or during practical demonstrations for the purpose of presenting to the authorised body to demonstrate administration of VET, research relating to education, including surveys and data linkage.</li>
                            <li>Confirm all the details provided in this form including provision of study rights are true and are presented with the intention of attaining a qualification in accordance with the law, Privacy and NCVER ACT.</li>
                            <li>Assure that I have been informed about the training, assessment and support services to be provided and on my rights and obligations as a student at National College Australia.</li>
                        </ul>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Full Name of the Candidate <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="candidate_full_name" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="candidate_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Signature of the Candidate <small class="text-muted">(type full name)</small></label>
                                <input type="text" class="form-control" name="candidate_signature" placeholder="Full name as signature">
                            </div>
                        </div>
                    </div>

                    <!-- OPTIONAL LINKING -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Enquiry ID <small class="text-muted">(optional – link to existing enquiry)</small></label>
                                    <select name="enquiry_id" class="form-control selectpicker" title="-- Optional --">
                                        <option value="">-- Optional --</option>
                                        <?php while($eq = mysqli_fetch_array($Enquiries)): ?>
                                        <option value="<?php echo htmlspecialchars($eq['st_enquiry_id']); ?>"><?php echo htmlspecialchars($eq['st_enquiry_id']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">RTO Name</label>
                                    <input type="text" class="form-control" name="rto_name" value="National College Australia">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Branch Name</label>
                                    <input type="text" class="form-control" name="branch_name" placeholder="Branch Name">
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label class="form-label">Photo Upload</label>
                                    <input type="file" class="form-control" id="photo_upload" name="photo_upload[]" accept="image/*" multiple>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DISABILITY CATEGORIES (informational) -->
                    <div class="card mb-3">
                        <div class="card-header bg-light fw-bold">Disability Categories</div>
                        <div class="card-body">
                            <p class="privacy-text mb-2"><strong>Introduction:</strong> Disability in this context does not include short-term disabling health conditions such as a fractured leg, influenza, or corrected physical conditions such as impaired vision managed by wearing glasses or lenses.</p>
                            <div class="row" style="font-size:0.82rem;">
                                <div class="col-md-6"><strong>'11 — Hearing/deaf'</strong> — Hearing impairment; acquired mild, moderate, severe or profound hearing loss after learning to speak.</div>
                                <div class="col-md-6"><strong>'12 — Physical'</strong> — Affects the mobility or dexterity of a person; may include total or partial loss of a body part.</div>
                                <div class="col-md-6 mt-2"><strong>'13 — Intellectual'</strong> — Low general intellectual functioning and difficulties in adaptive behaviour manifested before age 18.</div>
                                <div class="col-md-6 mt-2"><strong>'14 — Learning'</strong> — Significant difficulties in the acquisition and use of listening, speaking, reading, writing, reasoning, or mathematical abilities.</div>
                                <div class="col-md-6 mt-2"><strong>'15 — Mental illness'</strong> — A cluster of psychological and physiological symptoms causing suffering or distress.</div>
                                <div class="col-md-6 mt-2"><strong>'16 — Acquired brain impairment'</strong> — Injury to the brain resulting in deterioration in cognitive, physical, emotional or independent functioning.</div>
                                <div class="col-md-6 mt-2"><strong>'17 — Vision'</strong> — Partial loss of sight causing difficulties in seeing, up to and including blindness.</div>
                                <div class="col-md-6 mt-2"><strong>'18 — Medical condition'</strong> — A temporary or permanent condition that may be hereditary, genetically acquired or of unknown origin.</div>
                                <div class="col-md-12 mt-2"><strong>'19 — Other'</strong> — A disability, impairment or long-term condition not suitably described by one or several disability types in combination.</div>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-lg" id="enrolment_new_submit">Submit Enrolment Form</button>
                            <span class="ms-3" id="submit_status_new"></span>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>
<script>
$(function(){
    $('.selectpicker').selectpicker('refresh');

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

    $('input[name="emailAddress"]').on('input', function(){
        $(this).removeClass('is-invalid')[0].setCustomValidity('');
        $(this).closest('.col-md-6').find('.invalid-feedback').remove();
    });

    $('#enrolment_new_form').on('submit', function(e){
        e.preventDefault();
        var $btn    = $('#enrolment_new_submit');
        var $status = $('#submit_status_new');
        var $email  = $('input[name="emailAddress"]');

        // Validate email
        $email.removeClass('is-invalid');
        var emailVal = $email.val().trim();
        var emailRe  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal) {
            $email.addClass('is-invalid');
            $email[0].setCustomValidity('Email address is required.');
            $email.closest('.col-md-6').find('.invalid-feedback').remove();
            $email.after('<div class="invalid-feedback d-block">Email address is required.</div>');
            $email[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        if (!emailRe.test(emailVal)) {
            $email.addClass('is-invalid');
            $email.closest('.col-md-6').find('.invalid-feedback').remove();
            $email.after('<div class="invalid-feedback d-block">Please enter a valid email address.</div>');
            $email[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        $email[0].setCustomValidity('');

        $btn.prop('disabled', true).text('Submitting…');
        $status.text('').removeClass('text-danger text-success');

        var formData = new FormData(this);
        formData.append('formName', 'save_enrolment_form_new');

        var details = {};
        $('#enrolment_new_form').find('input, select, textarea').each(function(){
            var $el = $(this), name = $el.attr('name');
            if(!name || name === 'photo_upload[]') return;
            if($el.attr('type') === 'file') return;
            if($el.attr('type') === 'radio'){
                if($el.is(':checked')) details[name] = $el.val();
            } else if($el.attr('type') === 'checkbox'){
                if(name.indexOf('[]') !== -1){
                    var base = name.replace('[]','');
                    if(!details[base]) details[base] = [];
                    if($el.is(':checked')) details[base].push($el.val());
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
        for(var i = 0; i < files.length; i++) formData.append('image[]', files[i]);

        $.ajax({
            url: 'includes/datacontrol',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                try {
                    var data = typeof res === 'string' ? JSON.parse(res) : res;
                    if(data.success && data.unique_id){
                        $status.html('<strong class="text-success">Enrolment saved! Student ID: ' + data.unique_id + (data.pdf_url ? ' &nbsp;<a href="'+data.pdf_url+'" target="_blank">Download PDF</a>' : '') + '</strong>');
                        $('#office_student_id').val(data.unique_id);
                        $btn.prop('disabled', false).text('Submit Enrolment Form');
                    } else {
                        $status.text(data.message || 'Error saving enrolment.').addClass('text-danger');
                        $btn.prop('disabled', false).text('Submit Enrolment Form');
                    }
                } catch(err) {
                    $status.text(res || 'Saved.').addClass('text-success');
                    $btn.prop('disabled', false).text('Submit Enrolment Form');
                }
            },
            error: function(xhr){
                $status.text('Error: ' + (xhr.responseText || 'Request failed.')).addClass('text-danger');
                $btn.prop('disabled', false).text('Submit Enrolment Form');
            }
        });
    });
});
</script>
</body>
</html>

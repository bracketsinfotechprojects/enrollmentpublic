<?php include('includes/dbconnect.php'); ?>
<?php
session_start();
if (@$_SESSION['user_type'] == '') {
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
    <title>Enrolment Form (Online)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include('includes/app_includes.php'); ?>
    <link rel="shortcut icon" href="assets/images/favicon.ico">
</head>
<body>
    <div class="main-wrapper">
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        <div class="page-wrapper">
            <div class="content pb-0">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Enrolment Form (Online) – National College Australia</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="enrolment.php">Enrolment</a></li>
                                        <li class="breadcrumb-item active">Enrolment Form Online</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="enrolment_online_form">
                        <input type="hidden" name="form_source" value="online" />

                        <!-- STUDENT DETAILS -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">STUDENT DETAILS</h5>
                                        <p class="text-muted small">Unique Student Identifier (USI) is a 10-digit identification. If you do not have a USI, refer to the USI section. You must write your name exactly as on your identity document.</p>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Qualification Code & Title</label>
                                                <input type="text" class="form-control" id="qualification_code_title" name="qualification_code_title" placeholder="e.g. CHC33015 Certificate III in Individual Support" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Unique Student Identifier (USI) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="usi_id" name="usi_id" placeholder="10-digit USI" maxlength="10" required />
                                                <div class="error-feedback">Please enter your USI.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="given_name" name="given_name" required />
                                                <div class="error-feedback">Please enter First Name.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="surname" name="surname" required />
                                                <div class="error-feedback">Please enter Last Name.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Age Declaration</label>
                                                <div class="d-flex gap-3 align-items-center">
                                                    <label class="d-flex align-items-center gap-1"><input type="checkbox" name="age_declaration_18" id="age_declaration_18" value="1" /> I am at least 18 years of age</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Date of Birth (DD/MM/YYYY) <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="dob" name="dob" required />
                                                <div class="error-feedback">Please enter Date of Birth.</div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <label><input type="radio" name="gender_check" value="1" class="gender_check" /> Male</label>
                                                    <label><input type="radio" name="gender_check" value="2" class="gender_check" /> Female</label>
                                                    <label><input type="radio" name="gender_check" value="3" class="gender_check" /> Other</label>
                                                </div>
                                                <div class="error-feedback">Please select Gender.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ADDRESS DETAILS -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Address Details</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">House and Street Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="street_details" name="street_details" required />
                                                <div class="error-feedback">Please enter address.</div>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Post Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="post_code" name="post_code" maxlength="6" required />
                                                <div class="error-feedback">Please enter Post Code.</div>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Suburb <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="sub_urb" name="sub_urb" required />
                                                <div class="error-feedback">Please enter Suburb.</div>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">State <span class="text-danger">*</span></label>
                                                <select class="form-control" id="stu_state" name="stu_state" required>
                                                    <option value="">-- Select --</option>
                                                    <option value="NSW">NSW</option>
                                                    <option value="VIC">VIC</option>
                                                    <option value="QLD">QLD</option>
                                                    <option value="WA">WA</option>
                                                    <option value="SA">SA</option>
                                                    <option value="TAS">TAS</option>
                                                    <option value="ACT">ACT</option>
                                                    <option value="NT">NT</option>
                                                </select>
                                                <div class="error-feedback">Please select State.</div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-label">Postal Address</label>
                                                <div class="d-flex gap-2 mb-2">
                                                    <label><input type="radio" name="postal_same_as_above" value="1" class="postal_same" /> Same as Above</label>
                                                    <label><input type="radio" name="postal_same_as_above" value="0" class="postal_same" /> Enter postal address below</label>
                                                </div>
                                                <textarea class="form-control postal_address_field" id="postal_address" name="postal_address" rows="2" placeholder="Postal address (if different)" style="display:none;"></textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Are you able to read, write, and understand English? <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="english_read_write" value="1" /> Yes</label>
                                                    <label><input type="radio" name="english_read_write" value="2" /> No</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CONTACT & EMERGENCY -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Contact & Emergency</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="mobile_num" name="mobile_num" required />
                                                <div class="error-feedback">Please enter Mobile.</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Work Phone</label>
                                                <input type="text" class="form-control" id="work_phone" name="work_phone" />
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Home Phone</label>
                                                <input type="text" class="form-control" id="home_phone" name="home_phone" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="email" name="emailAddress" required />
                                                <div class="error-feedback">Please enter Email.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Emergency Contact (Name & Relation) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="em_full_name" name="em_full_name" placeholder="Full Name" />
                                                <input type="text" class="form-control mt-1" id="em_relation" name="em_relation" placeholder="Relationship" />
                                                <div class="error-feedback">Please enter Emergency Contact.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Emergency Mobile Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="em_mobile_num" name="em_mobile_num" required />
                                                <div class="error-feedback">Please enter Emergency Mobile.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- LANGUAGE AND CULTURAL DIVERSITY -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Language and Cultural Diversity</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Country of Birth <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="birth_country" name="birth_country" required />
                                                <div class="error-feedback">Please enter Country of Birth.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">City of Birth</label>
                                                <input type="text" class="form-control" id="city_of_birth" name="city_of_birth" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Do you speak a language other than English? <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="lan_spoken" value="2" /> No</label>
                                                    <label><input type="radio" name="lan_spoken" value="1" /> Yes</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-6 mb-3 lan_spoken_other_wrap" style="display:none;">
                                                <label class="form-label">Language Spoken (at Home)</label>
                                                <input type="text" class="form-control" id="lan_spoken_other" name="lan_spoken_other" placeholder="Specify language" />
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Are you Aboriginal and/or Torres Strait Islander? <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <label><input type="radio" name="origin" value="1" /> No</label>
                                                    <label><input type="radio" name="origin" value="2" /> Yes, Aboriginal</label>
                                                    <label><input type="radio" name="origin" value="3" /> Yes, Torres Strait Islander</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DISABILITY -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Disability</h5>
                                        <p class="text-muted small">Do you live with any disability, impairment, or long-term condition that may affect your participation in the course?</p>
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <label><input type="radio" name="disability" value="1" class="disability_opt" /> Yes (if yes, tick relevant)</label>
                                                <label class="ms-3"><input type="radio" name="disability" value="2" class="disability_opt" /> No</label>
                                            </div>
                                            <div class="col-12 disability_types_wrap" style="display:none;">
                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                    <label><input type="checkbox" name="st_disability_type[]" value="0" /> Hearing/Deaf</label>
                                                    <label><input type="checkbox" name="st_disability_type[]" value="1" /> Physical</label>
                                                    <label><input type="checkbox" name="st_disability_type[]" value="2" /> Intellectual</label>
                                                    <label><input type="checkbox" name="st_disability_type[]" value="3" /> Medical Condition</label>
                                                    <label><input type="checkbox" name="st_disability_type[]" value="4" /> Mental Illness</label>
                                                    <label><input type="checkbox" name="st_disability_type[]" value="5" /> Acquired brain impairment</label>
                                                    <label><input type="checkbox" name="st_disability_type[]" value="6" /> Learning</label>
                                                    <label><input type="checkbox" name="st_disability_type[]" value="7" /> Vision</label>
                                                    <label><input type="checkbox" name="st_disability_type[]" value="8" /> Other</label>
                                                </div>
                                                <div class="disability_other_wrap" style="display:none;">
                                                    <input type="text" class="form-control" id="disability_type_other" name="disability_type_other" placeholder="Other (specify)" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- EDUCATION -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Education and Training Details</h5>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">What is your highest school level COMPLETED? (tick one only) <span class="text-danger">*</span></label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label><input type="radio" name="highest_school" value="1" /> Year 12 or equivalent</label>
                                                    <label><input type="radio" name="highest_school" value="2" /> Year 11 or equivalent</label>
                                                    <label><input type="radio" name="highest_school" value="3" /> Year 10 or equivalent</label>
                                                    <label><input type="radio" name="highest_school" value="4" /> Year 9 or equivalent</label>
                                                    <label><input type="radio" name="highest_school" value="5" /> Year 8 or below</label>
                                                    <label><input type="radio" name="highest_school" value="6" /> Never attended school</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Are you still enrolled in secondary or senior secondary education? <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="sec_school" value="1" /> Yes</label>
                                                    <label><input type="radio" name="sec_school" value="2" /> No</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">In which YEAR did you complete the above school level?</label>
                                                <input type="text" class="form-control" id="year_completed_school" name="year_completed_school" placeholder="e.g. 2020" maxlength="4" />
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Mode of Delivery <span class="text-danger">*</span></label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label><input type="radio" name="mode_delivery" value="Classroom" /> Classroom</label>
                                                    <label><input type="radio" name="mode_delivery" value="Online" /> Online (Virtual)</label>
                                                    <label><input type="radio" name="mode_delivery" value="Blended" /> Blended</label>
                                                    <label><input type="radio" name="mode_delivery" value="Workplace" /> Workplace Based</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Choose A Qualification <span class="text-danger">*</span></label>
                                                <select name="courses" id="courses" class="form-control selectpicker" data-live-search="true" title="-- Select course(s) --" multiple>
                                                    <?php while ($c = mysqli_fetch_array($courses)) { ?>
                                                        <option value="<?php echo (int)$c['course_id']; ?>"><?php echo htmlspecialchars($c['course_sname'] . ' - ' . $c['course_name']); ?></option>
                                                    <?php } ?>
                                                </select>
                                                <div class="error-feedback">Please select at least one course.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QUALIFICATIONS (Page 2 style) -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Have you successfully completed any of the following qualifications? (Tick most relevant)</h5>
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label><input type="checkbox" name="qual_cert1" value="1" /> Certificate I</label>
                                                    <label><input type="checkbox" name="qual_cert2" value="1" /> Certificate II</label>
                                                    <label><input type="checkbox" name="qual_cert3" value="1" /> Certificate III (Trade Cert)</label>
                                                    <label><input type="checkbox" name="qual_cert4" value="1" /> Certificate IV</label>
                                                    <label><input type="checkbox" name="qual_diploma" value="1" /> Diploma (or associate diploma)</label>
                                                    <label><input type="checkbox" name="qual_adv_diploma" value="1" /> Advanced Diploma/Associate Degree</label>
                                                    <label><input type="checkbox" name="qual_bachelor" value="1" /> Bachelor's degree or Higher</label>
                                                    <label><input type="checkbox" name="qual_other" value="1" /> Other education (not listed above)</label>
                                                    <label><input type="checkbox" name="qual_none" value="1" /> None</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Attained</label>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="qualification_attained" value="Australia" /> Attained in Australia</label>
                                                    <label><input type="radio" name="qualification_attained" value="Equivalent" /> Australian Equivalent</label>
                                                    <label><input type="radio" name="qualification_attained" value="International" /> International</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- EMPLOYMENT -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Employment Details</h5>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Employment Status <span class="text-danger">*</span></label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label><input type="radio" name="emp_status" value="1" /> Full-time employee</label>
                                                    <label><input type="radio" name="emp_status" value="2" /> Part-time employee</label>
                                                    <label><input type="radio" name="emp_status" value="3" /> Self-employed - not employing others</label>
                                                    <label><input type="radio" name="emp_status" value="4" /> Self-employed - employing others</label>
                                                    <label><input type="radio" name="emp_status" value="5" /> Employed - unpaid worker in family business</label>
                                                    <label><input type="radio" name="emp_status" value="6" /> Unemployed - seeking full-time work</label>
                                                    <label><input type="radio" name="emp_status" value="7" /> Unemployed - seeking part-time work</label>
                                                    <label><input type="radio" name="emp_status" value="8" /> Unemployed - not seeking employment</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Industry of Work (Refer ANZSCO codes online)</label>
                                                <input type="text" class="form-control" id="industry_of_work" name="industry_of_work" placeholder="Industry / ANZSCO" />
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Reason for Enrolling in this Course of Study <span class="text-danger">*</span></label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label><input type="radio" name="study_reason" value="1" /> To get a job</label>
                                                    <label><input type="radio" name="study_reason" value="2" /> To get a better job or promotion</label>
                                                    <label><input type="radio" name="study_reason" value="3" /> It was a requirement for my job</label>
                                                    <label><input type="radio" name="study_reason" value="4" /> I wanted extra skills for my job</label>
                                                    <label><input type="radio" name="study_reason" value="5" /> To start my own business</label>
                                                    <label><input type="radio" name="study_reason" value="6" /> To get into another course of study</label>
                                                    <label><input type="radio" name="study_reason" value="7" /> To try for a different career</label>
                                                    <label><input type="radio" name="study_reason" value="8" /> To develop my existing business</label>
                                                    <label><input type="radio" name="study_reason" value="9" /> For personal interest or self-development</label>
                                                    <label><input type="radio" name="study_reason" value="10" /> To get skills for community/voluntary work</label>
                                                    <label><input type="radio" name="study_reason" value="11" /> Other reasons</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-12 study_reason_other_wrap" style="display:none;">
                                                <label class="form-label">Which BEST describes your main reason? Enter Text Below</label>
                                                <input type="text" class="form-control" id="study_reason_other" name="study_reason_other" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- COURSE ENROLMENT & CREDIT TRANSFER -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Course Enrolment Details</h5>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Do you want to apply for Credit Transfer (CT) / Recognise Prior Learning? <span class="text-danger">*</span></label>
                                                <p class="small text-muted">A candidate is required to fill additional form with details for CT/RPL application.</p>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="cred_tansf" value="1" /> Yes</label>
                                                    <label><input type="radio" name="cred_tansf" value="2" /> No</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ADDITIONAL INFORMATION -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Additional Information</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Do you have access to a computer and the internet? <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="computer_access" value="1" /> Yes</label>
                                                    <label><input type="radio" name="computer_access" value="2" /> No</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">What level of computer literacy do you have? <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="computer_literacy" value="Excellent" /> Excellent</label>
                                                    <label><input type="radio" name="computer_literacy" value="Good" /> Good</label>
                                                    <label><input type="radio" name="computer_literacy" value="Basic" /> Basic</label>
                                                    <label><input type="radio" name="computer_literacy" value="Poor" /> Poor</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">How do you rate your numeracy skills? <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="numeracy_skills" value="Excellent" /> Excellent</label>
                                                    <label><input type="radio" name="numeracy_skills" value="Good" /> Good</label>
                                                    <label><input type="radio" name="numeracy_skills" value="Basic" /> Basic</label>
                                                    <label><input type="radio" name="numeracy_skills" value="Poor" /> Poor</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Do you require additional support? <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-2">
                                                    <label><input type="radio" name="additional_support" value="1" /> No</label>
                                                    <label><input type="radio" name="additional_support" value="2" /> Yes (please specify below)</label>
                                                </div>
                                                <div class="error-feedback">Please select.</div>
                                            </div>
                                            <div class="col-md-12 additional_support_specify_wrap" style="display:none;">
                                                <label class="form-label">Please specify</label>
                                                <input type="text" class="form-control" id="additional_support_specify" name="additional_support_specify" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DECLARATIONS (USI, Privacy, Refund) -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Declarations</h5>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label><input type="checkbox" name="usi_declaration" id="usi_declaration" value="1" /> I understand that my results will be uploaded into USI records as per company policy and information will be found online. Yes, I understand and declare.</label>
                                                <div class="error-feedback">Please confirm USI declaration.</div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label><input type="checkbox" name="privacy_declaration" id="privacy_declaration" value="1" /> I have read and understand the Privacy Notice (NCVER collection, use and disclosure). Yes, I understand and declare.</label>
                                                <div class="error-feedback">Please confirm Privacy declaration.</div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label><input type="checkbox" name="refund_declaration" id="refund_declaration" value="1" /> I have read the Refund Policy / Fees and Charges. Yes, I understand and declare.</label>
                                                <div class="error-feedback">Please confirm Refund declaration.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- OFFICE USE ONLY -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card border-secondary">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Office Use Only</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Student ID #</label>
                                                <input type="text" class="form-control" id="office_student_id" name="office_student_id" readonly placeholder="Auto-generated" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Enrolment Coordinator/Admin Name</label>
                                                <input type="text" class="form-control" id="office_coordinator_name" name="office_coordinator_name" />
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="d-flex flex-wrap gap-3">
                                                    <label><input type="checkbox" name="office_invoice_provided" value="1" /> Invoice Provided</label>
                                                    <label><input type="checkbox" name="office_receipt_collected" value="1" /> Receipt Collected</label>
                                                    <label><input type="checkbox" name="office_lms_access" value="1" /> LMS Access Granted</label>
                                                    <label><input type="checkbox" name="office_resources_access" value="1" /> Resources Access</label>
                                                    <label><input type="checkbox" name="office_uploaded_sms" value="1" /> Uploaded into SMS</label>
                                                    <label><input type="checkbox" name="office_welcome_pack_sent" value="1" /> Welcome Pack Sent</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CANDIDATE DECLARATION -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Candidate Declaration</h5>
                                        <p class="small">I understand and declare that I have read the Student Handbook including Privacy, Fee Administration and Refund Policy; I agree to allow collection of LLN and assessment information; I give consent to release my details to relevant government bodies; I agree to participate in mandatory course requirements; I confirm details provided are true.</p>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label><input type="checkbox" name="candidate_declaration" id="candidate_declaration" value="1" /> Yes, I understand and declare.</label>
                                                <div class="error-feedback">Please confirm Candidate declaration.</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Full Name of the Candidate <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="candidate_full_name" name="candidate_full_name" />
                                                <div class="error-feedback">Please enter Full Name.</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="candidate_date" name="candidate_date" />
                                                <div class="error-feedback">Please enter Date.</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Signature (type name as signature)</label>
                                                <input type="text" class="form-control" id="candidate_signature" name="candidate_signature" placeholder="Full name as signature" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ENQUIRY ID (optional link from enquiry) -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Enquiry ID (optional – link to existing enquiry)</label>
                                                <select name="enquiry_id" id="enquiry_id" class="form-control selectpicker" title="-- Optional --">
                                                    <option value="">-- Optional --</option>
                                                    <?php
                                                    mysqli_data_seek($Enquiries, 0);
                                                    while ($eq = mysqli_fetch_array($Enquiries)) {
                                                        echo '<option value="' . htmlspecialchars($eq['st_enquiry_id']) . '">' . htmlspecialchars($eq['st_enquiry_id']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">RTO Name</label>
                                                <input type="text" class="form-control" id="rto_name" name="rto_name" value="National College Australia" placeholder="RTO Name" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Branch Name</label>
                                                <input type="text" class="form-control" id="branch_name" name="branch_name" placeholder="Branch Name" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Photo Upload</label>
                                                <input type="file" class="form-control" id="photo_upload" name="photo_upload[]" accept="image/*" multiple />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <button type="submit" class="btn btn-primary btn-lg" id="enrolment_online_submit">Submit Enrolment Form</button>
                                        <span class="ms-2 text-muted" id="submit_status"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer_includes.php'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>
    <script>
    $(function() {
        $('.selectpicker').selectpicker('refresh');

        $('.postal_same').on('change', function() {
            var v = $(this).val();
            $('.postal_address_field').toggle(v === '0');
        });
        $('input[name="lan_spoken"]').on('change', function() {
            $('.lan_spoken_other_wrap').toggle($(this).val() === '1');
        });
        $('.disability_opt').on('change', function() {
            $('.disability_types_wrap').toggle($(this).val() === '1');
        });
        $('input[name="st_disability_type[]"]').on('change', function() {
            var hasOther = $('input[name="st_disability_type[]"][value="8"]').is(':checked');
            $('.disability_other_wrap').toggle(hasOther);
        });
        $('input[name="study_reason"]').on('change', function() {
            $('.study_reason_other_wrap').toggle($(this).val() === '11');
        });
        $('input[name="additional_support"]').on('change', function() {
            $('.additional_support_specify_wrap').toggle($(this).val() === '2');
        });

        $('#enrolment_online_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#enrolment_online_submit');
            var $status = $('#submit_status');
            $btn.prop('disabled', true);
            $status.text('Submitting...').removeClass('text-danger text-success').addClass('text-muted');

            var formData = new FormData(this);
            formData.append('formName', 'student_enrols_online');

            var details = {};
            $('#enrolment_online_form').find('input, select, textarea').each(function() {
                var $el = $(this);
                var name = $el.attr('name');
                if (!name) return;
                if (name === 'form_source' || name === 'photo_upload[]') return;
                if ($el.attr('type') === 'file') return;
                if ($el.attr('type') === 'radio') {
                    if ($el.is(':checked')) details[name] = $el.val();
                } else if ($el.attr('type') === 'checkbox') {
                    if (name.indexOf('[]') !== -1) {
                        var base = name.replace('[]', '');
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
            if (files && files.length > 0) {
                for (var i = 0; i < files.length; i++) formData.append('image[]', files[i]);
            }

            $.ajax({
                url: 'includes/datacontrol',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    try {
                        var data = typeof res === 'string' ? JSON.parse(res) : res;
                        if (data.success && data.unique_id) {
                            $status.text('Enrolment saved. Student ID: ' + data.unique_id).addClass('text-success');
                            $('#office_student_id').val(data.unique_id);
                            if (data.pdf_url) {
                                $status.append(' <a href="' + data.pdf_url + '" target="_blank" class="ms-2">Download PDF</a>');
                            }
                        } else {
                            $status.text(data.message || 'Error saving enrolment.').addClass('text-danger');
                            $btn.prop('disabled', false);
                        }
                    } catch (err) {
                        if (res && res.indexOf('http') === 0) {
                            $status.html('Enrolment saved. <a href="' + res + '" target="_blank">Download PDF</a>').addClass('text-success');
                            $('#office_student_id').val(res.split('/').pop().replace('.pdf',''));
                        } else {
                            $status.text(res || 'Saved.').addClass('text-success');
                            $btn.prop('disabled', false);
                        }
                    }
                },
                error: function(xhr) {
                    $status.text('Error: ' + (xhr.responseText || 'Request failed')).addClass('text-danger');
                    $btn.prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
include('includes/dbconnect.php');
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: index.php');
    exit;
}
if (@$_SESSION['user_type'] != 1 && @$_SESSION['user_type'] != 2) {
    header('Location: dashboard.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: enrolment_list.php');
    exit;
}

$result = mysqli_query($connection, "SELECT * FROM enrolment_form_new WHERE id = $id LIMIT 1");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: enrolment_list.php');
    exit;
}
$r = mysqli_fetch_assoc($result);

// Helpers
function val($v, $fallback = '—') {
    return (isset($v) && $v !== '' && $v !== null) ? htmlspecialchars($v) : $fallback;
}
function yn($v) {
    if ($v == 1) return '<span class="badge bg-success">Yes</span>';
    if ($v == 2) return '<span class="badge bg-danger">No</span>';
    return '—';
}
function chk($v) {
    return $v ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
}
function tick($v) {
    return $v ? '<i class="ti ti-circle-check text-success fs-5"></i>' : '<i class="ti ti-circle-x text-secondary fs-5"></i>';
}

$gender_map  = [1 => 'Male', 2 => 'Female', 3 => 'Other'];
$school_map  = [1 => 'Year 12 or equivalent', 2 => 'Year 11 or equivalent', 3 => 'Year 10 or equivalent',
                4 => 'Year 9 or equivalent',  5 => 'Year 8 or below',       6 => 'Never attended school'];
$emp_map     = [1 => 'Full-time employee', 2 => 'Part-time employee', 3 => 'Self-employed – not employing others',
                4 => 'Self-employed – employing others', 5 => 'Employed – unpaid worker in a family business',
                6 => 'Unemployed – seeking full-time work', 7 => 'Unemployed – seeking part-time work',
                8 => 'Unemployed – not seeking employment'];
$reason_map  = [1 => 'To get a job', 2 => 'To get a better job or promotion', 3 => 'It was a requirement for my job',
                4 => 'I wanted extra skills for my job', 5 => 'To start my own business',
                6 => 'To get into another course of study', 7 => 'To try for a different career',
                8 => 'To develop my existing business', 9 => 'For personal interest or self-development',
                10 => 'To get skills for community/voluntary work', 11 => 'Other reasons'];
$origin_map  = [1 => 'No', 2 => 'Yes – Aboriginal', 3 => 'Yes – Torres Strait Islander'];
$dis_map     = [0 => 'Hearing/Deaf', 1 => 'Physical', 2 => 'Intellectual', 3 => 'Medical Condition',
                4 => 'Mental Illness', 5 => 'Acquired brain impairment', 6 => 'Learning', 7 => 'Vision', 8 => 'Other'];

// Courses
$courses_arr   = json_decode($r['courses'] ?? '[]', true) ?: [];
$courses_label = '—';
if (!empty($courses_arr)) {
    $in   = implode(',', array_map('intval', $courses_arr));
    $cr   = mysqli_query($connection, "SELECT course_sname, course_name FROM courses WHERE course_id IN ($in)");
    $names = [];
    while ($c = mysqli_fetch_assoc($cr)) $names[] = htmlspecialchars($c['course_sname'] . ' – ' . $c['course_name']);
    $courses_label = implode('<br>', $names);
}

// Disability types
$dis_types_label = '—';
if ($r['st_disability_type'] !== '') {
    $bits = array_filter(explode(',', $r['st_disability_type']), 'is_numeric');
    $dis_types_label = implode(', ', array_map(fn($v) => $dis_map[(int)$v] ?? $v, $bits));
}

// Photo paths
$photos = json_decode($r['photo_paths'] ?? '[]', true) ?: [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enrolment Detail – <?php echo val($r['office_student_id']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .section-header {
            background-color: #d4edda;
            font-weight: 700;
            font-size: 0.92rem;
            padding: 7px 12px;
            border: 1px solid #aed4b5;
            margin-bottom: 0;
        }
        .section-body {
            border: 1px solid #aed4b5;
            border-top: none;
            padding: 14px 16px;
            margin-bottom: 18px;
            background: #fff;
        }
        .field-label { font-size: 0.78rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
        .field-value { font-size: 0.92rem; color: #212529; }
        .office-box { border: 2px solid #c00; padding: 0; margin-bottom: 18px; }
        .office-box .section-header { background-color: #f8d7da; border-color: #c00; color: #c00; }
        .office-box .section-body { border-color: #c00; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">

                <!-- Page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Enrolment Detail</h4>
                            <div class="page-title-right d-flex gap-2 align-items-center">
                                <a href="enrolment_list.php" class="btn btn-outline-secondary btn-sm no-print">
                                    <i class="ti ti-arrow-left me-1"></i>Back to List
                                </a>
                                <button onclick="window.print()" class="btn btn-outline-primary btn-sm no-print">
                                    <i class="ti ti-printer me-1"></i>Print
                                </button>
                                <ol class="breadcrumb m-0 ms-2">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="enrolment_list.php">Enrolment List</a></li>
                                    <li class="breadcrumb-item active">Detail</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Header card -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    <?php echo val(trim($r['given_name'] . ' ' . $r['surname'])); ?>
                                </h5>
                                <p class="mb-1 text-muted">
                                    <strong>Student ID:</strong>
                                    <span class="badge bg-primary fs-12"><?php echo val($r['office_student_id']); ?></span>
                                </p>
                                <?php if ($r['enquiry_id']): ?>
                                <p class="mb-1 text-muted"><strong>Enquiry ID:</strong> <?php echo val($r['enquiry_id']); ?></p>
                                <?php endif; ?>
                                <p class="mb-0 text-muted" style="font-size:0.82rem;">
                                    Submitted by <strong><?php echo val($r['username']); ?></strong>
                                    <span class="badge bg-secondary"><?php echo val($r['user_type']); ?></span>
                                    &nbsp;on <?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?>
                                </p>
                            </div>
                            <img src="assets/images/logo-dark.webp" alt="NCA" height="55" onerror="this.style.display='none'">
                        </div>
                    </div>
                </div>

                <!-- STUDENT DETAILS -->
                <div class="section-header">STUDENT DETAILS</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="field-label">USI</div>
                            <div class="field-value"><?php echo val($r['usi_id']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">First Name</div>
                            <div class="field-value"><?php echo val($r['given_name']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Last Name</div>
                            <div class="field-value"><?php echo val($r['surname']); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Date of Birth</div>
                            <div class="field-value"><?php echo $r['dob'] ? date('d/m/Y', strtotime($r['dob'])) : '—'; ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Gender</div>
                            <div class="field-value"><?php echo val($gender_map[$r['gender_check']] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Age Declaration (18+)</div>
                            <div class="field-value"><?php echo chk($r['age_declaration_18']); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Qualification Code & Title</div>
                            <div class="field-value"><?php echo val($r['qualification_code_title']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- ADDRESS DETAILS -->
                <div class="section-header">ADDRESS DETAILS</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="field-label">Street Address</div>
                            <div class="field-value"><?php echo val($r['street_details']); ?></div>
                        </div>
                        <div class="col-md-2">
                            <div class="field-label">Suburb</div>
                            <div class="field-value"><?php echo val($r['sub_urb']); ?></div>
                        </div>
                        <div class="col-md-2">
                            <div class="field-label">State</div>
                            <div class="field-value"><?php echo val($r['stu_state']); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Post Code</div>
                            <div class="field-value"><?php echo val($r['post_code']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Postal Address</div>
                            <div class="field-value">
                                <?php if ($r['postal_same_as_above'] == 1): ?>
                                <em class="text-muted">Same as above</em>
                                <?php else: ?>
                                <?php echo val($r['postal_address']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Reads/Writes English</div>
                            <div class="field-value"><?php echo yn($r['english_read_write']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Mobile</div>
                            <div class="field-value"><?php echo val($r['mobile_num']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Work Phone</div>
                            <div class="field-value"><?php echo val($r['work_phone']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Home Phone</div>
                            <div class="field-value"><?php echo val($r['home_phone']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Email Address</div>
                            <div class="field-value"><?php echo val($r['email_address']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Emergency Contact</div>
                            <div class="field-value">
                                <?php echo val($r['em_full_name']); ?>
                                <?php if ($r['em_relation']): ?> <small class="text-muted">(<?php echo val($r['em_relation']); ?>)</small><?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Emergency Mobile</div>
                            <div class="field-value"><?php echo val($r['em_mobile_num']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- LANGUAGE & CULTURAL DIVERSITY -->
                <div class="section-header">LANGUAGE AND CULTURAL DIVERSITY</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="field-label">Country of Birth</div>
                            <div class="field-value"><?php echo val($r['birth_country']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">City of Birth</div>
                            <div class="field-value"><?php echo val($r['city_of_birth']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Aboriginal / Torres Strait Islander</div>
                            <div class="field-value"><?php echo val($origin_map[$r['origin']] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Speaks Language Other Than English</div>
                            <div class="field-value"><?php echo yn($r['lan_spoken']); ?></div>
                        </div>
                        <?php if ($r['lan_spoken'] == 1): ?>
                        <div class="col-md-4">
                            <div class="field-label">Language Spoken</div>
                            <div class="field-value"><?php echo val($r['lan_spoken_other']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- DISABILITY -->
                <div class="section-header">DISABILITY</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="field-label">Has Disability / Impairment</div>
                            <div class="field-value"><?php echo yn($r['disability']); ?></div>
                        </div>
                        <?php if ($r['disability'] == 1): ?>
                        <div class="col-md-5">
                            <div class="field-label">Disability Types</div>
                            <div class="field-value"><?php echo $dis_types_label; ?></div>
                        </div>
                        <?php if ($r['disability_type_other']): ?>
                        <div class="col-md-3">
                            <div class="field-label">Other (specify)</div>
                            <div class="field-value"><?php echo val($r['disability_type_other']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- EDUCATION & TRAINING -->
                <div class="section-header">EDUCATION AND TRAINING DETAILS</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="field-label">Highest School Level Completed</div>
                            <div class="field-value"><?php echo val($school_map[$r['highest_school']] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Year Completed</div>
                            <div class="field-value"><?php echo val($r['year_completed_school']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Still in Secondary Education</div>
                            <div class="field-value"><?php echo yn($r['sec_school']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Mode of Delivery</div>
                            <div class="field-value"><?php echo val($r['mode_delivery']); ?></div>
                        </div>
                        <div class="col-md-8">
                            <div class="field-label">Course(s) Enrolled</div>
                            <div class="field-value"><?php echo $courses_label; ?></div>
                        </div>
                    </div>
                </div>

                <!-- QUALIFICATIONS -->
                <div class="section-header">QUALIFICATIONS COMPLETED</div>
                <div class="section-body">
                    <div class="row g-2 mb-2">
                        <?php
                        $quals = [
                            'qual_cert1'      => 'Certificate I',
                            'qual_cert2'      => 'Certificate II',
                            'qual_cert3'      => 'Certificate III (Trade Cert)',
                            'qual_cert4'      => 'Certificate IV',
                            'qual_diploma'    => 'Diploma',
                            'qual_adv_diploma'=> 'Advanced Diploma / Associate Degree',
                            'qual_bachelor'   => "Bachelor's Degree or Higher",
                            'qual_other'      => 'Other Education',
                            'qual_none'       => 'None',
                        ];
                        foreach ($quals as $key => $label): ?>
                        <div class="col-md-4">
                            <?php echo tick($r[$key]); ?> <span style="font-size:0.875rem;"><?php echo $label; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="field-label mt-2">Qualification Attained</div>
                    <div class="field-value"><?php echo val($r['qualification_attained']); ?></div>
                </div>

                <!-- EMPLOYMENT -->
                <div class="section-header">EMPLOYMENT DETAILS</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-label">Employment Status</div>
                            <div class="field-value"><?php echo val($emp_map[$r['emp_status']] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-label">Industry of Work</div>
                            <div class="field-value"><?php echo val($r['industry_of_work']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- REASON FOR ENROLLING -->
                <div class="section-header">REASON FOR ENROLLING</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-label">Main Reason</div>
                            <div class="field-value"><?php echo val($reason_map[$r['study_reason']] ?? '—'); ?>
                                <?php if ($r['study_reason'] == 11 && $r['study_reason_other']): ?>
                                <br><small class="text-muted"><?php echo val($r['study_reason_other']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Credit Transfer / RPL</div>
                            <div class="field-value"><?php echo yn($r['cred_tansf']); ?></div>
                        </div>
                        <?php if ($r['study_reason_text']): ?>
                        <div class="col-12">
                            <div class="field-label">Additional Notes</div>
                            <div class="field-value"><?php echo nl2br(val($r['study_reason_text'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ADDITIONAL INFORMATION -->
                <div class="section-header">ADDITIONAL INFORMATION</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="field-label">Computer & Internet Access</div>
                            <div class="field-value"><?php echo yn($r['computer_access']); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Computer Literacy</div>
                            <div class="field-value"><?php echo val($r['computer_literacy']); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Numeracy Skills</div>
                            <div class="field-value"><?php echo val($r['numeracy_skills']); ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="field-label">Requires Additional Support</div>
                            <div class="field-value">
                                <?php echo yn($r['additional_support']); ?>
                                <?php if ($r['additional_support'] == 2 && $r['additional_support_specify']): ?>
                                <br><small class="text-muted"><?php echo val($r['additional_support_specify']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DECLARATIONS -->
                <div class="section-header">DECLARATIONS</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="field-label">USI Declaration</div>
                            <div class="field-value"><?php echo chk($r['usi_declaration']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Privacy Notice</div>
                            <div class="field-value"><?php echo chk($r['privacy_declaration']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Refund Policy</div>
                            <div class="field-value"><?php echo chk($r['refund_declaration']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- OFFICE USE ONLY -->
                <div class="office-box mb-3">
                    <div class="section-header" style="background:#f8d7da;border-color:#c00;color:#c00;">OFFICE USE ONLY</div>
                    <div class="section-body" style="border-color:#c00;">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="field-label">Student ID #</div>
                                <div class="field-value"><strong><?php echo val($r['office_student_id']); ?></strong></div>
                            </div>
                            <div class="col-md-5">
                                <div class="field-label">Enrolment Coordinator / Admin Name</div>
                                <div class="field-value"><?php echo val($r['office_coordinator_name']); ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-label">RTO Name</div>
                                <div class="field-value"><?php echo val($r['rto_name']); ?></div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-4">
                            <div><?php echo tick($r['office_invoice_provided']); ?> Invoice Provided</div>
                            <div><?php echo tick($r['office_receipt_collected']); ?> Receipt Collected</div>
                            <div><?php echo tick($r['office_lms_access']); ?> LMS Access Granted</div>
                            <div><?php echo tick($r['office_resources_access']); ?> Resources Access</div>
                            <div><?php echo tick($r['office_uploaded_sms']); ?> Uploaded into SMS</div>
                            <div><?php echo tick($r['office_welcome_pack_sent']); ?> Welcome Pack Sent</div>
                        </div>
                    </div>
                </div>

                <!-- CANDIDATE DECLARATION -->
                <div class="section-header">CANDIDATE DECLARATION</div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="field-label">Declaration Agreed</div>
                            <div class="field-value"><?php echo chk($r['candidate_declaration']); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-label">Full Name of Candidate</div>
                            <div class="field-value"><?php echo val($r['candidate_full_name']); ?></div>
                        </div>
                        <div class="col-md-2">
                            <div class="field-label">Date</div>
                            <div class="field-value"><?php echo $r['candidate_date'] ? date('d/m/Y', strtotime($r['candidate_date'])) : '—'; ?></div>
                        </div>
                        <div class="col-md-2">
                            <div class="field-label">Signature</div>
                            <div class="field-value"><?php echo val($r['candidate_signature']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- PHOTOS -->
                <?php if (!empty($photos)): ?>
                <div class="section-header">UPLOADED PHOTOS</div>
                <div class="section-body">
                    <div class="d-flex flex-wrap gap-3">
                        <?php foreach ($photos as $photo): ?>
                        <a href="uploads/<?php echo htmlspecialchars($photo); ?>" target="_blank">
                            <img src="uploads/<?php echo htmlspecialchars($photo); ?>"
                                 alt="Photo" style="height:100px;width:auto;border-radius:6px;border:1px solid #dee2e6;">
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action buttons -->
                <div class="mb-4 no-print d-flex gap-2 align-items-center flex-wrap">
                    <a href="enrolment_list.php" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Back to Enrolment List
                    </a>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#raiseQueryModal">
                        <i class="ti ti-message-report me-1"></i>Raise Query
                    </button>
                    <?php if ($r['status'] === 'complete'): ?>
                    <span class="badge bg-success fs-12 px-3 py-2">
                        <i class="ti ti-circle-check me-1"></i>Enrolment Completed
                    </span>
                    <?php else: ?>
                    <button type="button" class="btn btn-success" id="completeEnrolBtn">
                        <i class="ti ti-check me-1"></i>Complete Enrolment
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Raise Query Modal -->
                <div class="modal fade" id="raiseQueryModal" tabindex="-1" aria-labelledby="raiseQueryModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="raiseQueryModalLabel">
                                    <i class="ti ti-message-report me-1 text-warning"></i>Raise Query
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small mb-3">
                                    Student: <strong><?php echo val(trim($r['given_name'] . ' ' . $r['surname'])); ?></strong>
                                    &nbsp;|&nbsp; ID: <strong><?php echo val($r['office_student_id']); ?></strong>
                                </p>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="query_subject" placeholder="e.g. Missing document, USI mismatch…">
                                    <div class="invalid-feedback">Please enter a subject.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="query_message" rows="4" placeholder="Describe the query in detail…"></textarea>
                                    <div class="invalid-feedback">Please enter a message.</div>
                                </div>
                                <div id="query_feedback" class="d-none"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-warning" id="query_submit_btn">
                                    <i class="ti ti-send me-1"></i>Submit Query
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
<script>
$(function(){
    $('#query_submit_btn').on('click', function(){
        var subject = $('#query_subject').val().trim();
        var message = $('#query_message').val().trim();
        var $fb     = $('#query_feedback');
        var valid   = true;

        $('#query_subject').removeClass('is-invalid');
        $('#query_message').removeClass('is-invalid');
        $fb.addClass('d-none').removeClass('alert alert-danger alert-success').text('');

        if (!subject) { $('#query_subject').addClass('is-invalid'); valid = false; }
        if (!message) { $('#query_message').addClass('is-invalid'); valid = false; }
        if (!valid) return;

        var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting…');

        $.ajax({
            url: 'includes/datacontrol.php',
            method: 'POST',
            data: {
                action:       'raise_enrolment_query',
                enrolment_id: <?php echo $r['id']; ?>,
                subject:      subject,
                message:      message
            },
            dataType: 'json',
            success: function(res){
                if (res.success) {
                    window.location.href = 'enrolment_list.php';
                } else {
                    $fb.removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message || 'Failed to submit query.');
                }
            },
            error: function(){
                $fb.removeClass('d-none alert-success').addClass('alert alert-danger').text('Server error. Please try again.');
            },
            complete: function(){
                $btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i>Submit Query');
            }
        });
    });

    // Clear modal state on close
    $('#raiseQueryModal').on('hidden.bs.modal', function(){
        $('#query_subject, #query_message').val('').removeClass('is-invalid');
        $('#query_feedback').addClass('d-none').text('');
    });

    // Complete Enrolment
    $('#completeEnrolBtn').on('click', function(){
        if (!confirm('Mark this enrolment as Complete? This cannot be undone.')) return;
        var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');
        $.ajax({
            url: 'includes/datacontrol.php',
            method: 'POST',
            data: { action: 'complete_enrolment', enrolment_id: <?php echo $r['id']; ?> },
            dataType: 'json',
            success: function(res){
                if (res.success) {
                    $btn.closest('.d-flex')
                        .find('#completeEnrolBtn')
                        .replaceWith('<span class="badge bg-success fs-12 px-3 py-2"><i class="ti ti-circle-check me-1"></i>Enrolment Completed</span>');
                } else {
                    alert(res.message || 'Could not complete enrolment.');
                    $btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Complete Enrolment');
                }
            },
            error: function(){
                alert('Server error. Please try again.');
                $btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Complete Enrolment');
            }
        });
    });
});
</script>
</body>
</html>

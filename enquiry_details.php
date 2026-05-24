<?php
include('includes/dbconnect.php');
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
    header('Location: index.php');
    exit;
}
$ut = (int)@$_SESSION['user_type'];
if($ut !== 1 && $ut !== 2){
    header('Location: dashboard.php');
    exit;
}

$eq = isset($_GET['eq']) ? trim($_GET['eq']) : '';
$st_id = $eq ? (int)base64_decode($eq) : 0;
$enquiry = null;
$counselling = array();
$followups = array();

if($st_id > 0){
    $enquiry = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM student_enquiry WHERE st_enquiry_status != 1 AND st_id = $st_id"));
    if($enquiry){
        $eid = mysqli_real_escape_string($connection, $enquiry['st_enquiry_id']);
        $cq = mysqli_query($connection, "SELECT * FROM counseling_details WHERE st_enquiry_id = '$eid' AND counsil_enquiry_status = 0 ORDER BY counsil_id DESC");
        while($c = mysqli_fetch_assoc($cq)) $counselling[] = $c;
        $fq = mysqli_query($connection, "SELECT * FROM followup_calls WHERE enquiry_id = '$eid' AND flw_enquiry_status = 0 ORDER BY flw_id DESC");
        while($f = mysqli_fetch_assoc($fq)) $followups[] = $f;
    }
}

if(!$enquiry){
    header('Location: view_enquiries.php');
    exit;
}

$status_labels = array(
    1=>'New',
    2=>'Contacted',
    3=>'Follow-up Pending',
    4=>'In Progress',
    5=>'Enrol Pending',
    6=>'Converted',
    7=>'Closed / Lost',
    8=>'Invalid/Duplicate',
    9=>'Booked Counselling',
    10=>'Re-enquired',
    11=>'Counselling Pending'
);
$status_classes = array(
    1=>'secondary',
    2=>'info',
    3=>'warning',
    4=>'primary',
    5=>'info',
    6=>'success',
    7=>'danger',
    8=>'secondary',
    9=>'warning',
    10=>'info',
    11=>'warning'
);
$flow_status = (int)(isset($enquiry['st_enquiry_flow_status']) ? $enquiry['st_enquiry_flow_status'] : 1);
$courseNames = array();
if(!empty($enquiry['st_course'])){
    $ids = json_decode($enquiry['st_course']);
    if(is_array($ids)) foreach($ids as $id){
        $c = mysqli_fetch_array(mysqli_query($connection, "SELECT course_sname, course_name FROM courses WHERE course_status!=1 AND course_id=".(int)$id));
        if($c) $courseNames[] = ($c['course_sname']??'').' - '.($c['course_name']??'');
    }
}
$course_type = array('','Need exemption','Regular','Regular - Group','Short courses','Short course - Group');
$enquiry_for = array('','Self','Family Member');
$sources = array('','Website form','Phone call','Walk-in','Email','WhatsApp','Facebook / Instagram ads','Agent / referral (legacy)');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enquiry Details | <?php echo htmlspecialchars($enquiry['st_enquiry_id']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .detail-section{ margin-bottom: 1.5rem; }
        .detail-section h6{ border-bottom: 1px solid var(--bs-border-color); padding-bottom: 0.5rem; margin-bottom: 0.75rem; }
        .detail-row{ display: flex; flex-wrap: wrap; gap: 1rem 2rem; }
        .detail-item{ min-width: 180px; }
        .detail-item label{ font-size: 0.75rem; color: var(--bs-secondary); margin-bottom: 0.15rem; }
        .detail-item .val{ font-weight: 500; }
    </style>
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
                            <h4 class="mb-sm-0">Enquiry Details – <?php echo htmlspecialchars($enquiry['st_enquiry_id']); ?></h4>
                            <div class="page-title-right">
                                <a href="view_enquiries.php" class="btn btn-outline-secondary btn-sm me-1">Back to List</a>
                                <a href="student_enquiry.php?eq=<?php echo urlencode($eq); ?>" class="btn btn-primary btn-sm">Edit Enquiry</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="badge bg-<?php echo $status_classes[$flow_status]; ?>"><?php echo $status_labels[$flow_status]; ?></span>
                            <?php if(!empty($enquiry['st_enquiry_source']) && isset($sources[(int)$enquiry['st_enquiry_source']])){ ?>
                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($sources[(int)$enquiry['st_enquiry_source']]); ?></span>
                            <?php } ?>
                        </div>

                        <!-- Primary Details -->
                        <div class="detail-section">
                            <h6 class="text-uppercase">Primary Details</h6>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <label>Enquiry ID</label>
                                    <div class="val"><?php echo htmlspecialchars($enquiry['st_enquiry_id']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Name</label>
                                    <div class="val"><?php echo htmlspecialchars(trim(($enquiry['st_enquiry_for']==1 ? $enquiry['st_name'] : $enquiry['st_member_name']).' '.$enquiry['st_surname'])); ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Email</label>
                                    <div class="val"><?php echo htmlspecialchars($enquiry['st_email']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Mobile</label>
                                    <div class="val"><?php echo htmlspecialchars($enquiry['st_phno']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Enquiry Date</label>
                                    <div class="val"><?php echo $enquiry['st_enquiry_date'] ? date('d M Y', strtotime($enquiry['st_enquiry_date'])) : '-'; ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Enquiring For</label>
                                    <div class="val"><?php echo isset($enquiry['st_enquiry_for']) && isset($enquiry_for[$enquiry['st_enquiry_for']]) ? $enquiry_for[$enquiry['st_enquiry_for']] : '-'; ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Course(s)</label>
                                    <div class="val"><?php echo count($courseNames) ? htmlspecialchars(implode(', ', $courseNames)) : '-'; ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Course Type</label>
                                    <div class="val"><?php echo isset($enquiry['st_course_type']) && isset($course_type[$enquiry['st_course_type']]) ? $course_type[$enquiry['st_course_type']] : '-'; ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Details -->
                        <div class="detail-section">
                            <h6 class="text-uppercase">Secondary Details</h6>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <label>Address</label>
                                    <div class="val"><?php echo htmlspecialchars(trim(($enquiry['st_street_details']??'').', '.(isset($enquiry['st_suburb'])?$enquiry['st_suburb']:'').' '.(isset($enquiry['st_state'])?$enquiry['st_state']:'').' '.(isset($enquiry['st_post_code'])?$enquiry['st_post_code']:''))); ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Visa Status</label>
                                    <div class="val"><?php echo isset($enquiry['st_visa_status']) && $enquiry['st_visa_status'] !== '' ? ($enquiry['st_visa_status'] ? 'Yes' : 'No') : '-'; ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Plan to Start</label>
                                    <div class="val"><?php echo !empty($enquiry['st_startplan_date']) ? date('d M Y', strtotime($enquiry['st_startplan_date'])) : '-'; ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Heard About</label>
                                    <div class="val"><?php echo htmlspecialchars($enquiry['st_heared'] ?? '-'); ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Comments</label>
                                    <div class="val"><?php echo nl2br(htmlspecialchars($enquiry['st_comments'] ?? $enquiry['st_pref_comments'] ?? '-')); ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Remarks</label>
                                    <div class="val"><?php echo nl2br(htmlspecialchars($enquiry['st_remarks'] ?? '-')); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Follow-ups -->
                        <div class="detail-section">
                            <h6 class="text-uppercase">Follow-ups</h6>
                            <?php if(empty($followups)){ ?>
                                <p class="text-muted mb-0">No follow-up records.</p>
                            <?php } else { ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light"><tr><th>Date</th><th>Contacted Person</th><th>Mode</th><th>Notes</th><th>Next Follow-up</th><th>Outcome</th></tr></thead>
                                        <tbody>
                                        <?php foreach($followups as $f){ ?>
                                            <tr>
                                                <td><?php echo !empty($f['flw_date']) ? date('d M Y', strtotime($f['flw_date'])) : '-'; ?></td>
                                                <td><?php echo htmlspecialchars($f['flw_contacted_person'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($f['flw_mode_contact'] ?? $f['flw_followup_type'] ?? '-'); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($f['flw_follow_up_notes'] ?? $f['flw_remarks'] ?? '-')); ?></td>
                                                <td><?php echo !empty($f['flw_next_followup_date']) ? date('d M Y H:i', strtotime($f['flw_next_followup_date'])) : '-'; ?></td>
                                                <td><?php echo htmlspecialchars($f['flw_follow_up_outcome'] ?? '-'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Counselling -->
                        <div class="detail-section">
                            <h6 class="text-uppercase">Counselling</h6>
                            <?php if(empty($counselling)){ ?>
                                <p class="text-muted mb-0">No counselling records.</p>
                            <?php } else { ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light"><tr><th>Date / Time</th><th>Counsellor</th><th>Duration</th><th>Course</th><th>Qualification</th><th>Remarks</th></tr></thead>
                                        <tbody>
                                        <?php foreach($counselling as $c){ ?>
                                            <tr>
                                                <td><?php echo !empty($c['counsil_timing']) ? date('d M Y H:i', strtotime($c['counsil_timing'])) : '-'; ?></td>
                                                <td><?php echo htmlspecialchars($c['counsil_mem_name'] ?? '-'); ?></td>
                                                <td><?php echo !empty($c['counsil_end_time']) ? date('H:i', strtotime($c['counsil_end_time'])) : '-'; ?></td>
                                                <td><?php echo htmlspecialchars($c['counsil_course'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($c['counsil_qualification'] ?? '-'); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($c['counsil_remarks'] ?? '-')); ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer_includes.php'); ?>
</body>
</html>

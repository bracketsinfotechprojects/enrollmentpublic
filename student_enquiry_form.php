<?php
/**
 * Student-only enquiry page. Same form fields as admin student_enquiry.php
 * but no counselling/follow-up. Clean URL: student_enquiry_form.php or student_enquiry_form.php?eq=xxx
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: student_login.php');
    exit;
}
// Students only from student_users (user_type = 'student'). user_type=0 is staff, not students.
if (@$_SESSION['user_type'] !== 'student') {
    header('Location: dashboard.php');
    exit;
}
include('includes/dbconnect.php');

$student_user_id = (int)$_SESSION['user_id'];

// Fetch student details from student_users table (always — email may not be in session)
$student_details = array('full_name' => '', 'email' => '', 'phone' => '');
if ($student_user_id > 0) {
    $student_query = mysqli_query($connection, "SELECT full_name, email, phone FROM student_users WHERE id=$student_user_id AND status=1 LIMIT 1");
    if ($student_query && mysqli_num_rows($student_query) > 0) {
        $student_details = mysqli_fetch_assoc($student_query);
    }
}
$student_email = !empty($student_details['email']) ? $student_details['email'] : (!empty($_SESSION['student_email']) ? $_SESSION['student_email'] : '');

$eq_from_get = isset($_GET['eq']) ? (int)base64_decode($_GET['eq']) : 0;
$existing_st_id = null;

// Primary lookup by student_user_id; fallback to email for older records saved before student_user_id was stored
$_lookup_parts = ["st_enquiry_status != 1"];
if ($student_user_id > 0) {
    $_lookup_parts[] = "student_user_id = $student_user_id";
}
if ($student_email !== '') {
    $esc = mysqli_real_escape_string($connection, $student_email);
    $_lookup_parts[] = "st_email = '$esc'";
}
if (count($_lookup_parts) > 1) {
    $where_clause = $_lookup_parts[0] . " AND (" . implode(" OR ", array_slice($_lookup_parts, 1)) . ")";
    $q = mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE $where_clause ORDER BY st_id DESC LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $existing_st_id = (int)mysqli_fetch_assoc($q)['st_id'];
    }
}
// Enquiry to load: from ?eq=, or from session (set at login), or existing_st_id
$st_id_to_load = $eq_from_get > 0 ? $eq_from_get : (isset($_SESSION['student_eq_id']) && (int)$_SESSION['student_eq_id'] > 0 ? (int)$_SESSION['student_eq_id'] : 0);
if ($st_id_to_load <= 0 && $existing_st_id > 0) {
    $st_id_to_load = $existing_st_id;
}
$eqId = 0;
$queryRes = array();
$form_id = 0;
$rpl_array = ['rpl_exp'=>'','exp_in'=>'','exp_docs'=>'','exp_prev'=>'','exp_name'=>'','exp_years'=>'','exp_prev_name'=>''];
$short_grp = ['short_grp_org_name'=>'','short_grp_org_type'=>'','short_grp_campus'=>'','short_grp_date'=>'','short_grp_num_std'=>'','short_grp_ind_exp'=>'','short_grp_con_type'=>'','short_grp_con_num'=>'','short_grp_con_name'=>'','short_grp_con_email'=>'','short_grp_before'=>''];
$slot_book = ['slot_book_time'=>'','slot_book_purpose'=>'','slot_book_date'=>'','slot_book_by'=>'','slot_book_link'=>''];
$reg_grp = '';
$rpl_status = $short_grp_status = $reg_grp_status = $slot_book_status = 0;

if ($st_id_to_load > 0) {
    $queryRes = mysqli_fetch_array(mysqli_query($connection, "SELECT * FROM student_enquiry WHERE st_enquiry_status!=1 AND st_id=$st_id_to_load"));
    if (!$queryRes) {
        unset($_SESSION['student_eq_id']);
        header('Location: student_enquiry_form.php');
        exit;
    }
    $own = (int)($queryRes['student_user_id']??0) === $student_user_id || trim(strtolower($queryRes['st_email']??'')) === trim(strtolower($student_email));
    if (!$own) {
        unset($_SESSION['student_eq_id']);
        header('Location: student_enquiry_form.php');
        exit;
    }
    $_SESSION['student_eq_id'] = (int)$queryRes['st_id'];
    $eqId = (int)$queryRes['st_id'];
    $form_id = $eqId;
    $queryRes_rpl = mysqli_query($connection, "SELECT * FROM rpl_enquries WHERE enq_form_id=$form_id");
    if ($queryRes_rpl && mysqli_num_rows($queryRes_rpl)) {
        $rpl_status = 1;
        $r = mysqli_fetch_array($queryRes_rpl);
        $rpl_array = ['rpl_exp'=>$r['rpl_exp'],'exp_in'=>$r['rpl_exp_in'],'exp_docs'=>$r['rpl_exp_docs'],'exp_prev'=>$r['rpl_exp_prev_qual'],'exp_name'=>$r['rpl_exp_role'],'exp_years'=>$r['rpl_exp_years'],'exp_prev_name'=>$r['rpl_exp_qual_name']];
    }
    $queryRes_regGrp = mysqli_query($connection, "SELECT * FROM regular_group_form WHERE enq_form_id=$form_id");
    if ($queryRes_regGrp && mysqli_num_rows($queryRes_regGrp)) {
        $reg_grp_status = 1;
        $reg_grp = mysqli_fetch_array($queryRes_regGrp)['reg_grp_names'];
    }
    $queryRes_shortGrp = mysqli_query($connection, "SELECT * FROM short_group_form WHERE enq_form_id=$form_id");
    if ($queryRes_shortGrp && mysqli_num_rows($queryRes_shortGrp)) {
        $short_grp_status = 1;
        $s = mysqli_fetch_array($queryRes_shortGrp);
        $short_grp = ['short_grp_org_name'=>$s['sh_org_name'],'short_grp_org_type'=>$s['sh_grp_org_type'],'short_grp_campus'=>$s['sh_grp_campus'],'short_grp_date'=>$s['sh_grp_date'],'short_grp_num_std'=>$s['sh_grp_num_stds'],'short_grp_ind_exp'=>$s['sh_grp_ind_exp'],'short_grp_con_type'=>$s['sh_grp_con_us'],'short_grp_con_num'=>$s['sh_grp_phone'],'short_grp_con_name'=>$s['sh_grp_name'],'short_grp_con_email'=>$s['sh_grp_email'],'short_grp_before'=>$s['sh_grp_train_bef']];
    }
    $queryRes_slotBook = mysqli_query($connection, "SELECT * FROM slot_book WHERE enq_form_id=$form_id");
    if ($queryRes_slotBook && mysqli_num_rows($queryRes_slotBook)) {
        $slot_book_status = 1;
        $b = mysqli_fetch_array($queryRes_slotBook);
        $slot_book = ['slot_book_time'=>$b['slot_bk_datetime'],'slot_book_purpose'=>$b['slot_bk_purpose'],'slot_book_date'=>$b['slot_bk_on'],'slot_book_by'=>$b['slot_book_by'],'slot_book_link'=>$b['slot_book_email_link']];
    }
} else {
    $queryRes = array(
        'st_email'=>$student_details['email'],'st_enquiry_date'=>'','st_surname'=>'','st_enquiry_for'=>1,'st_name'=>$student_details['full_name'],'st_member_name'=>'','st_course_type'=>'','st_enquiry_source'=>0,'st_enquiry_college'=>0,
        'st_phno'=>$student_details['phone'],'st_street_details'=>'','st_suburb'=>'','st_state'=>'','st_post_code'=>'','st_visited'=>'','st_heared'=>'','st_hearedby'=>'','st_startplan_date'=>'','st_refered'=>'','st_refer_name'=>'','st_visa_status'=>'','st_visa_note'=>'','st_visa_condition'=>'','st_course'=>'','st_ethnicity'=>'','st_fee'=>'','st_comments'=>'','st_appoint_book'=>'','st_remarks'=>'','st_pref_comments'=>''
    );
}
$rpl_arrays = json_encode($rpl_array);
$short_grps = json_encode($short_grp);
$slot_books = json_encode($slot_book);
$enquirySourceStaffUsers = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name");
$courses = mysqli_query($connection, "SELECT * FROM courses WHERE course_status!=1");
$visaStatus = mysqli_query($connection, "SELECT * FROM visa_statuses WHERE visa_state_status!=1");
$is_student_portal = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Enquiry | National College Australia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
</head>
<body>
<div id="loader-container" style="display:none;"><div class="loader"></div></div>
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Student – My Enquiry</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item active">My Enquiry</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include('includes/student_enquiry_form_body.inc.php'); ?>
            </div>
        </div>
    </div>
</div>
<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
<?php include('includes/student_enquiry_form_script.inc.php'); ?>
</body>
</html>

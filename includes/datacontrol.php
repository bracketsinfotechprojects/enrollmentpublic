<?php include(__DIR__ . '/dbconnect.php'); ?>
<?php
// Emergency bootstrap diagnostics for API calls.
// Keeps datacontrol from failing with blank 500 pages by returning JSON errors.
$GLOBALS['crm_datacontrol_boot_step'] = 'bootstrap_start';
$crm_dc_form_name = isset($_POST['formName']) ? (string)$_POST['formName'] : '';
$crm_dc_debug = (isset($_REQUEST['debug']) && (string)$_REQUEST['debug'] === '1')
    || (isset($_SERVER['HTTP_X_CRM_DEBUG']) && (string)$_SERVER['HTTP_X_CRM_DEBUG'] === '1');
$crm_dc_is_ajax = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
$crm_dc_is_api = ($crm_dc_form_name !== '') || $crm_dc_is_ajax;
$crm_dc_is_export = in_array($crm_dc_form_name, array('exportEnquiryReportsExcel', 'exportEnquiryReportsPdf'), true)
    || (isset($_GET['export']) && in_array((string)$_GET['export'], array('enquiry_reports_excel', 'enquiry_reports_pdf'), true));
$crm_dc_should_emit_json_errors = $crm_dc_is_api && !$crm_dc_is_export;

if($crm_dc_should_emit_json_errors){
    @ini_set('display_errors', '0');
    @error_reporting(E_ALL);

    set_exception_handler(function($e){
        if(headers_sent()){
            return;
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        $payload = array(
            'success' => false,
            'message' => 'Server error while processing request.',
            'debug' => array(
                'step' => (string)($GLOBALS['crm_datacontrol_boot_step'] ?? 'unknown'),
                'type' => is_object($e) ? get_class($e) : 'Exception',
                'error' => is_object($e) && method_exists($e, 'getMessage') ? (string)$e->getMessage() : 'Unknown error',
                'file' => is_object($e) && method_exists($e, 'getFile') ? basename((string)$e->getFile()) : '',
                'line' => is_object($e) && method_exists($e, 'getLine') ? (int)$e->getLine() : 0
            )
        );
        echo json_encode($payload);
        exit;
    });

    register_shutdown_function(function(){
        $e = error_get_last();
        if(!$e){
            return;
        }
        $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);
        if(!in_array((int)$e['type'], $fatalTypes, true)){
            return;
        }
        if(headers_sent()){
            return;
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'success' => false,
            'message' => 'Fatal server error while processing request.',
            'debug' => array(
                'step' => (string)($GLOBALS['crm_datacontrol_boot_step'] ?? 'unknown'),
                'type' => (int)$e['type'],
                'error' => (string)$e['message'],
                'file' => basename((string)$e['file']),
                'line' => (int)$e['line']
            )
        ));
    });
}

$GLOBALS['crm_datacontrol_boot_step'] = 'require_dbconnect';
require('dbconnect.php');
$GLOBALS['crm_datacontrol_boot_step'] = 'require_counselling_helper';
require_once __DIR__ . '/enquiry_status_counselling_email_helper.php';
$GLOBALS['crm_datacontrol_boot_step'] = 'require_auto_map';
require_once __DIR__ . '/enquiry_status_auto_map.php';
$GLOBALS['crm_datacontrol_boot_step'] = 'post_require';
error_reporting(E_ALL);
if($crm_dc_should_emit_json_errors){
    @ini_set('display_errors', '0');
}else{
    @ini_set('display_errors', '1');
}
// use TCPDF;

session_start();

if (!function_exists('crm_ensure_student_enquiry_columns')) {
function crm_ensure_student_enquiry_columns($connection) {
    $columns = array(
        'st_contact_notes' => "ALTER TABLE student_enquiry ADD COLUMN st_contact_notes TEXT NULL",
        'st_enquiry_source' => "ALTER TABLE student_enquiry ADD COLUMN st_enquiry_source TINYINT NULL DEFAULT NULL COMMENT '1=Website,2=Phone,3=Walk-in,4=Email,5=WhatsApp,6=FB/IG,7=Agent' AFTER `st_gen_enq_type`",
        'st_location' => "ALTER TABLE student_enquiry ADD COLUMN st_location VARCHAR(255) NULL DEFAULT NULL COMMENT 'Location from website popup' AFTER `st_enquiry_source`",
        'st_enquiry_college' => "ALTER TABLE student_enquiry ADD COLUMN st_enquiry_college TINYINT NULL DEFAULT NULL COMMENT '1=Apt Training,2=Milton,3=NCA,4=Power Ed,5=Auz Training' AFTER `st_location`",
    );
    foreach($columns as $column => $alterSql) {
        if (!mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE '$column'"))) {
            @mysqli_query($connection, $alterSql);
        }
    }
}
}
crm_ensure_student_enquiry_columns($connection);

if(!function_exists('crm_require_tcpdf')){
function crm_require_tcpdf(){
    if(class_exists('TCPDF', false)){
        return true;
    }
    $tcpdfPath = __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
    if(file_exists($tcpdfPath)){
        require_once $tcpdfPath;
    }
    return class_exists('TCPDF', false);
}
}

if (!function_exists('crm_student_user_deactivate_if_no_active_enquiry_by_email')) {
function crm_student_user_deactivate_if_no_active_enquiry_by_email($connection, $emailRaw) {
    $email = strtolower(trim((string)$emailRaw));
    if ($email === '') {
        return;
    }
    $emailEsc = mysqli_real_escape_string($connection, $email);
    $activeQ = @mysqli_query(
        $connection,
        "SELECT st_id FROM student_enquiry WHERE st_enquiry_status=0 AND LOWER(TRIM(st_email))=LOWER('".$emailEsc."') LIMIT 1"
    );
    if ($activeQ && mysqli_num_rows($activeQ) > 0) {
        return;
    }
    @mysqli_query(
        $connection,
        "UPDATE student_users SET status=0 WHERE status=1 AND LOWER(TRIM(email))=LOWER('".$emailEsc."')"
    );
}
}

function enquiry_ack_email_body($name, $enquiry_id, $form_url, $register_url, $login_url) {
    if(!function_exists('send_mail')){
        require_once(__DIR__ . '/mail_function.php');
    }
    $site = 'National College Australia';
    $html = '<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;max-width:560px;margin:0 auto;color:#333;">';
    $html .= '<p style="font-size:16px;line-height:1.6;">Hi ' . htmlspecialchars($name) . ',</p>';
    $html .= '<p style="font-size:16px;line-height:1.6;">Thank you for your interest in <strong>' . htmlspecialchars($site) . '</strong>.</p>';
    $html .= '<p style="font-size:16px;line-height:1.6;">Your enquiry has been received. Please keep your <strong>Enquiry ID: ' . htmlspecialchars($enquiry_id) . '</strong> for reference.</p>';
    $html .= '<p style="font-size:16px;line-height:1.6;"><strong>Next step – complete your application:</strong></p>';
    $html .= '<p style="margin:20px 0;"><a href="' . htmlspecialchars($form_url) . '" style="display:inline-block;background:#158887;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:600;">Complete your form</a></p>';
    $html .= '<p style="font-size:14px;color:#666;">If you don\'t have an account yet, <a href="' . htmlspecialchars($register_url) . '">register here</a> using the same email, then complete your form. Already have an account? <a href="' . htmlspecialchars($login_url) . '">Log in here</a>.</p>';
    $html .= '<p style="font-size:14px;color:#666;margin-top:24px;">Best regards,<br>' . htmlspecialchars($site) . '</p>';
    $html .= '</div>';
    return $html;
}

/**
 * When counselling or follow-up is saved without an existing enquiry context, create (or reuse by email)
 * a minimal student_enquiry row so the record can link to st_enquiry_id.
 *
 * @return array{ok:bool,enquiry_id?:string,st_id?:int,error?:string}
 */
if (!function_exists('crm_ensure_enquiry_from_sidebar_contact')) {
function crm_ensure_enquiry_from_sidebar_contact($connection, $admin_id) {
    $emailRaw = trim((string)($_POST['emailAddress'] ?? ''));
    if ($emailRaw === '' || !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
        return array('ok' => false, 'error' => 'invalid_email');
    }
    $emailEsc = mysqli_real_escape_string($connection, $emailRaw);
    $dupQ = mysqli_query($connection, "SELECT st_id, st_enquiry_id FROM student_enquiry WHERE st_enquiry_status=0 AND LOWER(TRIM(st_email))=LOWER('" . $emailEsc . "') ORDER BY st_id DESC LIMIT 1");
    if ($dupQ && ($dupRow = mysqli_fetch_assoc($dupQ)) && !empty($dupRow['st_enquiry_id'])) {
        return array('ok' => true, 'enquiry_id' => $dupRow['st_enquiry_id'], 'st_id' => (int)($dupRow['st_id'] ?? 0));
    }
    $enquiryFor = isset($_POST['enquiryFor']) && $_POST['enquiryFor'] !== '' ? (int)$_POST['enquiryFor'] : 1;
    if ($enquiryFor !== 2) {
        $enquiryFor = 1;
    }
    $studentNameRaw = trim((string)($_POST['studentName'] ?? ''));
    $memberNameRaw = trim((string)($_POST['memberName'] ?? ''));
    if ($enquiryFor === 1) {
        $studentNameEsc = mysqli_real_escape_string($connection, $studentNameRaw);
        $memberNameEsc = mysqli_real_escape_string($connection, $memberNameRaw);
    } else {
        $studentNameEsc = mysqli_real_escape_string($connection, $memberNameRaw);
        $memberNameEsc = mysqli_real_escape_string($connection, $studentNameRaw);
    }
    $contactName = mysqli_real_escape_string($connection, trim((string)($_POST['contactName'] ?? '')));
    $surname = mysqli_real_escape_string($connection, trim((string)($_POST['surname'] ?? '')));
    $courses = mysqli_real_escape_string($connection, '[]');
    $payment = '';
    $visaStatus = 0;
    $visaCondition = 1;
    $visaNote = '';
    $suburb = '';
    $stuState = '0';
    $postCode = 0;
    $visit_before = 0;
    $hear_about = '';
    $hearedby = '';
    $plan_to_start_date = '';
    $refer_select = 0;
    $referer_name = '';
    $refer_alumni = 0;
    $comments = '';
    $prefComment = '';
    $appointment_booked = 0;
    $remarks = '';
    $streetDetails = '';
    $enquiryDate = mysqli_real_escape_string($connection, date('Y-m-d'));
    $courseType = 0;
    $shore = 0;
    $ethnicity = '';
    $created_by = (int)$admin_id;
    $query = mysqli_query($connection, "INSERT INTO student_enquiry(st_name,st_member_name,st_phno,st_email,st_course,st_fee,st_visa_status,st_visa_condition,st_visa_note,st_surname,st_suburb,st_state,st_post_code,st_visited,st_heared,st_hearedby,st_startplan_date,st_refered,st_refer_name,st_refer_alumni,st_comments,st_pref_comments,st_appoint_book,st_remarks,st_street_details,st_enquiry_for,st_enquiry_date,st_course_type,st_shore,st_ethnicity,st_created_by,st_enquiry_source,st_location,st_enquiry_college)VALUES('$studentNameEsc','$memberNameEsc','$contactName','$emailEsc','$courses','$payment',$visaStatus,$visaCondition,'$visaNote','$surname','$suburb','$stuState',$postCode,$visit_before,'$hear_about','$hearedby','$plan_to_start_date',$refer_select,'$referer_name',$refer_alumni,'$comments','$prefComment',$appointment_booked,'$remarks','$streetDetails',$enquiryFor,'$enquiryDate',$courseType,$shore,'$ethnicity',$created_by,NULL,NULL,NULL)");
    if (!$query) {
        return array('ok' => false, 'error' => 'insert_failed');
    }
    $lastId = mysqli_insert_id($connection);
    if ($lastId <= 0) {
        return array('ok' => false, 'error' => 'insert_failed');
    }
    $uniqueId = sprintf('EQ%05d', $lastId);
    $uniqueEsc = mysqli_real_escape_string($connection, $uniqueId);
    mysqli_query($connection, "UPDATE student_enquiry SET st_enquiry_id='$uniqueEsc' WHERE st_id=" . (int)$lastId);
    return array('ok' => true, 'enquiry_id' => $uniqueId, 'st_id' => (int)$lastId);
}
}

// Enquiry status email template (load by status code) and send email
if(isset($_POST['get_enquiry_status_template']) && isset($_POST['status_code'])){
    $status_code = (int)$_POST['status_code'];
    if (in_array($status_code, array(12, 13, 14), true)) {
        datacontrol_seed_counselling_outcome_email_templates($connection);
    }
    $enquiry_id = isset($_POST['enquiry_id']) ? mysqli_real_escape_string($connection, $_POST['enquiry_id']) : '';
    $student_name = '';
    $first_name = '';
    $course_name = '';
    if($enquiry_id){
        $r = @mysqli_fetch_array(mysqli_query($connection, "SELECT st_name, st_course FROM student_enquiry WHERE st_enquiry_id='$enquiry_id' AND st_enquiry_status!=1 LIMIT 1"));
            if($r){
                $student_name = $r['st_name'];
                $first_name = trim(strtok($student_name, ' '));
                if(!empty($r['st_course'])){
                    $ids = json_decode($r['st_course'], true);
                    if(is_array($ids) && count($ids)){
                        $intIds = array();
                        foreach($ids as $cid_raw){
                            $cid_i = (int)$cid_raw;
                            if($cid_i > 0) $intIds[] = $cid_i;
                        }
                        if(count($intIds)){
                            $idList = implode(',', $intIds);
                            $crs = mysqli_query($connection, "SELECT CONCAT(course_sname,' ',course_name) AS nm FROM courses WHERE course_id IN ($idList) AND course_status!=1 ORDER BY course_sname, course_name");
                            $names = array();
                            if($crs){
                                while($cr = mysqli_fetch_assoc($crs)){
                                    if(!empty($cr['nm'])) $names[] = $cr['nm'];
                                }
                            }
                            if(count($names)) $course_name = implode(', ', $names);
                        }
                    }
                }
            }
    }
    $q = mysqli_query($connection, "SELECT subject, body FROM enquiry_status_email_templates WHERE status_code=$status_code LIMIT 1");
    if($q && mysqli_num_rows($q)){
        $row = mysqli_fetch_assoc($q);
        $subject = $row['subject'];
        $body_tpl = $row['body'];
        $officer_name = '';
        if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
            $uid = (int)$_SESSION['user_id'];
            $ur = @mysqli_fetch_array(mysqli_query($connection, "SELECT user_name FROM users WHERE user_id=$uid LIMIT 1"));
            if ($ur && !empty($ur['user_name'])) {
                $officer_name = $ur['user_name'];
            }
        }
        $repl = array(
            '{{student_name}}' => $student_name,
            '{{FirstName}}'    => $first_name ?: $student_name,
            '{{CourseName}}'   => $course_name,
            '{{OfficerName}}'  => $officer_name
        );
        if ($status_code === 9 && $enquiry_id) {
            $eid = mysqli_real_escape_string($connection, $enquiry_id);
            $apt = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT appointment_date, appointment_time, appointment_end_time FROM appointments WHERE connected_enquiry_id='$eid' AND delete_status!=1 ORDER BY appointment_datetime DESC LIMIT 1"));
            if ($apt) {
                $repl['{{CounsellingDate}}'] = date('l, j F Y', strtotime($apt['appointment_date']));
                $start = date('g:i A', strtotime($apt['appointment_time']));
                $end = (isset($apt['appointment_end_time']) && $apt['appointment_end_time'] !== '') ? date('g:i A', strtotime($apt['appointment_end_time'])) : $start;
                $repl['{{CounsellingTime}}'] = $start . ' – ' . $end;
            } else {
                $repl['{{CounsellingDate}}'] = '[Counselling Date]';
                $repl['{{CounsellingTime}}'] = '[Counselling Start Time – Counselling End Time]';
            }
        } elseif (in_array($status_code, array(12, 13, 14), true)) {
            $raw_eid = isset($_POST['enquiry_id']) ? trim((string) $_POST['enquiry_id']) : '';
            $pd = isset($_POST['counselling_session_date']) ? trim((string) $_POST['counselling_session_date']) : '';
            $ps = isset($_POST['counselling_session_start']) ? trim((string) $_POST['counselling_session_start']) : '';
            $pe = isset($_POST['counselling_session_end']) ? trim((string) $_POST['counselling_session_end']) : '';
            $repl = array_merge($repl, datacontrol_counselling_session_repl($connection, $raw_eid, $pd, $ps, $pe));
        }
        $body = strtr($body_tpl, $repl);
        echo json_encode(array('subject'=>$subject, 'body'=>$body));
    } else echo json_encode(array('subject'=>'', 'body'=>''));
    exit;
}
if(isset($_POST['send_enquiry_status_email']) && isset($_POST['enquiry_id']) && isset($_POST['subject']) && isset($_POST['body'])){
    $enquiry_id = mysqli_real_escape_string($connection, $_POST['enquiry_id']);
    $subject = mysqli_real_escape_string($connection, $_POST['subject']);
    $body = $_POST['body'];
    $override_to_raw = isset($_POST['override_to']) ? trim((string) $_POST['override_to']) : '';
    $save_as_default = !empty($_POST['save_as_default']) && isset($_POST['status_code']);
    $status_code = $save_as_default ? (int)$_POST['status_code'] : 0;
    $send_status_for_placeholders = isset($_POST['status_code']) ? (int)$_POST['status_code'] : 0;
    $q = mysqli_query($connection, "SELECT st_email, st_name, st_course, st_id FROM student_enquiry WHERE st_enquiry_id='$enquiry_id' AND st_enquiry_status!=1 LIMIT 1");
    if($q && mysqli_num_rows($q)){
        $row = mysqli_fetch_assoc($q);
        $to = $row['st_email'];
        if ($override_to_raw !== '' && filter_var($override_to_raw, FILTER_VALIDATE_EMAIL)) {
            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student') {
                // Students cannot override recipient
            } else {
                $to = $override_to_raw;
            }
        }
        $student_name = $row['st_name'];
        $first_name = trim(strtok($student_name, ' '));
        $course_name = '';
        if(!empty($row['st_course'])){
            $ids = json_decode($row['st_course'], true);
            if(is_array($ids) && count($ids)){
                $intIds = array();
                foreach($ids as $cid_raw){
                    $cid_i = (int)$cid_raw;
                    if($cid_i > 0) $intIds[] = $cid_i;
                }
                if(count($intIds)){
                    $idList = implode(',', $intIds);
                    $crs = mysqli_query($connection, "SELECT CONCAT(course_sname,' ',course_name) AS nm FROM courses WHERE course_id IN ($idList) AND course_status!=1 ORDER BY course_sname, course_name");
                    $names = array();
                    if($crs){
                        while($cr = mysqli_fetch_assoc($crs)){
                            if(!empty($cr['nm'])) $names[] = $cr['nm'];
                        }
                    }
                    if(count($names)) $course_name = implode(', ', $names);
                }
            }
        }
        $officer_name = '';
        if(isset($_SESSION['user_id']) && $_SESSION['user_id']){
            $uid = (int)$_SESSION['user_id'];
            $ur = @mysqli_fetch_array(mysqli_query($connection, "SELECT user_name FROM users WHERE user_id=$uid LIMIT 1"));
            if($ur && !empty($ur['user_name'])) $officer_name = $ur['user_name'];
        }
        $repl = array(
            '{{student_name}}' => $student_name,
            '{{FirstName}}'    => $first_name ?: $student_name,
            '{{CourseName}}'   => $course_name,
            '{{OfficerName}}'  => $officer_name
        );
        if (in_array($send_status_for_placeholders, array(12, 13, 14), true)) {
            $raw_eid = trim((string) ($_POST['enquiry_id'] ?? ''));
            $pd = isset($_POST['counselling_session_date']) ? trim((string) $_POST['counselling_session_date']) : '';
            $ps = isset($_POST['counselling_session_start']) ? trim((string) $_POST['counselling_session_start']) : '';
            $pe = isset($_POST['counselling_session_end']) ? trim((string) $_POST['counselling_session_end']) : '';
            $repl = array_merge($repl, datacontrol_counselling_session_repl($connection, $raw_eid, $pd, $ps, $pe));
        } else {
            $eid = mysqli_real_escape_string($connection, $enquiry_id);
            $apt = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT appointment_date, appointment_time, appointment_end_time FROM appointments WHERE connected_enquiry_id='$eid' AND delete_status!=1 ORDER BY appointment_datetime DESC LIMIT 1"));
            if ($apt) {
                $repl['{{CounsellingDate}}'] = date('l, j F Y', strtotime($apt['appointment_date']));
                $start = date('g:i A', strtotime($apt['appointment_time']));
                $end = (isset($apt['appointment_end_time']) && $apt['appointment_end_time'] !== '') ? date('g:i A', strtotime($apt['appointment_end_time'])) : $start;
                $repl['{{CounsellingTime}}'] = $start . ' – ' . $end;
            } else {
                $repl['{{CounsellingDate}}'] = '[Counselling Date]';
                $repl['{{CounsellingTime}}'] = '[Counselling Start Time – Counselling End Time]';
            }
        }
        $body_sent = strtr($body, $repl);
        if(!function_exists('send_mail')){
            require_once(__DIR__ . '/mail_function.php');
        }
        $body_html = '<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.6;color:#333;">' . nl2br(htmlspecialchars($body_sent, ENT_QUOTES, 'UTF-8')) . '</div>';
        $st_id_for_log = isset($row['st_id']) ? (int) $row['st_id'] : 0;
        $mail_ctx = array(
            'email_category' => 'enquiry_status',
            'st_enquiry_id' => trim((string) ($_POST['enquiry_id'] ?? '')),
            'meta' => array(
                'status_code' => $send_status_for_placeholders,
            ),
        );
        if ($st_id_for_log > 0) {
            $mail_ctx['st_id'] = $st_id_for_log;
        }
        try {
            send_mail($to, $subject, $body_html, $mail_ctx);
            if ($save_as_default && (($status_code >= 1 && $status_code <= 11) || in_array($status_code, array(12, 13, 14), true))) {
                $body_esc = mysqli_real_escape_string($connection, $body);
                mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='$subject', body='$body_esc', updated_at=NOW() WHERE status_code=$status_code");
            }
            echo '1';
        } catch (Exception $e) {
            echo 'Failed to send email. ' . (trim($e->getMessage()) ? htmlspecialchars($e->getMessage()) : 'Check SMTP settings.');
        }
    } else echo 'Enquiry not found.';
    exit;
}
if(isset($_POST['save_enquiry_status_template']) && isset($_POST['status_code'])){
    $status_code = (int)$_POST['status_code'];
    if (($status_code >= 1 && $status_code <= 11) || in_array($status_code, array(12, 13, 14), true)) {
        if (in_array($status_code, array(12, 13, 14), true)) {
            datacontrol_seed_counselling_outcome_email_templates($connection);
        }
        $subject = mysqli_real_escape_string($connection, $_POST['subject']);
        $body = mysqli_real_escape_string($connection, $_POST['body']);
        $q = mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='$subject', body='$body', updated_at=NOW() WHERE status_code=$status_code");
        echo $q ? '1' : '0';
    } else echo '0';
    exit;
}

if(isset($_POST['save_appointment_email_template']) && isset($_POST['template_code'])){
    $template_code = preg_replace('/[^a-z0-9_\-]/i', '', (string)$_POST['template_code']);
    $subject = mysqli_real_escape_string($connection, trim((string)($_POST['subject'] ?? '')));
    $body = mysqli_real_escape_string($connection, (string)($_POST['body'] ?? ''));
    $valid_codes = array(
        'standard_booking' => 'Standard appointment confirmation',
        'phone_call_booking' => 'Phone call booking confirmation',
        'counselling_rescheduled' => 'Counselling rescheduled confirmation',
    );
    if(!isset($valid_codes[$template_code])){
        echo '0';
        exit;
    }
    @mysqli_query($connection, "CREATE TABLE IF NOT EXISTS appointment_email_templates (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        template_code VARCHAR(64) NOT NULL UNIQUE,
        template_name VARCHAR(128) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        updated_at DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $codeEsc = mysqli_real_escape_string($connection, $template_code);
    $nameEsc = mysqli_real_escape_string($connection, $valid_codes[$template_code]);
    $q = mysqli_query($connection, "INSERT INTO appointment_email_templates(template_code,template_name,subject,body,updated_at) VALUES('$codeEsc','$nameEsc','$subject','$body',NOW()) ON DUPLICATE KEY UPDATE template_name=VALUES(template_name), subject=VALUES(subject), body=VALUES(body), updated_at=NOW()");
    echo $q ? '1' : '0';
    exit;
}

if(@$_POST['formName']=='logout'){
    session_destroy();
    header('Location: ../index.php');
}
if(@$_POST['formName']=='create_qr'){
    $admin_id=$_POST['admin_id'];
    $query=mysqli_query($connection,"INSERT INTO `enquiry_forms` (`enq_admin_id`)VALUES($admin_id);");
    $last_inserted_id=mysqli_insert_id($connection);    
    echo base64_encode($last_inserted_id);

}

if(@$_POST['formName']=='phoneNumberCheck'){
    
    $number=$_POST['number'];
    $memberName=$_POST['memberName'];
    $enquiryFor=$_POST['enquiryFor'];    
    $check_update=$_POST['check_update'];    
    $oldenquiryFor=$_POST['oldenquiryFor'];    
    $oldNumber=$_POST['oldNumber'];   
    $updateCheck=0; 

    if($check_update!=0){

        if($oldenquiryFor!=$enquiryFor || $oldNumber!=$number){

            $checkPh=1;

            $updateCheck=1;

        }else{

            $checkPh=0;

        }

    }else{

        $checkPh=1;

    }


    if($checkPh==1){


        if($enquiryFor==1){


            if($updateCheck==0){


                $query2=mysqli_query($connection,"SELECT st_enquiry_id FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_phno` LIKE '%$number%' and `st_name` LIKE '$memberName'");

                // echo "SELECT * FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_phno` LIKE '%$number%' and st_enquiry_for=1";
                if(mysqli_num_rows($query2)==0){
                     echo 0;
                }else{
                    $query2Res=mysqli_fetch_array($query2);
                     echo '1||'.$query2Res['st_enquiry_id'];
                }

            }else{

                $query2=mysqli_query($connection,"SELECT st_enquiry_id FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_phno` LIKE '%$number%' and st_name='$memberName'");
                if(mysqli_num_rows($query2)==0){
                     echo 0;
                }else{
                    $query2Res=mysqli_fetch_array($query2);
                     echo '1||'.$query2Res['st_enquiry_id'];
                }

            }

        }else{
    
            if($updateCheck==0){


                $query2=mysqli_query($connection,"SELECT st_enquiry_id FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_phno` LIKE '%$number%' and `st_name` LIKE '$memberName'");

                // echo "SELECT * FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_phno` LIKE '%$number%' and st_enquiry_for=1";
                if(mysqli_num_rows($query2)==0){
                     echo 0;
                }else{
                    $query2Res=mysqli_fetch_array($query2);
                    echo '1||'.$query2Res['st_enquiry_id'];
                }

            }else{

                $query2=mysqli_query($connection,"SELECT st_enquiry_id FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_phno` LIKE '%$number%' and st_name='$memberName'");
                if(mysqli_num_rows($query2)==0){
                     echo 0;
                }else{
                    $query2Res=mysqli_fetch_array($query2);
                    echo '1||'.$query2Res['st_enquiry_id'];
                }

            }
    
        }

    }else{

        echo 0;

    }

}
if(@$_POST['formName']=='student_enquiry_common'){


    $enquiryFor=$_POST['enquiryFor'];
    if($enquiryFor==1){
        $studentName=$_POST['studentName'];
        $memberName=$_POST['memberName'];
    }else{
        $studentName=$_POST['memberName'];
        $memberName=$_POST['studentName'];
    }

    $contactName=$_POST['contactName'];
    $emailAddress=$_POST['emailAddress'];
    $courses=json_encode($_POST['courses']);
    $checkId=$_POST['checkId'];

    $surname=$_POST['surname'];
    $suburb=$_POST['suburb'];
    $prefComment=$_POST['prefComment'];
    $stuState=$_POST['stuState'];
    $postCode=$_POST['postCode'];
    $visit_before=$_POST['visit_before'];
    $hear_about=$_POST['hear_about'];
    $hearedby=$_POST['hearedby'];
    $plan_to_start_date=$_POST['plan_to_start_date'];
    $refer_select=$_POST['refer_select'];
    $referer_name=$_POST['referer_name'];
    $refer_alumni=$_POST['refer_alumni'];
    $streetDetails=$_POST['streetDetails'];
    $created_by=$_POST['admin_id'];
    $form_type=$_POST['form_type'];

    $query=mysqli_query($connection,"INSERT INTO student_enquiry(st_name,st_member_name,st_phno,st_email,st_course,st_surname,st_suburb,st_state,st_post_code,st_visited,st_heared,st_hearedby,st_startplan_date,st_refered,st_refer_name,st_refer_alumni,st_street_details,st_enquiry_for,st_pref_comments,st_created_by,st_gen_enq_type)VALUES('$studentName','$memberName','$contactName','$emailAddress','$courses','$surname','$suburb','$stuState',$postCode,$visit_before,'$hear_about','$hearedby','$plan_to_start_date',$refer_select,'$referer_name',$refer_alumni,'$streetDetails',$enquiryFor,'$prefComment',$created_by,$form_type)");

    $lastId=mysqli_insert_id($connection);
    $uniqueId=sprintf('EQ%05d', $lastId);
    $querys=mysqli_query($connection,"UPDATE student_enquiry SET st_enquiry_id='$uniqueId' WHERE st_id=$lastId");
    $error=mysqli_error($connection);
    if($error!=''){
        echo 0;
    }else{
        echo $uniqueId;
        // Email acknowledgement for this enquiry is disabled for now.
        // $mail_to=$emailAddress;
        // $mail_subject="Your Enquiry Successfully Created";
        // $mail_body="Please keep your enquiry ID noted for future uses<br><b>Enquiry ID: </b>".$uniqueId;
        // send_mail($mail_to,$mail_subject,$mail_body);
    }

}
// Public web enquiry (minimal form) - creates enquiry and returns Enquiry ID; student must register to access full form
if(@$_POST['formName']=='public_enquiry'){
    $enquiryFor = isset($_POST['enquiryFor']) ? (int)$_POST['enquiryFor'] : 1;
    if($enquiryFor==1){
        $studentName = mysqli_real_escape_string($connection, $_POST['studentName'] ?? '');
        $memberName = mysqli_real_escape_string($connection, $_POST['memberName'] ?? '');
    }else{
        $studentName = mysqli_real_escape_string($connection, $_POST['memberName'] ?? '');
        $memberName = mysqli_real_escape_string($connection, $_POST['studentName'] ?? '');
    }
    $contactName = mysqli_real_escape_string($connection, $_POST['contactName'] ?? '');
    $emailAddress = mysqli_real_escape_string($connection, $_POST['emailAddress'] ?? '');
    $courses = isset($_POST['courses']) && is_array($_POST['courses']) ? json_encode($_POST['courses']) : '[]';
    $surname = mysqli_real_escape_string($connection, $_POST['surname'] ?? '');
    $suburb = mysqli_real_escape_string($connection, $_POST['suburb'] ?? '');
    $prefComment = mysqli_real_escape_string($connection, $_POST['prefComment'] ?? '');
    $stuState = mysqli_real_escape_string($connection, $_POST['stuState'] ?? '0');
    $postCode = (int)($_POST['postCode'] ?? 0);
    $visit_before = (int)($_POST['visit_before'] ?? 0);
    $hear_about = mysqli_real_escape_string($connection, $_POST['hear_about'] ?? '');
    $hearedby = mysqli_real_escape_string($connection, $_POST['hearedby'] ?? '');
    $plan_to_start_date = mysqli_real_escape_string($connection, $_POST['plan_to_start_date'] ?? '0000-00-00 00:00:00');
    if($plan_to_start_date === '' || $plan_to_start_date === '0000-00-00') $plan_to_start_date = '0000-00-00 00:00:00';
    $refer_select = (int)($_POST['refer_select'] ?? 0);
    $referer_name = mysqli_real_escape_string($connection, $_POST['referer_name'] ?? '');
    $refer_alumni = (int)($_POST['refer_alumni'] ?? 0);
    $streetDetails = mysqli_real_escape_string($connection, $_POST['streetDetails'] ?? '');
    $enquiryDate = mysqli_real_escape_string($connection, $_POST['enquiry_date'] ?? date('Y-m-d'));
    $enquiryDate = $enquiryDate ? $enquiryDate . ' 00:00:00' : date('Y-m-d H:i:s');
    $student_user_id = 0; // public enquiry — no linked user
    $created_by = 0; // public
    $form_type = 2; // 2 = public web enquiry
    $payment = '';
    $visaStatus = 0;
    $visaCondition = 1;
    $visaNote = '';
    $comments = '';
    $remarks = '';
    $appointment_booked = 0;
    $courseType = 0;
    $shore = 0;
    $ethnicity = '';
    $query = mysqli_query($connection, "INSERT INTO student_enquiry(st_name,st_member_name,st_phno,st_email,st_course,st_fee,st_visa_status,st_visa_condition,st_visa_note,st_surname,st_suburb,st_state,st_post_code,st_visited,st_heared,st_hearedby,st_startplan_date,st_refered,st_refer_name,st_refer_alumni,st_comments,st_pref_comments,st_appoint_book,st_remarks,st_street_details,st_enquiry_for,st_enquiry_date,st_course_type,st_shore,st_ethnicity,student_user_id,st_created_by,st_gen_enq_type) VALUES ('$studentName','$memberName','$contactName','$emailAddress','$courses','$payment',$visaStatus,$visaCondition,'$visaNote','$surname','$suburb','$stuState',$postCode,$visit_before,'$hear_about','$hearedby','$plan_to_start_date',$refer_select,'$referer_name',$refer_alumni,'$comments','$prefComment',$appointment_booked,'$remarks','$streetDetails',$enquiryFor,'$enquiryDate',$courseType,$shore,'$ethnicity',$student_user_id,$created_by,$form_type)");
    $lastId = mysqli_insert_id($connection);
    $error = mysqli_error($connection);
    if($error !== '' || $lastId <= 0){
        echo json_encode(array('success' => false, 'message' => 'Could not save enquiry. Please try again.'));
        exit;
    }
    $uniqueId = sprintf('EQ%05d', $lastId);
    mysqli_query($connection, "UPDATE student_enquiry SET st_enquiry_id='$uniqueId' WHERE st_id=$lastId");
    echo json_encode(array('success' => true, 'enquiry_id' => $uniqueId));
    exit;
}
if(@$_POST['formName']=='student_enquiry'){

    if (!mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_contact_notes'"))) {
        @mysqli_query($connection, "ALTER TABLE student_enquiry ADD COLUMN st_contact_notes TEXT NULL");
    }
    if (!mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_source'"))) {
        @mysqli_query($connection, "ALTER TABLE student_enquiry ADD COLUMN st_enquiry_source TINYINT NULL DEFAULT NULL COMMENT '1=Website,2=Phone,3=Walk-in,4=Email,5=WhatsApp,6=FB/IG,7=Agent' AFTER `st_gen_enq_type`");
    }
    if (!mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_location'"))) {
        @mysqli_query($connection, "ALTER TABLE student_enquiry ADD COLUMN st_location VARCHAR(255) NULL DEFAULT NULL COMMENT 'Location from website popup' AFTER `st_enquiry_source`");
    }
    if (!mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_college'"))) {
        @mysqli_query($connection, "ALTER TABLE student_enquiry ADD COLUMN st_enquiry_college TINYINT NULL DEFAULT NULL COMMENT '1=Apt Training,2=Milton,3=NCA,4=Power Ed,5=Auz Training' AFTER `st_location`");
    }

    // Only mandatory field: valid email. Everything else can be empty (partial save / draft).
    $emailRaw = trim((string)($_POST['emailAddress'] ?? ''));
    if($emailRaw === '' || !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)){
        echo 'invalid_email';
        exit;
    }
    $emailAddress = mysqli_real_escape_string($connection, $emailRaw);

    $enquiryFor=isset($_POST['enquiryFor']) && $_POST['enquiryFor'] !== '' ? (int)$_POST['enquiryFor'] : 1;
    if($enquiryFor==1){
        $studentName=mysqli_real_escape_string($connection, trim((string)($_POST['studentName'] ?? '')));
        $memberName=mysqli_real_escape_string($connection, trim((string)($_POST['memberName'] ?? '')));
    }else{
        $studentName=mysqli_real_escape_string($connection, trim((string)($_POST['memberName'] ?? '')));
        $memberName=mysqli_real_escape_string($connection, trim((string)($_POST['studentName'] ?? '')));
    }

$contactName=mysqli_real_escape_string($connection, trim((string)($_POST['contactName'] ?? '')));
$courses_raw = isset($_POST['courses']) && is_array($_POST['courses']) ? $_POST['courses'] : array();
$courses=mysqli_real_escape_string($connection, json_encode($courses_raw));
$payment=mysqli_real_escape_string($connection, trim((string)($_POST['payment'] ?? '')));
$visaStatus=isset($_POST['visaStatus']) && $_POST['visaStatus'] !== '' ? (int)$_POST['visaStatus'] : 0;
$checkId=isset($_POST['checkId']) ? $_POST['checkId'] : 0;
$enquiryDate=trim((string)($_POST['enquiryDate'] ?? ''));
$enquiryDate=($enquiryDate === '') ? date('Y-m-d H:i:s') : $enquiryDate;
$enquiryDate=mysqli_real_escape_string($connection, $enquiryDate);

$surname=mysqli_real_escape_string($connection, trim((string)($_POST['surname'] ?? '')));
$suburb=mysqli_real_escape_string($connection, trim((string)($_POST['suburb'] ?? '')));
$stuState=isset($_POST['stuState']) && $_POST['stuState'] !== '' ? mysqli_real_escape_string($connection, (string)$_POST['stuState']) : '0';
$postCode=isset($_POST['postCode']) && $_POST['postCode'] !== '' ? (int)$_POST['postCode'] : 0;
$visit_before=isset($_POST['visit_before']) && $_POST['visit_before'] !== '' ? (int)$_POST['visit_before'] : 0;
$hear_about=mysqli_real_escape_string($connection, trim((string)($_POST['hear_about'] ?? '')));
$hearedby_raw = trim((string)($_POST['hearedby'] ?? ''));
$hearedby=mysqli_real_escape_string($connection, $hearedby_raw);
$plan_to_start_date=mysqli_real_escape_string($connection, trim((string)($_POST['plan_to_start_date'] ?? '')));
$refer_select=isset($_POST['refer_select']) && $_POST['refer_select'] !== '' ? (int)$_POST['refer_select'] : 0;
$referer_name=mysqli_real_escape_string($connection, trim((string)($_POST['referer_name'] ?? '')));
$refer_alumni=isset($_POST['refer_alumni']) && $_POST['refer_alumni'] !== '' ? (int)$_POST['refer_alumni'] : 0;
$visaCondition=isset($_POST['visaCondition']) && $_POST['visaCondition'] !== '' ? (int)$_POST['visaCondition'] : 1;
$comments=mysqli_real_escape_string($connection, trim((string)($_POST['comments'] ?? '')));
$prefComment=mysqli_real_escape_string($connection, trim((string)($_POST['prefComment'] ?? '')));
$appointment_booked=isset($_POST['appointment_booked']) && $_POST['appointment_booked'] !== '' ? (int)$_POST['appointment_booked'] : 0;
if(!empty($_POST['remarks']) && is_array($_POST['remarks'])){
    $remarks=mysqli_real_escape_string($connection, json_encode($_POST['remarks']));
}else{
    $remarks='';
}
$reg_grp_names=mysqli_real_escape_string($connection, trim((string)($_POST['reg_grp_names'] ?? '')));
$streetDetails=mysqli_real_escape_string($connection, trim((string)($_POST['streetDetails'] ?? '')));
$courseType=isset($_POST['courseType']) && $_POST['courseType'] !== '' ? (int)$_POST['courseType'] : 0;
$shore=isset($_POST['shore']) && $_POST['shore'] !== '' ? (int)$_POST['shore'] : 0;
$ethnicity=mysqli_real_escape_string($connection, trim((string)($_POST['ethnicity'] ?? '')));
$visaNote=mysqli_real_escape_string($connection, trim((string)($_POST['visaNote'] ?? '')));
$contact_notes=mysqli_real_escape_string($connection, trim((string)($_POST['contact_notes'] ?? '')));
$student_user_id = isset($_POST['student_user_id']) ? (int)$_POST['student_user_id'] : 0;
$created_by=isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;
$enquiry_source = isset($_POST['enquiry_source']) ? (int)$_POST['enquiry_source'] : 0;
if (in_array($enquiry_source, array(2, 4, 5, 6), true) && $hearedby_raw === '') {
    echo 'enquiry_source_staff_required';
    exit;
}
$location = mysqli_real_escape_string($connection, $_POST['location'] ?? '');
$enquiry_college = isset($_POST['enquiry_college']) ? (int)$_POST['enquiry_college'] : 0;
$formId=isset($_POST['formId']) ? $_POST['formId'] : 0;
$slot_book_status=isset($_POST['slot_book_status']) ? (int)$_POST['slot_book_status'] : 0;
$short_grp_status=isset($_POST['short_grp_status']) ? (int)$_POST['short_grp_status'] : 0;
$rpl_status=isset($_POST['rpl_status']) ? (int)$_POST['rpl_status'] : 0;
$reg_grp_status=isset($_POST['reg_grp_status']) ? (int)$_POST['reg_grp_status'] : 0;
$now=date('Y-m-d H:i:s');

$rpl_arrays=json_decode($_POST['rpl_arrays'] ?? '{}');
if($rpl_arrays === null){ $rpl_arrays = (object)array('rpl_exp'=>'','exp_in'=>'','exp_docs'=>'','exp_prev'=>'','exp_name'=>'','exp_years'=>'','exp_prev_name'=>''); }
$short_grps=json_decode($_POST['short_grps'] ?? '{}');
if($short_grps === null){ $short_grps = (object)array(); }
$slot_books=json_decode($_POST['slot_books'] ?? '{}');
if($slot_books === null){ $slot_books = (object)array('slot_book_time'=>'','slot_book_purpose'=>'','slot_book_date'=>'','slot_book_by'=>'','slot_book_link'=>0); }

// Student portal: allow update only for own enquiry
if((int)$checkId > 0 && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student'){
    $sid = (int)$_SESSION['user_id'];
    // Fetch student email for fallback ownership check (older records may have student_user_id = 0)
    $sid_email_row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT email FROM student_users WHERE id=$sid LIMIT 1"));
    $sid_email_esc = $sid_email_row ? mysqli_real_escape_string($connection, trim((string)$sid_email_row['email'])) : '';
    $check_own = mysqli_query($connection,
        "SELECT st_id, student_user_id FROM student_enquiry
         WHERE st_id=" . (int)$checkId . "
           AND (student_user_id=$sid" . ($sid_email_esc !== '' ? " OR LOWER(TRIM(st_email))=LOWER('$sid_email_esc')" : '') . ")"
    );
    if(!$check_own || mysqli_num_rows($check_own) === 0){
        echo 0;
        exit;
    }
    // Backfill student_user_id on older records where it was stored as 0
    $own_row = mysqli_fetch_assoc($check_own);
    if((int)($own_row['student_user_id'] ?? 0) !== $sid){
        mysqli_query($connection, "UPDATE student_enquiry SET student_user_id=$sid WHERE st_id=" . (int)$checkId . " LIMIT 1");
    }
    $created_by = 0; // student update: no staff modifier
}

// New enquiry (staff/admin only): if email already exists on an active enquiry, update that row instead of inserting
$dup_email_merge_update = false;
if((int)$checkId === 0 && (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student')){
    $dupQ = mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE st_enquiry_status=0 AND LOWER(TRIM(st_email))=LOWER('".$emailAddress."') ORDER BY st_id DESC LIMIT 1");
    if($dupQ && ($dupRow = mysqli_fetch_assoc($dupQ))){
        $existingId = (int)$dupRow['st_id'];
        if($existingId > 0){
            $checkId = $existingId;
            $formId = $existingId;
            $dup_email_merge_update = true;
            $rpl_status = mysqli_num_rows(mysqli_query($connection, "SELECT 1 FROM rpl_enquries WHERE enq_form_id=".$existingId." LIMIT 1")) > 0 ? 1 : 0;
            $short_grp_status = mysqli_num_rows(mysqli_query($connection, "SELECT 1 FROM short_group_form WHERE enq_form_id=".$existingId." LIMIT 1")) > 0 ? 1 : 0;
            $reg_grp_status = mysqli_num_rows(mysqli_query($connection, "SELECT 1 FROM regular_group_form WHERE enq_form_id=".$existingId." LIMIT 1")) > 0 ? 1 : 0;
            $slot_book_status = mysqli_num_rows(mysqli_query($connection, "SELECT 1 FROM slot_book WHERE enq_form_id=".$existingId." LIMIT 1")) > 0 ? 1 : 0;
        }
    }
}

// Duplicate-email path: merge POST with existing row — only overwrite fields that were actually submitted (non-empty).
// Otherwise empty POST values would wipe name, courses, remarks, RPL, etc.
if($dup_email_merge_update && (int)$checkId > 0){
    $dup_merge_ex_row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM student_enquiry WHERE st_id=".(int)$checkId." LIMIT 1"));
    if($dup_merge_ex_row){
        $e = $dup_merge_ex_row;
        $post_student_empty = trim((string)($_POST['studentName'] ?? '')) === '';
        $post_member_empty = trim((string)($_POST['memberName'] ?? '')) === '';
        if($post_student_empty){
            $studentName = mysqli_real_escape_string($connection, (string)$e['st_name']);
        }
        if($post_member_empty){
            $memberName = mysqli_real_escape_string($connection, (string)$e['st_member_name']);
        }
        if(trim((string)($_POST['contactName'] ?? '')) === ''){
            $contactName = mysqli_real_escape_string($connection, (string)$e['st_phno']);
        }
        if(empty($courses_raw) || (is_array($courses_raw) && count($courses_raw) === 0)){
            $courses = mysqli_real_escape_string($connection, (string)$e['st_course']);
        }
        if(trim((string)($_POST['payment'] ?? '')) === ''){
            $payment = mysqli_real_escape_string($connection, (string)$e['st_fee']);
        }
        if(trim((string)($_POST['surname'] ?? '')) === ''){
            $surname = mysqli_real_escape_string($connection, (string)$e['st_surname']);
        }
        if(trim((string)($_POST['suburb'] ?? '')) === ''){
            $suburb = mysqli_real_escape_string($connection, (string)$e['st_suburb']);
        }
        if(trim((string)($_POST['visaNote'] ?? '')) === ''){
            $visaNote = mysqli_real_escape_string($connection, (string)$e['st_visa_note']);
        }
        if(trim((string)($_POST['comments'] ?? '')) === ''){
            $comments = mysqli_real_escape_string($connection, (string)$e['st_comments']);
        }
        if(trim((string)($_POST['prefComment'] ?? '')) === ''){
            $prefComment = mysqli_real_escape_string($connection, (string)$e['st_pref_comments']);
        }
        if(trim((string)($_POST['streetDetails'] ?? '')) === ''){
            $streetDetails = mysqli_real_escape_string($connection, (string)$e['st_street_details']);
        }
        if(trim((string)($_POST['hear_about'] ?? '')) === ''){
            $hear_about = mysqli_real_escape_string($connection, (string)$e['st_heared']);
        }
        if(trim((string)($_POST['hearedby'] ?? '')) === ''){
            $hearedby = mysqli_real_escape_string($connection, (string)$e['st_hearedby']);
        }
        if(trim((string)($_POST['plan_to_start_date'] ?? '')) === ''){
            $plan_to_start_date = mysqli_real_escape_string($connection, (string)$e['st_startplan_date']);
        }
        if(trim((string)($_POST['referer_name'] ?? '')) === ''){
            $referer_name = mysqli_real_escape_string($connection, (string)$e['st_refer_name']);
        }
        if(trim((string)($_POST['ethnicity'] ?? '')) === ''){
            $ethnicity = mysqli_real_escape_string($connection, (string)$e['st_ethnicity']);
        }
        if(trim((string)($_POST['enquiryDate'] ?? '')) === ''){
            $enquiryDate = mysqli_real_escape_string($connection, (string)$e['st_enquiry_date']);
        }
        if(trim((string)($_POST['location'] ?? '')) === ''){
            $location = mysqli_real_escape_string($connection, (string)$e['st_location']);
        }
        if(trim((string)($_POST['contact_notes'] ?? '')) === '' && isset($e['st_contact_notes'])){
            $contact_notes = mysqli_real_escape_string($connection, (string)$e['st_contact_notes']);
        }
        if(empty($_POST['remarks']) || !is_array($_POST['remarks']) || count($_POST['remarks']) === 0){
            $remarks = mysqli_real_escape_string($connection, (string)$e['st_remarks']);
        }
        if(trim((string)($_POST['reg_grp_names'] ?? '')) === ''){
            $rgr = mysqli_fetch_assoc(mysqli_query($connection, "SELECT reg_grp_names FROM regular_group_form WHERE enq_form_id=".(int)$checkId." LIMIT 1"));
            if($rgr && isset($rgr['reg_grp_names'])){
                $reg_grp_names = mysqli_real_escape_string($connection, (string)$rgr['reg_grp_names']);
            }
        }
        if($post_student_empty && $post_member_empty){
            $enquiryFor = (int)$e['st_enquiry_for'];
        }
        // Numeric fields: POST often sends 0 as "unset" — keep DB when DB has a non-zero value
        if((int)$visaStatus === 0 && isset($e['st_visa_status']) && (int)$e['st_visa_status'] !== 0){
            $visaStatus = (int)$e['st_visa_status'];
        }
        if(isset($e['st_visa_condition']) && (int)$visaCondition === 1 && (int)$e['st_visa_condition'] !== 1){
            $visaCondition = (int)$e['st_visa_condition'];
        }
        if($postCode === 0 && isset($e['st_post_code']) && (int)$e['st_post_code'] !== 0){
            $postCode = (int)$e['st_post_code'];
        }
        if($visit_before === 0 && isset($e['st_visited']) && (int)$e['st_visited'] !== 0){
            $visit_before = (int)$e['st_visited'];
        }
        if($refer_select === 0 && isset($e['st_refered']) && (int)$e['st_refered'] !== 0){
            $refer_select = (int)$e['st_refered'];
        }
        if($refer_alumni === 0 && isset($e['st_refer_alumni']) && (int)$e['st_refer_alumni'] !== 0){
            $refer_alumni = (int)$e['st_refer_alumni'];
        }
        if($appointment_booked === 0 && isset($e['st_appoint_book']) && (int)$e['st_appoint_book'] !== 0){
            $appointment_booked = (int)$e['st_appoint_book'];
        }
        if((int)$courseType === 0 && isset($e['st_course_type']) && (int)$e['st_course_type'] !== 0){
            $courseType = (int)$e['st_course_type'];
        }
        if($shore === 0 && isset($e['st_shore']) && (int)$e['st_shore'] !== 0){
            $shore = (int)$e['st_shore'];
        }
        if($enquiry_source === 0 && isset($e['st_enquiry_source']) && (int)$e['st_enquiry_source'] !== 0){
            $enquiry_source = (int)$e['st_enquiry_source'];
        }
        if($enquiry_college === 0 && isset($e['st_enquiry_college']) && (int)$e['st_enquiry_college'] !== 0){
            $enquiry_college = (int)$e['st_enquiry_college'];
        }
        if(($stuState === '0' || (string)$stuState === '0') && isset($e['st_state']) && (string)$e['st_state'] !== '' && (string)$e['st_state'] !== '0'){
            $stuState = mysqli_real_escape_string($connection, (string)$e['st_state']);
        }
        // Reload sub-form payloads from DB when JSON from POST is empty (avoids wiping RPL / short group / slot)
        $rpl_post_empty = true;
        if(is_object($rpl_arrays)){
            foreach((array)$rpl_arrays as $rv){
                if(trim((string)$rv) !== ''){ $rpl_post_empty = false; break; }
            }
        }
        if($rpl_post_empty && (int)$courseType === 1){
            $rpl_row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM rpl_enquries WHERE enq_form_id=".(int)$checkId." LIMIT 1"));
            if($rpl_row){
                $rpl_arrays = (object)array(
                    'rpl_exp' => $rpl_row['rpl_exp'] ?? '',
                    'exp_in' => $rpl_row['rpl_exp_in'] ?? '',
                    'exp_docs' => $rpl_row['rpl_exp_docs'] ?? '',
                    'exp_prev' => $rpl_row['rpl_exp_prev_qual'] ?? '',
                    'exp_name' => $rpl_row['rpl_exp_role'] ?? '',
                    'exp_years' => $rpl_row['rpl_exp_years'] ?? '',
                    'exp_prev_name' => $rpl_row['rpl_exp_qual_name'] ?? '',
                );
            }
        }
        $short_post_empty = true;
        if(is_object($short_grps)){
            foreach((array)$short_grps as $sv){
                if(trim((string)$sv) !== ''){ $short_post_empty = false; break; }
            }
        }
        if($short_post_empty && ((int)$courseType === 4 || (int)$courseType === 5)){
            $sg = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM short_group_form WHERE enq_form_id=".(int)$checkId." LIMIT 1"));
            if($sg){
                $short_grps = (object)array(
                    'short_grp_org_name' => $sg['sh_org_name'] ?? '',
                    'short_grp_org_type' => $sg['sh_grp_org_type'] ?? '',
                    'short_grp_campus' => $sg['sh_grp_campus'] ?? '',
                    'short_grp_date' => $sg['sh_grp_date'] ?? '',
                    'short_grp_num_std' => $sg['sh_grp_num_stds'] ?? '',
                    'short_grp_ind_exp' => $sg['sh_grp_ind_exp'] ?? '',
                    'short_grp_before' => $sg['sh_grp_train_bef'] ?? '',
                    'short_grp_con_type' => $sg['sh_grp_con_us'] ?? '',
                    'short_grp_con_num' => $sg['sh_grp_phone'] ?? '',
                    'short_grp_con_name' => $sg['sh_grp_name'] ?? '',
                    'short_grp_con_email' => $sg['sh_grp_email'] ?? '',
                );
            }
        }
        $slot_post_empty = true;
        if(is_object($slot_books)){
            foreach((array)$slot_books as $bk){
                if(trim((string)$bk) !== '' && (string)$bk !== '0'){ $slot_post_empty = false; break; }
            }
        }
        if($slot_post_empty && (int)$appointment_booked === 1){
            $sb = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM slot_book WHERE enq_form_id=".(int)$checkId." LIMIT 1"));
            if($sb){
                $slot_books = (object)array(
                    'slot_book_time' => $sb['slot_bk_datetime'] ?? '',
                    'slot_book_purpose' => $sb['slot_bk_purpose'] ?? '',
                    'slot_book_date' => $sb['slot_bk_on'] ?? '',
                    'slot_book_by' => $sb['slot_book_by'] ?? '',
                    'slot_book_link' => isset($sb['slot_book_email_link']) ? (int)$sb['slot_book_email_link'] : 0,
                );
            }
        }
    }
}

if($checkId==0){

    $enquiry_source_val = $enquiry_source > 0 ? $enquiry_source : 'NULL';
$enquiry_college_val = $enquiry_college > 0 ? $enquiry_college : 'NULL';
$query=mysqli_query($connection,"INSERT INTO student_enquiry(st_name,st_member_name,st_phno,st_email,st_course,st_fee,st_visa_status,st_visa_condition,st_visa_note,st_surname,st_suburb,st_state,st_post_code,st_visited,st_heared,st_hearedby,st_startplan_date,st_refered,st_refer_name,st_refer_alumni,st_comments,st_pref_comments,st_appoint_book,st_remarks,st_street_details,st_enquiry_for,st_enquiry_date,st_course_type,st_shore,st_ethnicity,st_created_by,st_enquiry_source,st_location,st_enquiry_college,st_contact_notes,student_user_id)VALUES('$studentName','$memberName','$contactName','$emailAddress','$courses','$payment',$visaStatus,$visaCondition,'$visaNote','$surname','$suburb','$stuState',$postCode,$visit_before,'$hear_about','$hearedby','$plan_to_start_date',$refer_select,'$referer_name',$refer_alumni,'$comments','$prefComment',$appointment_booked,'$remarks','$streetDetails',$enquiryFor,'$enquiryDate',$courseType,$shore,'$ethnicity',$created_by,$enquiry_source_val,'$location',$enquiry_college_val,'$contact_notes',$student_user_id)");
    
    echo mysqli_error($connection);
    $lastId=mysqli_insert_id($connection);
    $uniqueId=sprintf('EQ%05d', $lastId);
    $querys=mysqli_query($connection,"UPDATE student_enquiry SET st_enquiry_id='$uniqueId' WHERE st_id=$lastId");
    $error=mysqli_error($connection);
    if($error!=''){
        echo 0;
    }else{
        echo $uniqueId;
        // Automatic enquiry acknowledgement email disabled for now (handled manually from Follow-up section).
        // $mail_to = $emailAddress;
        // $mail_subject = "Enquiry received – Ref: " . $uniqueId;
        // $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        // $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // $script_path = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        // $crm_base = rtrim($protocol . '://' . $host . $script_path, '/') . '/';
        // $eq_token = base64_encode((string)$lastId);
        // $enquiry_form_url = $crm_base . 'student_enquiry.php?eq=' . $eq_token;
        // $register_url = $crm_base . 'student_register.php';
        // $login_url = $crm_base . 'student_login.php';
        // $mail_body = enquiry_ack_email_body($studentName, $uniqueId, $enquiry_form_url, $register_url, $login_url);
        // send_mail($mail_to, $mail_subject, $mail_body);
        // insert course Type data
        if($courseType==1){

            $query=mysqli_query($connection,"INSERT INTO `rpl_enquries` (`enq_form_id`,`rpl_exp_in`,`rpl_exp_role`,`rpl_exp_years`,`rpl_exp_docs`,`rpl_exp_prev_qual`,`rpl_exp_qual_name`,`rpl_exp`) VALUES( $lastId,'".$rpl_arrays->exp_in."','".$rpl_arrays->exp_name."','".$rpl_arrays->exp_years."','".$rpl_arrays->exp_docs."','".$rpl_arrays->exp_prev."','".$rpl_arrays->exp_prev_name."','".$rpl_arrays->rpl_exp."' )");

        }else if($courseType==5 || $courseType==4){
         
            
            $query=mysqli_query($connection,"INSERT INTO `short_group_form` (`enq_form_id`) VALUES( $lastId )");

            $query2=mysqli_query($connection,"UPDATE `short_group_form` SET `sh_org_name`='$short_grps->short_grp_org_name',`sh_grp_org_type`='$short_grps->short_grp_org_type',`sh_grp_campus`='$short_grps->short_grp_campus',`sh_grp_date`='$short_grps->short_grp_date',`sh_grp_num_stds`='$short_grps->short_grp_num_std',`sh_grp_ind_exp`='$short_grps->short_grp_ind_exp',`sh_grp_train_bef`='$short_grps->short_grp_before',`sh_grp_con_us`='$short_grps->short_grp_con_type',`sh_grp_phone`='$short_grps->short_grp_con_num',`sh_grp_name`='$short_grps->short_grp_con_name',`sh_grp_email`='$short_grps->short_grp_con_email' WHERE `enq_form_id`=$lastId");


        }else if($courseType==3){        

            $query=mysqli_query($connection,"INSERT INTO `regular_group_form` (`enq_form_id`,`reg_grp_names`) VALUES($lastId,'".$reg_grp_names."')");

        }


        if($appointment_booked==1){

            $appointment_booked_time=date('Y-m-d H:i:s',strtotime($slot_books->slot_book_time));

            $query=mysqli_query($connection,"INSERT INTO `slot_book` (`enq_form_id`,`slot_bk_datetime`,`slot_bk_purpose`,`slot_bk_on`,`slot_book_by`,`slot_book_email_link`) VALUES( $lastId,'".$appointment_booked_time."','".$slot_books->slot_book_purpose."','".$slot_books->slot_book_date."','".$slot_books->slot_book_by."',".$slot_books->slot_book_link." )");
        }        

    }

}else{
    // On edit: always save submitted email (duplicate-checked). If linked to student_users, keep portal email in sync when it changes.
    $cidUp = (int)$checkId;
    if($cidUp > 0){
        $emR = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_email, student_user_id FROM student_enquiry WHERE st_id=".$cidUp." LIMIT 1"));
        if($emR && array_key_exists('st_email', $emR)){
            $dupOther = mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE st_enquiry_status=0 AND LOWER(TRIM(st_email))=LOWER('".$emailAddress."') AND st_id<>".$cidUp." LIMIT 1");
            if($dupOther && mysqli_num_rows($dupOther) > 0){
                echo 'email_duplicate';
                exit;
            }
            $suid = isset($emR['student_user_id']) ? (int)$emR['student_user_id'] : 0;
            $new_lower = strtolower(trim($emailRaw));
            $old_lower = strtolower(trim((string)$emR['st_email']));
            if($suid > 0 && $new_lower !== $old_lower){
                $em_exists = mysqli_query($connection, "SELECT id FROM student_users WHERE status=1 AND LOWER(TRIM(email))=LOWER('".$emailAddress."') AND id<>".$suid." LIMIT 1");
                if($em_exists && mysqli_num_rows($em_exists) > 0){
                    echo 'email_duplicate_student_users';
                    exit;
                }
                mysqli_query($connection, "UPDATE student_users SET email='".$emailAddress."' WHERE id=".$suid." LIMIT 1");
            }
        }
    }

    $enquiry_source_update = $enquiry_source > 0 ? $enquiry_source : 'NULL';
$enquiry_college_update = $enquiry_college > 0 ? $enquiry_college : 'NULL';
if(mysqli_query($connection,"UPDATE student_enquiry SET `st_name`='$studentName',`st_member_name`='$memberName' ,`st_phno`='$contactName',`st_email`='$emailAddress',`st_course`='$courses',`st_fee`='$payment',`st_visa_status`=$visaStatus,`st_visa_condition`=$visaCondition ,`st_visa_note`='$visaNote', `st_surname`='$surname' , `st_suburb`= '$suburb' , `st_state`='$stuState',`st_post_code`= $postCode,`st_visited`=$visit_before,`st_heared`='$hear_about',`st_hearedby`='$hearedby',`st_startplan_date`='$plan_to_start_date',`st_refered`=$refer_select,`st_refer_name`='$referer_name',`st_refer_alumni`=$refer_alumni,`st_comments`='$comments',`st_pref_comments`='$prefComment',`st_appoint_book`= $appointment_booked,`st_remarks`='$remarks',`st_street_details`= '$streetDetails' , `st_enquiry_for`= $enquiryFor , `st_enquiry_date`='$enquiryDate' ,`st_course_type`=$courseType , `st_shore`=$shore,`st_ethnicity`='$ethnicity',`student_user_id`=$student_user_id,`st_modified_by`= $created_by , `st_modified_date`='$now', `st_enquiry_source`=$enquiry_source_update, `st_location`='$location', `st_enquiry_college`=$enquiry_college_update, `st_contact_notes`='$contact_notes' WHERE `st_id`=$checkId")){        

        // insert course Type data
        if($courseType==1){

            if($rpl_status==1){

            $query=mysqli_query($connection,"UPDATE `rpl_enquries` set `rpl_exp_in` ='$rpl_arrays->exp_in' ,`rpl_exp_role` ='$rpl_arrays->exp_name' ,`rpl_exp_years` = '$rpl_arrays->exp_years',`rpl_exp_docs` ='$rpl_arrays->exp_docs' ,`rpl_exp_prev_qual` ='$rpl_arrays->exp_prev' ,`rpl_exp_qual_name` ='$rpl_arrays->exp_prev_name' ,`rpl_exp` ='$rpl_arrays->rpl_exp' WHERE `enq_form_id` = $formId");

            // echo "UPDATE `rpl_enquries` set `rpl_exp_in` ='$rpl_arrays->exp_in' ,`rpl_exp_role` ='$rpl_arrays->exp_name' ,`rpl_exp_years` = '$rpl_arrays->exp_years',`rpl_exp_docs` ='$rpl_arrays->exp_docs' ,`rpl_exp_prev_qual` ='$rpl_arrays->exp_prev' ,`rpl_exp_qual_name` ='$rpl_arrays->exp_prev_name' ,`rpl_exp` ='$rpl_arrays->rpl_exp' WHERE `enq_form_id` = $formId";

        }else{

            $query=mysqli_query($connection,"INSERT INTO `rpl_enquries` (`enq_form_id`,`rpl_exp_in`,`rpl_exp_role`,`rpl_exp_years`,`rpl_exp_docs`,`rpl_exp_prev_qual`,`rpl_exp_qual_name`,`rpl_exp`) VALUES( $formId,'".$rpl_arrays->exp_in."','".$rpl_arrays->exp_name."','".$rpl_arrays->exp_years."','".$rpl_arrays->exp_docs."','".$rpl_arrays->exp_prev."','".$rpl_arrays->exp_prev_name."','".$rpl_arrays->rpl_exp."' )");


        }

        }else if($courseType==5 || $courseType==4){

            if($short_grp_status==1){
                     
                    $query=mysqli_query($connection,"UPDATE `short_group_form` SET `sh_org_name`='$short_grps->short_grp_org_name',`sh_grp_org_type`='$short_grps->short_grp_org_type',`sh_grp_campus`='$short_grps->short_grp_campus',`sh_grp_date`='$short_grps->short_grp_date',`sh_grp_num_stds`='$short_grps->short_grp_num_std',`sh_grp_ind_exp`='$short_grps->short_grp_ind_exp',`sh_grp_train_bef`='$short_grps->short_grp_before',`sh_grp_con_us`='$short_grps->short_grp_con_type',`sh_grp_phone`='$short_grps->short_grp_con_num',`sh_grp_name`='$short_grps->short_grp_con_name',`sh_grp_email`='$short_grps->short_grp_con_email' WHERE `enq_form_id`=$formId");

            }else{

                $query=mysqli_query($connection,"INSERT INTO `short_group_form` (`enq_form_id`) VALUES( $formId )");

                $query2=mysqli_query($connection,"UPDATE `short_group_form` SET `sh_org_name`='$short_grps->short_grp_org_name',`sh_grp_org_type`='$short_grps->short_grp_org_type',`sh_grp_campus`='$short_grps->short_grp_campus',`sh_grp_date`='$short_grps->short_grp_date',`sh_grp_num_stds`='$short_grps->short_grp_num_std',`sh_grp_ind_exp`='$short_grps->short_grp_ind_exp',`sh_grp_train_bef`='$short_grps->short_grp_before',`sh_grp_con_us`='$short_grps->short_grp_con_type',`sh_grp_phone`='$short_grps->short_grp_con_num',`sh_grp_name`='$short_grps->short_grp_con_name',`sh_grp_email`='$short_grps->short_grp_con_email' WHERE `enq_form_id`=$formId");

            }

        }

        else if($courseType==3){

            if($reg_grp_status==1){

                $query=mysqli_query($connection,"UPDATE `regular_group_form` SET `reg_grp_names`='".$reg_grp_names."' WHERE `enq_form_id`=$formId");

            }else{

                $query=mysqli_query($connection,"INSERT INTO `regular_group_form` (`enq_form_id`,`reg_grp_names`) VALUES($formId,'".$reg_grp_names."')");

            }
            

        }


        if($appointment_booked==1){

            $appointment_booked_time=date('Y-m-d H:i:s',strtotime($slot_books->slot_book_time));

            if($slot_book_status==1){


                $query=mysqli_query($connection,"UPDATE `slot_book` SET `slot_bk_datetime` = '$appointment_booked_time',`slot_bk_purpose` ='$slot_books->slot_book_purpose' ,`slot_bk_on` = '$slot_books->slot_book_date',`slot_book_by` = '$slot_books->slot_book_by',`slot_book_email_link` = $slot_books->slot_book_link WHERE `enq_form_id` = $formId");

            }else{

                $query=mysqli_query($connection,"INSERT INTO `slot_book` (`enq_form_id`,`slot_bk_datetime`,`slot_bk_purpose`,`slot_bk_on`,`slot_book_by`,`slot_book_email_link`) VALUES( $formId,'".$appointment_booked_time."','".$slot_books->slot_book_purpose."','".$slot_books->slot_book_date."','".$slot_books->slot_book_by."',".$slot_books->slot_book_link." )");


            }
            


        }


        echo 2;
    }else{
        echo 0;
    }

}

}

if(@$_POST['formName']=='delete_enq'){
    $tableName = isset($_POST['tableName']) ? $_POST['tableName'] : '';
    // Enquiry soft-delete: admins only (staff cannot delete enquiries)
    if($tableName === 'student_enquiry' && (int)@$_SESSION['user_type'] !== 1){
        echo 0;
        exit;
    }
    $enq_id=$_POST['eq_id'];
    $note=$_POST['note'];
    $primId=$_POST['colPrefix'].'_id';
    $delColName=$_POST['colPrefix'].'_delete_note';
    $colPrefix=$_POST['colPrefix'].'_enquiry_status';

    $deleted_enq_email = '';
    if($tableName === 'student_enquiry'){
        $eqid = (int)$enq_id;
        $er = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_email FROM student_enquiry WHERE st_id=$eqid AND st_enquiry_status=0 LIMIT 1"));
        if($er && !empty($er['st_email'])) $deleted_enq_email = (string)$er['st_email'];
    }
    $query=mysqli_query($connection,"UPDATE $tableName SET `$delColName`='$note' , `$colPrefix`=1 WHERE `$primId`=$enq_id");
    // echo "UPDATE $tableName SET `$delColName`='$note' , `$colPrefix`=1 WHERE `$primId`=$enq_id";
if($query){
    if($tableName === 'student_enquiry' && $deleted_enq_email !== ''){
        crm_student_user_deactivate_if_no_active_enquiry_by_email($connection, $deleted_enq_email);
    }
    echo 1;
}else{
    echo 0;
}
}

// Bulk soft-delete enquiries (View Enquiries list) — same fields as delete_enq for student_enquiry (admins only)
if(@$_POST['formName']=='bulk_delete_enquiry'){
    header('Content-Type: application/json; charset=utf-8');
    if(!isset($_SESSION['user_id']) || (int)@$_SESSION['user_type'] !== 1){
        echo json_encode(array('ok'=>0, 'error'=>'forbidden'));
        exit;
    }
    $note = isset($_POST['note']) ? mysqli_real_escape_string($connection, trim((string)$_POST['note'])) : '';
    $ids_raw = isset($_POST['ids']) ? $_POST['ids'] : '';
    $ids = array();
    if(is_string($ids_raw) && $ids_raw !== ''){
        $decoded = json_decode($ids_raw, true);
        if(is_array($decoded)){
            foreach($decoded as $id){ $ids[] = (int)$id; }
        } else {
            foreach(explode(',', $ids_raw) as $id){
                $id = (int)trim($id);
                if($id > 0) $ids[] = $id;
            }
        }
    }
    $ids = array_values(array_unique(array_filter($ids)));
    if($note === '' || empty($ids)){
        echo json_encode(array('ok'=>0, 'error'=>'invalid'));
        exit;
    }
    $in = implode(',', $ids);
    $emails_to_check = array();
    $eqEmailsQ = @mysqli_query($connection, "SELECT DISTINCT LOWER(TRIM(st_email)) AS em FROM student_enquiry WHERE st_id IN ($in) AND st_enquiry_status=0");
    if($eqEmailsQ){
        while($emr = mysqli_fetch_assoc($eqEmailsQ)){
            if(!empty($emr['em'])) $emails_to_check[] = (string)$emr['em'];
        }
    }
    $query = mysqli_query($connection, "UPDATE student_enquiry SET st_delete_note='$note', st_enquiry_status=1 WHERE st_id IN ($in) AND st_enquiry_status=0");
    if($query){
        if(!empty($emails_to_check)){
            foreach(array_unique($emails_to_check) as $emx){
                crm_student_user_deactivate_if_no_active_enquiry_by_email($connection, $emx);
            }
        }
        echo json_encode(array('ok'=>1, 'affected'=>(int)mysqli_affected_rows($connection)));
    } else {
        echo json_encode(array('ok'=>0, 'error'=>'db'));
    }
    exit;
}

if(@$_POST['formName']=='process_to_lln'){
    if(!isset($_SESSION['user_id']) || (int)@$_SESSION['user_type'] !== 1){
        echo '0';
        exit;
    }
    $st_id = isset($_POST['st_id']) ? (int)$_POST['st_id'] : 0;
    if($st_id <= 0){
        echo '0';
        exit;
    }
    $enquiry = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_enquiry_id, st_name, st_member_name, st_course, st_surname, st_phno, st_email, student_user_id FROM student_enquiry WHERE st_id=$st_id AND st_enquiry_status=0 LIMIT 1"));
    if(!$enquiry || empty($enquiry['st_enquiry_id'])){
        echo '0';
        exit;
    }
    $st_enquiry_id = mysqli_real_escape_string($connection, trim($enquiry['st_enquiry_id']));
    $counsel_name = mysqli_real_escape_string($connection, trim($enquiry['st_member_name'] !== '' ? $enquiry['st_member_name'] : $enquiry['st_name']));
    $counsel_course = mysqli_real_escape_string($connection, trim($enquiry['st_course']));
    $st_surname = mysqli_real_escape_string($connection, trim($enquiry['st_surname']));
    $st_phno = mysqli_real_escape_string($connection, trim($enquiry['st_phno']));
    $st_email = mysqli_real_escape_string($connection, trim($enquiry['st_email']));
    $student_user_id = (int)$enquiry['student_user_id'];
    $existing = @mysqli_fetch_row(mysqli_query($connection, "SELECT 1 FROM counseling_details WHERE st_enquiry_id='$st_enquiry_id' AND counsil_enquiry_status=0 LIMIT 1"));
    if($existing){
        echo 'exists';
        exit;
    }
    $admin_id = (int)$_SESSION['user_id'];
    $query = mysqli_query($connection, "INSERT INTO counseling_details (`st_enquiry_id`,`counsil_mem_name`,`counsil_course`,`counsil_timing`,`counsil_createdby`, `student_user_id`) VALUES ('$st_enquiry_id','$counsel_name','$counsel_course',NOW(),$admin_id, $student_user_id)");
    if(!$query){
        echo '0';
        exit;
    }

    $existing_enrol = @mysqli_fetch_row(mysqli_query($connection, "SELECT 1 FROM student_enrolments WHERE st_enquiry_id='$st_enquiry_id' LIMIT 1"));
    if(!$existing_enrol){
        $insertEnrol = "INSERT INTO student_enrolments (`st_unique_id`,`st_enquiry_id`,`st_rto_name`,`st_courses`,`st_branch`,`st_photo`,`st_given_name`,`st_surname`,`st_email`,`st_mobile`,`st_status`,`st_created_by`) VALUES ('', '$st_enquiry_id', '', '$counsel_course', '', '', '$counsel_name', '$st_surname', '$st_email', '$st_phno', 0, $admin_id)";
        @mysqli_query($connection, $insertEnrol);
    }

    echo '1';
    exit;
}

if(@$_POST['formName']=='delete_enrol'){
    $enrol_id=$_POST['enrol_id'];
    $query=mysqli_query($connection,"UPDATE `student_enrolment` SET `st_enrol_status`=1 WHERE `st_enrol_id`=$enrol_id");
if($query){
    echo 1;
}else{
    echo 0;
}
}

if(@$_POST['formName']=='student_filter'){

    $crm_enquiry_delete_allowed = isset($_SESSION['user_id']) && (int)@$_SESSION['user_type'] === 1;

    $visa_status=$_POST['visa_status'];
    $appointment_status=$_POST['appointment_status'];
    $course_type_status=$_POST['course_type_status'];
    $state_status=$_POST['state_status'];
    $WHERE='';    

    if($visa_status!=0){
        $WHERE.=" AND st_visa_condition=$visa_status";
    }

    if($appointment_status!=0){
        $WHERE.=" AND st_appoint_book=$appointment_status";
    }
    
    if($course_type_status!=0){
        $WHERE.=" AND st_course_type=$course_type_status";
    }

    if($state_status!=0){
        $WHERE.=" AND st_state=$state_status";
    }



    $filterQuery="SELECT * FROM `student_enquiry` WHERE st_enquiry_status=0 $WHERE";

    $filterQueryget=mysqli_query($connection,$filterQuery);
    $tbody='';



if(mysqli_num_rows($filterQueryget)!=0){

    while($filterQueryRes=mysqli_fetch_array($filterQueryget)){

        $tbody.='<tr>';

        $coursesNames=json_decode($filterQueryRes['st_course']);
        $coursesName='<div class="td_scroll_height">';
        foreach($coursesNames as $value){
            $courses=mysqli_fetch_array(mysqli_query($connection,"SELECT * from courses where course_status!=1 AND course_id=".$value));
            $coursesName.= $courses['course_sname'].'-'.$courses['course_name']." , <br>"; 
        }

        $st_states=['--select--','NSW - New South Wales','VIC - Victoria','ACT - Australian Capital Territory','NT - Northern Territoy','WA - Western Australia','QLD - Queensland','SA - South Australia','TAS - Tasmania'];
        $state_name= $st_states[$filterQueryRes['st_state']];
        
        $st_course_type=['-','Need exemption','Regular','Regular - Group','Short courses','Short course - Group'];
        $courseTypeId=$filterQueryRes['st_course_type'];
    
        $coursesNamePos = strrpos($coursesName, ',');
        $coursesName = substr($coursesName, 0, $coursesNamePos);
        $coursesName.='</div>';
    
        $visited=$filterQueryRes['st_visited']==1 ? 'Visited' : ( $filterQueryRes['st_visited']==2 ? 'Not Visited' : ' - ' ) ;
        
        $visastatus=$filterQueryRes['st_visa_condition']==1 ? 'Approved' : 'Not Approved' ;
    
        $refered_names = $filterQueryRes['st_refer_name'];
    
        $startPlanDate=date('d M Y',strtotime($filterQueryRes['st_startplan_date']));
    
        $staff_comments=$filterQueryRes['st_comments'];
        $preference=$filterQueryRes['st_pref_comments'];
    
        $st_remarks=['--select--','Seems to be interested to do course and need to contact asap','contacted and followed','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose'];    
    
        if($filterQueryRes['st_remarks']!=''){
            $remarksNotes='<div class="td_scroll_height">';
    
        foreach(json_decode($filterQueryRes['st_remarks']) as $remark  ){                   
            
            $remarksNotes.=$st_remarks[$remark].' , <br>';
    
        }
        $remarksNotes.='</div>';
        }else{
            $remarksNotes=' - ';
            
        }
    
        $street=$filterQueryRes['st_street_details'];
        $suburb=$filterQueryRes['st_suburb'];
        $post_code=$filterQueryRes['st_post_code'];
        $appointment=$filterQueryRes['st_appoint_book']==1 ? 'Booked' : ( $filterQueryRes['st_appoint_book']==2 ? 'Not Booked' : ' - ' );
        
        $querys2=mysqli_query($connection,"select visa_status_name from `visa_statuses` WHERE visa_id=".$filterQueryRes['st_visa_status']);
        if(mysqli_num_rows($querys2)!=0){
        $visaCondition=mysqli_fetch_array($querys2);
    
        if(@$visaCondition['visa_status_name'] && $visaCondition['visa_status_name']!=''){
            $visacCond=$visaCondition['visa_status_name'];
        }else{
            $visacCond=' - ';
        }
        }else{
            $visacCond=' - ';
        }

        $appointment=$filterQueryRes['st_appoint_book']==1 ? 'Booked' : ( $filterQueryRes['st_appoint_book']==2 ? 'Not Booked' : ' - ' );

        $dateCreated=date('d M Y',strtotime($filterQueryRes['st_enquiry_date']));
        
    
            $view='<a class="btn btn-outline-primary btn-sm edit_enq" style="margin-right:10px;" href="student_enquiry.php?eq='.base64_encode($filterQueryRes['st_id']).'">Edit</a>';
            if(!empty($crm_enquiry_delete_allowed)){
                $view.='<button onclick="delete_enq(\'student_enquiry\',\'st\','.$filterQueryRes['st_id'].')" type="button" class="btn btn-outline-danger btn-sm">Delete</button>';
            }


            $tbody.='<td>'.$filterQueryRes['st_enquiry_id'].'</td>
                    <td>'.$filterQueryRes['st_name'].'</td>
                    <td>'.$filterQueryRes['st_phno'].'</td>
                    <td>'.$filterQueryRes['st_email'].'</td>
                    <td>'.$street.'</td>
                    <td>'.$suburb.'</td>
                    <td>'.$state_name.'</td>
                    <td>'.$post_code.'</td>
                    <td>'.$coursesName.'</td>
                    <td>'.$startPlanDate.'</td>
                    <td>'.$st_course_type[$courseTypeId].'</td>
                    <td>'.$visited.'</td>
                    <td>'.$dateCreated.'</td>
                    <td>'.$refered_names.'</td>
                    <td>'.$filterQueryRes['st_fee'].'</td>
                    <td>'.$appointment.'</td>
                    <td>'.$visacCond.'</td>
                    <td>'.$visastatus.'</td></tr>';
    
            // array_push($enquiries['data'],array('st_enquiry_id'=>$filterQueryRes['st_enquiry_id'],'std_name'=>$filterQueryRes['st_name'], 'std_phno'=>$filterQueryRes['st_phno'],'std_email'=>$filterQueryRes['st_email'],'street'=>$street,'suburb'=>$suburb,'post_code'=>$post_code,'std_course'=>$coursesName,'startplan_date'=>$startPlanDate,'referedby'=>$refered_names,'visited'=>$visited,'st_coursetype'=>$st_course_type[$courseTypeId],'std_fee'=>$filterQueryRes['st_fee'],'appointment'=>$appointment,'Visa_condition'=>$visacCond,'std_visa_status'=>$visastatus));
            
        }

        echo $tbody;
    }else{
        echo "<tr><td>No Records</td></tr>";
    }


    
}
if(@$_POST['formName']=='followup_call'){
        if (!mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_change_stage'"))) {
            @mysqli_query($connection, "ALTER TABLE `student_enquiry` ADD COLUMN `st_enquiry_flow_change_stage` VARCHAR(8) NULL DEFAULT NULL COMMENT 'PEFU or PCFU when enquiry status last set from follow-up outcome'");
        }
        if (!mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_followup_stage'"))) {
            @mysqli_query($connection, "ALTER TABLE followup_calls ADD COLUMN `flw_followup_stage` VARCHAR(32) NOT NULL DEFAULT 'enquiry'");
        }
        $followup_stage_raw = isset($_POST['followup_stage']) ? trim((string) $_POST['followup_stage']) : 'enquiry';
        $followup_stage_esc = ($followup_stage_raw === 'post_counselling') ? 'post_counselling' : 'enquiry';
        $student_name=mysqli_real_escape_string($connection,isset($_POST['student_name']) ? $_POST['student_name'] : '');
        $date=isset($_POST['date']) && $_POST['date']!='' ? date('Y-m-d',strtotime($_POST['date'])) : (isset($_POST['contacted_time']) && $_POST['contacted_time']!='' ? date('Y-m-d',strtotime($_POST['contacted_time'])) : '');
        $contacted_person=mysqli_real_escape_string($connection,isset($_POST['contacted_person']) ? $_POST['contacted_person'] : '');
        $contacted_time=isset($_POST['contacted_time']) && $_POST['contacted_time']!='' ? date('Y-m-d H:i:s',strtotime($_POST['contacted_time'])) : date('Y-m-d H:i:s');
        $contactMode=isset($_POST['contactMode']) ? mysqli_real_escape_string($connection,$_POST['contactMode']) : '';
        $followup_type=isset($_POST['followup_type']) ? mysqli_real_escape_string($connection,$_POST['followup_type']) : '';
        $enquiry_flow_status=isset($_POST['enquiry_flow_status']) && $_POST['enquiry_flow_status']!=='' ? (int)$_POST['enquiry_flow_status'] : null;
        $follow_up_outcome_trim = isset($_POST['follow_up_outcome']) ? trim((string) $_POST['follow_up_outcome']) : '';
        $followup_outcome_drove_status = false;
        if ($follow_up_outcome_trim !== '') {
            $mapped_fu = enquiry_flow_status_for_followup_outcome($follow_up_outcome_trim);
            if ($mapped_fu !== null) {
                $enquiry_flow_status = $mapped_fu;
                $followup_outcome_drove_status = true;
            }
        }
        $follow_up_notes=isset($_POST['follow_up_notes']) ? mysqli_real_escape_string($connection,$_POST['follow_up_notes']) : '';
        $next_followup_date=isset($_POST['next_followup_date']) && $_POST['next_followup_date']!='' ? date('Y-m-d H:i:s',strtotime($_POST['next_followup_date'])) : null;
        $follow_up_outcome=mysqli_real_escape_string($connection, $follow_up_outcome_trim);
        $progress_status=isset($_POST['progress_status']) ? mysqli_real_escape_string($connection,$_POST['progress_status']) : '';
        if($progress_status==='' && $enquiry_flow_status!==null){
            $progress_status=(string)(int)$enquiry_flow_status;
        }
        $contact_num=mysqli_real_escape_string($connection,isset($_POST['contact_num']) ? $_POST['contact_num'] : '');
        $enquiry_id_trim = isset($_POST['enquiry_id']) ? trim((string)$_POST['enquiry_id']) : '';
        if ($enquiry_id_trim === '0') {
            $enquiry_id_trim = '';
        }
        $enquiry_id = mysqli_real_escape_string($connection, $enquiry_id_trim);
        $checkId=isset($_POST['checkId']) ? (int)$_POST['checkId'] : 0;
        if(@$_POST['remarks'] && $_POST['remarks']!=''){
            $remarks=json_encode($_POST['remarks']);
        }else{
            $remarks='';
        }
        $comments=mysqli_real_escape_string($connection,isset($_POST['comments']) ? $_POST['comments'] : '');
        $admin_id=isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;

        $followup_side_crm_st_id = 0;
        $followup_auto_linked_enquiry = ($checkId == 0 && $enquiry_id_trim === '');
        if ($followup_auto_linked_enquiry) {
            $ens = crm_ensure_enquiry_from_sidebar_contact($connection, $admin_id);
            if (!$ens['ok']) {
                echo ($ens['error'] === 'invalid_email') ? 'invalid_email' : '0';
                exit;
            }
            $enquiry_id = mysqli_real_escape_string($connection, $ens['enquiry_id']);
            $followup_side_crm_st_id = isset($ens['st_id']) ? (int)$ens['st_id'] : 0;
        }

        if($checkId==0){
            $next_sql = $next_followup_date !== null ? "'".mysqli_real_escape_string($connection,$next_followup_date)."'" : 'NULL';
            $mode_contact_val = $contactMode ?: $followup_type;
            $flw_now = mysqli_real_escape_string($connection, date('Y-m-d H:i:s'));
            $mod_by = (int)$admin_id;
            $query=mysqli_query($connection,"INSERT INTO followup_calls(`enquiry_id`,`flw_name`,`flw_phone`,`flw_contacted_person`,`flw_contacted_time`,`flw_date`,`flw_remarks`,`flw_comments`,`flw_mode_contact`,`flw_followup_type`,`flw_follow_up_notes`,`flw_next_followup_date`,`flw_follow_up_outcome`,`flw_progress_state`,`flw_created_by`,`flw_modified_date`,`flw_modifiedby`,`flw_followup_stage`)VALUES('$enquiry_id','$student_name','$contact_num','$contacted_person','$contacted_time','$date','$remarks','$comments','$mode_contact_val','$followup_type','$follow_up_notes',$next_sql,'$follow_up_outcome','$progress_status',$admin_id,'$flw_now',$mod_by,'$followup_stage_esc')");
            $lastId=mysqli_insert_id($connection);
            if($lastId!=''){
                if($enquiry_flow_status!==null) {
                    if (!empty($followup_outcome_drove_status)) {
                        $fu_stage_tag = ($followup_stage_esc === 'post_counselling') ? 'PCFU' : 'PEFU';
                        $fu_stage_esc = mysqli_real_escape_string($connection, $fu_stage_tag);
                        mysqli_query($connection,"UPDATE student_enquiry SET st_enquiry_flow_status=$enquiry_flow_status, st_enquiry_flow_change_stage='$fu_stage_esc' WHERE st_enquiry_id='$enquiry_id'");
                    } else {
                        mysqli_query($connection,"UPDATE student_enquiry SET st_enquiry_flow_status=$enquiry_flow_status, st_enquiry_flow_change_stage=NULL WHERE st_enquiry_id='$enquiry_id'");
                    }
                }
                // Automatic counselling email for Status 9 disabled; use explicit Send Email in Follow-up section instead.
                // if($enquiry_flow_status === 9){
                //     $eid = mysqli_real_escape_string($connection, $enquiry_id);
                //     $apt = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT appointment_date, appointment_time FROM appointments WHERE connected_enquiry_id='$eid' AND delete_status!=1 ORDER BY appointment_datetime DESC LIMIT 1"));
                //     if($apt){
                //         $er = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_email, st_name, st_course FROM student_enquiry WHERE st_enquiry_id='$eid' AND st_enquiry_status!=1 LIMIT 1"));
                //         if($er && !empty(trim($er['st_email']))){
                //             $tpl = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT subject, body FROM enquiry_status_email_templates WHERE status_code=9 LIMIT 1"));
                //             if($tpl){
                //                 $first_name = trim(strtok($er['st_name'], ' '));
                //                 $course_name = '';
                //                 if(!empty($er['st_course'])){
                //                     $ids = json_decode($er['st_course'], true);
                //                     if(is_array($ids) && count($ids)){
                //                         $cid = (int)$ids[0];
                //                         $cr = @mysqli_fetch_array(mysqli_query($connection, "SELECT CONCAT(course_sname,' ',course_name) AS nm FROM courses WHERE course_id=$cid AND course_status!=1 LIMIT 1"));
                //                         if($cr && !empty($cr['nm'])) $course_name = $cr['nm'];
                //                     }
                //                 }
                //                 $repl = array(
                //                     '{{student_name}}' => $er['st_name'],
                //                     '{{FirstName}}' => $first_name ?: $er['st_name'],
                //                     '{{CourseName}}' => $course_name,
                //                     '{{CounsellingDate}}' => date('l, j F Y', strtotime($apt['appointment_date'])),
                //                     '{{CounsellingTime}}' => date('g:i A', strtotime($apt['appointment_time'])) . ' – ' . ((isset($apt['appointment_end_time']) && $apt['appointment_end_time'] !== '') ? date('g:i A', strtotime($apt['appointment_end_time'])) : date('g:i A', strtotime($apt['appointment_time'])))
                //                 );
                //                 $body = strtr($tpl['body'], $repl);
                //                 if(!function_exists('send_mail')) require_once(__DIR__ . '/mail_function.php');
                //                 $body_html = '<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.6;color:#333;">' . nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) . '</div>';
                //                 @send_mail(trim($er['st_email']), $tpl['subject'], $body_html);
                //             }
                //         }
                //     }
                // }
                if($next_followup_date !== null){
                    if(!function_exists('google_calendar_create_event')) require_once(__DIR__ . '/google_calendar_helper.php');
                    $title = 'Follow-up: ' . $enquiry_id . ' – ' . $student_name;
                    $end_dt = date('Y-m-d H:i:s', strtotime($next_followup_date) + 1800);
                    @google_calendar_create_event($connection, $title, $next_followup_date, $end_dt, $follow_up_notes ?: 'Enquiry follow-up reminder.');
                }
                $resp_fu = '1';
                if ($followup_auto_linked_enquiry) {
                    $sid_fu = $followup_side_crm_st_id;
                    if ($sid_fu <= 0) {
                        $r_fu = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE st_enquiry_id='$enquiry_id' AND st_enquiry_status!=1 LIMIT 1"));
                        if ($r_fu) {
                            $sid_fu = (int)$r_fu['st_id'];
                        }
                    }
                    if ($sid_fu > 0) {
                        $resp_fu = '1|' . $sid_fu;
                    }
                }
                echo $resp_fu;
            }else{
                echo "0";
            }
        }else{
            $has_flw_stage_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_followup_stage'"));
            if ($has_flw_stage_col) {
                $cid_fu = (int)$checkId;
                $stRow = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT `flw_followup_stage`, `enquiry_id` FROM `followup_calls` WHERE `flw_id`=$cid_fu AND `flw_enquiry_status`=0 LIMIT 1"));
                if (!$stRow) {
                    echo '0';
                    exit;
                }
                $rowSt = isset($stRow['flw_followup_stage']) ? trim((string)$stRow['flw_followup_stage']) : '';
                if ($rowSt === '') {
                    $rowSt = 'enquiry';
                }
                if ($rowSt !== $followup_stage_esc) {
                    echo 'followup_stage_mismatch';
                    exit;
                }
                $db_eid = trim((string)($stRow['enquiry_id'] ?? ''));
                if ($db_eid !== $enquiry_id_trim) {
                    echo '0';
                    exit;
                }
            }
            $dates=date('Y-m-d H:i:s');
            $next_sql = $next_followup_date !== null ? "'".mysqli_real_escape_string($connection,$next_followup_date)."'" : 'NULL';
            $mode_contact_val = $contactMode ?: $followup_type;
            $query=mysqli_query($connection,"UPDATE followup_calls SET `enquiry_id`='$enquiry_id',`flw_progress_state`='$progress_status',`flw_name`='$student_name',`flw_phone`='$contact_num',`flw_contacted_person`='$contacted_person',`flw_contacted_time`='$contacted_time',`flw_date`='$date',`flw_remarks`='$remarks',`flw_comments`='$comments',`flw_mode_contact`='$mode_contact_val',`flw_followup_type`='$followup_type',`flw_follow_up_notes`='$follow_up_notes',`flw_next_followup_date`=$next_sql,`flw_follow_up_outcome`='$follow_up_outcome',`flw_modified_date`='$dates',`flw_modifiedby`=$admin_id,`flw_followup_stage`='$followup_stage_esc' WHERE `flw_id`=$checkId");
            if($query){
                if($enquiry_flow_status!==null) {
                    if (!empty($followup_outcome_drove_status)) {
                        $fu_stage_tag = ($followup_stage_esc === 'post_counselling') ? 'PCFU' : 'PEFU';
                        $fu_stage_esc = mysqli_real_escape_string($connection, $fu_stage_tag);
                        mysqli_query($connection,"UPDATE student_enquiry SET st_enquiry_flow_status=$enquiry_flow_status, st_enquiry_flow_change_stage='$fu_stage_esc' WHERE st_enquiry_id='$enquiry_id'");
                    } else {
                        mysqli_query($connection,"UPDATE student_enquiry SET st_enquiry_flow_status=$enquiry_flow_status, st_enquiry_flow_change_stage=NULL WHERE st_enquiry_id='$enquiry_id'");
                    }
                }
                // Automatic counselling email for Status 9 disabled; use explicit Send Email in Follow-up section instead.
                // if($enquiry_flow_status === 9){
                //     $eid = mysqli_real_escape_string($connection, $enquiry_id);
                //     $apt = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT appointment_date, appointment_time FROM appointments WHERE connected_enquiry_id='$eid' AND delete_status!=1 ORDER BY appointment_datetime DESC LIMIT 1"));
                //     if($apt){
                //         $er = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_email, st_name, st_course FROM student_enquiry WHERE st_enquiry_id='$eid' AND st_enquiry_status!=1 LIMIT 1"));
                //         if($er && !empty(trim($er['st_email']))){
                //             $tpl = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT subject, body FROM enquiry_status_email_templates WHERE status_code=9 LIMIT 1"));
                //             if($tpl){
                //                 $first_name = trim(strtok($er['st_name'], ' '));
                //                 $course_name = '';
                //                 if(!empty($er['st_course'])){
                //                     $ids = json_decode($er['st_course'], true);
                //                     if(is_array($ids) && count($ids)){
                //                         $cid = (int)$ids[0];
                //                         $cr = @mysqli_fetch_array(mysqli_query($connection, "SELECT CONCAT(course_sname,' ',course_name) AS nm FROM courses WHERE course_id=$cid AND course_status!=1 LIMIT 1"));
                //                         if($cr && !empty($cr['nm'])) $course_name = $cr['nm'];
                //                     }
                //                 }
                //                 $repl = array(
                //                     '{{student_name}}' => $er['st_name'],
                //                     '{{FirstName}}' => $first_name ?: $er['st_name'],
                //                     '{{CourseName}}' => $course_name,
                //                     '{{CounsellingDate}}' => date('l, j F Y', strtotime($apt['appointment_date'])),
                //                     '{{CounsellingTime}}' => date('g:i A', strtotime($apt['appointment_time'])) . ' – ' . ((isset($apt['appointment_end_time']) && $apt['appointment_end_time'] !== '') ? date('g:i A', strtotime($apt['appointment_end_time'])) : date('g:i A', strtotime($apt['appointment_time'])))
                //                 );
                //                 $body = strtr($tpl['body'], $repl);
                //                 if(!function_exists('send_mail')) require_once(__DIR__ . '/mail_function.php');
                //                 $body_html = '<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.6;color:#333;">' . nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) . '</div>';
                //                 @send_mail(trim($er['st_email']), $tpl['subject'], $body_html);
                //             }
                //         }
                //     }
                // }
                if($next_followup_date !== null){
                    if(!function_exists('google_calendar_create_event')) require_once(__DIR__ . '/google_calendar_helper.php');
                    $title = 'Follow-up: ' . $enquiry_id . ' – ' . $student_name;
                    $end_dt = date('Y-m-d H:i:s', strtotime($next_followup_date) + 1800);
                    @google_calendar_create_event($connection, $title, $next_followup_date, $end_dt, $follow_up_notes ?: 'Enquiry follow-up reminder.');
                }
                echo "1";
            }else{
                echo "0";
            }
        }
}
if (@$_POST['formName'] === 'fetch_followup_history') {
    header('Content-Type: application/json; charset=utf-8');
    if (empty($_SESSION['user_id'])) {
        echo json_encode(array('rows' => array(), 'error' => 'auth'));
        exit;
    }
    $eid = isset($_POST['enquiry_id']) ? mysqli_real_escape_string($connection, trim((string)$_POST['enquiry_id'])) : '';
    if ($eid === '') {
        echo json_encode(array('rows' => array()));
        exit;
    }
    $has_fu_stage = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_followup_stage'"));
    $followup_stage_filter_sql = '';
    if ($has_fu_stage) {
        $fs = isset($_POST['followup_stage']) ? trim((string)$_POST['followup_stage']) : 'enquiry';
        $fs = ($fs === 'post_counselling') ? 'post_counselling' : 'enquiry';
        $fs_esc = mysqli_real_escape_string($connection, $fs);
        $followup_stage_filter_sql = " AND f.`flw_followup_stage` = '$fs_esc' ";
    }
    $status_labels = array(
        1 => 'New',
        2 => 'Contacted',
        3 => 'Follow-up Pending',
        4 => 'In Progress',
        5 => 'Enrol Pending',
        6 => 'Converted',
        7 => 'Closed / Lost',
        8 => 'Invalid / Duplicate',
        9 => 'Booked Counselling',
        10 => 'Re-enquired',
        11 => 'Counselling Pending',
    );
    // Same strings as followup_accordion_form.php $st_remarks (index in DB JSON may match checkbox value $i → $st_remarks[$i])
    $followup_st_remarks = array(
        'Seems to be interested to do course and need to contact asap',
        'Good with communication skills',
        'Sent enrollement form online/ hard copies',
        'Want to do the course asap',
        'Looking for government funding',
        'Have done counselling before but wants to get more info',
        'Counseling is done but enrolment is due',
        'Have done the counselling before',
        'Seems like having attitude',
        'Want to book an appointment for counselling',
        'Planning to relocate to other state',
        'Wants to get COE for visa purpose',
    );
    $fu_stage_sel = $has_fu_stage ? 'f.`flw_followup_stage`,' : '';
    $q = mysqli_query($connection, "SELECT f.`flw_id`, f.`enquiry_id`, f.`flw_name`, f.`flw_phone`, f.`flw_contacted_person`, f.`flw_contacted_time`, f.`flw_date`, f.`flw_followup_type`, f.`flw_follow_up_notes`, f.`flw_follow_up_outcome`, f.`flw_progress_state`, f.`flw_next_followup_date`, f.`flw_mode_contact`, f.`flw_remarks`, f.`flw_comments`, f.`flw_created_by`, f.`flw_created_date`, f.`flw_modified_date`, f.`flw_modifiedby`, $fu_stage_sel u.`user_name` AS `created_by_name`, um.`user_name` AS `modified_by_name` FROM `followup_calls` f LEFT JOIN `users` u ON u.`user_id` = f.`flw_created_by` AND u.`user_status` != 1 LEFT JOIN `users` um ON um.`user_id` = f.`flw_modifiedby` AND um.`user_status` != 1 WHERE f.`enquiry_id`='$eid' AND f.`flw_enquiry_status`=0 $followup_stage_filter_sql ORDER BY f.`flw_id` DESC");
    $rows = array();
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $sc = (int)($r['flw_progress_state'] ?? 0);
            $r['status_label'] = isset($status_labels[$sc]) ? $status_labels[$sc] : (($r['flw_progress_state'] ?? '') !== '' && $r['flw_progress_state'] !== null ? 'Status ' . $r['flw_progress_state'] : '—');
            $r['contacted_time_fmt'] = (!empty($r['flw_contacted_time']) && strtotime($r['flw_contacted_time'])) ? date('d M Y H:i', strtotime($r['flw_contacted_time'])) : '';
            $r['flw_date_fmt'] = (!empty($r['flw_date']) && strtotime($r['flw_date'])) ? date('d M Y', strtotime($r['flw_date'])) : '';
            $r['next_followup_fmt'] = (!empty($r['flw_next_followup_date']) && strtotime($r['flw_next_followup_date'])) ? date('d M Y H:i', strtotime($r['flw_next_followup_date'])) : '';
            $hasMod = !empty($r['flw_modified_date']) && strtotime((string)$r['flw_modified_date']);
            $hasCre = !empty($r['flw_created_date']) && strtotime((string)$r['flw_created_date']);
            if ($hasMod) {
                $line = date('d M Y H:i', strtotime((string)$r['flw_modified_date']));
                $mn = trim((string)($r['modified_by_name'] ?? ''));
                if ($mn !== '') {
                    $line .= ' · ' . $mn;
                }
                $r['last_updated_display'] = $line;
            } elseif ($hasCre) {
                $line = date('d M Y H:i', strtotime((string)$r['flw_created_date']));
                $cn = trim((string)($r['created_by_name'] ?? ''));
                if ($cn !== '') {
                    $line .= ' · ' . $cn;
                }
                $r['last_updated_display'] = $line . ' (created)';
            } else {
                $r['last_updated_display'] = '—';
            }
            $remarks_text = '';
            if (!empty($r['flw_remarks'])) {
                $rj = json_decode($r['flw_remarks'], true);
                if (is_array($rj)) {
                    $parts = array();
                    foreach ($rj as $idx_raw) {
                        $idx = (int)$idx_raw;
                        if ($idx >= 0 && $idx < count($followup_st_remarks)) {
                            $parts[] = $followup_st_remarks[$idx];
                        } elseif ($idx > 0) {
                            $parts[] = 'Remark #' . $idx;
                        }
                    }
                    $remarks_text = implode("\n", $parts);
                }
            }
            $r['remarks_text'] = $remarks_text;
            $stg = isset($r['flw_followup_stage']) ? (string) $r['flw_followup_stage'] : '';
            $r['followup_stage_label'] = ($stg === 'post_counselling') ? 'Post-counselling' : 'Post-enquiry';
            $rows[] = $r;
        }
    }
    $applied_fu_stage = null;
    if ($has_fu_stage) {
        $applied_fu_stage = isset($fs) ? $fs : 'enquiry';
    }
    echo json_encode(array('rows' => $rows, 'followup_stage' => $applied_fu_stage));
    exit;
}
if(@$_POST['formName']=='counseling_form'){
        if (!mysqli_fetch_assoc(@mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_change_stage'"))) {
            @mysqli_query($connection, "ALTER TABLE `student_enquiry` ADD COLUMN `st_enquiry_flow_change_stage` VARCHAR(8) NULL DEFAULT NULL COMMENT 'PEFU or PCFU when enquiry status last set from follow-up outcome'");
        }
        /* All fields optional: defaults match typical form presets so partial saves never 500/fail. */
        $enquiry_id=isset($_POST['enquiry_id']) ? trim((string)$_POST['enquiry_id']) : '';
        if ($enquiry_id === '0') {
            $enquiry_id = '';
        }
        $checkId=isset($_POST['checkId']) ? $_POST['checkId'] : 0;
        $admin_id=isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;
        $vaccine_status=(isset($_POST['vaccine_status']) && $_POST['vaccine_status']!=='') ? (int)$_POST['vaccine_status'] : 1;
        $job_nature=mysqli_real_escape_string($connection,isset($_POST['job_nature']) ? $_POST['job_nature'] : '');
        $module_result=mysqli_real_escape_string($connection,isset($_POST['module_result']) ? $_POST['module_result'] : '');
        $ct_raw=isset($_POST['counseling_timing']) ? trim((string)$_POST['counseling_timing']) : '';
        $counseling_timing=($ct_raw!=='' && @strtotime($ct_raw)) ? date('Y-m-d H:i:s',strtotime($ct_raw)) : date('Y-m-d H:i:s');
        $ce_raw=isset($_POST['counseling_end_timing']) ? trim((string)$_POST['counseling_end_timing']) : '';
        $counseling_end_timing=($ce_raw!=='' && @strtotime($ce_raw)) ? date('Y-m-d H:i:s',strtotime($ce_raw)) : '';
        $pref_comment=isset($_POST['pref_comment']) ? mysqli_real_escape_string($connection,$_POST['pref_comment']) : '';
        $counselling_notes=isset($_POST['counselling_notes']) ? mysqli_real_escape_string($connection,$_POST['counselling_notes']) : '';
        $counselling_outcome_trim = isset($_POST['counselling_outcome']) ? trim((string) $_POST['counselling_outcome']) : '';
        $counselling_outcome_esc = mysqli_real_escape_string($connection, $counselling_outcome_trim);
        $eng_rate=mysqli_real_escape_string($connection,isset($_POST['eng_rate']) ? $_POST['eng_rate'] : '');
        $mig_test=(isset($_POST['mig_test']) && $_POST['mig_test']!=='') ? (int)$_POST['mig_test'] : 1;
        $overall_result=mysqli_real_escape_string($connection,isset($_POST['overall_result']) ? $_POST['overall_result'] : '');
        $course=mysqli_real_escape_string($connection,isset($_POST['course']) ? $_POST['course'] : '');
        $university_name=mysqli_real_escape_string($connection,isset($_POST['university_name']) ? $_POST['university_name'] : '');
        $qualification=mysqli_real_escape_string($connection,isset($_POST['qualification']) ? $_POST['qualification'] : '');
        $counseling_type=(isset($_POST['counseling_type']) && $_POST['counseling_type']!=='') ? (int)$_POST['counseling_type'] : 1;
        $member_name=mysqli_real_escape_string($connection,isset($_POST['member_name']) ? $_POST['member_name'] : '');
        $preferred_intake_date=isset($_POST['preferred_intake_date']) && $_POST['preferred_intake_date']!='' ? date('Y-m-d',strtotime($_POST['preferred_intake_date'])) : '';
        $mode_of_study=isset($_POST['mode_of_study']) && $_POST['mode_of_study']!='' ? (int)$_POST['mode_of_study'] : null;
        $aus_duration=mysqli_real_escape_string($connection,isset($_POST['aus_duration']) ? $_POST['aus_duration'] : '');
        $visa_condition=(isset($_POST['visa_condition']) && $_POST['visa_condition']!=='' && $_POST['visa_condition']!==null) ? (int)$_POST['visa_condition'] : 0;
        $education=mysqli_real_escape_string($connection,isset($_POST['education']) ? $_POST['education'] : '');
        $aus_study=(isset($_POST['aus_study']) && $_POST['aus_study']!=='') ? (int)$_POST['aus_study'] : 1;
        $work_status=(isset($_POST['work_status']) && $_POST['work_status']!=='') ? (int)$_POST['work_status'] : 1;

        // Normalise enquiry_id: if a numeric st_id was posted instead of st_enquiry_id (e.g. EQ00033),
        // look up the proper st_enquiry_id so counseling_details always stores the canonical code.
        if($enquiry_id !== ''){
            if(stripos($enquiry_id, 'EQ') !== 0){
                $tmp_id = (int)$enquiry_id;
                if($tmp_id > 0){
                    $tmp_row = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_enquiry_id FROM student_enquiry WHERE st_id=$tmp_id LIMIT 1"));
                    if($tmp_row && !empty($tmp_row['st_enquiry_id'])){
                        $enquiry_id = $tmp_row['st_enquiry_id'];
                    }
                }
            }
        }


        if(@$_POST['remarks'] && $_POST['remarks']!=''){
        $remarks=json_encode($_POST['remarks']);
        }else{
            $remarks='';
        }
        $remarks=mysqli_real_escape_string($connection,$remarks);

    $do_insert = ($checkId == 0);
    if (!$do_insert) {
        $checkId = (int)$checkId;
        $exists = mysqli_fetch_row(mysqli_query($connection, "SELECT 1 FROM counseling_details WHERE counsil_id = $checkId AND counsil_enquiry_status = 0 LIMIT 1"));
        if (!$exists) {
            $do_insert = true; // no such counselling record -> INSERT (e.g. checkId was enquiry st_id by mistake)
        }
    }

    $counsel_side_crm_st_id = 0;
    $counsel_auto_linked_enquiry = ($do_insert && $enquiry_id === '');
    if ($counsel_auto_linked_enquiry) {
        $ens = crm_ensure_enquiry_from_sidebar_contact($connection, $admin_id);
        if (!$ens['ok']) {
            echo ($ens['error'] === 'invalid_email') ? 'invalid_email' : '0';
            exit;
        }
        $enquiry_id = $ens['enquiry_id'];
        $counsel_side_crm_st_id = isset($ens['st_id']) ? (int)$ens['st_id'] : 0;
    }

        $enquiry_id_sql=mysqli_real_escape_string($connection,$enquiry_id);

    $cd_has_outcome_col = false;
    $_cdcol = @mysqli_query($connection, "SHOW COLUMNS FROM counseling_details LIKE 'counsil_outcome'");
    if ($_cdcol && mysqli_num_rows($_cdcol) > 0) {
        $cd_has_outcome_col = true;
    }

    if ($do_insert) {
        $mode_of_study_sql = $mode_of_study !== null ? $mode_of_study : 'NULL';
        $preferred_intake_sql = $preferred_intake_date !== '' ? "'$preferred_intake_date'" : 'NULL';
        if ($cd_has_outcome_col) {
            $query=mysqli_query($connection,"INSERT INTO counseling_details(`st_enquiry_id`,`counsil_mem_name`,`counsil_preferred_intake_date`,`counsil_mode_of_study`,`counsil_vaccine_status`,`counsil_job_nature`,`counsil_module_result`,`counsil_timing`,`counsil_end_time`,`counsil_pref_comments`,`counsil_eng_rate`,`counsil_migration_test`,`counsil_overall_result`,`counsil_course`,`counsil_university`,`counsil_qualification`,`counsil_type`,`counsil_aus_stay_time`,`counsil_visa_condition`,`counsil_education`,`counsil_aus_study_status`,`counsil_work_status`,`counsil_remarks`,`counsil_notes`,`counsil_outcome`,`counsil_createdby`)VALUES('$enquiry_id_sql','$member_name',$preferred_intake_sql,$mode_of_study_sql,$vaccine_status,'$job_nature','$module_result','$counseling_timing','$counseling_end_timing','$pref_comment','$eng_rate',$mig_test,'$overall_result','$course','$university_name','$qualification',$counseling_type,'$aus_duration',$visa_condition,'$education',$aus_study,$work_status,'$remarks','$counselling_notes','$counselling_outcome_esc',$admin_id)");
        } else {
            $query=mysqli_query($connection,"INSERT INTO counseling_details(`st_enquiry_id`,`counsil_mem_name`,`counsil_preferred_intake_date`,`counsil_mode_of_study`,`counsil_vaccine_status`,`counsil_job_nature`,`counsil_module_result`,`counsil_timing`,`counsil_end_time`,`counsil_pref_comments`,`counsil_eng_rate`,`counsil_migration_test`,`counsil_overall_result`,`counsil_course`,`counsil_university`,`counsil_qualification`,`counsil_type`,`counsil_aus_stay_time`,`counsil_visa_condition`,`counsil_education`,`counsil_aus_study_status`,`counsil_work_status`,`counsil_remarks`,`counsil_notes`,`counsil_createdby`)VALUES('$enquiry_id_sql','$member_name',$preferred_intake_sql,$mode_of_study_sql,$vaccine_status,'$job_nature','$module_result','$counseling_timing','$counseling_end_timing','$pref_comment','$eng_rate',$mig_test,'$overall_result','$course','$university_name','$qualification',$counseling_type,'$aus_duration',$visa_condition,'$education',$aus_study,$work_status,'$remarks','$counselling_notes',$admin_id)");
        }
        $lastId=mysqli_insert_id($connection);
        if($lastId!=''){
            if ($counselling_outcome_trim !== '' && $enquiry_id_sql !== '') {
                $auto_cs = enquiry_flow_status_for_counselling_outcome($counselling_outcome_trim);
                if ($auto_cs !== null) {
                    mysqli_query($connection, 'UPDATE student_enquiry SET st_enquiry_flow_status=' . (int) $auto_cs . ", st_enquiry_flow_change_stage='CONS' WHERE st_enquiry_id='$enquiry_id_sql' AND st_enquiry_status!=1 LIMIT 1");
                }
            }
            $resp_cs = '1';
            if ($counsel_auto_linked_enquiry) {
                $sid_cs = $counsel_side_crm_st_id;
                if ($sid_cs <= 0) {
                    $r_cs = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE st_enquiry_id='$enquiry_id_sql' AND st_enquiry_status!=1 LIMIT 1"));
                    if ($r_cs) {
                        $sid_cs = (int)$r_cs['st_id'];
                    }
                }
                if ($sid_cs > 0) {
                    $resp_cs = '1|' . $sid_cs;
                }
            }
            echo $resp_cs;
        }else{
            echo "0";
        }
    } else {
        $mod_date=date('Y-m-d');
        if ($cd_has_outcome_col) {
            $query=mysqli_query($connection,"UPDATE counseling_details SET `counsil_mem_name`='$member_name',`counsil_preferred_intake_date`=".($preferred_intake_date!='' ? "'$preferred_intake_date'" : 'NULL').",`counsil_mode_of_study`=".($mode_of_study!==null ? $mode_of_study : 'NULL').",`counsil_vaccine_status`=$vaccine_status,`counsil_job_nature`='$job_nature',`counsil_module_result`='$module_result',`counsil_timing`='$counseling_timing',`counsil_end_time`='$counseling_end_timing',`counsil_pref_comments`='$pref_comment',`counsil_eng_rate`='$eng_rate',`counsil_migration_test`=$mig_test,`counsil_overall_result`='$overall_result',`counsil_course`='$course',`counsil_university`='$university_name',`counsil_qualification`='$qualification',`counsil_type`=$counseling_type,`counsil_aus_stay_time`='$aus_duration',`counsil_visa_condition`=$visa_condition,`counsil_education`='$education',`counsil_aus_study_status`=$aus_study,`counsil_work_status`=$work_status,`counsil_remarks`='$remarks',`counsil_notes`='$counselling_notes',`counsil_outcome`='$counselling_outcome_esc',`counsil_modified_date`='$mod_date',`counsil_modified_by`=$admin_id WHERE `counsil_id`=".(int)$checkId);
        } else {
            $query=mysqli_query($connection,"UPDATE counseling_details SET `counsil_mem_name`='$member_name',`counsil_preferred_intake_date`=".($preferred_intake_date!='' ? "'$preferred_intake_date'" : 'NULL').",`counsil_mode_of_study`=".($mode_of_study!==null ? $mode_of_study : 'NULL').",`counsil_vaccine_status`=$vaccine_status,`counsil_job_nature`='$job_nature',`counsil_module_result`='$module_result',`counsil_timing`='$counseling_timing',`counsil_end_time`='$counseling_end_timing',`counsil_pref_comments`='$pref_comment',`counsil_eng_rate`='$eng_rate',`counsil_migration_test`=$mig_test,`counsil_overall_result`='$overall_result',`counsil_course`='$course',`counsil_university`='$university_name',`counsil_qualification`='$qualification',`counsil_type`=$counseling_type,`counsil_aus_stay_time`='$aus_duration',`counsil_visa_condition`=$visa_condition,`counsil_education`='$education',`counsil_aus_study_status`=$aus_study,`counsil_work_status`=$work_status,`counsil_remarks`='$remarks',`counsil_notes`='$counselling_notes',`counsil_modified_date`='$mod_date',`counsil_modified_by`=$admin_id WHERE `counsil_id`=".(int)$checkId);
        }
        if($query){
            if ($counselling_outcome_trim !== '' && $enquiry_id_sql !== '') {
                $auto_cs = enquiry_flow_status_for_counselling_outcome($counselling_outcome_trim);
                if ($auto_cs !== null) {
                    mysqli_query($connection, 'UPDATE student_enquiry SET st_enquiry_flow_status=' . (int) $auto_cs . ", st_enquiry_flow_change_stage='CONS' WHERE st_enquiry_id='$enquiry_id_sql' AND st_enquiry_status!=1 LIMIT 1");
                }
            }
            echo "1";
        }else{
            echo "0";
        }
    }




}


if(@$_POST['formName']=='date_filter'){

    $crm_enquiry_delete_allowed = isset($_SESSION['user_id']) && (int)@$_SESSION['user_type'] === 1;

    if($_POST['from_date']>$_POST['to_date']){
        $from_date=$_POST['to_date'];
        $to_date=$_POST['from_date'];
    }else{
        $from_date=$_POST['from_date'];
        $to_date=$_POST['to_date'];
    }

    $WHERE='';        
    $WHERE.=" AND created_date between '$from_date' AND '$to_date'";
    
    $filterQuery="SELECT * FROM `student_enquiry` WHERE st_enquiry_status=0 $WHERE";

    // echo $filterQuery;

    $filterQueryget=mysqli_query($connection,$filterQuery);
    $tbody='';



if(mysqli_num_rows($filterQueryget)!=0){

    while($filterQueryRes=mysqli_fetch_array($filterQueryget)){

        $tbody.='<tr>';

        $coursesNames=json_decode($filterQueryRes['st_course']);
        $coursesName='<div class="td_scroll_height">';
        foreach($coursesNames as $value){
            $courses=mysqli_fetch_array(mysqli_query($connection,"SELECT * from courses where course_status!=1 AND course_id=".$value));
            $coursesName.= $courses['course_sname'].'-'.$courses['course_name']." , <br>"; 
        }

        $st_states=['--select--','NSW - New South Wales','VIC - Victoria','ACT - Australian Capital Territory','NT - Northern Territoy','WA - Western Australia','QLD - Queensland','SA - South Australia','TAS - Tasmania'];
        $state_name= $st_states[$filterQueryRes['st_state']];
        
        $st_course_type=['-','Need exemption','Regular','Regular - Group','Short courses','Short course - Group'];
        $courseTypeId=$filterQueryRes['st_course_type'];
    
        $coursesNamePos = strrpos($coursesName, ',');
        $coursesName = substr($coursesName, 0, $coursesNamePos);
        $coursesName.='</div>';
    
        $visited=$filterQueryRes['st_visited']==1 ? 'Visited' : ( $filterQueryRes['st_visited']==2 ? 'Not Visited' : ' - ' ) ;
        
        $visastatus=$filterQueryRes['st_visa_condition']==1 ? 'Approved' : 'Not Approved' ;
    
        $refered_names = $filterQueryRes['st_refer_name'];
    
        $startPlanDate=date('d M Y',strtotime($filterQueryRes['st_startplan_date']));
    
        $staff_comments=$filterQueryRes['st_comments'];
        $preference=$filterQueryRes['st_pref_comments'];
    
        $st_remarks=['--select--','Seems to be interested to do course and need to contact asap','contacted and followed','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose'];    
    
        if($filterQueryRes['st_remarks']!=''){
            $remarksNotes='<div class="td_scroll_height">';
    
        foreach(json_decode($filterQueryRes['st_remarks']) as $remark  ){                   
            
            $remarksNotes.=$st_remarks[$remark].' , <br>';
    
        }
        $remarksNotes.='</div>';
        }else{
            $remarksNotes=' - ';
            
        }
    
        $street=$filterQueryRes['st_street_details'];
        $suburb=$filterQueryRes['st_suburb'];
        $post_code=$filterQueryRes['st_post_code'];
        $appointment=$filterQueryRes['st_appoint_book']==1 ? 'Booked' : ( $filterQueryRes['st_appoint_book']==2 ? 'Not Booked' : ' - ' );
        
        $querys2=mysqli_query($connection,"select visa_status_name from `visa_statuses` WHERE visa_id=".$filterQueryRes['st_visa_status']);
        if(mysqli_num_rows($querys2)!=0){
        $visaCondition=mysqli_fetch_array($querys2);
    
        if(@$visaCondition['visa_status_name'] && $visaCondition['visa_status_name']!=''){
            $visacCond=$visaCondition['visa_status_name'];
        }else{
            $visacCond=' - ';
        }
        }else{
            $visacCond=' - ';
        }

        $appointment=$filterQueryRes['st_appoint_book']==1 ? 'Booked' : ( $filterQueryRes['st_appoint_book']==2 ? 'Not Booked' : ' - ' );

        $dateCreated=date('d M Y',strtotime($filterQueryRes['st_enquiry_date']));
        
    
            $view='<a class="btn btn-outline-primary btn-sm edit_enq" style="margin-right:10px;" href="student_enquiry.php?eq='.base64_encode($filterQueryRes['st_id']).'">Edit</a>';
            if(!empty($crm_enquiry_delete_allowed)){
                $view.='<button onclick="delete_enq(\'student_enquiry\',\'st\','.$filterQueryRes['st_id'].')" type="button" class="btn btn-outline-danger btn-sm">Delete</button>';
            }


            $tbody.='<td>'.$filterQueryRes['st_enquiry_id'].'</td>
                    <td>'.$filterQueryRes['st_name'].'</td>
                    <td>'.$filterQueryRes['st_phno'].'</td>
                    <td>'.$filterQueryRes['st_email'].'</td>
                    <td>'.$street.'</td>
                    <td>'.$suburb.'</td>
                    <td>'.$state_name.'</td>
                    <td>'.$post_code.'</td>
                    <td>'.$coursesName.'</td>
                    <td>'.$startPlanDate.'</td>
                    <td>'.$st_course_type[$courseTypeId].'</td>
                    <td>'.$visited.'</td>
                    <td>'.$dateCreated.'</td>
                    <td>'.$refered_names.'</td>
                    <td>'.$filterQueryRes['st_fee'].'</td>
                    <td>'.$appointment.'</td>
                    <td>'.$visacCond.'</td>
                    <td>'.$visastatus.'</td></tr>';
            
        }

        echo $tbody;
    }else{
        echo "<tr><td>No Records</td></tr>";
    }


}
    if(@$_POST['formName']=='student_enrol'){
$qualifications=$_POST['qualifications'];
$venue=$_POST['venues'];
$middle_name=$_POST['middle_name'];
$st_enquiry_id=$_POST['enquiry_id'];
$courseName=strtoupper($_POST['courseName'][0]);
$courseId=$_POST['courses'];
$source=$_POST['source'];
$name_main=$_POST['name_main'];
$emailAddress=$_POST['emailAddress'];
$contactName=$_POST['contactName'];
$given_name=$_POST['given_name'];
$checkId=$_POST['checkId'];
$dateYear=date("Y");

if($checkId==0){

$query=mysqli_query($connection,"INSERT INTO student_enrolment(st_qualifications,st_email,st_mobile,st_enquiry_id,st_enrol_course,st_venue,st_middle_name,st_name,st_source,st_given_name)VALUES('$qualifications','$emailAddress','$contactName','$st_enquiry_id',$courseId,'$venue','$middle_name','$name_main',$source,'$given_name')");
$lastId=mysqli_insert_id($connection);

$courseID=mysqli_fetch_array(mysqli_query($connection,"SELECT * FROM courses WHERE course_id=$courseId"));

$uniqueId=sprintf($dateYear.$courseID['course_sname'].'%04d', $lastId);

$querys=mysqli_query($connection,"UPDATE student_enrolment SET st_unique_id='$uniqueId' WHERE st_enrol_id=$lastId");
$error=mysqli_error($connection);
if($error!=''){
    echo 1;
}else{
    echo $uniqueId;
}

}else{

    if(mysqli_query($connection,"UPDATE student_enrolment SET `st_qualifications`='$qualifications',`st_email`='$emailAddress',`st_mobile`='$contactName',`st_enrol_course`=$courseId,`st_venue`='$venue',`st_middle_name`='$middle_name',`st_name`='$name_main',`st_source`=$source,`st_given_name`='$given_name' WHERE `st_enrol_id`=$checkId")){
        echo 2;
    }else{
        echo 0;
    }
    
}

}

if (@$_POST['formName'] == 'student_enrols') {
    $formData = json_decode($_POST['details']);
    $uploadDir = 'uploads/';
    $uploadedFiles = [];

    // Handle multiple file uploads
    if (!empty($_FILES['image']['name'][0])) {
        foreach ($_FILES['image']['name'] as $key => $fileName) {
            $tmpName = $_FILES['image']['tmp_name'][$key];
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueName = rand(1000, 1000000) . '_' . time() . '.' . strtolower($fileExt);
            $targetPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $uploadedFiles[] = $uniqueName;
            }
        }
    }

    // Store uploaded filenames as JSON
    $photo = json_encode($uploadedFiles);

    // --- Other field assignments ---
    $enquiry_id = $formData->enquiry_id;
    $rto_name = $formData->rto_name;
    $courses = json_encode($formData->courses);
    $branch_name = $formData->branch_name;
    $given_name = $formData->given_name;
    $surname = $formData->surname;
    $dob = $formData->dob;
    $birth_country = $formData->birth_country;
    $street_details = $formData->street_details;
    $sub_urb = $formData->sub_urb;
    $post_code = $formData->post_code;
    $tel_num = $formData->tel_num;
    $mobile_num = $formData->mobile_num;
    $emailAddress = $formData->emailAddress;
    $stu_state = $formData->stu_state;
    $em_full_name = $formData->em_full_name;
    $em_relation = $formData->em_relation;
    $em_mobile_num = $formData->em_mobile_num;
    $em_agree_check = $formData->em_agree_check;
    $usi_id = $formData->usi_id;
    $emp_status = $formData->emp_status;
    $self_status = $formData->self_status;
    $st_citizen = $formData->st_citizen;
    $highest_school = $formData->highest_school;
    $study_reason = $formData->study_reason;
    $study_reason_other = $formData->study_reason_other;
    $gender_check = $formData->gender_check;
    $cred_tansf = $formData->cred_tansf;
    $sec_school = $formData->sec_school;
    $born_country = $formData->born_country;
    $origin = $formData->origin;
    $lan_spoken = $formData->lan_spoken;
    $disability = $formData->disability;
    $qual_1 = $formData->qual_1;
    $qual_2 = $formData->qual_2;
    $qual_3 = $formData->qual_3;
    $qual_4 = $formData->qual_4;
    $qual_5 = $formData->qual_5;
    $qual_6 = $formData->qual_6;
    $qual_7 = $formData->qual_7;
    $qual_8 = $formData->qual_8;
    $qual_9 = $formData->qual_9;
    $qual_10 = $formData->qual_10;
    $st_born_country = $formData->st_born_country;
    $qual_name_8_other = $formData->qual_name_8_other;
    $qual_name_9_other = $formData->qual_name_9_other;
    $qual_name_10_other = $formData->qual_name_10_other;
    $lan_spoken_other = $formData->lan_spoken_other;
    $st_disability_type = json_encode($formData->st_disability_type);
    $disability_type_other = $formData->disability_type_other;
    $admin_id = $_SESSION['user_id'];

    // --- Insert query ---
    $query = "INSERT INTO `student_enrolments`
    (`st_unique_id`, `st_enquiry_id`, `st_rto_name`, `st_courses`, `st_branch`, `st_photo`,
     `st_given_name`, `st_surname`, `st_dob`, `st_country_birth`, `st_street`, `st_suburb`,
     `st_state`, `st_post_code`, `st_tel_num`, `st_email`, `st_mobile`, `st_emerg_name`,
     `st_emerg_relation`, `st_emerg_mobile`, `st_emerg_agree`, `st_usi`, `st_emp_status`,
     `st_self_status`, `st_citizenship`, `st_gender`, `st_credit_transfer`, `st_highest_school`,
     `st_secondary_school`, `st_born_country`, `st_born_country_other`, `st_origin`, `st_lan_spoken`,
     `st_lan_spoken_other`, `st_disability`, `st_disability_type`, `st_disability_type_other`,
     `st_study_reason`, `st_study_reason_other`, `st_qual_1`, `st_qual_2`, `st_qual_3`, `st_qual_4`,
     `st_qual_5`, `st_qual_6`, `st_qual_7`, `st_qual_8`, `st_qual_9`, `st_qual_10`, `st_qual_8_other`,
     `st_qual_9_other`, `st_qual_10_other`, `st_created_by`)
    VALUES
    ('1','$enquiry_id','$rto_name','$courses','$branch_name','$photo','$given_name','$surname',
     '$dob','$birth_country','$street_details','$sub_urb','$stu_state','$post_code','$tel_num',
     '$emailAddress','$mobile_num','$em_full_name','$em_relation','$em_mobile_num','$em_agree_check',
     '$usi_id','$emp_status','$self_status','$st_citizen','$gender_check','$cred_tansf','$highest_school',
     '$sec_school','$born_country','$st_born_country','$origin','$lan_spoken','$lan_spoken_other',
     '$disability','$st_disability_type','$disability_type_other','$study_reason','$study_reason_other',
     '$qual_1','$qual_2','$qual_3','$qual_4','$qual_5','$qual_6','$qual_7','$qual_8','$qual_9','$qual_10',
     '$qual_name_8_other','$qual_name_9_other','$qual_name_10_other',$admin_id)";

    $queryExec = mysqli_query($connection, $query);
    $lastId = mysqli_insert_id($connection);

    // Generate unique ID based on year + course name + ID
    $courseId = json_decode($courses)[0];
    $courseID = mysqli_fetch_array(mysqli_query($connection, "SELECT * FROM courses WHERE course_id=$courseId"));
    $dateYear = date('Y');
    $uniqueId = sprintf($dateYear . $courseID['course_name'] . '%04d', $lastId);

    $querys = mysqli_query($connection, "UPDATE student_enrolment SET st_unique_id='$uniqueId' WHERE st_enrol_id=$lastId");
    $error = mysqli_error($connection);

    echo ($error != '') ? 1 : $uniqueId;
}

// --- Enrolment Form Online (PDF form) ---
if (@$_POST['formName'] == 'student_enrols_online') {
    $raw = isset($_POST['details']) ? json_decode($_POST['details'], true) : array();
    if (!is_array($raw)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid form data.'));
        exit;
    }
    $d = function($key, $def = '') use ($raw) {
        return isset($raw[$key]) && $raw[$key] !== '' ? $raw[$key] : $def;
    };
    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    $photo = '[]';
    if (!empty($_FILES['image']['name'][0])) {
        $uploadedFiles = array();
        foreach ($_FILES['image']['name'] as $key => $fileName) {
            $tmpName = $_FILES['image']['tmp_name'][$key];
            if (!is_uploaded_file($tmpName)) {
                continue;
            }
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueName = rand(1000, 999999) . '_' . time() . '.' . strtolower($fileExt);
            $targetPath = $uploadDir . $uniqueName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $uploadedFiles[] = $uniqueName;
            }
        }
        $photo = json_encode($uploadedFiles);
    }
    $enquiry_id = mysqli_real_escape_string($connection, $d('enquiry_id'));
    $rto_name = mysqli_real_escape_string($connection, $d('rto_name'));
    $branch_name = mysqli_real_escape_string($connection, $d('branch_name'));
    $courses = is_array($raw['courses']) ? $raw['courses'] : array();
    $coursesJson = json_encode($courses);
    $given_name = mysqli_real_escape_string($connection, $d('given_name'));
    $surname = mysqli_real_escape_string($connection, $d('surname'));
    $dob = mysqli_real_escape_string($connection, $d('dob'));
    $birth_country = mysqli_real_escape_string($connection, $d('birth_country'));
    $street_details = mysqli_real_escape_string($connection, $d('street_details'));
    $sub_urb = mysqli_real_escape_string($connection, $d('sub_urb'));
    $stu_state = mysqli_real_escape_string($connection, $d('stu_state'));
    $post_code = mysqli_real_escape_string($connection, $d('post_code'));
    $mobile_num = mysqli_real_escape_string($connection, $d('mobile_num'));
    $emailAddress = mysqli_real_escape_string($connection, $d('emailAddress'));
    $em_full_name = mysqli_real_escape_string($connection, $d('em_full_name'));
    $em_relation = mysqli_real_escape_string($connection, $d('em_relation'));
    $em_mobile_num = mysqli_real_escape_string($connection, $d('em_mobile_num'));
    $usi_id = mysqli_real_escape_string($connection, $d('usi_id'));
    $gender_check = mysqli_real_escape_string($connection, $d('gender_check'));
    $highest_school = mysqli_real_escape_string($connection, $d('highest_school'));
    $sec_school = mysqli_real_escape_string($connection, $d('sec_school'));
    $study_reason = mysqli_real_escape_string($connection, $d('study_reason'));
    $study_reason_other = mysqli_real_escape_string($connection, $d('study_reason_other'));
    $cred_tansf = mysqli_real_escape_string($connection, $d('cred_tansf'));
    $origin = mysqli_real_escape_string($connection, $d('origin'));
    $lan_spoken = mysqli_real_escape_string($connection, $d('lan_spoken'));
    $lan_spoken_other = mysqli_real_escape_string($connection, $d('lan_spoken_other'));
    $disability = mysqli_real_escape_string($connection, $d('disability'));
    $st_disability_type = isset($raw['st_disability_type']) && is_array($raw['st_disability_type']) ? json_encode($raw['st_disability_type']) : '[]';
    $disability_type_other = mysqli_real_escape_string($connection, $d('disability_type_other'));
    $emp_status = mysqli_real_escape_string($connection, $d('emp_status'));
    $admin_id = (int)($_SESSION['user_id'] ?? 0);

    $qualification_code_title = mysqli_real_escape_string($connection, $d('qualification_code_title'));
    $age_declaration_18 = (int)$d('age_declaration_18');
    $city_of_birth = mysqli_real_escape_string($connection, $d('city_of_birth'));
    $postal_same_as_above = $d('postal_same_as_above') !== '' ? (int)$d('postal_same_as_above') : 'NULL';
    $postal_address = mysqli_real_escape_string($connection, $d('postal_address'));
    $english_read_write = mysqli_real_escape_string($connection, $d('english_read_write'));
    $work_phone = mysqli_real_escape_string($connection, $d('work_phone'));
    $home_phone = mysqli_real_escape_string($connection, $d('home_phone'));
    $year_completed_school = mysqli_real_escape_string($connection, $d('year_completed_school'));
    $mode_delivery = mysqli_real_escape_string($connection, $d('mode_delivery'));
    $qualification_attained = mysqli_real_escape_string($connection, $d('qualification_attained'));
    $industry_of_work = mysqli_real_escape_string($connection, $d('industry_of_work'));
    $computer_access = mysqli_real_escape_string($connection, $d('computer_access'));
    $computer_literacy = mysqli_real_escape_string($connection, $d('computer_literacy'));
    $numeracy_skills = mysqli_real_escape_string($connection, $d('numeracy_skills'));
    $additional_support = mysqli_real_escape_string($connection, $d('additional_support'));
    $additional_support_specify = mysqli_real_escape_string($connection, $d('additional_support_specify'));
    $usi_declaration = (int)$d('usi_declaration');
    $privacy_declaration = (int)$d('privacy_declaration');
    $refund_declaration = (int)$d('refund_declaration');
    $office_coordinator_name = mysqli_real_escape_string($connection, $d('office_coordinator_name'));
    $office_invoice_provided = (int)$d('office_invoice_provided');
    $office_receipt_collected = (int)$d('office_receipt_collected');
    $office_lms_access = (int)$d('office_lms_access');
    $office_resources_access = (int)$d('office_resources_access');
    $office_uploaded_sms = (int)$d('office_uploaded_sms');
    $office_welcome_pack_sent = (int)$d('office_welcome_pack_sent');
    $candidate_declaration = (int)$d('candidate_declaration');
    $candidate_full_name = mysqli_real_escape_string($connection, $d('candidate_full_name'));
    $candidate_date = mysqli_real_escape_string($connection, $d('candidate_date'));
    $candidate_signature = mysqli_real_escape_string($connection, $d('candidate_signature'));

    $tel_num = $home_phone;
    $em_agree_check = '1';
    $self_status = '';
    $st_citizen = '';
    $born_country = '';
    $st_born_country = '';
    $qual_1 = $qual_2 = $qual_3 = $qual_4 = $qual_5 = $qual_6 = $qual_7 = $qual_8 = $qual_9 = $qual_10 = '';
    $qual_name_8_other = $qual_name_9_other = $qual_name_10_other = '';

    $cols = "st_unique_id, st_enquiry_id, st_rto_name, st_courses, st_branch, st_photo, st_given_name, st_surname, st_dob, st_country_birth, st_street, st_suburb, st_state, st_post_code, st_tel_num, st_email, st_mobile, st_emerg_name, st_emerg_relation, st_emerg_mobile, st_emerg_agree, st_usi, st_emp_status, st_self_status, st_citizenship, st_gender, st_credit_transfer, st_highest_school, st_secondary_school, st_born_country, st_born_country_other, st_origin, st_lan_spoken, st_lan_spoken_other, st_disability, st_disability_type, st_disability_type_other, st_study_reason, st_study_reason_other, st_qual_1, st_qual_2, st_qual_3, st_qual_4, st_qual_5, st_qual_6, st_qual_7, st_qual_8, st_qual_9, st_qual_10, st_qual_8_other, st_qual_9_other, st_qual_10_other, st_created_by";
    $vals = "'1','$enquiry_id','$rto_name','$coursesJson','$branch_name','$photo','$given_name','$surname','$dob','$birth_country','$street_details','$sub_urb','$stu_state','$post_code','$tel_num','$emailAddress','$mobile_num','$em_full_name','$em_relation','$em_mobile_num','$em_agree_check','$usi_id','$emp_status','$self_status','$st_citizen','$gender_check','$cred_tansf','$highest_school','$sec_school','$born_country','$st_born_country','$origin','$lan_spoken','$lan_spoken_other','$disability','$st_disability_type','$disability_type_other','$study_reason','$study_reason_other','$qual_1','$qual_2','$qual_3','$qual_4','$qual_5','$qual_6','$qual_7','$qual_8','$qual_9','$qual_10','$qual_name_8_other','$qual_name_9_other','$qual_name_10_other',$admin_id";

    $query = "INSERT INTO student_enrolments ($cols) VALUES ($vals)";
    $queryExec = mysqli_query($connection, $query);
    if (!$queryExec) {
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . mysqli_error($connection)));
        exit;
    }
    $lastId = mysqli_insert_id($connection);
    $courseId = !empty($courses) ? (int)$courses[0] : 0;
    $courseID = $courseId ? mysqli_fetch_array(mysqli_query($connection, "SELECT * FROM courses WHERE course_id=$courseId")) : null;
    $dateYear = date('Y');
    $uniqueId = $courseID ? sprintf($dateYear . $courseID['course_name'] . '%04d', $lastId) : ($dateYear . 'ENR' . sprintf('%04d', $lastId));
    $coursesDisplay = '';
    if (!empty($courses) && $courseID) {
        $names = array();
        foreach ($courses as $cid) {
            $r = mysqli_fetch_array(mysqli_query($connection, "SELECT course_sname, course_name FROM courses WHERE course_id=" . (int)$cid));
            if ($r) $names[] = $r['course_sname'] . '-' . $r['course_name'];
        }
        $coursesDisplay = implode(', ', $names);
    }

    $updateEnrol = "INSERT INTO student_enrolment (st_enquiry_id, st_unique_id, st_enrol_status, st_given_name, st_name, st_mobile, st_email, st_qualifications, st_enrol_course, st_venue, st_middle_name, st_source) VALUES ('$enquiry_id','$uniqueId',0,'$given_name','$surname','$mobile_num','$emailAddress','','$courseId','','','')";
    mysqli_query($connection, $updateEnrol);

    $updateStUnique = "UPDATE student_enrolments SET st_unique_id='$uniqueId' WHERE st_enrol_id=$lastId";
    mysqli_query($connection, $updateStUnique);

    $updNew = "UPDATE student_enrolments SET qualification_code_title='$qualification_code_title', age_declaration_18=" . ($age_declaration_18 ? 1 : 'NULL') . ", city_of_birth='$city_of_birth', postal_same_as_above=$postal_same_as_above, postal_address='$postal_address', english_read_write='$english_read_write', work_phone='$work_phone', home_phone='$home_phone', year_completed_school='$year_completed_school', mode_delivery='$mode_delivery', qualification_attained='$qualification_attained', industry_of_work='$industry_of_work', computer_access='$computer_access', computer_literacy='$computer_literacy', numeracy_skills='$numeracy_skills', additional_support='$additional_support', additional_support_specify='$additional_support_specify', usi_declaration=$usi_declaration, privacy_declaration=$privacy_declaration, refund_declaration=$refund_declaration, office_coordinator_name='$office_coordinator_name', office_invoice_provided=$office_invoice_provided, office_receipt_collected=$office_receipt_collected, office_lms_access=$office_lms_access, office_resources_access=$office_resources_access, office_uploaded_sms=$office_uploaded_sms, office_welcome_pack_sent=$office_welcome_pack_sent, candidate_declaration=$candidate_declaration, candidate_full_name='$candidate_full_name', candidate_date='" . ($candidate_date ? $candidate_date : '') . "', candidate_signature='$candidate_signature', form_source='online' WHERE st_enrol_id=$lastId";
    @mysqli_query($connection, $updNew);

    $pdfData = array_merge($raw, array(
        'office_student_id' => $uniqueId,
        'courses_display'   => $coursesDisplay,
        'emailAddress'     => $emailAddress,
    ));
    $pdfDir = __DIR__ . '/enrolments_pdf/';
    if (!is_dir($pdfDir)) {
        @mkdir($pdfDir, 0755, true);
    }
    $pdfPath = $pdfDir . 'Enrolment_' . $uniqueId . '.pdf';
    require_once __DIR__ . '/enrolment_pdf_generator.php';
    enrolment_generate_pdf($pdfData, $pdfPath);

    $pdfUrl = 'includes/enrolments_pdf/Enrolment_' . $uniqueId . '.pdf';
    echo json_encode(array('success' => true, 'unique_id' => $uniqueId, 'pdf_url' => $pdfUrl));
    exit;
}

// --- Enrolment Form New (dedicated table) ---
if (@$_POST['formName'] == 'save_enrolment_form_new') {
    ob_clean();
    header('Content-Type: application/json');

    $raw = isset($_POST['details']) ? json_decode($_POST['details'], true) : array();
    if (!is_array($raw)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid form data.'));
        exit;
    }

    $d = function($key, $def = '') use ($raw) {
        return isset($raw[$key]) && $raw[$key] !== '' ? $raw[$key] : $def;
    };
    $di = function($key) use ($raw) {
        return isset($raw[$key]) ? (int)$raw[$key] : 0;
    };

    // Email is mandatory
    $email_check = trim($raw['emailAddress'] ?? '');
    if ($email_check === '') {
        echo json_encode(array('success' => false, 'message' => 'Email address is required.'));
        exit;
    }
    if (!filter_var($email_check, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array('success' => false, 'message' => 'Please enter a valid email address.'));
        exit;
    }

    // Submitter info from session
    $sess_type = @$_SESSION['user_type'];
    if ($sess_type === 1)        $user_type = 'admin';
    elseif ($sess_type === 2)    $user_type = 'staff';
    elseif ($sess_type === 'student') $user_type = 'student';
    else                         $user_type = 'admin';
    $username = mysqli_real_escape_string($connection, @$_SESSION['user_name'] ?? '');
    $user_type = mysqli_real_escape_string($connection, $user_type);

    // Photo upload
    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
    $photo_paths = '[]';
    if (!empty($_FILES['image']['name'][0])) {
        $uploaded = array();
        foreach ($_FILES['image']['name'] as $k => $fileName) {
            $tmp = $_FILES['image']['tmp_name'][$k];
            if (!is_uploaded_file($tmp)) continue;
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $uname = rand(1000, 999999) . '_' . time() . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $uname)) $uploaded[] = $uname;
        }
        $photo_paths = json_encode($uploaded);
    }

    // Signature image upload
    $signature_image_val = '';
    if (!empty($_FILES['signature_image']['tmp_name']) && is_uploaded_file($_FILES['signature_image']['tmp_name'])) {
        $sig_ext   = strtolower(pathinfo($_FILES['signature_image']['name'], PATHINFO_EXTENSION));
        $sig_uname = 'sig_' . rand(1000, 999999) . '_' . time() . '.' . $sig_ext;
        if (move_uploaded_file($_FILES['signature_image']['tmp_name'], $uploadDir . $sig_uname)) {
            $signature_image_val = $sig_uname;
        }
    }

    // Sanitize all fields
    $qualification_code_title    = mysqli_real_escape_string($connection, $d('qualification_code_title'));
    $usi_id                      = mysqli_real_escape_string($connection, $d('usi_id'));
    $given_name                  = mysqli_real_escape_string($connection, $d('given_name'));
    $surname                     = mysqli_real_escape_string($connection, $d('surname'));
    $age_declaration_18          = $di('age_declaration_18');
    $dob                         = mysqli_real_escape_string($connection, $d('dob'));
    $gender_check                = $di('gender_check');

    $street_details              = mysqli_real_escape_string($connection, $d('street_details'));
    $post_code                   = mysqli_real_escape_string($connection, $d('post_code'));
    $sub_urb                     = mysqli_real_escape_string($connection, $d('sub_urb'));
    $stu_state                   = mysqli_real_escape_string($connection, $d('stu_state'));
    $postal_same_as_above        = $d('postal_same_as_above') !== '' ? $di('postal_same_as_above') : 0;
    $postal_address              = mysqli_real_escape_string($connection, $d('postal_address'));
    $english_read_write          = $di('english_read_write');
    $mobile_num                  = mysqli_real_escape_string($connection, $d('mobile_num'));
    $work_phone                  = mysqli_real_escape_string($connection, $d('work_phone'));
    $home_phone                  = mysqli_real_escape_string($connection, $d('home_phone'));
    $email_address               = mysqli_real_escape_string($connection, $d('emailAddress'));
    $em_full_name                = mysqli_real_escape_string($connection, $d('em_full_name'));
    $em_relation                 = mysqli_real_escape_string($connection, $d('em_relation'));
    $em_mobile_num               = mysqli_real_escape_string($connection, $d('em_mobile_num'));

    $birth_country               = mysqli_real_escape_string($connection, $d('birth_country'));
    $city_of_birth               = mysqli_real_escape_string($connection, $d('city_of_birth'));
    $lan_spoken                  = $di('lan_spoken');
    $lan_spoken_other            = mysqli_real_escape_string($connection, $d('lan_spoken_other'));
    $origin                      = $di('origin');

    $disability                  = $di('disability');
    $st_disability_type_raw      = isset($raw['st_disability_type']) && is_array($raw['st_disability_type'])
                                    ? implode(',', array_map('intval', $raw['st_disability_type'])) : '';
    $st_disability_type          = mysqli_real_escape_string($connection, $st_disability_type_raw);
    $disability_type_other       = mysqli_real_escape_string($connection, $d('disability_type_other'));

    $highest_school              = $di('highest_school');
    $sec_school                  = $di('sec_school');
    $year_completed_school       = mysqli_real_escape_string($connection, $d('year_completed_school'));
    $mode_delivery               = mysqli_real_escape_string($connection, $d('mode_delivery'));
    $courses_arr                 = isset($raw['courses']) && is_array($raw['courses']) ? $raw['courses'] : array();
    $courses_json                = mysqli_real_escape_string($connection, json_encode($courses_arr));

    $qual_cert1                  = $di('qual_cert1');
    $qual_cert2                  = $di('qual_cert2');
    $qual_cert3                  = $di('qual_cert3');
    $qual_cert4                  = $di('qual_cert4');
    $qual_diploma                = $di('qual_diploma');
    $qual_adv_diploma            = $di('qual_adv_diploma');
    $qual_bachelor               = $di('qual_bachelor');
    $qual_other                  = $di('qual_other');
    $qual_none                   = $di('qual_none');
    $qualification_attained      = mysqli_real_escape_string($connection, $d('qualification_attained'));

    $emp_status                  = $di('emp_status');
    $industry_of_work            = mysqli_real_escape_string($connection, $d('industry_of_work'));

    $study_reason                = $di('study_reason');
    $study_reason_other          = mysqli_real_escape_string($connection, $d('study_reason_other'));
    $study_reason_text           = mysqli_real_escape_string($connection, $d('study_reason_text'));
    $cred_tansf                  = $di('cred_tansf');

    $computer_access             = $di('computer_access');
    $computer_literacy           = mysqli_real_escape_string($connection, $d('computer_literacy'));
    $numeracy_skills             = mysqli_real_escape_string($connection, $d('numeracy_skills'));
    $additional_support          = $di('additional_support');
    $additional_support_specify  = mysqli_real_escape_string($connection, $d('additional_support_specify'));

    $usi_declaration             = $di('usi_declaration');
    $privacy_declaration         = $di('privacy_declaration');
    $refund_declaration          = $di('refund_declaration');

    $office_coordinator_name     = mysqli_real_escape_string($connection, $d('office_coordinator_name'));
    $office_invoice_provided     = $di('office_invoice_provided');
    $office_receipt_collected    = $di('office_receipt_collected');
    $office_lms_access           = $di('office_lms_access');
    $office_resources_access     = $di('office_resources_access');
    $office_uploaded_sms         = $di('office_uploaded_sms');
    $office_welcome_pack_sent    = $di('office_welcome_pack_sent');

    $candidate_declaration       = $di('candidate_declaration');
    $candidate_full_name         = mysqli_real_escape_string($connection, $d('candidate_full_name'));
    $candidate_date              = mysqli_real_escape_string($connection, $d('candidate_date'));
    $candidate_signature         = mysqli_real_escape_string($connection, $d('candidate_signature'));

    $enquiry_id                  = mysqli_real_escape_string($connection, $d('enquiry_id'));
    $rto_name                    = mysqli_real_escape_string($connection, $d('rto_name'));
    $branch_name                 = mysqli_real_escape_string($connection, $d('branch_name'));
    $student_user_id_val         = intval($_POST['student_user_id'] ?? 0);

    $dob_val   = $dob ? "'$dob'" : 'NULL';
    $cdate_val = $candidate_date ? "'$candidate_date'" : 'NULL';

    // Add student_user_id column if missing
    $col_chk = mysqli_query($connection, "SHOW COLUMNS FROM `enrolment_form_new` LIKE 'student_user_id'");
    if ($col_chk && mysqli_num_rows($col_chk) === 0) {
        mysqli_query($connection, "ALTER TABLE `enrolment_form_new` ADD COLUMN `student_user_id` INT UNSIGNED DEFAULT NULL");
    }

    // Add signature_image column if missing
    $sig_col_chk = mysqli_query($connection, "SHOW COLUMNS FROM `enrolment_form_new` LIKE 'signature_image'");
    if ($sig_col_chk && mysqli_num_rows($sig_col_chk) === 0) {
        mysqli_query($connection, "ALTER TABLE `enrolment_form_new` ADD COLUMN `signature_image` VARCHAR(255) DEFAULT NULL");
    }

    $sig_img_esc = mysqli_real_escape_string($connection, $signature_image_val);

    $insert = "INSERT INTO enrolment_form_new (
        user_type, username,
        qualification_code_title,
        usi_id, given_name, surname, age_declaration_18, dob, gender_check,
        street_details, post_code, sub_urb, stu_state,
        postal_same_as_above, postal_address, english_read_write,
        mobile_num, work_phone, home_phone, email_address,
        em_full_name, em_relation, em_mobile_num,
        birth_country, city_of_birth, lan_spoken, lan_spoken_other, origin,
        disability, st_disability_type, disability_type_other,
        highest_school, sec_school, year_completed_school, mode_delivery, courses,
        qual_cert1, qual_cert2, qual_cert3, qual_cert4,
        qual_diploma, qual_adv_diploma, qual_bachelor, qual_other, qual_none,
        qualification_attained,
        emp_status, industry_of_work,
        study_reason, study_reason_other, study_reason_text, cred_tansf,
        computer_access, computer_literacy, numeracy_skills,
        additional_support, additional_support_specify,
        usi_declaration, privacy_declaration, refund_declaration,
        office_coordinator_name, office_invoice_provided, office_receipt_collected,
        office_lms_access, office_resources_access, office_uploaded_sms, office_welcome_pack_sent,
        candidate_declaration, candidate_full_name, candidate_date, candidate_signature,
        enquiry_id, rto_name, branch_name, photo_paths, updated_status, student_user_id, signature_image
    ) VALUES (
        '$user_type', '$username',
        '$qualification_code_title',
        '$usi_id', '$given_name', '$surname', $age_declaration_18, $dob_val, $gender_check,
        '$street_details', '$post_code', '$sub_urb', '$stu_state',
        $postal_same_as_above, '$postal_address', $english_read_write,
        '$mobile_num', '$work_phone', '$home_phone', '$email_address',
        '$em_full_name', '$em_relation', '$em_mobile_num',
        '$birth_country', '$city_of_birth', $lan_spoken, '$lan_spoken_other', $origin,
        $disability, '$st_disability_type', '$disability_type_other',
        $highest_school, $sec_school, '$year_completed_school', '$mode_delivery', '$courses_json',
        $qual_cert1, $qual_cert2, $qual_cert3, $qual_cert4,
        $qual_diploma, $qual_adv_diploma, $qual_bachelor, $qual_other, $qual_none,
        '$qualification_attained',
        $emp_status, '$industry_of_work',
        $study_reason, '$study_reason_other', '$study_reason_text', $cred_tansf,
        $computer_access, '$computer_literacy', '$numeracy_skills',
        $additional_support, '$additional_support_specify',
        $usi_declaration, $privacy_declaration, $refund_declaration,
        '$office_coordinator_name', $office_invoice_provided, $office_receipt_collected,
        $office_lms_access, $office_resources_access, $office_uploaded_sms, $office_welcome_pack_sent,
        $candidate_declaration, '$candidate_full_name', $cdate_val, '$candidate_signature',
        '$enquiry_id', '$rto_name', '$branch_name', '$photo_paths', 'pending', $student_user_id_val, '$sig_img_esc'
    )";

    if (!mysqli_query($connection, $insert)) {
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . mysqli_error($connection)));
        exit;
    }
    $last_id = mysqli_insert_id($connection);

    // Generate unique student ID
    $course_id = !empty($courses_arr) ? (int)$courses_arr[0] : 0;
    $course_row = $course_id ? mysqli_fetch_assoc(mysqli_query($connection, "SELECT course_sname FROM courses WHERE course_id=$course_id")) : null;
    $year = date('Y');
    $unique_id = $course_row
        ? $year . strtoupper(preg_replace('/\s+/', '', $course_row['course_sname'])) . sprintf('%04d', $last_id)
        : $year . 'ENR' . sprintf('%04d', $last_id);

    mysqli_query($connection, "UPDATE enrolment_form_new SET office_student_id='" . mysqli_real_escape_string($connection, $unique_id) . "' WHERE id=$last_id");

    echo json_encode(array('success' => true, 'unique_id' => $unique_id));
    exit;
}

// --- Update Enrolment Form New (student edit) ---
if (@$_POST['formName'] == 'update_enrolment_form_new') {
    ob_clean();
    header('Content-Type: application/json');

    $enrolment_id_upd    = intval($_POST['enrolment_id'] ?? 0);
    $student_user_id_upd = intval($_POST['student_user_id'] ?? 0);
    if ($enrolment_id_upd <= 0) {
        echo json_encode(array('success' => false, 'message' => 'Invalid enrolment ID.'));
        exit;
    }

    $raw = isset($_POST['details']) ? json_decode($_POST['details'], true) : array();
    if (!is_array($raw)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid form data.'));
        exit;
    }

    $d = function($key, $def = '') use ($raw) {
        return isset($raw[$key]) && $raw[$key] !== '' ? $raw[$key] : $def;
    };
    $di = function($key) use ($raw) {
        return isset($raw[$key]) ? (int)$raw[$key] : 0;
    };

    $email_check = trim($raw['emailAddress'] ?? '');
    if ($email_check === '' || !filter_var($email_check, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array('success' => false, 'message' => 'A valid email address is required.'));
        exit;
    }

    // Photo upload — append new photos if any
    $photo_paths_upd = null;
    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
    if (!empty($_FILES['image']['name'][0])) {
        $uploaded = array();
        foreach ($_FILES['image']['name'] as $k => $fileName) {
            $tmp = $_FILES['image']['tmp_name'][$k];
            if (!is_uploaded_file($tmp)) continue;
            $ext  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $uname = rand(1000, 999999) . '_' . time() . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $uname)) $uploaded[] = $uname;
        }
        if (!empty($uploaded)) $photo_paths_upd = mysqli_real_escape_string($connection, json_encode($uploaded));
    }

    // Signature image upload — replace if new file provided
    $signature_image_upd = null;
    if (!empty($_FILES['signature_image']['tmp_name']) && is_uploaded_file($_FILES['signature_image']['tmp_name'])) {
        $sig_ext   = strtolower(pathinfo($_FILES['signature_image']['name'], PATHINFO_EXTENSION));
        $sig_uname = 'sig_' . rand(1000, 999999) . '_' . time() . '.' . $sig_ext;
        if (move_uploaded_file($_FILES['signature_image']['tmp_name'], $uploadDir . $sig_uname)) {
            $signature_image_upd = mysqli_real_escape_string($connection, $sig_uname);
        }
    }

    $qualification_code_title    = mysqli_real_escape_string($connection, $d('qualification_code_title'));
    $usi_id                      = mysqli_real_escape_string($connection, $d('usi_id'));
    $given_name                  = mysqli_real_escape_string($connection, $d('given_name'));
    $surname                     = mysqli_real_escape_string($connection, $d('surname'));
    $age_declaration_18          = $di('age_declaration_18');
    $dob                         = mysqli_real_escape_string($connection, $d('dob'));
    $gender_check                = $di('gender_check');
    $street_details              = mysqli_real_escape_string($connection, $d('street_details'));
    $post_code                   = mysqli_real_escape_string($connection, $d('post_code'));
    $sub_urb                     = mysqli_real_escape_string($connection, $d('sub_urb'));
    $stu_state                   = mysqli_real_escape_string($connection, $d('stu_state'));
    $postal_same_as_above        = $d('postal_same_as_above') !== '' ? $di('postal_same_as_above') : 0;
    $postal_address              = mysqli_real_escape_string($connection, $d('postal_address'));
    $english_read_write          = $di('english_read_write');
    $mobile_num                  = mysqli_real_escape_string($connection, $d('mobile_num'));
    $work_phone                  = mysqli_real_escape_string($connection, $d('work_phone'));
    $home_phone                  = mysqli_real_escape_string($connection, $d('home_phone'));
    $email_address               = mysqli_real_escape_string($connection, $d('emailAddress'));
    $em_full_name                = mysqli_real_escape_string($connection, $d('em_full_name'));
    $em_relation                 = mysqli_real_escape_string($connection, $d('em_relation'));
    $em_mobile_num               = mysqli_real_escape_string($connection, $d('em_mobile_num'));
    $birth_country               = mysqli_real_escape_string($connection, $d('birth_country'));
    $city_of_birth               = mysqli_real_escape_string($connection, $d('city_of_birth'));
    $lan_spoken                  = $di('lan_spoken');
    $lan_spoken_other            = mysqli_real_escape_string($connection, $d('lan_spoken_other'));
    $origin                      = $di('origin');
    $disability                  = $di('disability');
    $st_disability_type_raw      = isset($raw['st_disability_type']) && is_array($raw['st_disability_type'])
                                    ? implode(',', array_map('intval', $raw['st_disability_type'])) : '';
    $st_disability_type          = mysqli_real_escape_string($connection, $st_disability_type_raw);
    $disability_type_other       = mysqli_real_escape_string($connection, $d('disability_type_other'));
    $highest_school              = $di('highest_school');
    $sec_school                  = $di('sec_school');
    $year_completed_school       = mysqli_real_escape_string($connection, $d('year_completed_school'));
    $mode_delivery               = mysqli_real_escape_string($connection, $d('mode_delivery'));
    $courses_arr_upd             = isset($raw['courses']) && is_array($raw['courses']) ? $raw['courses'] : array();
    $courses_json_upd            = mysqli_real_escape_string($connection, json_encode($courses_arr_upd));
    $qual_cert1                  = $di('qual_cert1');
    $qual_cert2                  = $di('qual_cert2');
    $qual_cert3                  = $di('qual_cert3');
    $qual_cert4                  = $di('qual_cert4');
    $qual_diploma                = $di('qual_diploma');
    $qual_adv_diploma            = $di('qual_adv_diploma');
    $qual_bachelor               = $di('qual_bachelor');
    $qual_other                  = $di('qual_other');
    $qual_none                   = $di('qual_none');
    $qualification_attained      = mysqli_real_escape_string($connection, $d('qualification_attained'));
    $emp_status                  = $di('emp_status');
    $industry_of_work            = mysqli_real_escape_string($connection, $d('industry_of_work'));
    $study_reason                = $di('study_reason');
    $study_reason_other          = mysqli_real_escape_string($connection, $d('study_reason_other'));
    $study_reason_text           = mysqli_real_escape_string($connection, $d('study_reason_text'));
    $cred_tansf                  = $di('cred_tansf');
    $computer_access             = $di('computer_access');
    $computer_literacy           = mysqli_real_escape_string($connection, $d('computer_literacy'));
    $numeracy_skills             = mysqli_real_escape_string($connection, $d('numeracy_skills'));
    $additional_support          = $di('additional_support');
    $additional_support_specify  = mysqli_real_escape_string($connection, $d('additional_support_specify'));
    $usi_declaration             = $di('usi_declaration');
    $privacy_declaration         = $di('privacy_declaration');
    $refund_declaration          = $di('refund_declaration');
    $candidate_declaration       = $di('candidate_declaration');
    $candidate_full_name         = mysqli_real_escape_string($connection, $d('candidate_full_name'));
    $candidate_date              = mysqli_real_escape_string($connection, $d('candidate_date'));
    $candidate_signature         = mysqli_real_escape_string($connection, $d('candidate_signature'));
    $dob_val_upd   = $dob ? "'$dob'" : 'NULL';
    $cdate_val_upd = $candidate_date ? "'$candidate_date'" : 'NULL';

    $photo_set     = $photo_paths_upd     ? ", photo_paths='$photo_paths_upd'"               : '';
    $sig_image_set = $signature_image_upd ? ", signature_image='$signature_image_upd'" : '';

    $upd = "UPDATE enrolment_form_new SET
        qualification_code_title='$qualification_code_title',
        usi_id='$usi_id', given_name='$given_name', surname='$surname',
        age_declaration_18=$age_declaration_18, dob=$dob_val_upd, gender_check=$gender_check,
        street_details='$street_details', post_code='$post_code', sub_urb='$sub_urb', stu_state='$stu_state',
        postal_same_as_above=$postal_same_as_above, postal_address='$postal_address',
        english_read_write=$english_read_write,
        mobile_num='$mobile_num', work_phone='$work_phone', home_phone='$home_phone',
        email_address='$email_address',
        em_full_name='$em_full_name', em_relation='$em_relation', em_mobile_num='$em_mobile_num',
        birth_country='$birth_country', city_of_birth='$city_of_birth',
        lan_spoken=$lan_spoken, lan_spoken_other='$lan_spoken_other', origin=$origin,
        disability=$disability, st_disability_type='$st_disability_type',
        disability_type_other='$disability_type_other',
        highest_school=$highest_school, sec_school=$sec_school,
        year_completed_school='$year_completed_school', mode_delivery='$mode_delivery',
        courses='$courses_json_upd',
        qual_cert1=$qual_cert1, qual_cert2=$qual_cert2, qual_cert3=$qual_cert3, qual_cert4=$qual_cert4,
        qual_diploma=$qual_diploma, qual_adv_diploma=$qual_adv_diploma,
        qual_bachelor=$qual_bachelor, qual_other=$qual_other, qual_none=$qual_none,
        qualification_attained='$qualification_attained',
        emp_status=$emp_status, industry_of_work='$industry_of_work',
        study_reason=$study_reason, study_reason_other='$study_reason_other',
        study_reason_text='$study_reason_text', cred_tansf=$cred_tansf,
        computer_access=$computer_access, computer_literacy='$computer_literacy',
        numeracy_skills='$numeracy_skills',
        additional_support=$additional_support, additional_support_specify='$additional_support_specify',
        usi_declaration=$usi_declaration, privacy_declaration=$privacy_declaration,
        refund_declaration=$refund_declaration,
        candidate_declaration=$candidate_declaration, candidate_full_name='$candidate_full_name',
        candidate_date=$cdate_val_upd, candidate_signature='$candidate_signature',
        updated_status='resolve_query',
        student_user_id=$student_user_id_upd
        $photo_set
        $sig_image_set
        WHERE id=$enrolment_id_upd";

    if (!mysqli_query($connection, $upd)) {
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . mysqli_error($connection)));
        exit;
    }
    echo json_encode(array('success' => true));
    exit;
}

if (@$_POST['formName'] == 'invoice_submit_company') {

     // Sanitize input
     
    $invoiceNumber = uniqid('INV_');
     $address = mysqli_real_escape_string($connection, $_POST['address']);
     $phone = mysqli_real_escape_string($connection, $_POST['phone']);
     
     $contact_number = mysqli_real_escape_string($connection, $_POST['contact_number']);
     $contact_name = mysqli_real_escape_string($connection, $_POST['contact_name']);
     $contact_email = mysqli_real_escape_string($connection, $_POST['contact_email']);
     $contact_role = mysqli_real_escape_string($connection, $_POST['contact_role']);
     
     $num_students = (int) $_POST['num_students'];
     $students_names = explode("\n", trim($_POST['students_names'])); // Convert to array
     $students_names_json = json_encode($students_names);
     
     $course_name = mysqli_real_escape_string($connection, $_POST['course_name']);
     $total_amount = (float) $_POST['total_amount'];
     $paid_amount = (float) $_POST['paid_amount'];
     $date_time = mysqli_real_escape_string($connection, $_POST['date_time']);
     $payment_mode = mysqli_real_escape_string($connection, $_POST['payment_mode']);
     $balance_amount = (float) $_POST['balance_amount'];
     
     $date = date('Y');
 
     // Contact Person Data as JSON
     $contact_person = json_encode([
         "name" => $contact_name,
         "email" => $contact_email,
         "role" => $contact_role,
         "phone" => $contact_number
     ]);
 
     // Insert data into database with invoice_type = 2
     $query = "INSERT INTO payment_records 
         (address, phone, contact_person, num_students, students_names, course, total_amount, paid_amount, dateTime, paymentMode, balance_amount, invoice_number, invoice_type)
         VALUES 
         ( '$address', '$phone', '$contact_person', '$num_students', '$students_names_json', '$course_name', '$total_amount', '$paid_amount', '$date_time', '$payment_mode', '$balance_amount', '$invoiceNumber', '2')";
 
     $insert = mysqli_query($connection, $query);
 
     if ($insert) {
         $lastId = mysqli_insert_id($connection);
         $uniqueId = sprintf('INV%s%05d', $date, $lastId);
 
         // Update with unique invoice ID
         mysqli_query($connection, "UPDATE payment_records SET invoice_number='$uniqueId' WHERE id=$lastId");
 
        // Generate PDF invoice
        if(!crm_require_tcpdf()){
            echo '0';
            exit;
        }
         $pdf = new TCPDF();
         $pdf->SetCreator(PDF_CREATOR);
         $pdf->SetAuthor('Auz Training College Pty Ltd');
         $pdf->SetTitle('Invoice');
 
         $pdf->AddPage();
 
         // Company Information
         $company_title = "Auz Training College Pty Ltd";
         $company_bsb = "BSB: 065 000";
         $company_account = "A/c Number: 1255 8010";
 
         // Add Logo
         $pdf->Image('../assets/images/logo-dark.webp', 15, 10, 40, 20, '', '', '', false, 300);
 
         // Set Title
         $pdf->SetFont('helvetica', 'B', 16);
         $pdf->Cell(0, 40, "Invoice", 0, 1, 'C');
 
         // Company Details
         $pdf->SetFont('helvetica', '', 10);
         $pdf->Cell(0, 5, $company_title, 0, 1, 'R');
         $pdf->Cell(0, 5, $company_bsb, 0, 1, 'R');
         $pdf->Cell(0, 5, $company_account, 0, 1, 'R');
         $pdf->Cell(0, 5, "Invoice Number: " . $uniqueId, 0, 1, 'R');
         $pdf->Ln(10); // Line break
 
         // Invoice Content
         $html = "
         <style>
             table { border-collapse: collapse; width: 100%; }
             th, td { border: 1px solid #000; padding: 8px; text-align: left; }
             th { background-color: #f2f2f2; }
         </style>
         <table>
             <tr><th>Field</th><th>Details</th></tr>
             <tr><td><strong>Company Name:</strong></td><td>test company</td></tr>
             <tr><td><strong>Address:</strong></td><td>$address</td></tr>
             <tr><td><strong>Phone No:</strong></td><td>$phone</td></tr>
             <tr><td><strong>Contact Person's Name:</strong></td><td>$contact_name</td></tr>
             <tr><td><strong>Contact Person's Email:</strong></td><td>$contact_email</td></tr>
             <tr><td><strong>Contact Person's Role:</strong></td><td>$contact_role</td></tr>
             <tr><td><strong>Contact Person's Phone:</strong></td><td>$contact_number</td></tr>
             <tr><td><strong>Number of Students:</strong></td><td>$num_students</td></tr>
             <tr><td><strong>Students Names:</strong></td><td>$students_names_json</td></tr>
             <tr><td><strong>Course Name:</strong></td><td>$course_name</td></tr>
             <tr><td><strong>Total Amount:</strong></td><td>$$total_amount</td></tr>
             <tr><td><strong>Paid Amount:</strong></td><td>$$paid_amount</td></tr>
             <tr><td><strong>Date & Time:</strong></td><td>$date_time</td></tr>
             <tr><td><strong>Payment Mode:</strong></td><td>$payment_mode</td></tr>
             <tr><td><strong>Balance Amount:</strong></td><td>$$balance_amount</td></tr>
         </table>
         <br><br>
         <strong>Terms & Conditions:</strong><br>
         Please do the payment using the below Bank account details.<br>
         <br>
         <strong>A/c Name:</strong> Auz Training College Pty Ltd<br>
         <strong>BSB:</strong> 065 000<br>
         <strong>A/c Number:</strong> 1255 8010<br>
         <br>
         Invoices we paid – Raise training, placement invoices.
         ";
 
         $pdf->writeHTML($html, true, false, true, false, '');

         $invoicePdfPath = __DIR__ . "/invoices/$uniqueId.pdf";
         $pdf->Output($invoicePdfPath, 'F'); // Save to file
 
         echo json_encode(["status" => "success", "invoice_number" => $uniqueId, "pdf_path" => $invoicePdfPath]);
     } else {
         echo json_encode(["status" => "error", "message" => mysqli_error($connection)]);
     }
}


if(@$_POST['formName']=='invoice_submit'){

    $invoice_number = uniqid('INV_');
    $given_name = $_POST['given_name'];
    $surname = $_POST['surname'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $totalFees = $_POST['totalFees'];
    $paymentDone = $_POST['paymentDone'];
    $datePaid = $_POST['datePaid'];
    $remainingDue = $_POST['remainingDue'];
    $comments = $_POST['comments'];
    $instalmentPaid = $_POST['instalmentPaid'];
    $dateTime = $_POST['dateTime'];
    $whoTookPayment = $_POST['whoTookPayment'];
    $paymentMode = $_POST['paymentMode'];
    $fundsReceived = $_POST['fundsReceived'];
    $whoChecked = $_POST['whoChecked'];
    $receiptEmailed = $_POST['receiptEmailed'];


    $date = date('Y');

    // Insert data into database
    $query = mysqli_query($connection, "INSERT INTO payment_records 
        (given_name, surname, address, phone, email, course, totalFees, paymentDone, datePaid, remainingDue, comments, instalmentPaid, dateTime, whoTookPayment, paymentMode, fundsReceived, whoChecked, receiptEmailed , invoice_number)
        VALUES 
        ('$given_name', '$surname', '$address', '$phone', '$email', '$course', '$totalFees', '$paymentDone', '$datePaid', '$remainingDue', '$comments', '$instalmentPaid', '$dateTime', '$whoTookPayment', '$paymentMode', '$fundsReceived', '$whoChecked', '$receiptEmailed' , '$invoice_number')");

    $lastId = mysqli_insert_id($connection);
    $uniqueId = sprintf('INV%s%05d', $date, $lastId);
    $updateQuery = mysqli_query($connection, "UPDATE payment_records SET invoice_number='$uniqueId' WHERE id=$lastId");

    if ($updateQuery) {
        // Generate PDF invoice
       

    // Create PDF instance
    if(!crm_require_tcpdf()){
        echo '0';
        exit;
    }
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Auz Training');
    $pdf->SetTitle('Payment Invoice');
    $pdf->AddPage();
    
    // Company Information
    $company_name = "Auz Training";
    $company_abn = "ABN: 74 615 207 237";
    $company_address = "Level 1/118 King William Street, Adelaide 5000";
    $company_phone = "0468 449 468";
    $date_time = date('Y-M-d H:m:s');
    
    // Customer Information
    $customer_name = $surname . $given_name;
    $customer_email = $email;
    $customer_phone = $phone;
    $course_name = $course;
    
    // Payment Details
    $course_fees = $totalFees;
    $amount_paid = $paymentDone;
    $amount_due = $remainingDue;
    $payment_plan = "The remaining instalments need to be paid as follows:\n\n"
        . "$400 by 27th March 2025\n"
        . "$400 by 10th April 2025\n"
        . "$349 by 24th April 2025.";
    $orientation_details = "14th March 2025 (Friday) from 3 PM to 5 PM.";
    
    // Company Logo
    $pdf->Image('../assets/images/logo-dark.webp', 160, 10, 40, 20, '', '', '', false, 300);
    $pdf->Ln(30);
    
    // Title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, "INVOICE", 0, 1, 'C');
    $pdf->Ln(5);
    
    // Company Details
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, $company_name, 0, 1, 'L');
    $pdf->Cell(0, 5, $company_abn, 0, 1, 'L');
    $pdf->Cell(0, 5, $company_address, 0, 1, 'L');
    $pdf->Cell(0, 5, "M: " . $company_phone, 0, 1, 'L');
    $pdf->Ln(5);
    
    // Customer Details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 5, "To: " . $customer_name, 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, "Email: " . $customer_email, 0, 1, 'L');
    $pdf->Cell(0, 5, "M: " . $customer_phone, 0, 1, 'L');
    $pdf->Ln(5);

        
    // Invoice Details
    $pdf->Cell(0, 5, "Invoice No: " . $invoice_number, 0, 1, 'R');
    $pdf->Cell(0, 5, "Date: " . $date_time, 0, 1, 'R');
    $pdf->Ln(10); 
    
    // Course Details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 5, "Course Name: " . $course_name, 0, 1, 'L');
    $pdf->Ln(5);
    
    // Payment Details Table
    $html = '<table border="1" cellspacing="0" cellpadding="5">
    <tr>
    <th><strong>Item Description</strong></th>
    <th><strong>Amount</strong></th>
    </tr>
    <tr>
    <td>Course Fees</td>
    <td>$' . $course_fees . '</td>
    </tr>
    <tr>
    <td>Pending Amount</td>
    <td>$' . $course_fees . '</td>
    </tr>
    <tr>
    <td>Amount Paid</td>
    <td>$' . $amount_paid . '</td>
    </tr>
    <tr>
    <td>Amount Due</td>
    <td>$' . $amount_due . '</td>
    </tr>
    </table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(5);
    
    // Additional Notes
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->MultiCell(0, 5, "Additional Notes: \nThe down payment of $700 has been paid during enrolment on 13th March 2025.\n\n$payment_plan", 0, 'L');
    $pdf->Ln(5);
    
    // Orientation Details
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 5, "Total amount has to be paid within the duration of Theoretical part. 
If not paid by the due date, additional charges will be applicable. 
A mandatory online Orientation session has been booked for you. \n\n You can attend the Orientation session on the date mentioned 
below: \n\n\n\n\n\n\n\n The Zoom link for Online Orientation has been emailed to you and you are expected to come 
online on Friday by 3 PM and be there for 2 hours with the video on. \n\n $orientation_details", 0, 'L');
    $pdf->Ln(5);
    
    // Payment Instructions
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->MultiCell(0, 5, "In case Payment is not done, please find the below details to make payment:\nAccount Name: Auz Training College Pty Ltd\nBSB: 065000\nAcc. No. 1255 8010", 0, 'L');
    $pdf->Ln(10);
    
    // Save PDF
    $pdfFilePath = __DIR__ . "/invoices/$invoice_number.pdf";
    $pdf->Output($pdfFilePath, 'F');

        echo json_encode(["status" => "success", "invoice_number" => $invoice_number, "pdf_path" => $pdfFilePath]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($connection)]);
    }

}
// if(@$_POST['formName']=='invoice_submit'){
//     $payment_date=$_POST['payment_date'];
//     $amount_due=$_POST['amount_due'];
//     $amount_paid=$_POST['amount_paid'];
//     $course_fee=$_POST['course_fee'];
//     $course_name=$_POST['course_name'];
//     $enrol_id=$_POST['enrol_id'];
//     $student_name=$_POST['student_name'];
//     $date=date('Y');

//     $query=mysqli_query($connection,"INSERT INTO invoices(inv_std_name,st_unique_id,inv_course,inv_fee,inv_paid,inv_due,inv_payment_date)VALUES('$student_name','$enrol_id','$course_name','$course_fee','$amount_paid',$amount_due,'$payment_date')");
//     $lastId=mysqli_insert_id($connection);
//     $uniqueId=sprintf('INV'.$date.'%05d', $lastId);

//     $querys=mysqli_query($connection,"UPDATE invoices SET inv_auto_id='$uniqueId' WHERE inv_id=$lastId");

//     $error=mysqli_error($connection);
//     if($error!=''){
//         echo 1;
//     }else{
//         echo $uniqueId;
//     }
// }

if(!function_exists('auth_send_login_otp')){
    function auth_send_login_otp($to, $otp, $label){
        if(!function_exists('send_mail')){
            require_once(__DIR__ . '/mail_function.php');
        }
        $subject = 'Your Login OTP - National College Australia';
        $body = '<div style="font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;">'
              . '<h3 style="margin:0 0 12px;">Login verification</h3>'
              . '<p style="margin:0 0 10px;">Use this OTP to continue your ' . htmlspecialchars($label) . ' login.</p>'
              . '<p style="font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;">' . htmlspecialchars($otp) . '</p>'
              . '<p style="margin:0;color:#666;">This OTP is valid for 10 minutes.</p>'
              . '<p style="margin-top:14px;color:#666;">If this was not you, please ignore this email.</p>'
              . '</div>';
        send_mail($to, $subject, $body, array('email_category' => 'login_otp', 'meta' => array('context' => $label)));
    }
}

if(!function_exists('auth_login_otp_client_ip')){
    function auth_login_otp_client_ip(){
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $p = trim($parts[0]);
            if($p !== '') return substr($p, 0, 45);
        }
        return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    }
}

if(!function_exists('auth_login_otp_user_agent')){
    function auth_login_otp_user_agent(){
        return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512);
    }
}

if(!function_exists('auth_login_otp_ensure_table')){
    function auth_login_otp_ensure_table($connection){
        static $ok = null;
        if($ok !== null) return $ok;
        $sql = "CREATE TABLE IF NOT EXISTS `login_otp_challenges` (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `channel` enum('admin','student') NOT NULL,
          `email` varchar(255) NOT NULL,
          `user_pk` bigint(20) UNSIGNED NOT NULL,
          `otp_code` varchar(10) NOT NULL COMMENT 'plain OTP (testing)',
          `session_bind` char(64) NOT NULL,
          `expires_at` datetime NOT NULL,
          `is_used` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
          `verified_at` datetime DEFAULT NULL,
          `verify_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
          `max_verify_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
          `ip_request` varchar(45) DEFAULT NULL,
          `ip_last_verify` varchar(45) DEFAULT NULL,
          `user_agent` varchar(512) DEFAULT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `session_bind` (`session_bind`),
          KEY `idx_channel_email_active` (`channel`,`email`,`is_used`,`expires_at`),
          KEY `idx_expires` (`expires_at`),
          KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try{
            $ok = (bool)mysqli_query($connection, $sql);
        }catch(Exception $e){
            $ok = false;
        }
        if($ok){
            static $migrated = false;
            if(!$migrated){
                $migrated = true;
                try{
                    $chk = @mysqli_query($connection, "SHOW COLUMNS FROM `login_otp_challenges` LIKE 'otp_hash'");
                    if($chk && mysqli_num_rows($chk) > 0){
                        @mysqli_query($connection, "ALTER TABLE `login_otp_challenges` CHANGE COLUMN `otp_hash` `otp_code` varchar(10) NOT NULL COMMENT 'plain OTP (testing)'");
                    }
                }catch(Exception $e){
                    // Ignore migration failure; OTP can still function with existing schema.
                }
            }
        }
        return $ok;
    }
}

if(!function_exists('auth_login_otp_random_code')){
    function auth_login_otp_random_code(){
        if(function_exists('random_int')){
            return (string)random_int(100000, 999999);
        }
        return (string)mt_rand(100000, 999999);
    }
}

if(!function_exists('auth_login_otp_random_bind')){
    function auth_login_otp_random_bind(){
        if(function_exists('random_bytes')){
            return bin2hex(random_bytes(32));
        }
        if(function_exists('openssl_random_pseudo_bytes')){
            $raw = openssl_random_pseudo_bytes(32);
            if($raw !== false){
                return bin2hex($raw);
            }
        }
        return hash('sha256', uniqid(mt_rand(), true) . microtime(true));
    }
}

if(!function_exists('auth_login_otp_normalize_email')){
    function auth_login_otp_normalize_email($email){
        return strtolower(trim((string)$email));
    }
}

if(!function_exists('auth_env_value')){
    function auth_env_value($key){
        $key = trim((string)$key);
        if($key === ''){
            return '';
        }
        $v = getenv($key);
        if($v !== false && trim((string)$v) !== ''){
            return trim((string)$v);
        }
        static $envMap = null;
        if($envMap === null){
            $envMap = array();
            $envPath = dirname(__DIR__) . '/.env';
            if(is_file($envPath) && is_readable($envPath)){
                $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if(is_array($lines)){
                    foreach($lines as $line){
                        $line = trim((string)$line);
                        if($line === '' || strpos($line, '#') === 0){
                            continue;
                        }
                        $pos = strpos($line, '=');
                        if($pos === false){
                            continue;
                        }
                        $k = strtoupper(trim(substr($line, 0, $pos)));
                        $val = trim(substr($line, $pos + 1));
                        if($val !== '' && (($val[0] === '"' && substr($val, -1) === '"') || ($val[0] === "'" && substr($val, -1) === "'"))){
                            $val = substr($val, 1, -1);
                        }
                        $envMap[$k] = $val;
                    }
                }
            }
        }
        return isset($envMap[strtoupper($key)]) ? trim((string)$envMap[strtoupper($key)]) : '';
    }
}

if(!function_exists('auth_is_default_email_login')){
    function auth_is_default_email_login($email){
        $emailNorm = auth_login_otp_normalize_email($email);
        if($emailNorm === ''){
            return false;
        }
        $defaultEnvEmail = auth_env_value('DEFAULT_EMAIL');
        if($defaultEnvEmail === ''){
            $defaultEnvEmail = auth_env_value('default_email');
        }
        if($defaultEnvEmail === ''){
            return false;
        }
        return hash_equals(auth_login_otp_normalize_email($defaultEnvEmail), $emailNorm);
    }
}

if(!function_exists('auth_login_otp_rate_ok')){
    function auth_login_otp_rate_ok($connection, $channel, $email_esc, $max_per_hour){
        $ch = mysqli_real_escape_string($connection, $channel);
        $q = mysqli_query($connection, "SELECT COUNT(*) AS c FROM login_otp_challenges WHERE channel='$ch' AND email='$email_esc' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        if(!$q) return true;
        $r = mysqli_fetch_assoc($q);
        return (int)($r['c'] ?? 0) < $max_per_hour;
    }
}

if(!function_exists('auth_login_otp_revoke_active')){
    function auth_login_otp_revoke_active($connection, $channel, $email_esc){
        $ch = mysqli_real_escape_string($connection, $channel);
        mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=2 WHERE channel='$ch' AND email='$email_esc' AND is_used=0");
    }
}

if(!defined('AUTH_LOGIN_OTP_TTL_SECONDS')){
    define('AUTH_LOGIN_OTP_TTL_SECONDS', 600);
}
if(!defined('AUTH_LOGIN_OTP_MAX_PER_EMAIL_HOUR')){
    define('AUTH_LOGIN_OTP_MAX_PER_EMAIL_HOUR', 12);
}

if(@$_POST['formName']=='login_request_otp'){
    $debug_login = (isset($_POST['debug']) && (string)$_POST['debug'] === '1') || (isset($_GET['debug']) && (string)$_GET['debug'] === '1');
    $debug_step = 'start';
    if($debug_login){
        @ini_set('display_errors', '1');
        @error_reporting(E_ALL);
        register_shutdown_function(function(){
            $e = error_get_last();
            if(!$e){ return; }
            $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);
            if(!in_array((int)$e['type'], $fatalTypes, true)){ return; }
            if(!headers_sent()){
                header('Content-Type: application/json; charset=UTF-8');
            }
            echo json_encode(array(
                'success' => false,
                'message' => 'Fatal error during login_request_otp.',
                'debug' => array(
                    'type' => (int)$e['type'],
                    'error' => (string)$e['message'],
                    'file' => basename((string)$e['file']),
                    'line' => (int)$e['line']
                )
            ));
        });
    }
    try{
        $debug_step = 'ensure_table';
        if(!auth_login_otp_ensure_table($connection)){
            $resp = array('success'=>false,'message'=>'Login verification is temporarily unavailable.');
            if($debug_login){
                $resp['debug'] = array('step' => $debug_step, 'mysqli_error' => mysqli_error($connection));
            }
            echo json_encode($resp);
            exit;
        }
        $debug_step = 'read_input';
        $email_raw = auth_login_otp_normalize_email($_POST['email'] ?? '');
        $email_esc = mysqli_real_escape_string($connection, $email_raw);
        $password = trim($_POST['password'] ?? '');
        $is_default_email_login = auth_is_default_email_login($email_raw);
        if($email_raw === ''){
            echo json_encode(array('success'=>false,'message'=>'Email is required.'));
            exit;
        }
        if(!$is_default_email_login && $password === ''){
            echo json_encode(array('success'=>false,'message'=>'Password is required.'));
            exit;
        }
        if($is_default_email_login){
            $query = mysqli_query($connection,"SELECT user_id,user_type,user_name,user_log_id,user_email FROM users WHERE LOWER(TRIM(user_email))='$email_esc' LIMIT 1");
            $id = ($query && mysqli_num_rows($query) > 0) ? mysqli_fetch_assoc($query) : null;
            if(!$id){
                echo json_encode(array('success'=>false,'message'=>'Account not found for DEFAULT_EMAIL.'));
                exit;
            }
            $_SESSION['user_id'] = (int)$id['user_id'];
            $_SESSION['user_type'] = (int)$id['user_type'];
            $_SESSION['user_name'] = $id['user_name'];
            $_SESSION['user_log_id'] = $id['user_log_id'];
            unset($_SESSION['login_otp_pending'], $_SESSION['login_otp_admin'], $_SESSION['login_otp_student'], $_SESSION['login_otp_bind']);
            echo json_encode(array('success'=>true,'auto_login'=>true,'user_type'=>(int)$_SESSION['user_type'],'redirect'=>'dashboard.php'));
            exit;
        }
        $debug_step = 'rate_limit';
        if(!auth_login_otp_rate_ok($connection, 'admin', $email_esc, AUTH_LOGIN_OTP_MAX_PER_EMAIL_HOUR)){
            echo json_encode(array('success'=>false,'message'=>'Too many verification codes requested. Try again in an hour.'));
            exit;
        }
        $debug_step = 'fetch_user';
        $query = mysqli_query($connection,"SELECT user_id,user_type,user_name,user_log_id,user_email FROM users WHERE LOWER(TRIM(user_email))='$email_esc' AND user_password='".mysqli_real_escape_string($connection, $password)."' LIMIT 1");
        $id = ($query && mysqli_num_rows($query) > 0) ? mysqli_fetch_assoc($query) : null;
        if(!$id){
            echo json_encode(array('success'=>false,'message'=>'Invalid email or password.'));
            exit;
        }
        $debug_step = 'session_write';
        $_SESSION['user_id']     = (int)$id['user_id'];
        $_SESSION['user_type']   = (int)$id['user_type'];
        $_SESSION['user_name']   = $id['user_name'];
        $_SESSION['user_log_id'] = $id['user_log_id'];
        unset($_SESSION['login_otp_pending'], $_SESSION['login_otp_admin'], $_SESSION['login_otp_student'], $_SESSION['login_otp_bind']);
        $redirect = ((int)$id['user_type'] === 1 || (int)$id['user_type'] === 2) ? 'dashboard.php' : 'student_docs.php';
        echo json_encode(array('success'=>true,'auto_login'=>true,'user_type'=>(int)$id['user_type'],'redirect'=>$redirect));
        exit;
    }catch(Exception $e){
        $resp = array('success'=>false,'message'=>'Login verification failed. Please try again.');
        if($debug_login){
            $resp['debug'] = array(
                'step' => $debug_step,
                'exception' => $e->getMessage(),
                'file' => basename((string)$e->getFile()),
                'line' => (int)$e->getLine()
            );
        }
        echo json_encode($resp);
        exit;
    }
}

if(@$_POST['formName']=='login_verify_otp'){
    if(!auth_login_otp_ensure_table($connection)){
        echo json_encode(array('success'=>false,'message'=>'Login verification is temporarily unavailable.'));
        exit;
    }
    $otp = trim($_POST['otp'] ?? '');
    if($otp === ''){
        echo json_encode(array('success'=>false,'message'=>'Please enter OTP.'));
        exit;
    }
    $pend = $_SESSION['login_otp_pending'] ?? null;
    $bind = is_array($pend) ? ($pend['bind'] ?? '') : '';
    $ch = is_array($pend) ? ($pend['channel'] ?? '') : '';
    if($bind === '' || $ch !== 'admin'){
        echo json_encode(array('success'=>false,'message'=>'Verification session expired. Please log in again.'));
        exit;
    }
    $bind_esc = mysqli_real_escape_string($connection, $bind);
    $rq = mysqli_query($connection, "SELECT * FROM login_otp_challenges WHERE session_bind='$bind_esc' AND channel='admin' AND is_used=0 LIMIT 1");
    $row = ($rq && mysqli_num_rows($rq) > 0) ? mysqli_fetch_assoc($rq) : null;
    if(!$row){
        unset($_SESSION['login_otp_pending']);
        echo json_encode(array('success'=>false,'message'=>'Invalid or expired verification. Please log in again.'));
        exit;
    }
    $rid = (int)$row['id'];
    if(strtotime($row['expires_at']) < time()){
        mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=4 WHERE id=$rid");
        unset($_SESSION['login_otp_pending']);
        echo json_encode(array('success'=>false,'message'=>'OTP expired. Please log in again.'));
        exit;
    }
    if((int)$row['verify_attempts'] >= (int)$row['max_verify_attempts']){
        mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=3 WHERE id=$rid");
        unset($_SESSION['login_otp_pending']);
        echo json_encode(array('success'=>false,'message'=>'Too many invalid attempts. Please log in again.'));
        exit;
    }
    $want = trim((string)$row['otp_code']);
    $got = trim($otp);
    if(strlen($want) !== 6 || strlen($got) !== 6 || !hash_equals($want, $got)){
        $attempts = (int)$row['verify_attempts'] + 1;
        $max = (int)$row['max_verify_attempts'];
        $ip_esc = mysqli_real_escape_string($connection, auth_login_otp_client_ip());
        mysqli_query($connection, "UPDATE login_otp_challenges SET verify_attempts=$attempts, ip_last_verify='$ip_esc' WHERE id=$rid");
        if($attempts >= $max){
            mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=3 WHERE id=$rid");
            unset($_SESSION['login_otp_pending']);
            echo json_encode(array('success'=>false,'message'=>'Too many invalid attempts. Please log in again.'));
            exit;
        }
        $left = $max - $attempts;
        echo json_encode(array('success'=>false,'message'=>'Invalid OTP. ' . $left . ' attempt' . ($left === 1 ? '' : 's') . ' left.'));
        exit;
    }
    mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=1, verified_at=NOW(), ip_last_verify='".mysqli_real_escape_string($connection, auth_login_otp_client_ip())."' WHERE id=$rid");
    $email_row_esc = mysqli_real_escape_string($connection, $row['email']);
    mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=2 WHERE channel='admin' AND email='$email_row_esc' AND is_used=0 AND id<>$rid");
    $uid = (int)$row['user_pk'];
    $uq = mysqli_query($connection, "SELECT user_id,user_type,user_name,user_log_id FROM users WHERE user_id=$uid LIMIT 1");
    $u = ($uq && mysqli_num_rows($uq) > 0) ? mysqli_fetch_assoc($uq) : null;
    if(!$u){
        unset($_SESSION['login_otp_pending']);
        echo json_encode(array('success'=>false,'message'=>'Account not found. Please contact support.'));
        exit;
    }
    $_SESSION['user_id'] = (int)$u['user_id'];
    $_SESSION['user_type'] = (int)$u['user_type'];
    $_SESSION['user_name'] = $u['user_name'];
    $_SESSION['user_log_id'] = $u['user_log_id'];
    unset($_SESSION['login_otp_pending'], $_SESSION['login_otp_admin'], $_SESSION['login_otp_student'], $_SESSION['login_otp_bind']);
    echo json_encode(array('success'=>true,'user_type'=>(int)$_SESSION['user_type']));
    exit;
}

// Student portal: register (link enquiry by email)
if(@$_POST['formName']=='student_register'){
    $email = mysqli_real_escape_string($connection, trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $full_name = mysqli_real_escape_string($connection, trim($_POST['full_name'] ?? ''));
    $enquiry_id_input = mysqli_real_escape_string($connection, trim($_POST['enquiry_id'] ?? ''));
    if($email === '' || $password === '' || $full_name === ''){
        echo json_encode(array('success' => false, 'message' => 'Email, password and full name are required.'));
        exit;
    }
    if(strlen($password) < 6){
        echo json_encode(array('success' => false, 'message' => 'Password must be at least 6 characters.'));
        exit;
    }
    $check = mysqli_query($connection, "SELECT id FROM student_users WHERE LOWER(TRIM(email))=LOWER('$email') AND status=1 LIMIT 1");
    if($check && mysqli_num_rows($check) > 0){
        echo json_encode(array('success' => false, 'message' => 'This email is already registered. Please log in.'));
        exit;
    }
    if($enquiry_id_input !== ''){
        $enq = mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE st_enquiry_status=0 AND st_enquiry_id='$enquiry_id_input' AND st_email='$email' LIMIT 1");
    } else {
        $enq = mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE st_enquiry_status=0 AND st_email='$email' ORDER BY st_id DESC LIMIT 1");
    }
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $student_id = 0;
    $inactive = mysqli_query($connection, "SELECT id FROM student_users WHERE status=0 AND LOWER(TRIM(email))=LOWER('$email') ORDER BY id DESC LIMIT 1");
    if($inactive && ($ir = mysqli_fetch_assoc($inactive)) && !empty($ir['id'])){
        $student_id = (int)$ir['id'];
        $up_inactive = mysqli_query($connection, "UPDATE student_users SET password_hash='$password_hash', full_name='$full_name', status=1 WHERE id=$student_id LIMIT 1");
        if(!$up_inactive){
            echo json_encode(array('success' => false, 'message' => 'Registration failed. Please try again.'));
            exit;
        }
    } else {
        $ins = mysqli_query($connection, "INSERT INTO student_users (email, password_hash, full_name, status) VALUES ('$email','$password_hash','$full_name',1)");
        if(!$ins){
            echo json_encode(array('success' => false, 'message' => 'Registration failed. Please try again.'));
            exit;
        }
        $student_id = mysqli_insert_id($connection);
    }
    if(mysqli_num_rows($enq) > 0){
        $er = mysqli_fetch_array($enq);
        $st_id = (int)$er['st_id'];
        mysqli_query($connection, "UPDATE student_enquiry SET student_user_id=$student_id WHERE st_id=$st_id");
    }
    echo json_encode(array('success' => true, 'message' => 'Registered successfully. You can now log in.'));
    exit;
}

// Student login (student_login.php): students only from student_users. user_type=0 = staff (admin login).
if(@$_POST['formName']=='student_login_request_otp'){
    if(!auth_login_otp_ensure_table($connection)){
        echo json_encode(array('success'=>false,'message'=>'Login verification is temporarily unavailable.'));
        exit;
    }
    $email_raw = auth_login_otp_normalize_email($_POST['email'] ?? '');
    $email_esc = mysqli_real_escape_string($connection, $email_raw);
    $password = $_POST['password'] ?? '';
    if($email_raw === '' || $password === ''){
        echo json_encode(array('success' => false, 'message' => 'Email and password required.'));
        exit;
    }
    if(!auth_login_otp_rate_ok($connection, 'student', $email_esc, AUTH_LOGIN_OTP_MAX_PER_EMAIL_HOUR)){
        echo json_encode(array('success'=>false,'message'=>'Too many verification codes requested. Try again in an hour.'));
        exit;
    }
    $q = mysqli_query($connection, "SELECT id, full_name, password_hash, email FROM student_users WHERE LOWER(TRIM(email))='$email_esc' AND status=1 LIMIT 1");
    if($q && mysqli_num_rows($q) > 0){
        $row = mysqli_fetch_assoc($q);
        if(!password_verify($password, $row['password_hash'])){
            echo json_encode(array('success' => false, 'message' => 'Invalid email or password.'));
            exit;
        }
        $_SESSION['user_id']   = (int)$row['id'];
        $_SESSION['user_type'] = 'student';
        $_SESSION['user_name'] = $row['full_name'];
        unset($_SESSION['login_otp_pending'], $_SESSION['login_otp_admin'], $_SESSION['login_otp_student'], $_SESSION['login_otp_bind']);
        echo json_encode(array('success' => true, 'auto_login' => true, 'redirect' => 'student_docs.php'));
        exit;
    }
    $qi = mysqli_query($connection, "SELECT id FROM student_users WHERE LOWER(TRIM(email))='$email_esc' AND status=0 LIMIT 1");
    if($qi && mysqli_num_rows($qi) > 0){
        echo json_encode(array('success' => false, 'message' => 'This account is inactive. Please submit a new enquiry and sign up again with the same email.'));
        exit;
    }
    echo json_encode(array('success' => false, 'message' => 'Invalid email or password.'));
    exit;
}

if(@$_POST['formName']=='student_login_verify_otp'){
    if(!auth_login_otp_ensure_table($connection)){
        echo json_encode(array('success'=>false,'message'=>'Login verification is temporarily unavailable.'));
        exit;
    }
    $otp = trim($_POST['otp'] ?? '');
    if($otp === ''){
        echo json_encode(array('success'=>false,'message'=>'Please enter OTP.'));
        exit;
    }
    $pend = $_SESSION['login_otp_pending'] ?? null;
    $bind = is_array($pend) ? ($pend['bind'] ?? '') : '';
    $ch = is_array($pend) ? ($pend['channel'] ?? '') : '';
    if($bind === '' || $ch !== 'student'){
        echo json_encode(array('success'=>false,'message'=>'Verification session expired. Please log in again.'));
        exit;
    }
    $bind_esc = mysqli_real_escape_string($connection, $bind);
    $rq = mysqli_query($connection, "SELECT * FROM login_otp_challenges WHERE session_bind='$bind_esc' AND channel='student' AND is_used=0 LIMIT 1");
    $row = ($rq && mysqli_num_rows($rq) > 0) ? mysqli_fetch_assoc($rq) : null;
    if(!$row){
        unset($_SESSION['login_otp_pending']);
        echo json_encode(array('success'=>false,'message'=>'Invalid or expired verification. Please log in again.'));
        exit;
    }
    $rid = (int)$row['id'];
    if(strtotime($row['expires_at']) < time()){
        mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=4 WHERE id=$rid");
        unset($_SESSION['login_otp_pending']);
        echo json_encode(array('success'=>false,'message'=>'OTP expired. Please log in again.'));
        exit;
    }
    if((int)$row['verify_attempts'] >= (int)$row['max_verify_attempts']){
        mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=3 WHERE id=$rid");
        unset($_SESSION['login_otp_pending']);
        echo json_encode(array('success'=>false,'message'=>'Too many invalid attempts. Please log in again.'));
        exit;
    }
    $want = trim((string)$row['otp_code']);
    $got = trim($otp);
    if(strlen($want) !== 6 || strlen($got) !== 6 || !hash_equals($want, $got)){
        $attempts = (int)$row['verify_attempts'] + 1;
        $max = (int)$row['max_verify_attempts'];
        $ip_esc = mysqli_real_escape_string($connection, auth_login_otp_client_ip());
        mysqli_query($connection, "UPDATE login_otp_challenges SET verify_attempts=$attempts, ip_last_verify='$ip_esc' WHERE id=$rid");
        if($attempts >= $max){
            mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=3 WHERE id=$rid");
            unset($_SESSION['login_otp_pending']);
            echo json_encode(array('success'=>false,'message'=>'Too many invalid attempts. Please log in again.'));
            exit;
        }
        $left = $max - $attempts;
        echo json_encode(array('success'=>false,'message'=>'Invalid OTP. ' . $left . ' attempt' . ($left === 1 ? '' : 's') . ' left.'));
        exit;
    }
    mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=1, verified_at=NOW(), ip_last_verify='".mysqli_real_escape_string($connection, auth_login_otp_client_ip())."' WHERE id=$rid");
    $email_row_esc = mysqli_real_escape_string($connection, $row['email']);
    mysqli_query($connection, "UPDATE login_otp_challenges SET is_used=2 WHERE channel='student' AND email='$email_row_esc' AND is_used=0 AND id<>$rid");
    $sid = (int)$row['user_pk'];
    $sq = mysqli_query($connection, "SELECT id, full_name, email FROM student_users WHERE id=$sid AND status=1 LIMIT 1");
    $srow = ($sq && mysqli_num_rows($sq) > 0) ? mysqli_fetch_assoc($sq) : null;
    if(!$srow){
        unset($_SESSION['login_otp_pending']);
        echo json_encode(array('success'=>false,'message'=>'Account not found. Please contact support.'));
        exit;
    }
    $_SESSION['user_id'] = (int)$srow['id'];
    $_SESSION['user_type'] = 'student';
    $_SESSION['user_name'] = $srow['full_name'];
    $_SESSION['student_email'] = $srow['email'];
    $em = mysqli_real_escape_string($connection, $srow['email']);
    $eq = mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE st_email='$em' AND st_enquiry_status!=1 ORDER BY st_id DESC LIMIT 1");
    $_SESSION['student_eq_id'] = ($eq && mysqli_num_rows($eq) > 0) ? (int)mysqli_fetch_assoc($eq)['st_id'] : 0;
    unset($_SESSION['login_otp_pending'], $_SESSION['login_otp_admin'], $_SESSION['login_otp_student'], $_SESSION['login_otp_bind']);
    echo json_encode(array('success'=>true,'redirect'=>'student_enquiry_form.php'));
    exit;
}

if(@$_POST['formName'] == 'get_user'){
    $id = $_POST['user_id'];
    $result = mysqli_query($connection, "SELECT * FROM users WHERE user_id='$id'");
    echo json_encode(mysqli_fetch_assoc($result));
}

// Update logged-in user's own profile (Settings page)
if(@$_POST['formName'] == 'update_profile'){
    if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
        echo 0;
        exit;
    }
    $id = (int)$_SESSION['user_id'];
    $sessionType = $_SESSION['user_type'] ?? null;
    $name = mysqli_real_escape_string($connection, $_POST['user_name']);
    $email = mysqli_real_escape_string($connection, $_POST['user_email']);
    $phone = isset($_POST['user_phone']) ? mysqli_real_escape_string($connection, $_POST['user_phone']) : '';
    $address = isset($_POST['user_address']) ? mysqli_real_escape_string($connection, $_POST['user_address']) : '';
    $modified = date('Y-m-d H:i:s');

    if($sessionType === 'student'){
        // Self-registered student: update student_users
        $update = mysqli_query($connection, "UPDATE student_users SET full_name='$name', email='$email', phone='$phone' WHERE id=$id");
        if($update){
            $_SESSION['user_name'] = $name;
            $_SESSION['student_email'] = $email;
            echo 1;
        }else{
            echo 0;
        }
    }else{
        // Admin / staff / admin-created student: update users table
        $hasPhoneColumn = mysqli_query($connection, "SHOW COLUMNS FROM users LIKE 'user_phone'");
        if($hasPhoneColumn && mysqli_num_rows($hasPhoneColumn)){
            $update = mysqli_query($connection, "UPDATE users SET user_name='$name', user_email='$email', user_phone='$phone', user_address='$address', modified_date='$modified' WHERE user_id=$id");
        }else{
            $update = mysqli_query($connection, "UPDATE users SET user_name='$name', user_email='$email', modified_date='$modified' WHERE user_id=$id");
        }

        if($update){
            $_SESSION['user_name'] = $name;
            echo 1;
        }else{
            echo 0;
        }
    }
}

// Change password for logged-in user (Settings page)
if(@$_POST['formName'] == 'change_password'){
    if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
        echo 0;
        exit;
    }
    $id = (int)$_SESSION['user_id'];
    $sessionType = $_SESSION['user_type'] ?? null;
    $current = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    if($new === ''){
        echo 0;
        exit;
    }

    if($sessionType === 'student'){
        // Self-registered student: password stored as hash in student_users
        $res = mysqli_query($connection, "SELECT password_hash FROM student_users WHERE id=$id AND status=1 LIMIT 1");
        $row = $res ? mysqli_fetch_assoc($res) : null;
        if(!$row || !password_verify($current, $row['password_hash'])){
            echo 'INVALID';
            exit;
        }
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $ok = mysqli_query($connection, "UPDATE student_users SET password_hash='".mysqli_real_escape_string($connection,$newHash)."' WHERE id=$id AND status=1 LIMIT 1");
        echo $ok ? '1' : '0';
    }else{
        // Users table: plain password as used elsewhere
        $res = mysqli_query($connection, "SELECT user_password FROM users WHERE user_id=$id LIMIT 1");
        $row = $res ? mysqli_fetch_assoc($res) : null;
        if(!$row || $row['user_password'] !== $current){
            echo 'INVALID';
            exit;
        }
        $modified = date('Y-m-d H:i:s');
        $newEsc = mysqli_real_escape_string($connection, $new);
        $ok = mysqli_query($connection, "UPDATE users SET user_password='$newEsc', modified_date='$modified' WHERE user_id=$id");
        echo $ok ? '1' : '0';
    }
}

// EDIT USER (admins only)
if(@$_POST['formName'] == 'edit_user'){
    if((int)@$_SESSION['user_type'] !== 1){
        echo 0;
        exit;
    }
    $id = $_POST['user_id'];
    $name = $_POST['user_name'];
    $email = $_POST['user_email'];
    $password = $_POST['user_password'];
    $type = $_POST['user_type'];
    $status = $_POST['user_status'];
    $modified = date('Y-m-d H:i:s');

    if($password != ''){
        $update = mysqli_query($connection, "UPDATE users SET user_name='$name', user_email='$email', user_password='$password', user_type='$type', user_status='$status', modified_date='$modified' WHERE user_id='$id'");
    } else {
        $update = mysqli_query($connection, "UPDATE users SET user_name='$name', user_email='$email', user_type='$type', user_status='$status', modified_date='$modified' WHERE user_id='$id'");
    }

    if($update){
        // Reload staff list HTML (Admin + Staff only)
        $users = mysqli_query($connection, "SELECT * FROM users WHERE user_type IN (1,2) ORDER BY user_id DESC");
        include('../includes/user_list_partial.php');
    }else{
        echo 1;
    }
}

if(@$_POST['formName'] == 'create_user'){
    if((int)@$_SESSION['user_type'] !== 1){
        echo 0;
        exit;
    }
    $name = $_POST['user_name'];
    $email = $_POST['user_email'];
    $password = $_POST['user_password'];
    $type = $_POST['user_type'];
    $status = $_POST['user_status'];

    $user_log_id = strtoupper(substr(md5(uniqid()), 0, 8));
    $created = date('Y-m-d H:i:s');

    $insert = mysqli_query($connection, "INSERT INTO users (user_log_id, user_name, user_email, user_password, user_type, user_status, created_date)
                                         VALUES ('$user_log_id', '$name', '$email', '$password', '$type', '$status', '$created')");
    if($insert){
        // Reload staff list HTML (Admin + Staff only)
        $users = mysqli_query($connection, "SELECT * FROM users WHERE user_type IN (1,2) ORDER BY user_id DESC");
        include('../includes/user_list_partial.php');
    }else{
        echo 1;
    }
}

if(@$_REQUEST['name']=='singleinvoice'){
    $studentId=$_REQUEST['id'];
    $invoices['data']=[];
    $query=mysqli_query($connection,"SELECT * FROM `invoices` WHERE `st_unique_id`='$studentId'");
    while($queryRes=mysqli_fetch_array($query)){

    array_push($invoices['data'],array('autoId'=>$queryRes['inv_auto_id'],'course'=>$queryRes['inv_course'], 'fee'=>$queryRes['inv_fee'],'paid'=>$queryRes['inv_paid'],'date'=>$queryRes['inv_payment_date']));

    }
    header("Content-Type: application/json");
    echo json_encode($invoices);
}


if(@$_REQUEST['name']=='studentEnquiry'){
    $crm_enquiry_delete_allowed = isset($_SESSION['user_id']) && (int)@$_SESSION['user_type'] === 1;
    $enquiries['data']=[];
    $query=mysqli_query($connection,"SELECT * from student_enquiry where st_enquiry_status!=1");
    while($queryRes=mysqli_fetch_array($query)){

    $coursesNames=json_decode($queryRes['st_course']);
    $coursesName='<div class="td_scroll_height">';
    foreach($coursesNames as $value){
        $courses=mysqli_fetch_array(mysqli_query($connection,"SELECT * from courses where course_status!=1 AND course_id=".$value));
        $coursesName.= $courses['course_sname'].'-'.$courses['course_name']." , <br>"; 
    }
    
    $st_course_type=['-','Need exemption','Regular','Regular - Group','Short courses','Short course - Group'];
    $courseTypeId=$queryRes['st_course_type'];

    $coursesNamePos = strrpos($coursesName, ',');
    $coursesName = substr($coursesName, 0, $coursesNamePos);
    $coursesName.='</div>';

    $visited=$queryRes['st_visited']==1 ? 'Visited' : ( $queryRes['st_visited']==2 ? 'Not Visited' : ' - ' ) ;
    
    $visastatus=$queryRes['st_visa_condition']==1 ? 'Approved' : 'Not Approved' ;

    $refered_names = $queryRes['st_refer_name'];

    $startPlanDate=date('d M Y',strtotime($queryRes['st_startplan_date']));

    $staff_comments=$queryRes['st_comments'];
    $preference=$queryRes['st_pref_comments'];

    $st_remarks=['--select--','Seems to be interested to do course and need to contact asap','contacted and followed','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose'];    

    if($queryRes['st_remarks']!=''){
        $remarksNotes='<div class="td_scroll_height">';

    foreach(json_decode($queryRes['st_remarks']) as $remark  ){                   
        
        $remarksNotes.=$st_remarks[$remark].' , <br>';

    }
    $remarksNotes.='</div>';
    }else{
        $remarksNotes=' - ';
        
    }

    $street=$queryRes['st_street_details'];
    $suburb=$queryRes['st_suburb'];
    $post_code=$queryRes['st_post_code'];
    $appointment=$queryRes['st_appoint_book']==1 ? 'Booked' : ( $queryRes['st_appoint_book']==2 ? 'Not Booked' : ' - ' );
    
    $querys2=mysqli_query($connection,"select visa_status_name from `visa_statuses` WHERE visa_id=".$queryRes['st_visa_status']);
    if(mysqli_num_rows($querys2)!=0){
    $visaCondition=mysqli_fetch_array($querys2);

    if(@$visaCondition['visa_status_name'] && $visaCondition['visa_status_name']!=''){
        $visacCond=$visaCondition['visa_status_name'];
    }else{
        $visacCond=' - ';
    }
    }else{
        $visacCond=' - ';
    }
    

        $view='<a class="btn btn-outline-primary btn-sm edit_enq" style="margin-right:10px;" href="student_enquiry.php?eq='.base64_encode($queryRes['st_id']).'">Edit</a>';
        if(!empty($crm_enquiry_delete_allowed)){
            $view.='<button onclick="delete_enq(\'student_enquiry\',\'st\','.$queryRes['st_id'].')" type="button" class="btn btn-outline-danger btn-sm">Delete</button>';
        }

        array_push($enquiries['data'],array('st_enquiry_id'=>$queryRes['st_enquiry_id'],'std_name'=>$queryRes['st_name'], 'std_phno'=>$queryRes['st_phno'],'std_email'=>$queryRes['st_email'],'street'=>$street,'suburb'=>$suburb,'post_code'=>$post_code,'std_course'=>$coursesName,'startplan_date'=>$startPlanDate,'referedby'=>$refered_names,'visited'=>$visited,'st_coursetype'=>$st_course_type[$courseTypeId],'std_fee'=>$queryRes['st_fee'],'appointment'=>$appointment,'Visa_condition'=>$visacCond,'std_visa_status'=>$visastatus,'staffComments'=>$staff_comments,'preferences'=>$preference,'remarksNotes'=>$remarksNotes,'action'=>$view));
        
    }
    header("Content-Type: application/json");
    echo json_encode($enquiries);
}

if(@$_REQUEST['name']=='followup_calls'){

    $followups['data']=[];

    $checkQry=mysqli_query($connection,"SELECT * FROM `followup_calls` WHERE `flw_enquiry_status`=0");
    if(mysqli_num_rows($checkQry)!=0){

        while($checkQryRes=mysqli_fetch_array($checkQry)){

            $st_remarks=['--select--','Seems to be interested to do course and need to contact asap','contacted and followed','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose'];    

            if($checkQryRes['flw_remarks']!=''){
                $remarksNotes='<div class="td_scroll_height">';
        
            foreach(json_decode($checkQryRes['flw_remarks']) as $remark  ){                   
                
                $remarksNotes.=$st_remarks[$remark].' , <br>';
        
            }
            $remarksNotes.='</div>';
            }else{
                $remarksNotes=' - ';
                
            }

            
        $view='<button type="button" data="'.$checkQryRes['flw_id'].'" class="btn btn-outline-primary btn-sm edit_enrol" style="margin-right:10px;"><a href="followup_call.php?flw_id='.base64_encode($checkQryRes['flw_id']).'">Edit</a></button><button onclick="delete_enq(\'followup_calls\',\'flw\','.$checkQryRes['flw_id'].')" type="button" class="btn btn-outline-danger btn-sm">Delete</button>';


            array_push($followups['data'],array('enquiry_id'=>$checkQryRes['enquiry_id'],'name'=>$checkQryRes['flw_name'],'phone'=>$checkQryRes['flw_phone'],'contacted_person'=>$checkQryRes['flw_contacted_person'],'contacted_time'=>date('d M y H:i',strtotime($checkQryRes['flw_contacted_time'])),'date'=>$checkQryRes['flw_date'],'mode_contact'=>$checkQryRes['flw_mode_contact'],'action'=>$view));

        }
                

    }

    header("Content-Type: application/json");
    echo json_encode($followups);



}

if(@$_REQUEST['formName']=='appointments_table'){


    $team_mems=$_POST['filter'];
    $where='';
    
    if($team_mems==''){
        $where.=" AND DATE(slot_bk_datetime) = CURDATE() AND TIME(slot_bk_datetime) <= CURTIME()";
    }else{
        $where.="AND `slot_book_by` LIKE '%$team_mems%' ";
    }

    $tbody='';

    $checkQry=mysqli_query($connection,"SELECT * FROM `slot_book` WHERE `slot_bk_id`!='' $where");

    // echo "SELECT * FROM `slot_book` WHERE `slot_bk_id`!='' $where";
    
    if(mysqli_num_rows($checkQry)!=0){

        while($checkQryRes=mysqli_fetch_array($checkQry)){

            $link=$checkQryRes['slot_book_email_link']==1 ? 'Yes' : 'No';
        
            $tbody.='<tr>';
            $tbody.='<td>'.$checkQryRes['slot_bk_id'].'</td>';
            $tbody.='<td>'.$checkQryRes['slot_bk_purpose'].'</td>';
            $tbody.='<td>'.$checkQryRes['slot_book_by'].'</td>';
            $tbody.='<td>'.$link.'</td>';
            $tbody.='<td>'.$checkQryRes['slot_bk_datetime'].'</td>';
            $tbody.='</tr>';

        }

    }

    echo $tbody;

}


if(@$_REQUEST['name']=='counselings'){

    $counselings['data']=[];

    $checkQry=mysqli_query($connection,"SELECT *, TIMESTAMPDIFF(DAY, counsil_timing, counsil_end_time) AS days, TIMESTAMPDIFF(HOUR, counsil_timing, counsil_end_time) % 24 AS hours, TIMESTAMPDIFF(MINUTE, counsil_timing, counsil_end_time) % 60 AS mins FROM `counseling_details` WHERE `counsil_enquiry_status` = 0;");
    if(mysqli_num_rows($checkQry)!=0){

        while($checkQryRes=mysqli_fetch_array($checkQry)){

            $st_remarks=['--select--','Seems to be interested to do course and need to contact asap','contacted and followed','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose'];    

            if($checkQryRes['counsil_remarks']!=''){
                $remarksNotes='<div class="td_scroll_height">';
        
            foreach(json_decode($checkQryRes['counsil_remarks']) as $remark  ){                   
                
                $remarksNotes.=$st_remarks[$remark].' , <br>';
        
            }
            $remarksNotes.='</div>';
            }else{
                $remarksNotes=' - ';
                
            }

            if($checkQryRes['counsil_type']==1){
                $type="Face to Face";
            }else{
                $type="Video";
            }
            
            if($checkQryRes['counsil_work_status']==1){
                $work_status="Yes";
            }else{
                $work_status="No";
            }

            $querys2=mysqli_query($connection,"select visa_status_name from `visa_statuses` WHERE visa_id=".$checkQryRes['counsil_visa_condition']);
            if(mysqli_num_rows($querys2)!=0){
            $visaCondition=mysqli_fetch_array($querys2);
        
            if(@$visaCondition['visa_status_name'] && $visaCondition['visa_status_name']!=''){
                $visacCond=$visaCondition['visa_status_name'];
            }else{
                $visacCond=' - ';
            }
            }else{
                $visacCond=' - ';
            }



            $timeSpent=$checkQryRes['days']=='' && $checkQryRes['hours']==0 ? 'Not Available' : $checkQryRes['days'].' Days '.$checkQryRes['hours'].' Hours '.$checkQryRes['mins'].' Minutes';

            
        $view='<button type="button" data="'.$checkQryRes['counsil_id'].'" class="btn btn-outline-primary btn-sm edit_enrol" style="margin-right:10px;"><a href="counselling_form.php?eq='.base64_encode($checkQryRes['counsil_id']).'">Edit</a></button><button onclick="delete_enq(\'counseling_details\',\'counsil\','.$checkQryRes['counsil_id'].')" type="button" class="btn btn-outline-danger btn-sm">Delete</button>';


            array_push($counselings['data'],array('member_name'=>$checkQryRes['counsil_mem_name'],'counsil_type'=>$type,'work_status'=>$work_status,'visa'=>$visacCond,'education'=>$checkQryRes['counsil_education'],'counsil_timing'=>date('d M y H:i',strtotime($checkQryRes['counsil_timing'])),'time_spent'=>$timeSpent,'action'=>$view));

        }
                

    }

    header("Content-Type: application/json");
    echo json_encode($counselings);



}



if(@$_REQUEST['name']=='student_enrol'){
    $enrol['data']=[];
    $query=mysqli_query($connection,"SELECT * from student_enrolment where st_enrol_status!=1");
    while($queryRes=mysqli_fetch_array($query)){

        if($queryRes['st_qualifications']==1){
            $qualifications='Masters Degree';
        }else if($queryRes['st_qualifications']==2){
            $qualifications='Bachelors Degree';
        }else{
            $qualifications='MCA';
        }

        if($queryRes['st_venue']==1){
            $venue='Adeladie';
        }else if($queryRes['st_venue']==2){
            $venue='New Jersey';
        }else{
            $venue='Australia';
        }

        if($queryRes['st_source']==1){
            $source='Friends';
        }else if($queryRes['st_source']==2){
            $source='Google';
        }else{
            $source='Website';
        }


        $view='<button type="button" data="'.$queryRes['st_enrol_id'].'" class="btn btn-outline-primary btn-sm edit_enrol" style="margin-right:10px;"><a href="enrolment.php?enrol='.base64_encode($queryRes['st_enrol_id']).'">Edit</a></button><button onclick="delete_enrol('.$queryRes['st_enrol_id'].')" type="button" class="btn btn-outline-danger btn-sm">Delete</button>';

        array_push($enrol['data'],array('st_enrol_name'=>$queryRes['st_name'],'st_enrol_id'=>$queryRes['st_unique_id'],'st_enq_id'=>$queryRes['st_enquiry_id'], 'st_enrol_givenname'=>$queryRes['st_given_name'],'st_enrol_middlename'=>$queryRes['st_middle_name'],'st_enrol_qual'=>$qualifications,'st_enrol_venue'=>$venue,'st_enrol_source'=>$source,'action'=>$view));
        
    }
    header("Content-Type: application/json");
    echo json_encode($enrol);
}


if(@$_REQUEST['name']=='all_students'){
    $enrol['data']=[];
    $query=mysqli_query($connection,"SELECT st_unique_id,st_name,st_mobile,st_email,st_enrol_course,created_date from student_enrolment where st_enrol_status!=1");
    while($queryRes=mysqli_fetch_array($query)){

        $enrolDate=date('d-M-Y',strtotime($queryRes['created_date']));

        // if($queryRes['st_enrol_course']==1){
        //     $course='Basic';
        // }else if($queryRes['st_enrol_course']==2){
        //     $course='Intermediate';
        // }else{
        //     $course='Expert';
        // }

        $courses=mysqli_fetch_array(mysqli_query($connection,"SELECT * from courses where course_status!=1 AND course_id=".$queryRes['st_enrol_course']));

        $uniq_id='<a href="studentData.php?check='.base64_encode($queryRes['st_unique_id']).'" style="color:var(--color)">'.$queryRes['st_unique_id'].'</a>';

        $view='<button type="button" data="'.$queryRes['st_unique_id'].'" class="btn btn-outline-primary btn-sm edit_enrol" style="margin-right:10px;"><a href="studentData.php?check='.base64_encode($queryRes['st_unique_id']).'">View</a></button>';

        array_push($enrol['data'],array('st_unique_id'=>$uniq_id,'st_enrol_name'=>$queryRes['st_name'], 'std_phno'=>$queryRes['st_mobile'],'std_email'=>$queryRes['st_email'],'course'=>$courses['course_sname'].'-'.$courses['course_name'],'std_date'=>$enrolDate,'action'=>$view));
        
    }
    header("Content-Type: application/json");
    echo json_encode($enrol);
}

// Handle invoices list request
if(@$_REQUEST['name']=='invoices_list'){
    ob_clean();
    header('Content-Type: application/json');

    $invoices = array('data' => []);

    $query = mysqli_query($connection,
        "SELECT ei.*,
                COALESCE(SUM(eis.amount), 0)     AS total_amount,
                COALESCE(SUM(eis.gst_amount), 0) AS total_gst
         FROM enrolment_invoices ei
         LEFT JOIN enrolment_invoice_installments eis ON eis.invoice_id = ei.id
         GROUP BY ei.id
         ORDER BY ei.created_at DESC"
    );
    if ($query && mysqli_num_rows($query) > 0) {
        while ($row = mysqli_fetch_assoc($query)) {
            $invoices['data'][] = array(
                'id'             => $row['id'],
                'invoice_number' => $row['invoice_number'],
                'student_name'   => $row['student_name'],
                'student_id'     => $row['student_id'],
                'email_address'  => $row['email_address'],
                'total_due'      => $row['total_amount'],
                'total_gst'      => $row['total_gst'],
                'status'         => $row['status'],
                'created_at'     => date('d M Y', strtotime($row['created_at']))
            );
        }
    }

    echo json_encode($invoices);
    exit;
}

if(@$_POST['formName']=='lookupLoad'){
    if($_POST['selected']=='1'){
        $query=mysqli_query($connection,"SELECT st_mobile as datas from student_enrolment WHERE st_enrol_status!=1");
    }else{
        $query=mysqli_query($connection,"SELECT st_email as datas from student_enrolment WHERE st_enrol_status!=1");
    }
    $body= "<option value='0'>--select--</option>";
    while($queryRes=mysqli_fetch_array($query)){
        $body.="<option value=".$queryRes['datas'].">".$queryRes['datas']."</option>";
    }
    echo $body;
}

if(@$_POST['formName']=='lookupdata'){
    if($_POST['selected']=='1'){
        $query=mysqli_fetch_array(mysqli_query($connection,"SELECT * from student_enrolment WHERE st_enrol_status!=1 AND st_mobile='".$_POST['values']."'"));
    }else{
        $query=mysqli_fetch_array(mysqli_query($connection,"SELECT * from student_enrolment WHERE st_enrol_status!=1 AND st_email='".$_POST['values']."'"));
    }
    
    echo json_encode($query);
}

if(@$_POST['formName']=='lookupLoad2'){
    if($_POST['selected']=='1'){
        $query=mysqli_query($connection,"SELECT st_phno as datas from student_enquiry WHERE st_enquiry_status!=1");
    }else{
        $query=mysqli_query($connection,"SELECT st_email as datas from student_enquiry WHERE st_enquiry_status!=1");
    }
    $body= "<option value='0'>--select--</option>";
    while($queryRes=mysqli_fetch_array($query)){
        $body.="<option value=".$queryRes['datas'].">".$queryRes['datas']."</option>";
    }
    echo $body;
}

if(@$_POST['formName']=='lookupdata2'){
    if($_POST['selected']=='1'){
        $query=mysqli_fetch_array(mysqli_query($connection,"SELECT * from student_enquiry WHERE st_enquiry_status!=1 AND st_phno='".$_POST['values']."'"));
    }else{
        $query=mysqli_fetch_array(mysqli_query($connection,"SELECT * from student_enquiry WHERE st_enquiry_status!=1 AND st_email='".$_POST['values']."'"));
    }
    
    echo json_encode($query);
}


if(@$_REQUEST['name']=='all_attendance'){
    $attendance['data']=[];

    $query=mysqli_fetch_all(mysqli_query($connection,"select DISTINCT(st_unique_id) from student_attendance"),MYSQLI_ASSOC); 
    $queryCrs=mysqli_fetch_all(mysqli_query($connection,"select DISTINCT(st_course_unit) from student_attendance"),MYSQLI_ASSOC); 

    $query=mysqli_query($connection,"select * from `student_attendance`");
    while($queryRes=mysqli_fetch_array($query)){

        $id=$queryRes['st_unique_id'];
        $selectName=mysqli_fetch_array(mysqli_query($connection,"SELECT * FROM student_enrolment where st_unique_id='$id'"));
        if($selectName!=''){
        // echo "SELECT * FROM student_enrolment where st_unique_id='$id'";

        array_push($attendance['data'],array('student_id'=>$queryRes['st_unique_id'],'student_name'=>$selectName['st_given_name'].' '.$selectName['st_middle_name'],'course'=>$queryRes['st_course_unit'],'mobile'=>$selectName['st_mobile'],'email'=>$selectName['st_email'],'attenddate'=>$queryRes['st_unit_date']));
        }
        
    }
    // header("Content-Type: application/json");
    // echo json_encode($attendance);
}

if(@$_REQUEST['name']=='single_attendance'){
    $attendance['data']=[];
    $id=$_REQUEST['enrolid'];
    $query=mysqli_query($connection,"select st1.st_unique_id as student_id,st1.st_course_unit as course ,st1.st_unit_date as at_date from student_attendance st1 inner join student_enrolment st2 where st1.st_unique_id=$id");
    while($queryRes=mysqli_fetch_array($query)){

        array_push($attendance['data'],array('student_id'=>$queryRes['student_id'],'student_name'=>$queryRes['name'].' '.$queryRes['mname'],'course'=>$queryRes['course'],'mobile'=>$queryRes['mobile'],'email'=>$queryRes['email'],'attenddate'=>$queryRes['at_date']));
        
    }
    header("Content-Type: application/json");
    echo json_encode($attendance);
}

if(@$_POST['formName']=='studentDocs'){
 
    $arrayUploaded=array();
    $enrollId=$_POST['enrollId'];
    $count=count($_FILES['fileUpload']["name"]);
    $excelArr=array('xlsx','xlx','csv');
    $pdfArr=array('pdf');
    $docArr=array('doc','docx');
    $targetDir = "uploads/";
    $dbImgArray=array();

    for($i=0;$i<$count;$i++){
        $fileName=explode('.',$_FILES["fileUpload"]["name"][$i])[0];        
        $fileType = pathinfo('uploads/'.basename($_FILES["fileUpload"]["name"][$i]), PATHINFO_EXTENSION);
        $currentSeconds = round(microtime(true) * 1000);
        $targetFile = $targetDir . $fileName.'_'.$currentSeconds.'.'.$fileType;
        if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"][$i], $targetFile)) {

            if($_POST['docType']=='dob'){
                array_push($arrayUploaded,'includes/'.$targetFile."||dob");  
            }else{
                array_push($arrayUploaded,'includes/'.$targetFile."||address");  
            }

            // if (in_array($fileType, $excelArr)) {
            //     array_push($arrayUploaded,'includes/uploads/'.$targetFile."||xlsx.png");                
            // }elseif(in_array($fileType, $pdfArr)){
            //     array_push($arrayUploaded,'includes/uploads/'.$targetFile."||pdf.png");
            // }elseif(in_array($fileType, $docArr)){
            //     array_push($arrayUploaded,'includes/uploads/'.$targetFile."||docx.png");                
            // }
        }
    }

    $selectQry=mysqli_query($connection,"SELECT st_unique_id,st_doc_names FROM `student_docs` WHERE `st_unique_id`='$enrollId'");
    $rows=mysqli_num_rows($selectQry);
    if($rows==0){
        $qry=mysqli_query($connection,"INSERT INTO `student_docs` (`st_unique_id`,`st_doc_names`) VALUES('$enrollId','".json_encode($arrayUploaded)."')");
        $inserted=mysqli_insert_id($connection);
        if($inserted!=''){
            // echo json_encode($arrayUploaded);
            echo 1;
        }else{
            echo 0; 
        }
    }else{
        $selectQryRes=mysqli_fetch_array($selectQry);
        $fetchArray=array_merge(json_decode($selectQryRes['st_doc_names']),$arrayUploaded);

        // if(($key = array_search(4, $array1)) !== false) {
        //     unset($array1[$key]);
        // }

        $qry=mysqli_query($connection,"UPDATE `student_docs` SET `st_modified_date`='".date('Y-m-d')."',`st_doc_names`= '".json_encode($fetchArray)."' WHERE `st_unique_id`='$enrollId'");
        if($qry){
            echo 1;
            // echo json_encode($fetchArray);
        }else{
            echo 0; 
        }
    }
}

if(@$_POST['formName']=='deleteProof'){
    $enrolid=$_POST['enrolID'];
    $delType=$_POST['delType'];
    $arrayUploaded=array();
    if($delType=='dob_del'){
        $type="dob";
    }else{
        $type="address";
    }
    
    // echo "SELECT st_unique_id,st_doc_names FROM `student_docs` WHERE `st_unique_id`='$enrolid'"
    $selectQry=mysqli_query($connection,"SELECT st_unique_id,st_doc_names FROM `student_docs` WHERE `st_unique_id`='$enrolid'");
    $selectQryRes=mysqli_fetch_array($selectQry);
    $fetchArray=json_decode($selectQryRes['st_doc_names']); 

      
        // if(($key = array_search($type, $fetchArray)) !== false) {

        //     unset($fetchArray[$key]);
        // }
        $keyVal=0; 
        foreach ($fetchArray as $value) {
            if (strpos($value, $type) !== false) {
                unset($fetchArray[$keyVal]);      
                $arrayUploaded=array_values($fetchArray);
            }
            $keyVal++;
        }
        

        $qry=mysqli_query($connection,"UPDATE `student_docs` SET `st_modified_date`='".date('Y-m-d')."',`st_doc_names`= '".json_encode($arrayUploaded)."' WHERE `st_unique_id`='$enrolid'");
        if($qry){
            echo 1;
            // echo json_encode($fetchArray);
        }else{
            echo 0; 
        }


}

?>

<?php 

if(@$_POST['formName']=='uploadExcel'){
    
require 'vendor/autoload.php'; 


    $targetDir = "uploads/attendance/"; // Adjust the directory as needed
    $targetFile = $targetDir . basename($_FILES["fileUpload"]["name"]);
    $fileType = pathinfo($targetFile, PATHINFO_EXTENSION);
    $uploadOk = 1;

    // Check if the file is an Excel file
    if ($fileType != "xlsx" && $fileType != "xls") {
        echo "Only Excel files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $targetFile)) {
            // echo "The file " . basename($_FILES["fileUpload"]["name"]) . " has been uploaded.";

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($targetFile);
            $worksheet = $spreadsheet->getActiveSheet();

            $headers = [];
            $cellIterator = $worksheet->getRowIterator()->current()->getCellIterator();
            foreach ($cellIterator as $cell) {
                $headers[] = $cell->getValue();
            }
            $tbody='';
            foreach ($worksheet->getRowIterator(2) as $row) { 
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);

                $data = [];
                foreach ($cellIterator as $cell) {
                    $data[] = $cell->getValue();
                }

                $unixTimestamp = ($data[2] - 25569) * 86400; 
                if($data[0]!='' && $data[1]!='' ){
                    $tbody.='<tr>';
                    $tbody.='<td>'.$data[0].'</td>';
                    $tbody.='<td>'.$data[1].'</td>';
                    $tbody.='<td>'.date('m-d-Y',$unixTimestamp).'</td>';

                    // $sql = "INSERT INTO student_attendance (" . implode(", ", $headers) . ") VALUES ('" . implode("', '", $data) . "')";
                    $sql = "INSERT INTO student_attendance (`st_unique_id`,`st_course_unit`,`st_unit_date`) VALUES ('".$data[0]."','".$data[1]."','".date('Y-m-d',$unixTimestamp)."')";
                    if ($connection->query($sql) !== TRUE) {
                        echo "Error: " . $connection->error;
                    }
                    $tbody.='</tr>';
                }
            }
            echo $tbody;
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

$connection->close();

}

if (@$_POST['formName'] == 'uploadEnrolmentExcel') {

    $targetDir = "uploads/attendance/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $targetFile = $targetDir . basename($_FILES["fileUpload"]["name"]);
    $fileType = pathinfo($targetFile, PATHINFO_EXTENSION);

    if ($fileType != "csv") {
        exit("Only CSV files (.csv) are allowed.");
    }

    if (!move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $targetFile)) {
        exit("Sorry, there was an error uploading your file.");
    }

    if (($handle = fopen($targetFile, "r")) === false) {
        exit("Unable to open uploaded CSV file.");
    }

    $tbody = '';
    $rowCount = 0;

    // Read header
    $headers = fgetcsv($handle, 10000, ",");

    // 🔹 Define mapping arrays for dropdowns
    $stateMap = [
        "NSW - New South Wales" => 1,
        "VIC - Victoria" => 2,
        "ACT - Australian Capital Territory" => 3,
        "NT - Northern Territoy" => 4,
        "WA - Western Australia" => 5,
        "QLD - Queensland" => 6,
        "SA - South Australia" => 7,
        "TAS - Tasmania" => 8,
    ];

    $empStatusMap = [
        "Full time employee (More than 35 hours)" => 1,
        "Part time employee (Less than 35 hours)" => 2,
        "Self employed - Not employing others Employer" => 3,
        "Employed - Unpaid family worker in a family business" => 4,
        "Unemployed - Seeking full time work" => 5,
        "Unemployed - Seeking part time work" => 6,
        "Not employed - Not seeking employment" => 7,
    ];

    $selfStatusMap = [
        "A sole supporting parent" => 1,
        "A person with a history of short term employment experience" => 2,
        "A person returning to the workforce after an absence of 12 month or more" => 3,
        "A person who requires assistance with reading and writing" => 4,
    ];

    $citizenMap = [
        "Australian Citizen" => 1,
        "New Zealand Citizen" => 2,
        "Australian Permanent Resident" => 3,
        "Humanitarian Visa" => 4,
        "Temporary Resident" => 5,
    ];

    $highestSchoolMap = [
        "Completed Year 12 Completed Year 11" => 1,
        "Completed Year 10 Completed Year 9" => 2,
        "Completed Year 8 Never Attended School" => 3,
    ];

    $studyReasonMap = [
        "To get a job" => 1,
        "To develop my existing business" => 2,
        "To start my own business" => 3,
        "To try for a dierent career" => 4,
        "To get a better job / promotion" => 5,
        "It was a requirement of my job" => 6,
        "I wanted extra skills for my job" => 7,
        "To get into another course or study" => 8,
        "For personal interest or self-development" => 9,
        "Other Reason" => 10,
    ];

    while (($data = fgetcsv($handle, 10000, ",")) !== false) {
        if (empty($data[0]) || empty($data[1])) continue;

        // Basic fields
        $enquiry_id         = $data[0];
        $rto_name           = $data[1];
        $courses_raw        = $data[2];
        $branch_name        = $data[3];
        $given_name         = $data[4];
        $surname            = $data[5];
        $dob                = date('Y-m-d', strtotime($data[6]));
        $birth_country      = $data[7];
        $street_details     = $data[8];
        $sub_urb            = $data[9];
        $post_code          = $data[10];
        $tel_num            = $data[11];
        $mobile_num         = $data[12];
        $emailAddress       = $data[13];
        $stu_state_raw      = trim($data[14]);
        $em_full_name       = $data[15];
        $em_relation        = $data[16];
        $em_mobile_num      = $data[17];
        $em_agree_check     = $data[18];
        $usi_id             = $data[19];
        $emp_status_raw     = trim($data[20]);
        $self_status_raw    = trim($data[21]);
        $st_citizen_raw     = trim($data[22]);
        $highest_school_raw = trim($data[23]);
        $study_reason_raw   = trim($data[24]); // Multi-select
        $study_reason_other = $data[25];
        $gender_check       = $data[26];
        $cred_tansf         = $data[27];
        $sec_school         = $data[28];
        $born_country       = $data[29];
        $origin             = $data[30];
        $lan_spoken         = $data[31];
        $disability         = $data[32];
        $qual_1             = $data[33];
        $qual_2             = $data[34];
        $qual_3             = $data[35];
        $qual_4             = $data[36];
        $qual_5             = $data[37];
        $qual_6             = $data[38];
        $qual_7             = $data[39];
        $qual_8             = $data[40];
        $qual_9             = $data[41];
        $qual_10            = $data[42];
        $st_born_country    = $data[43];
        $qual_name_8_other  = $data[44];
        $qual_name_9_other  = $data[45];
        $qual_name_10_other = $data[46];
        $lan_spoken_other   = $data[47];
        $disability_type_other = $data[48];
        $st_disability_type = json_encode([]);
        $photo              = json_encode([]);
        $admin_id           = $_SESSION['user_id'] ?? 1;

        // 🔹 Convert dropdown values by mapping name → ID
        $stu_state        = $stateMap[$stu_state_raw] ?? 0;
        $emp_status       = $empStatusMap[$emp_status_raw] ?? 0;
        $self_status      = $selfStatusMap[$self_status_raw] ?? 0;
        $st_citizen       = $citizenMap[$st_citizen_raw] ?? 0;
        $highest_school   = $highestSchoolMap[$highest_school_raw] ?? 0;

        // 🔹 Multi-select: study_reason
        $reasonNames = array_map('trim', explode(',', $study_reason_raw));
        $reasonIds = [];
        foreach ($reasonNames as $r) {
            if (isset($studyReasonMap[$r])) {
                $reasonIds[] = $studyReasonMap[$r];
            }
        }
        $study_reason = json_encode($reasonIds);

        // 🔹 Convert course names → IDs
        $courseNames = array_map('trim', explode(',', $courses_raw));
        $courseIds = [];
        foreach ($courseNames as $cname) {
            $cname = mysqli_real_escape_string($connection, $cname);
            $actualCourseName = explode('-', $cname)[0];
            $q = mysqli_query($connection, "SELECT course_id FROM courses WHERE course_sname='$actualCourseName' LIMIT 1");
            if ($q && mysqli_num_rows($q) > 0) {
                $row = mysqli_fetch_assoc($q);
                $courseIds[] = $row['course_id'];
            }
        }
        $courses = json_encode($courseIds);

        // 🔹 Insert
        $sql = "INSERT INTO student_enrolments
        (st_unique_id,st_enquiry_id, st_rto_name, st_courses, st_branch, st_photo, st_given_name, st_surname, st_dob, st_country_birth, st_street, st_suburb, st_post_code, st_tel_num, st_email, st_mobile, st_state, st_emerg_name, st_emerg_relation, st_emerg_mobile, st_emerg_agree, st_usi, st_emp_status, st_self_status, st_citizenship, st_highest_school, st_study_reason, st_study_reason_other, st_gender, st_credit_transfer, st_secondary_school, st_born_country, st_origin, st_lan_spoken, st_disability, st_qual_1, st_qual_2, st_qual_3, st_qual_4, st_qual_5, st_qual_6, st_qual_7, st_qual_8, st_qual_9, st_qual_10, st_born_country_other, st_qual_8_other, st_qual_9_other, st_qual_10_other, st_lan_spoken_other, st_disability_type, st_disability_type_other, st_created_by)
        VALUES
        ('1','$enquiry_id','$rto_name','$courses','$branch_name','$photo','$given_name','$surname','$dob','$birth_country','$street_details','$sub_urb','$post_code','$tel_num','$emailAddress','$mobile_num','$stu_state','$em_full_name','$em_relation','$em_mobile_num','$em_agree_check','$usi_id','$emp_status','$self_status','$st_citizen','$highest_school','$study_reason','$study_reason_other','$gender_check','$cred_tansf','$sec_school','$born_country','$origin','$lan_spoken','$disability','$qual_1','$qual_2','$qual_3','$qual_4','$qual_5','$qual_6','$qual_7','$qual_8','$qual_9','$qual_10','$st_born_country','$qual_name_8_other','$qual_name_9_other','$qual_name_10_other','$lan_spoken_other','$st_disability_type','$disability_type_other',$admin_id)";

        if (mysqli_query($connection, $sql)) {
            $rowCount++;
            $tbody .= "<tr>
                <td>$enquiry_id</td>
                <td>$rto_name</td>
                <td>$given_name $surname</td>
                <td>$emailAddress</td>
                <td>$mobile_num</td>
            </tr>";
        } else {
            $tbody .= "<tr><td colspan='5' style='color:red;'>DB Error: " . mysqli_error($connection) . "</td></tr>";
        }
    }

    fclose($handle);

    echo "<p><b>Uploaded successfully:</b> $rowCount rows inserted.</p>";
    echo "<table border='1' cellpadding='5'>
            <tr><th>Enquiry ID</th><th>RTO</th><th>Name</th><th>Email</th><th>Mobile</th></tr>
            $tbody
          </table>";

    mysqli_close($connection);
}




// Enquiry dashboard counts for counsellors (View Enquiries page) – now honour filters
if(@$_POST['formName']=='fetchEnquiryDashboard'){
    $search = isset($_POST['search']) ? mysqli_real_escape_string($connection, trim($_POST['search'])) : '';
    $filter_course = isset($_POST['filter_course']) ? (int)$_POST['filter_course'] : 0;
    $filter_status = isset($_POST['filter_status']) ? (int)$_POST['filter_status'] : -1;
    $filter_date_from = isset($_POST['filter_date_from']) ? mysqli_real_escape_string($connection, $_POST['filter_date_from']) : '';
    $filter_date_to = isset($_POST['filter_date_to']) ? mysqli_real_escape_string($connection, $_POST['filter_date_to']) : '';
    $filter_counsellor = isset($_POST['filter_counsellor']) ? (int)$_POST['filter_counsellor'] : 0;
    $filter_source = isset($_POST['filter_source']) ? (int)$_POST['filter_source'] : -1;

    $flow_status_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_status'")) ? 'COALESCE(e.st_enquiry_flow_status,1)' : '1';
    $source_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_source'")) ? 'e.st_enquiry_source' : '0';

    $where = " e.st_enquiry_status = 0 ";
    if($search !== ''){
        $where .= " AND (e.st_name LIKE '%$search%' OR e.st_phno LIKE '%$search%' OR e.st_email LIKE '%$search%' OR e.st_enquiry_id LIKE '%$search%') ";
    }
    if($filter_course > 0){
        $where .= " AND (e.st_course LIKE '%\"$filter_course\"%' OR e.st_course LIKE '%$filter_course%') ";
    }
    if($filter_status >= 0){
        // Status filter must always apply to enquiry status (st_enquiry_flow_status).
        $where .= " AND $flow_status_col = ".(int)$filter_status;
    }
    if($filter_date_from !== ''){
        $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) >= '".date('Y-m-d', strtotime($filter_date_from))."' ";
    }
    if($filter_date_to !== ''){
        $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) <= '".date('Y-m-d', strtotime($filter_date_to))."' ";
    }
    if($filter_counsellor > 0){
        $where .= " AND EXISTS (
            SELECT 1 FROM counseling_details c 
            WHERE c.st_enquiry_id = e.st_enquiry_id 
              AND c.counsil_enquiry_status=0 
              AND (
                    c.counsil_createdby = $filter_counsellor 
                 OR c.counsil_mem_name IN (SELECT user_name FROM users WHERE user_id=$filter_counsellor)
              )
        ) ";
    }
    if($filter_source >= 0 && $source_col !== '0'){
        $where .= " AND $source_col = ".(int)$filter_source;
    }

    $base_from = " FROM student_enquiry e WHERE $where ";

    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');
    $week_start = date('Y-m-d 00:00:00', strtotime('-7 days'));
    $month_start = date('Y-m-d 00:00:00', strtotime('-30 days'));
    $last_week_start = date('Y-m-d 00:00:00', strtotime('-7 days'));

    $total_today = 0;
    $total_week = 0;
    $total_month = 0;
    $new_last_week = 0;

    $res_today = mysqli_query($connection, "SELECT COUNT(*)".$base_from." AND COALESCE(e.created_date, e.st_enquiry_date) >= '$today_start' AND COALESCE(e.created_date, e.st_enquiry_date) <= '$today_end'");
    if($res_today && ($row = mysqli_fetch_row($res_today))){ $total_today = (int)$row[0]; }

    $res_week = mysqli_query($connection, "SELECT COUNT(*)".$base_from." AND COALESCE(e.created_date, e.st_enquiry_date) >= '$week_start'");
    if($res_week && ($row = mysqli_fetch_row($res_week))){ $total_week = (int)$row[0]; }

    $res_month = mysqli_query($connection, "SELECT COUNT(*)".$base_from." AND COALESCE(e.created_date, e.st_enquiry_date) >= '$month_start'");
    if($res_month && ($row = mysqli_fetch_row($res_month))){ $total_month = (int)$row[0]; }

    $res_last_week = mysqli_query($connection, "SELECT COUNT(*)".$base_from." AND COALESCE(e.created_date, e.st_enquiry_date) >= '$last_week_start'");
    if($res_last_week && ($row = mysqli_fetch_row($res_last_week))){ $new_last_week = (int)$row[0]; }

    $followups_due_today = 0;
    $next_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_next_followup_date'"));
    if($next_col){
        $today_d = date('Y-m-d');
        // Count only latest active follow-up row per enquiry to avoid stale/history duplicates.
        $fq = mysqli_query($connection, "SELECT COUNT(DISTINCT e.st_id)
            FROM student_enquiry e
            INNER JOIN (
                SELECT enquiry_id, MAX(flw_id) AS last_flw_id
                FROM followup_calls
                WHERE flw_enquiry_status = 0
                GROUP BY enquiry_id
            ) lf ON lf.enquiry_id = e.st_enquiry_id
            INNER JOIN followup_calls f ON f.flw_id = lf.last_flw_id
            WHERE $where AND DATE(f.flw_next_followup_date) = '$today_d'");
        if($fq && ($row = mysqli_fetch_row($fq))){ $followups_due_today = (int)$row[0]; }
    }

    $converted = 0;
    // Keep card consistent with Status filter and enquiry status semantics.
    if($flow_status_col !== '1'){
        $cq = mysqli_query($connection, "SELECT COUNT(*)".$base_from." AND $flow_status_col = 6");
        if($cq && ($row = mysqli_fetch_row($cq))){ $converted = (int)$row[0]; }
    }

    $lost = 0;
    if($flow_status_col !== '1'){
        $lq = mysqli_query($connection, "SELECT COUNT(*)".$base_from." AND $flow_status_col = 7");
        if($lq && ($row = mysqli_fetch_row($lq))){ $lost = (int)$row[0]; }
    }

    header('Content-Type: application/json');
    echo json_encode(array(
        'total_today'=>$total_today,
        'total_week'=>$total_week,
        'total_month'=>$total_month,
        'new_last_week'=>$new_last_week,
        'followups_due_today'=>$followups_due_today,
        'converted'=>$converted,
        'lost'=>$lost
    ));
    exit;
}

// Enquiry list for View Enquiries: filters, sort, next_followup_date, status (HTML or DataTables JSON)
if(@$_POST['formName']=='fetchEnquiryList'){
    if (!mysqli_fetch_assoc(@mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_change_stage'"))) {
        @mysqli_query($connection, "ALTER TABLE `student_enquiry` ADD COLUMN `st_enquiry_flow_change_stage` VARCHAR(8) NULL DEFAULT NULL COMMENT 'PEFU or PCFU when enquiry status last set from follow-up outcome'");
    }
    $search = isset($_POST['search']) ? mysqli_real_escape_string($connection, trim($_POST['search'])) : '';
    $filter_course = isset($_POST['filter_course']) ? (int)$_POST['filter_course'] : 0;
    $filter_status = isset($_POST['filter_status']) ? (int)$_POST['filter_status'] : -1;
    $filter_date_from = isset($_POST['filter_date_from']) ? mysqli_real_escape_string($connection, $_POST['filter_date_from']) : '';
    $filter_date_to = isset($_POST['filter_date_to']) ? mysqli_real_escape_string($connection, $_POST['filter_date_to']) : '';
    $filter_counsellor = isset($_POST['filter_counsellor']) ? (int)$_POST['filter_counsellor'] : 0;
    $filter_source = isset($_POST['filter_source']) ? (int)$_POST['filter_source'] : -1;
    $sort_by = isset($_POST['sort_by']) ? $_POST['sort_by'] : 'latest';
    $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 0;
    $is_datatable = ($draw > 0);
    $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    if($start < 0){ $start = 0; }
    $length = isset($_POST['length']) ? (int)$_POST['length'] : 999999;
    if($length === -1){ $length = 500; }
    if($length <= 0){ $length = 10; }
    if($length > 500){ $length = 500; }

    // View Enquiries: checkboxes + row delete — admins only (user_type 1). Staff see View only.
    $view_enq_list_admin = isset($_SESSION['user_id']) && (int)@$_SESSION['user_type'] === 1;

    $flow_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_status'")) ? 'COALESCE(e.st_enquiry_flow_status,1)' : '1';
    $source_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_source'")) ? 'e.st_enquiry_source' : '0';
    $where = " e.st_enquiry_status = 0 ";
    if($search !== ''){
        $where .= " AND (e.st_name LIKE '%$search%' OR e.st_phno LIKE '%$search%' OR e.st_email LIKE '%$search%' OR e.st_enquiry_id LIKE '%$search%') ";
    }
    if($filter_course > 0){
        $where .= " AND (e.st_course LIKE '%\"$filter_course\"%' OR e.st_course LIKE '%$filter_course%') ";
    }
    if($filter_status >= 0){
        $where .= " AND $flow_col = ".(int)$filter_status;
    }
    if($filter_date_from !== ''){
        $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) >= '".date('Y-m-d', strtotime($filter_date_from))."' ";
    }
    if($filter_date_to !== ''){
        $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) <= '".date('Y-m-d', strtotime($filter_date_to))."' ";
    }
    if($filter_counsellor > 0){
        $where .= " AND EXISTS (
            SELECT 1 FROM counseling_details c 
            WHERE c.st_enquiry_id = e.st_enquiry_id 
              AND c.counsil_enquiry_status=0 
              AND (
                    c.counsil_createdby = $filter_counsellor 
                 OR c.counsil_mem_name IN (SELECT user_name FROM users WHERE user_id=$filter_counsellor)
              )
        ) ";
    }
    if($filter_source >= 0){
        $where .= " AND $source_col = ".(int)$filter_source;
    }
    // "Latest" = newest enquiry date first (same basis as Enquiry Date column: created_date or st_enquiry_date)
    $enquiry_dt_expr = "COALESCE(e.created_date, e.st_enquiry_date)";
    $order_sql = " ORDER BY $enquiry_dt_expr DESC, e.st_id DESC ";
    if($sort_by === 'status'){
        $order_sql = " ORDER BY $flow_col ASC, $enquiry_dt_expr DESC, e.st_id DESC ";
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
    // Canonical outcome keys (same as Follow Up Outcome in followup_accordion_form.php) for normalising DB values
    $outcome_keys_canonical = array('No Answer','Call Back Later','Booked Counselling','Application Started','Enrolled','Requested More Information','Not Interested','Do not Call','Wrong No','Enrolled Elsewhere','Course not Offered','Funding Enquiry');
    $outcome_normalize = array();
    foreach($outcome_keys_canonical as $k) $outcome_normalize[strtolower(trim($k))] = $k;

    $outcome_display = array(
        'No Answer' => array('label'=>'No Answer','btn'=>'btn-fup-no-answer','date_btn'=>true),
        'Call Back Later' => array('label'=>'Call Back Later','btn'=>'btn-fup-callback','date_btn'=>true),
        'Booked Counselling' => array('label'=>'Booked Counselling','btn'=>'btn-fup-booked','date_btn'=>true),
        // Outcome column should show exact follow-up outcome text; Status column shows mapped business state.
        'Application Started' => array('label'=>'Application Started','outcome_btn'=>'btn-fup-progressing'),
        'Enrolled' => array('label'=>'Enrolled','outcome_btn'=>'btn-fup-converted'),
        'Requested More Information' => array('label'=>'Requested More Information','outcome_btn'=>'btn-fup-provide-info'),
        'Not Interested' => array('label'=>'Not Interested','outcome_btn'=>'btn-fup-lost'),
        'Do not Call' => array('label'=>'Do not Call','outcome_btn'=>'btn-fup-lost'),
        'Wrong No' => array('label'=>'Wrong No','outcome_btn'=>'btn-fup-lost'),
        'Enrolled Elsewhere' => array('label'=>'Enrolled Elsewhere','outcome_btn'=>'btn-fup-lost'),
        'Course not Offered' => array('label'=>'Course not Offered','outcome_btn'=>'btn-fup-lost'),
        'Funding Enquiry' => array('label'=>'Funding Enquiry','outcome_btn'=>'btn-fup-lost')
    );

    /** Per-enquiry meta for View Enquiries: latest follow-up (PE vs PC tracked separately, newest wins) + counselling */
    $enquiry_list_fetch_row_meta = function ($connection, $st_enquiry_id) use ($outcome_normalize) {
        $eid_esc = mysqli_real_escape_string($connection, (string) $st_enquiry_id);
        $next_fup = null;
        $follow_up_outcome = '';
        $follow_ts = 0;
        $fr = null;
        $has_fu_st = mysqli_fetch_assoc(@mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_followup_stage'"));
        if ($has_fu_st) {
            $bestTs = -1;
            $bestRow = null;
            $bestFid = 0;
            foreach (array('enquiry', 'post_counselling') as $stg) {
                $stg_esc = mysqli_real_escape_string($connection, $stg);
                $fq1 = mysqli_query($connection, "SELECT * FROM followup_calls WHERE enquiry_id='$eid_esc' AND flw_enquiry_status=0 AND flw_followup_stage='$stg_esc' ORDER BY flw_id DESC LIMIT 1");
                if ($fq1 && ($row = mysqli_fetch_assoc($fq1))) {
                    $md = isset($row['flw_modified_date']) ? trim((string) $row['flw_modified_date']) : '';
                    $cd = isset($row['flw_created_date']) ? trim((string) $row['flw_created_date']) : '';
                    $ts = 0;
                    if ($md !== '' && $md !== '0000-00-00 00:00:00' && strtotime($md)) {
                        $ts = strtotime($md);
                    } elseif ($cd !== '' && strtotime($cd)) {
                        $ts = strtotime($cd);
                    }
                    $fid = (int)($row['flw_id'] ?? 0);
                    if ($ts > $bestTs || ($ts === $bestTs && $fid > $bestFid)) {
                        $bestTs = $ts;
                        $bestFid = $fid;
                        $bestRow = $row;
                    }
                }
            }
            $fr = $bestRow;
            if ($fr) {
                $follow_ts = $bestTs > 0 ? $bestTs : 0;
            }
        } else {
            $fq = mysqli_query($connection, "SELECT * FROM followup_calls WHERE enquiry_id='$eid_esc' AND flw_enquiry_status=0 ORDER BY flw_id DESC LIMIT 1");
            if ($fq) {
                $fr = mysqli_fetch_assoc($fq);
            }
        }
        if ($fr) {
            if (isset($fr['flw_next_followup_date']) && $fr['flw_next_followup_date'] !== null && $fr['flw_next_followup_date'] !== '') {
                $next_fup = $fr['flw_next_followup_date'];
            }
            if (isset($fr['flw_follow_up_outcome']) && $fr['flw_follow_up_outcome'] !== null) {
                $follow_up_outcome = trim((string) $fr['flw_follow_up_outcome']);
                if ($follow_up_outcome !== '' && isset($outcome_normalize[strtolower($follow_up_outcome)])) {
                    $follow_up_outcome = $outcome_normalize[strtolower($follow_up_outcome)];
                }
            }
            if (!$has_fu_st) {
                $md = isset($fr['flw_modified_date']) ? trim((string) $fr['flw_modified_date']) : '';
                $cd = isset($fr['flw_created_date']) ? trim((string) $fr['flw_created_date']) : '';
                if ($md !== '' && $md !== '0000-00-00 00:00:00' && strtotime($md)) {
                    $follow_ts = strtotime($md);
                } elseif ($cd !== '' && strtotime($cd)) {
                    $follow_ts = strtotime($cd);
                }
            }
        }
        $couns_outcome = '';
        $couns_ts = 0;
        if (mysqli_fetch_assoc(@mysqli_query($connection, "SHOW COLUMNS FROM counseling_details LIKE 'counsil_outcome'"))) {
            $cq = mysqli_query($connection, "SELECT `counsil_outcome`,`counsil_created_date`,`counsil_modified_date` FROM counseling_details WHERE st_enquiry_id='$eid_esc' AND counsil_enquiry_status=0 ORDER BY counsil_id DESC LIMIT 1");
            if ($cq && ($cr = mysqli_fetch_assoc($cq))) {
                $raw = isset($cr['counsil_outcome']) ? trim((string) $cr['counsil_outcome']) : '';
                if ($raw !== '') {
                    $lk = strtolower($raw);
                    $cm = array('counselling done' => 'Counselling Done', 'counseling done' => 'Counselling Done', 'rejected' => 'Rejected', 'rescheduled' => 'Rescheduled');
                    $couns_outcome = isset($cm[$lk]) ? $cm[$lk] : $raw;
                    $t0 = (!empty($cr['counsil_created_date']) && strtotime((string) $cr['counsil_created_date'])) ? strtotime((string) $cr['counsil_created_date']) : 0;
                    $mdt = isset($cr['counsil_modified_date']) ? trim((string) $cr['counsil_modified_date']) : '';
                    $t1 = ($mdt !== '' && $mdt !== '0000-00-00' && strtotime($mdt)) ? strtotime($mdt . ' 23:59:59') : 0;
                    $couns_ts = max($t0, $t1);
                }
            }
        }
        $appointment_id = null;
        $appointment_date = null;
        if (in_array($follow_up_outcome, array('No Answer', 'Call Back Later', 'Booked Counselling'), true)) {
            $aq = mysqli_query($connection, "SELECT appointment_id, appointment_datetime, appointment_date FROM appointments WHERE connected_enquiry_id='" . mysqli_real_escape_string($connection, (string) $st_enquiry_id) . "' AND delete_status!=1 ORDER BY appointment_datetime DESC LIMIT 1");
            if ($aq && ($ar = mysqli_fetch_assoc($aq))) {
                $appointment_id = (int) $ar['appointment_id'];
                $appointment_date = !empty($ar['appointment_datetime']) ? $ar['appointment_datetime'] : (!empty($ar['appointment_date']) ? $ar['appointment_date'] : null);
            }
        }
        return array(
            'next_fup' => $next_fup,
            'follow_up_outcome' => $follow_up_outcome,
            'follow_ts' => (int) $follow_ts,
            'couns_outcome' => $couns_outcome,
            'couns_ts' => (int) $couns_ts,
            'appointment_id' => $appointment_id,
            'appointment_date' => $appointment_date,
        );
    };

    $records_total = 0;
    $rtq = mysqli_query($connection, "SELECT COUNT(*) FROM student_enquiry WHERE st_enquiry_status=0");
    if($rtq && ($rtr = mysqli_fetch_row($rtq))){ $records_total = (int)$rtr[0]; }

    $records_filtered = 0;
    $rfq = mysqli_query($connection, "SELECT COUNT(*) FROM student_enquiry e WHERE $where");
    if($rfq && ($rfr = mysqli_fetch_row($rfq))){ $records_filtered = (int)$rfr[0]; }

    $rows = array();
    $fu_stage_sel = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_change_stage'")) ? 'e.st_enquiry_flow_change_stage' : 'NULL';
    $select_sql = "SELECT e.st_id, e.st_enquiry_id, e.st_name, e.st_phno, e.st_email, e.st_course, e.st_course_type, e.st_enquiry_date, e.created_date, $flow_col AS flow_status, $fu_stage_sel AS flow_change_stage FROM student_enquiry e WHERE $where ";

    if($sort_by === 'followup_date'){
        $q = mysqli_query($connection, $select_sql.$order_sql);
        while($r = mysqli_fetch_assoc($q)){
            $meta = $enquiry_list_fetch_row_meta($connection, $r['st_enquiry_id']);
            $rows[] = array_merge(array('r'=>$r), $meta);
        }
        usort($rows, function($a,$b){
            $ta = $a['next_fup'] ? strtotime($a['next_fup']) : 0;
            $tb = $b['next_fup'] ? strtotime($b['next_fup']) : 0;
            return $tb - $ta;
        });
        if($is_datatable){
            $rows = array_slice($rows, $start, $length);
        }
    } else {
        if($is_datatable){
            $lim = " LIMIT ".(int)$start.", ".(int)$length;
            $q = mysqli_query($connection, $select_sql.$order_sql.$lim);
        } else {
            $q = mysqli_query($connection, $select_sql.$order_sql);
        }
        while($r = mysqli_fetch_assoc($q)){
            $meta = $enquiry_list_fetch_row_meta($connection, $r['st_enquiry_id']);
            $rows[] = array_merge(array('r'=>$r), $meta);
        }
    }

    $tbody = '';
    $dt_data = array();
    foreach($rows as $row){
        $r = $row['r'];
        $next_fup = $row['next_fup'];
        $follow_up_outcome = $row['follow_up_outcome'];
        $follow_ts = isset($row['follow_ts']) ? (int) $row['follow_ts'] : 0;
        $couns_outcome = isset($row['couns_outcome']) ? (string) $row['couns_outcome'] : '';
        $couns_ts = isset($row['couns_ts']) ? (int) $row['couns_ts'] : 0;
        $appointment_id = $row['appointment_id'];
        $appointment_date = $row['appointment_date'];
        $courseNames = array();
        if(!empty($r['st_course'])){
            $ids = json_decode($r['st_course']);
            if(is_array($ids)) foreach($ids as $id){
                $c = mysqli_fetch_array(mysqli_query($connection, "SELECT course_sname, course_name FROM courses WHERE course_status!=1 AND course_id=".(int)$id));
                if($c) $courseNames[] = trim(($c['course_sname']??'').'-'.($c['course_name']??''));
            }
        }
        if(count($courseNames)){
            $firstCourse = $courseNames[0];
            $extraCount = count($courseNames) - 1;
            $displayText = htmlspecialchars($firstCourse, ENT_QUOTES, 'UTF-8');
            if($extraCount > 0){
                $displayText .= ' +'.$extraCount.' more';
            }
            $tooltipText = htmlspecialchars(implode("\n", $courseNames), ENT_QUOTES, 'UTF-8');
            $course_name = '<span class="course-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="'.$tooltipText.'">'.$displayText.'</span>';
        } else {
            $course_name = '-';
        }
        // Enquiry Date column must reflect the actual enquiry field (st_enquiry_date).
        $enquiry_dt_raw = !empty($r['st_enquiry_date']) ? $r['st_enquiry_date'] : $r['created_date'];
        $enquiry_date = $enquiry_dt_raw ? date('d/m/Y', strtotime($enquiry_dt_raw)) : '-';
        $flow_status = (int)($r['flow_status'] ?? 1);
        $status_label = $status_labels[$flow_status] ?? 'New';
        $status_class = $status_classes[$flow_status] ?? 'secondary';
        $flow_stage_acr = isset($r['flow_change_stage']) && $r['flow_change_stage'] !== null ? trim((string) $r['flow_change_stage']) : '';
        if ($flow_stage_acr === 'ENQ') {
            // Backward compatibility for older rows saved before stage acronym migration.
            $flow_stage_acr = 'PEFU';
        }
        if ($flow_stage_acr === 'CONS') {
            $stage_tooltip_esc = htmlspecialchars('CONS → Counselling', ENT_QUOTES, 'UTF-8');
            $stage_html = '<span class="course-tooltip" title="' . $stage_tooltip_esc . '"><span class="badge bg-secondary enq-stage-badge">' . htmlspecialchars($flow_stage_acr) . '</span></span>';
        } elseif ($flow_stage_acr === 'PEFU') {
            $stage_tooltip_esc = htmlspecialchars('PEFU → Post Enquiry Follow Up', ENT_QUOTES, 'UTF-8');
            $stage_html = '<span class="course-tooltip" title="' . $stage_tooltip_esc . '"><span class="badge bg-secondary enq-stage-badge">' . htmlspecialchars($flow_stage_acr) . '</span></span>';
        } elseif ($flow_stage_acr === 'PCFU') {
            $stage_tooltip_esc = htmlspecialchars('PCFU → Post Counselling Follow-Up', ENT_QUOTES, 'UTF-8');
            $stage_html = '<span class="course-tooltip" title="' . $stage_tooltip_esc . '"><span class="badge bg-secondary enq-stage-badge">' . htmlspecialchars($flow_stage_acr) . '</span></span>';
        } else {
            $stage_html = '<span class="text-muted">—</span>';
        }
        $status_tip = htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8');
        $status_cell_html = '<span class="course-tooltip" title="' . $status_tip . '"><span class="badge bg-' . $status_class . ' enq-status-badge">' . htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8') . '</span></span>';
        $outcome_html = '<span class="badge bg-secondary">No follow-up yet</span>';
        $use_counselling_outcome = ($couns_outcome !== '' && ($follow_up_outcome === '' || $couns_ts > $follow_ts));
        if ($use_counselling_outcome) {
            $cbc = 'secondary';
            if ($couns_outcome === 'Counselling Done') {
                $cbc = 'success';
            } elseif ($couns_outcome === 'Rejected') {
                $cbc = 'danger';
            } elseif ($couns_outcome === 'Rescheduled') {
                $cbc = 'warning';
            }
            $outcome_html = '<span class="badge bg-' . $cbc . '">' . htmlspecialchars($couns_outcome) . '</span>';
        } elseif ($follow_up_outcome !== '' && isset($outcome_display[$follow_up_outcome])) {
            $od = $outcome_display[$follow_up_outcome];
            if (!empty($od['date_btn'])) {
                $date_show = ($appointment_date !== null && $appointment_date !== '') ? $appointment_date : $next_fup;
                $date_str = $date_show ? date('d/m/Y', strtotime($date_show)) : '';
                if ($date_str !== '') {
                    $attr = $appointment_id ? ' data-appointment-id="' . (int) $appointment_id . '"' : '';
                    $outcome_html = '<span class="btn-fup-date ' . $od['btn'] . '"' . $attr . ' title="' . htmlspecialchars($follow_up_outcome) . '">' . $date_str . '</span>';
                } else {
                    $outcome_html = '<span class="btn-fup-date ' . $od['btn'] . '">' . htmlspecialchars($od['label']) . '</span>';
                }
            } elseif (!empty($od['outcome_btn'])) {
                $outcome_html = '<span class="btn-fup-outcome ' . $od['outcome_btn'] . '">' . htmlspecialchars($od['label']) . '</span>';
            } else {
                $outcome_html = '<span class="badge bg-secondary">' . htmlspecialchars($od['label']) . '</span>';
            }
        } elseif ($follow_up_outcome !== '') {
            $outcome_html = '<span class="badge bg-secondary">' . htmlspecialchars($follow_up_outcome) . '</span>';
        } elseif ($next_fup) {
            $next_ts = strtotime($next_fup);
            $outcome_html = '<span class="text-muted">' . date('d/m/Y', $next_ts) . '</span>';
        }
        $eq_enc = base64_encode($r['st_id']);
        $st_id = (int)$r['st_id'];
        $cb_html = $view_enq_list_admin ? '<input type="checkbox" class="enq-row-cb form-check-input" value="'.$st_id.'">' : '';
        $action_html = '<div class="d-inline-flex align-items-center gap-1 flex-wrap view-enq-actions">'
            .'<a href="student_enquiry.php?eq='.$eq_enc.'&amp;view=1" class="btn btn-sm btn-outline-primary view-enq-btn" title="View enquiry" aria-label="View enquiry"><i class="ti ti-eye"></i></a>';
        if($view_enq_list_admin){
            $lln_esc  = mysqli_real_escape_string($connection, (string)($r['st_enquiry_id'] ?? ''));
            $lln_done = $lln_esc !== '' && mysqli_fetch_row(mysqli_query($connection,
                "SELECT 1 FROM counseling_details WHERE st_enquiry_id='$lln_esc' AND counsil_enquiry_status=0 LIMIT 1"
            ));
            $lln_btn = $lln_done
                ? '<button type="button" class="btn btn-secondary btn-sm" disabled title="LLN already processed">Proceed to LLN</button>'
                : '<button type="button" class="btn btn-outline-secondary btn-sm btn-enq-lln" data-st-id="'.$st_id.'" title="Process to LLN" aria-label="Process to LLN">Process to LLN</button>';
            $action_html .= '<button type="button" class="btn btn-sm btn-outline-danger btn-enq-delete view-enq-btn" data-st-id="'.$st_id.'" title="Delete" aria-label="Delete"><i class="ti ti-trash"></i></button>
            '.$lln_btn;
        }
        $action_html .= '</div>';
        if($view_enq_list_admin){
            $cells = array(
                $cb_html,
                $stage_html,
                $outcome_html,
                $enquiry_date,
                htmlspecialchars($r['st_name']),
                htmlspecialchars($r['st_phno']),
                $course_name,
                $status_cell_html,
                $action_html
            );
        } else {
            $cells = array(
                $stage_html,
                $outcome_html,
                $enquiry_date,
                htmlspecialchars($r['st_name']),
                htmlspecialchars($r['st_phno']),
                $course_name,
                $status_cell_html,
                $action_html
            );
        }
        if($is_datatable){
            $dt_data[] = $cells;
        } else {
            $tbody .= '<tr>';
            foreach($cells as $c){
                $tbody .= '<td>'.$c.'</td>';
            }
            $tbody .= '</tr>';
        }
    }

    if($is_datatable){
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => $records_total,
            'recordsFiltered' => $records_filtered,
            'data' => $dt_data
        ));
        exit;
    }
    $empty_colspan = $view_enq_list_admin ? 9 : 8;
    echo $tbody ?: '<tr><td colspan="'.$empty_colspan.'">No records</td></tr>';
    exit;
}

// Enquiry reports data (management)
if(@$_POST['formName']=='fetchEnquiryReports'){
    // Capture and discard any notices/warnings so JSON stays clean
    if(function_exists('ob_start')){ ob_start(); }

    $search = isset($_POST['search']) ? mysqli_real_escape_string($connection, trim($_POST['search'])) : '';
    $filter_course = isset($_POST['filter_course']) ? (int)$_POST['filter_course'] : 0;
    $filter_status = isset($_POST['filter_status']) ? (int)$_POST['filter_status'] : -1;
    $filter_date_from = isset($_POST['filter_date_from']) ? mysqli_real_escape_string($connection, $_POST['filter_date_from']) : '';
    $filter_date_to = isset($_POST['filter_date_to']) ? mysqli_real_escape_string($connection, $_POST['filter_date_to']) : '';
    $filter_counsellor = isset($_POST['filter_counsellor']) ? (int)$_POST['filter_counsellor'] : 0;
    $filter_source = isset($_POST['filter_source']) ? (int)$_POST['filter_source'] : -1;

    $flow_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_status'")) ? 'COALESCE(e.st_enquiry_flow_status,1)' : '1';
    $source_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_source'")) ? 'e.st_enquiry_source' : '0';
    $where = " e.st_enquiry_status = 0 ";
    if($search !== ''){
        $where .= " AND (e.st_name LIKE '%$search%' OR e.st_phno LIKE '%$search%' OR e.st_email LIKE '%$search%' OR e.st_enquiry_id LIKE '%$search%') ";
    }
    if($filter_course > 0){
        $where .= " AND (e.st_course LIKE '%\"$filter_course\"%' OR e.st_course LIKE '%$filter_course%') ";
    }
    if($filter_status >= 0){
        $where .= " AND $flow_col = ".(int)$filter_status;
    }
    if($filter_date_from !== ''){
        $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) >= '".date('Y-m-d', strtotime($filter_date_from))."' ";
    }
    if($filter_date_to !== ''){
        $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) <= '".date('Y-m-d', strtotime($filter_date_to))."' ";
    }
    if($filter_counsellor > 0){
        $where .= " AND EXISTS (SELECT 1 FROM counseling_details c WHERE c.st_enquiry_id = e.st_enquiry_id AND c.counsil_enquiry_status=0 AND (c.counsil_createdby = $filter_counsellor OR c.counsil_mem_name IN (SELECT user_name FROM users WHERE user_id=$filter_counsellor))) ";
    }
    if($filter_source >= 0){
        $where .= " AND $source_col = ".(int)$filter_source;
    }
    $base_where = " FROM student_enquiry e WHERE $where ";
    $total_enquiries = 0;
    $converted_count = 0;
    $conversion_rate = 0;
    $by_course = array();
    $by_source = array();
    $counsellor_perf = array();
    $followup_effect = array('with_followup'=>0, 'converted_with_followup'=>0);
    $lost_count = 0;

    $res_total = mysqli_query($connection, "SELECT COUNT(*)" . $base_where);
    if($res_total && ($row = mysqli_fetch_row($res_total))){ $total_enquiries = (int)$row[0]; }
    $res_conv = mysqli_query($connection, "SELECT COUNT(*) FROM student_enquiry e WHERE $where AND e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL)");
    if($res_conv && ($row = mysqli_fetch_row($res_conv))){ $converted_count = (int)$row[0]; }
    if($total_enquiries > 0){ $conversion_rate = round(($converted_count / $total_enquiries) * 100, 1); }

    $cq = mysqli_query($connection, "SELECT c.course_id, c.course_sname, c.course_name, COUNT(*) AS cnt FROM student_enquiry e INNER JOIN courses c ON (e.st_course LIKE CONCAT('%', c.course_id, '%')) AND c.course_status != 1 WHERE $where GROUP BY c.course_id, c.course_sname, c.course_name ORDER BY cnt DESC");
    if($cq){
        while($r = mysqli_fetch_assoc($cq)){
            $by_course[] = array('course'=>$r['course_sname'].' - '.$r['course_name'], 'count'=>(int)$r['cnt']);
        }
    }
    $sources = array('','Website form','Phone call','Walk-in','Email','WhatsApp','Facebook / Instagram ads','Agent / referral (legacy)');
    $sq = mysqli_query($connection, "SELECT $source_col AS src, COUNT(*) AS cnt FROM student_enquiry e WHERE $where GROUP BY $source_col");
    if($sq){
        while($r = mysqli_fetch_assoc($sq)){
            $idx = (int)$r['src'];
            $by_source[] = array('source'=> isset($sources[$idx]) ? $sources[$idx] : ('Source '.$idx), 'count'=>(int)$r['cnt']);
        }
    }
    $cpq = mysqli_query($connection, "SELECT c.counsil_mem_name AS name, COUNT(DISTINCT c.st_enquiry_id) AS enquiries, COUNT(DISTINCT CASE WHEN e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL) THEN e.st_enquiry_id END) AS converted FROM counseling_details c INNER JOIN student_enquiry e ON e.st_enquiry_id = c.st_enquiry_id AND c.counsil_enquiry_status = 0 AND $where GROUP BY c.counsil_mem_name ORDER BY enquiries DESC");
    if($cpq){
        while($r = mysqli_fetch_assoc($cpq)){
            $enq = (int)$r['enquiries'];
            $conv = (int)$r['converted'];
            $rate = $enq > 0 ? round(($conv / $enq)*100,1) : 0;
            $counsellor_perf[] = array('counsellor'=>$r['name'], 'enquiries'=>$enq, 'converted'=>$conv, 'rate'=>$rate);
        }
    }
    $next_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_next_followup_date'"));
    if($next_col){
        $fq = mysqli_query($connection, "SELECT COUNT(DISTINCT e.st_id) AS with_fup FROM student_enquiry e INNER JOIN followup_calls f ON f.enquiry_id = e.st_enquiry_id WHERE $where");
        if($fq && ($fr = mysqli_fetch_assoc($fq))) $followup_effect['with_followup'] = (int)$fr['with_fup'];
        $fq2 = mysqli_query($connection, "SELECT COUNT(DISTINCT e.st_id) AS cnt FROM student_enquiry e INNER JOIN followup_calls f ON f.enquiry_id = e.st_enquiry_id WHERE $where AND e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL)");
        if($fq2 && ($fr2 = mysqli_fetch_assoc($fq2))) $followup_effect['converted_with_followup'] = (int)$fr2['cnt'];
    }
    if($flow_col !== '1'){
        $res_lost = mysqli_query($connection, "SELECT COUNT(*) FROM student_enquiry e WHERE $where AND COALESCE(e.st_enquiry_flow_status,1) = 7");
        if($res_lost && ($row = mysqli_fetch_row($res_lost))) $lost_count = (int)$row[0];
    }

    if(function_exists('ob_get_length') && ob_get_length()){ @ob_clean(); }
    header('Content-Type: application/json');
    echo json_encode(array(
        'by_course'=>$by_course,
        'by_source'=>$by_source,
        'conversion_rate'=>$conversion_rate,
        'total_enquiries'=>$total_enquiries,
        'converted_count'=>$converted_count,
        'counsellor_performance'=>$counsellor_perf,
        'followup_effectiveness'=>$followup_effect,
        'lost_count'=>$lost_count
    ));
    exit;
}

// Export Enquiry Reports as Excel
if(@$_POST['formName']=='exportEnquiryReportsExcel' || @$_GET['export']==='enquiry_reports_excel'){
    if(!isset($_SESSION['user_id']) || ((int)@$_SESSION['user_type'] !== 1 && (int)@$_SESSION['user_type'] !== 2)){ header('HTTP/1.0 403 Forbidden'); exit; }
    $search = isset($_POST['search']) ? mysqli_real_escape_string($connection, trim($_POST['search'])) : '';
    $filter_course = isset($_POST['filter_course']) ? (int)$_POST['filter_course'] : 0;
    $filter_status = isset($_POST['filter_status']) ? (int)$_POST['filter_status'] : -1;
    $filter_date_from = isset($_POST['filter_date_from']) ? mysqli_real_escape_string($connection, $_POST['filter_date_from']) : '';
    $filter_date_to = isset($_POST['filter_date_to']) ? mysqli_real_escape_string($connection, $_POST['filter_date_to']) : '';
    $filter_counsellor = isset($_POST['filter_counsellor']) ? (int)$_POST['filter_counsellor'] : 0;
    $filter_source = isset($_POST['filter_source']) ? (int)$_POST['filter_source'] : -1;
    $flow_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_status'")) ? 'COALESCE(e.st_enquiry_flow_status,1)' : '1';
    $source_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_source'")) ? 'e.st_enquiry_source' : '0';
    $where = " e.st_enquiry_status = 0 ";
    if($search !== '') $where .= " AND (e.st_name LIKE '%$search%' OR e.st_phno LIKE '%$search%' OR e.st_email LIKE '%$search%' OR e.st_enquiry_id LIKE '%$search%') ";
    if($filter_course > 0) $where .= " AND (e.st_course LIKE '%\"$filter_course\"%' OR e.st_course LIKE '%$filter_course%') ";
    if($filter_status >= 0) $where .= " AND $flow_col = ".(int)$filter_status;
    if($filter_date_from !== '') $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) >= '".date('Y-m-d', strtotime($filter_date_from))."' ";
    if($filter_date_to !== '') $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) <= '".date('Y-m-d', strtotime($filter_date_to))."' ";
    if($filter_counsellor > 0) $where .= " AND EXISTS (SELECT 1 FROM counseling_details c WHERE c.st_enquiry_id = e.st_enquiry_id AND c.counsil_enquiry_status=0 AND (c.counsil_createdby = $filter_counsellor OR c.counsil_mem_name IN (SELECT user_name FROM users WHERE user_id=$filter_counsellor))) ";
    if($filter_source >= 0) $where .= " AND $source_col = ".(int)$filter_source;
    $base_where = " FROM student_enquiry e WHERE $where ";
    $total_enquiries = (int)mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*)" . $base_where))[0];
    $converted_count = (int)mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM student_enquiry e WHERE $where AND e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL)"))[0];
    $conversion_rate = $total_enquiries > 0 ? round(($converted_count / $total_enquiries) * 100, 1) : 0;
    $by_course = array(); $cq = mysqli_query($connection, "SELECT c.course_id, c.course_sname, c.course_name, COUNT(*) AS cnt FROM student_enquiry e INNER JOIN courses c ON (e.st_course LIKE CONCAT('%', c.course_id, '%')) AND c.course_status != 1 WHERE $where GROUP BY c.course_id, c.course_sname, c.course_name ORDER BY cnt DESC");
    while($r = mysqli_fetch_assoc($cq)) $by_course[] = array('course'=>$r['course_sname'].' - '.$r['course_name'], 'count'=>(int)$r['cnt']);
    $sources = array('','Website form','Phone call','Walk-in','Email','WhatsApp','Facebook / Instagram ads','Agent / referral (legacy)');
    $by_source = array(); $sq = mysqli_query($connection, "SELECT $source_col AS src, COUNT(*) AS cnt FROM student_enquiry e WHERE $where GROUP BY $source_col");
    while($r = mysqli_fetch_assoc($sq)){ $idx = (int)$r['src']; $by_source[] = array('source'=> isset($sources[$idx]) ? $sources[$idx] : ('Source '.$idx), 'count'=>(int)$r['cnt']); }
    $counsellor_perf = array(); $cpq = mysqli_query($connection, "SELECT c.counsil_mem_name AS name, COUNT(DISTINCT c.st_enquiry_id) AS enquiries, COUNT(DISTINCT CASE WHEN e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL) THEN e.st_enquiry_id END) AS converted FROM counseling_details c INNER JOIN student_enquiry e ON e.st_enquiry_id = c.st_enquiry_id AND c.counsil_enquiry_status = 0 AND $where GROUP BY c.counsil_mem_name ORDER BY enquiries DESC");
    while($r = mysqli_fetch_assoc($cpq)) $counsellor_perf[] = array('counsellor'=>$r['name'], 'enquiries'=>(int)$r['enquiries'], 'converted'=>(int)$r['converted'], 'rate'=> $r['enquiries'] > 0 ? round(((int)$r['converted']/$r['enquiries'])*100,1) : 0);
    $followup_effect = array('with_followup'=>0, 'converted_with_followup'=>0);
    $next_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_next_followup_date'"));
    if($next_col){ $fq = mysqli_query($connection, "SELECT COUNT(DISTINCT e.st_id) AS with_fup FROM student_enquiry e INNER JOIN followup_calls f ON f.enquiry_id = e.st_enquiry_id WHERE $where"); if($fq && $fr = mysqli_fetch_assoc($fq)) $followup_effect['with_followup'] = (int)$fr['with_fup']; $fq2 = mysqli_query($connection, "SELECT COUNT(DISTINCT e.st_id) AS cnt FROM student_enquiry e INNER JOIN followup_calls f ON f.enquiry_id = e.st_enquiry_id WHERE $where AND e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL)"); if($fq2 && $fr2 = mysqli_fetch_assoc($fq2)) $followup_effect['converted_with_followup'] = (int)$fr2['cnt']; }
    $lost_count = 0; if($flow_col !== '1') $lost_count = (int)mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM student_enquiry e WHERE $where AND COALESCE(e.st_enquiry_flow_status,1) = 7"))[0];
    if(!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')){ if(file_exists(__DIR__.'/vendor/autoload.php')) require_once __DIR__.'/vendor/autoload.php'; elseif(file_exists(__DIR__.'/../vendor/autoload.php')) require_once __DIR__.'/../vendor/autoload.php'; }
    if(!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')){ header('Content-Type: text/plain'); echo 'PhpSpreadsheet not available'; exit; }
    // Ensure no previous output before sending Excel file
    if(function_exists('ob_get_length') && ob_get_length()){ @ob_clean(); }
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Summary');
    $sheet->setCellValue('A1','Enquiry Reports - Summary');
    $sheet->setCellValue('A2','Total Enquiries'); $sheet->setCellValue('B2',$total_enquiries);
    $sheet->setCellValue('A3','Converted (Enrolled)'); $sheet->setCellValue('B3',$converted_count);
    $sheet->setCellValue('A4','Conversion Rate %'); $sheet->setCellValue('B4',$conversion_rate);
    $sheet->setCellValue('A5','Lost (Not Interested)'); $sheet->setCellValue('B5',$lost_count);
    $sheet->setCellValue('A7','By Course'); $sheet->setCellValue('A8','Course'); $sheet->setCellValue('B8','Count');
    $row=9; foreach($by_course as $x){ $sheet->setCellValue('A'.$row,$x['course']); $sheet->setCellValue('B'.$row,$x['count']); $row++; }
    $row++; $sheet->setCellValue('A'.$row,'By Source'); $row++; $sheet->setCellValue('A'.$row,'Source'); $sheet->setCellValue('B'.$row,'Count'); $row++;
    foreach($by_source as $x){ $sheet->setCellValue('A'.$row,$x['source']); $sheet->setCellValue('B'.$row,$x['count']); $row++; }
    $row++; $sheet->setCellValue('A'.$row,'Counsellor Performance'); $row++; $sheet->setCellValue('A'.$row,'Counsellor'); $sheet->setCellValue('B'.$row,'Enquiries'); $sheet->setCellValue('C'.$row,'Converted'); $sheet->setCellValue('D'.$row,'Rate %'); $row++;
    foreach($counsellor_perf as $x){ $sheet->setCellValue('A'.$row,$x['counsellor']); $sheet->setCellValue('B'.$row,$x['enquiries']); $sheet->setCellValue('C'.$row,$x['converted']); $sheet->setCellValue('D'.$row,$x['rate']); $row++; }
    $sheet->setCellValue('A'.($row+1),'Follow-up: Enquiries with follow-up'); $sheet->setCellValue('B'.($row+1),$followup_effect['with_followup']);
    $sheet->setCellValue('A'.($row+2),'Converted among those with follow-up'); $sheet->setCellValue('B'.($row+2),$followup_effect['converted_with_followup']);
    // Output Excel headers and content
    if(function_exists('ob_get_length') && ob_get_length()){ @ob_clean(); }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="enquiry_reports_'.date('Y-m-d').'.xlsx"');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Export Enquiry Reports as PDF
if(@$_POST['formName']=='exportEnquiryReportsPdf' || @$_GET['export']==='enquiry_reports_pdf'){
    if(!isset($_SESSION['user_id']) || ((int)@$_SESSION['user_type'] !== 1 && (int)@$_SESSION['user_type'] !== 2)){ header('HTTP/1.0 403 Forbidden'); exit; }
    $search = isset($_POST['search']) ? mysqli_real_escape_string($connection, trim($_POST['search'])) : '';
    $filter_course = isset($_POST['filter_course']) ? (int)$_POST['filter_course'] : 0;
    $filter_status = isset($_POST['filter_status']) ? (int)$_POST['filter_status'] : -1;
    $filter_date_from = isset($_POST['filter_date_from']) ? mysqli_real_escape_string($connection, $_POST['filter_date_from']) : '';
    $filter_date_to = isset($_POST['filter_date_to']) ? mysqli_real_escape_string($connection, $_POST['filter_date_to']) : '';
    $filter_counsellor = isset($_POST['filter_counsellor']) ? (int)$_POST['filter_counsellor'] : 0;
    $filter_source = isset($_POST['filter_source']) ? (int)$_POST['filter_source'] : -1;
    $flow_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_flow_status'")) ? 'COALESCE(e.st_enquiry_flow_status,1)' : '1';
    $source_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM student_enquiry LIKE 'st_enquiry_source'")) ? 'e.st_enquiry_source' : '0';
    $where = " e.st_enquiry_status = 0 ";
    if($search !== '') $where .= " AND (e.st_name LIKE '%$search%' OR e.st_phno LIKE '%$search%' OR e.st_email LIKE '%$search%' OR e.st_enquiry_id LIKE '%$search%') ";
    if($filter_course > 0) $where .= " AND (e.st_course LIKE '%\"$filter_course\"%' OR e.st_course LIKE '%$filter_course%') ";
    if($filter_status >= 0) $where .= " AND $flow_col = ".(int)$filter_status;
    if($filter_date_from !== '') $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) >= '".date('Y-m-d', strtotime($filter_date_from))."' ";
    if($filter_date_to !== '') $where .= " AND DATE(COALESCE(e.created_date, e.st_enquiry_date)) <= '".date('Y-m-d', strtotime($filter_date_to))."' ";
    if($filter_counsellor > 0) $where .= " AND EXISTS (SELECT 1 FROM counseling_details c WHERE c.st_enquiry_id = e.st_enquiry_id AND c.counsil_enquiry_status=0 AND (c.counsil_createdby = $filter_counsellor OR c.counsil_mem_name IN (SELECT user_name FROM users WHERE user_id=$filter_counsellor))) ";
    if($filter_source >= 0) $where .= " AND $source_col = ".(int)$filter_source;
    $base_where = " FROM student_enquiry e WHERE $where ";
    $total_enquiries = (int)mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*)" . $base_where))[0];
    $converted_count = (int)mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM student_enquiry e WHERE $where AND e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL)"))[0];
    $conversion_rate = $total_enquiries > 0 ? round(($converted_count / $total_enquiries) * 100, 1) : 0;
    $by_course = array(); $cq = mysqli_query($connection, "SELECT c.course_id, c.course_sname, c.course_name, COUNT(*) AS cnt FROM student_enquiry e INNER JOIN courses c ON (e.st_course LIKE CONCAT('%', c.course_id, '%')) AND c.course_status != 1 WHERE $where GROUP BY c.course_id, c.course_sname, c.course_name ORDER BY cnt DESC");
    while($r = mysqli_fetch_assoc($cq)) $by_course[] = array('course'=>$r['course_sname'].' - '.$r['course_name'], 'count'=>(int)$r['cnt']);
    $sources = array('','Website form','Phone call','Walk-in','Email','WhatsApp','Facebook / Instagram ads','Agent / referral (legacy)');
    $by_source = array(); $sq = mysqli_query($connection, "SELECT $source_col AS src, COUNT(*) AS cnt FROM student_enquiry e WHERE $where GROUP BY $source_col");
    while($r = mysqli_fetch_assoc($sq)){ $idx = (int)$r['src']; $by_source[] = array('source'=> isset($sources[$idx]) ? $sources[$idx] : ('Source '.$idx), 'count'=>(int)$r['cnt']); }
    $counsellor_perf = array(); $cpq = mysqli_query($connection, "SELECT c.counsil_mem_name AS name, COUNT(DISTINCT c.st_enquiry_id) AS enquiries, COUNT(DISTINCT CASE WHEN e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL) THEN e.st_enquiry_id END) AS converted FROM counseling_details c INNER JOIN student_enquiry e ON e.st_enquiry_id = c.st_enquiry_id AND c.counsil_enquiry_status = 0 AND $where GROUP BY c.counsil_mem_name ORDER BY enquiries DESC");
    while($r = mysqli_fetch_assoc($cpq)) $counsellor_perf[] = array('counsellor'=>$r['name'], 'enquiries'=>(int)$r['enquiries'], 'converted'=>(int)$r['converted'], 'rate'=> $r['enquiries'] > 0 ? round(((int)$r['converted']/$r['enquiries'])*100,1) : 0);
    $followup_effect = array('with_followup'=>0, 'converted_with_followup'=>0);
    $next_col = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_next_followup_date'"));
    if($next_col){ $fq = mysqli_query($connection, "SELECT COUNT(DISTINCT e.st_id) AS with_fup FROM student_enquiry e INNER JOIN followup_calls f ON f.enquiry_id = e.st_enquiry_id WHERE $where"); if($fq && $fr = mysqli_fetch_assoc($fq)) $followup_effect['with_followup'] = (int)$fr['with_fup']; $fq2 = mysqli_query($connection, "SELECT COUNT(DISTINCT e.st_id) AS cnt FROM student_enquiry e INNER JOIN followup_calls f ON f.enquiry_id = e.st_enquiry_id WHERE $where AND e.st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '' AND st_enquiry_id IS NOT NULL)"); if($fq2 && $fr2 = mysqli_fetch_assoc($fq2)) $followup_effect['converted_with_followup'] = (int)$fr2['cnt']; }
    $lost_count = 0; if($flow_col !== '1') $lost_count = (int)mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM student_enquiry e WHERE $where AND COALESCE(e.st_enquiry_flow_status,1) = 7"))[0];
    // Ensure no previous output before sending PDF file (TCPDF requirement)
    if(function_exists('ob_get_length') && ob_get_length()){ @ob_clean(); }
    if(!crm_require_tcpdf()){
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('success'=>false,'message'=>'PDF export service is unavailable.'));
        exit;
    }
    $pdf = new TCPDF('P','mm','A4',true,'UTF-8');
    $pdf->SetCreator('Auz Training');
    $pdf->SetTitle('Enquiry Reports');
    $pdf->AddPage();
    $pdf->SetFont('helvetica','B',14);
    $pdf->Cell(0,8,'Enquiry Reports - Summary',0,1);
    $pdf->SetFont('helvetica','',10);
    $pdf->Cell(0,6,'Total Enquiries: '.$total_enquiries,0,1);
    $pdf->Cell(0,6,'Converted (Enrolled): '.$converted_count,0,1);
    $pdf->Cell(0,6,'Conversion Rate: '.$conversion_rate.'%',0,1);
    $pdf->Cell(0,6,'Lost (Not Interested): '.$lost_count,0,1);
    $pdf->Ln(4);
    $pdf->SetFont('helvetica','B',11);
    $pdf->Cell(0,6,'By Course',0,1);
    $pdf->SetFont('helvetica','',9);
    $pdf->Cell(100,6,'Course',1,0); $pdf->Cell(30,6,'Count',1,1);
    foreach($by_course as $x){ $pdf->Cell(100,6,$x['course'],1,0); $pdf->Cell(30,6,$x['count'],1,1); }
    $pdf->Ln(4);
    $pdf->SetFont('helvetica','B',11);
    $pdf->Cell(0,6,'By Source',0,1);
    $pdf->SetFont('helvetica','',9);
    $pdf->Cell(100,6,'Source',1,0); $pdf->Cell(30,6,'Count',1,1);
    foreach($by_source as $x){ $pdf->Cell(100,6,$x['source'],1,0); $pdf->Cell(30,6,$x['count'],1,1); }
    $pdf->Ln(4);
    $pdf->SetFont('helvetica','B',11);
    $pdf->Cell(0,6,'Counsellor Performance',0,1);
    $pdf->Cell(60,6,'Counsellor',1,0); $pdf->Cell(30,6,'Enquiries',1,0); $pdf->Cell(30,6,'Converted',1,0); $pdf->Cell(30,6,'Rate %',1,1);
    foreach($counsellor_perf as $x){ $pdf->Cell(60,6,$x['counsellor'],1,0); $pdf->Cell(30,6,$x['enquiries'],1,0); $pdf->Cell(30,6,$x['converted'],1,0); $pdf->Cell(30,6,$x['rate'],1,1); }
    $pdf->Ln(4);
    $pdf->Cell(0,6,'Follow-up: Enquiries with follow-up: '.$followup_effect['with_followup'],0,1);
    $pdf->Cell(0,6,'Converted among those with follow-up: '.$followup_effect['converted_with_followup'],0,1);
    $pdf->Output('enquiry_reports_'.date('Y-m-d').'.pdf','D');
    exit;
}

if(@$_POST['formName']=='fetchEnquiries'){    

    $where='';
    if(@$_POST['objFilter']){
        $objFilter=$_POST['objFilter'];        
        foreach($objFilter as $key=>$value){

        if($key=='created_date'){
            $from_date=date('Y-m-d',strtotime(explode(' - ',$value)[0])).' 00:00:00';
            $to_date=date('Y-m-d',strtotime(explode(' - ',$value)[1])).' 23:59:59';
            $where.=' AND '.$key.' BETWEEN "'.$from_date.'" AND "'.$to_date.'"';
        }else{
            $where.=' AND '.$key.' LIKE "%'.$value.'%"';
        }

        }
    }else{
        $where='';
    }


    // echo "SELECT * FROM `student_enquiry` WHERE `st_enquiry_status`=0".$where;
    

    $tbody='';
    $selectData=mysqli_query($connection,"SELECT * FROM `student_enquiry` WHERE `st_enquiry_status`=0".$where);
    if(mysqli_num_rows($selectData)!=0){
        while($selectDataQry=mysqli_fetch_array($selectData)){


            $coursesNames = json_decode($selectDataQry['st_course']);
            $courseDisplayList = array();
            if (is_array($coursesNames)) {
                foreach ($coursesNames as $value) {
                    $courses = mysqli_fetch_array(mysqli_query($connection, "SELECT * from courses where course_status!=1 AND course_id=" . (int)$value));
                    if (!empty($courses['course_sname']) || !empty($courses['course_name'])) {
                        $courseDisplayList[] = $courses['course_sname'] . '-' . $courses['course_name'];
                    }
                }
            }
            $firstCourse = count($courseDisplayList) > 0 ? $courseDisplayList[0] : ' - ';
            $coursesName = '<span class="course-cell-first">' . htmlspecialchars($firstCourse) . '</span>';
            if (count($courseDisplayList) > 1) {
                $coursesName .= ' <span class="course-view-more" data-courses="' . htmlspecialchars(json_encode($courseDisplayList), ENT_QUOTES, 'UTF-8') . '" title="Courses"><i class="mdi mdi-plus-circle-outline"></i> <span class="course-view-more-text">view more</span></span>';
            }
    
            $st_states=['-','NSW - New South Wales','VIC - Victoria','ACT - Australian Capital Territory','NT - Northern Territoy','WA - Western Australia','QLD - Queensland','SA - South Australia','TAS - Tasmania'];
            $stateIndex = isset($selectDataQry['st_state']) ? (int)$selectDataQry['st_state'] : 0;
            $state_name = $st_states[$stateIndex] ?? '-';
            
            $st_course_type=['-','Need exemption','Regular','Regular - Group','Short courses','Short course - Group'];
            $courseTypeId=$selectDataQry['st_course_type'];
        
            $visited=$selectDataQry['st_visited']==1 ? 'Visited' : ( $selectDataQry['st_visited']==2 ? 'Not Visited' : ' - ' ) ;
            
            $visastatus=$selectDataQry['st_visa_condition']==1 ? 'Approved' : 'Not Approved' ;
        
            $refered_names = $selectDataQry['st_refer_name'];
        
            $startPlanDate=date('d M Y',strtotime($selectDataQry['st_startplan_date']));
        
            $staff_comments=$selectDataQry['st_comments'];
            $preference=$selectDataQry['st_pref_comments'];
        
            $st_remarks=['--select--','Seems to be interested to do course and need to contact asap','contacted and followed','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose'];    
        
            if($selectDataQry['st_remarks']!=''){
                $remarksNotes='<div class="td_scroll_height">';
        
            foreach(json_decode($selectDataQry['st_remarks']) as $remark  ){                   
                
                $remarksNotes.=$st_remarks[$remark].' , <br>';
        
            }
            $remarksNotes.='</div>';
            }else{
                $remarksNotes=' - ';
                
            }
        
            $street=$selectDataQry['st_street_details'];
            $suburb=$selectDataQry['st_suburb'];
            $post_code=$selectDataQry['st_post_code'];
            $appointment=$selectDataQry['st_appoint_book']==1 ? 'Booked' : ( $selectDataQry['st_appoint_book']==2 ? 'Not Booked' : ' - ' );
            
            $querys2=mysqli_query($connection,"select visa_status_name from `visa_statuses` WHERE visa_id=".$selectDataQry['st_visa_status']);
            if(mysqli_num_rows($querys2)!=0){
            $visaCondition=mysqli_fetch_array($querys2);
        
            if(@$visaCondition['visa_status_name'] && $visaCondition['visa_status_name']!=''){
                $visacCond=$visaCondition['visa_status_name'];
            }else{
                $visacCond=' - ';
            }
            }else{
                $visacCond=' - ';
            }
    
            $appointment=$selectDataQry['st_appoint_book']==1 ? 'Booked' : ( $selectDataQry['st_appoint_book']==2 ? 'Not Booked' : ' - ' );
    
            $dateCreated=date('d M Y',strtotime($selectDataQry['st_enquiry_date']));


            $tbody.='<tr>';
            $tbody.='<td>'.$selectDataQry['st_enquiry_id'].'</td>';
            $tbody.='<td>'.$selectDataQry['st_name'].'</td>';
            $tbody.='<td>'.$selectDataQry['st_phno'].'</td>';
            $tbody.='<td>'.$selectDataQry['st_email'].'</td>';
            $tbody.='<td>'.$st_course_type[$courseTypeId].'</td>';
            $tbody.='<td class="imp-none">'.$selectDataQry['created_date'].'</td>';
            $tbody.='<td class="imp-none">'.$state_name.'</td>';
            $tbody.='<td class="imp-none">'.$coursesName.'</td>';
            $tbody.='<td class="imp-none">'.$visacCond.'</td>';
            $tbody.='<td class="imp-none">'.$visastatus.'</td>';
            $tbody.='<td><a class="btn btn-outline-primary btn-sm" href="student_enquiry.php?eq='.base64_encode($selectDataQry['st_id']).'">Edit</a></td>';
            $tbody.='</tr>';
        }
    }

    echo $tbody;

}
if(@$_POST['formName']=='fetchAppoints'){    

    $where='';
    if(@$_POST['objFilter']){
        $objFilter=$_POST['objFilter'];        
        foreach($objFilter as $key=>$value){

        if($key=='slot_book_by'){
            $teamArray=explode(',',$value);
            array_walk($teamArray, function (&$item) {
                $item = '"%' . $item . '%"';
            });
            $team=implode(' OR slot_book_by LIKE ',$teamArray);
            $where.=' AND `slot_book_by` LIKE '.$team;
        }else if($key=='slot_bk_datetime'){
            $from_date=date('Y-m-d',strtotime(explode(' - ',$value)[0])).' 00:00:00';
            $to_date=date('Y-m-d',strtotime(explode(' - ',$value)[1])).' 23:59:59';
            $where.=' AND '.$key.' BETWEEN "'.$from_date.'" AND "'.$to_date.'"';            
        }else{
            $where.=' AND '.$key.' LIKE "%'.$value.'%"';
        }

        }
    }else{
        $where='';
    }    
    

    $tbody='';
    $selectData=mysqli_query($connection,"SELECT * FROM `slot_book` WHERE `slot_bk_id`!=''".$where);
    echo "SELECT * FROM `slot_book` WHERE `slot_bk_id`!=''".$where;
    if(mysqli_num_rows($selectData)!=0){
        while($selectDataQry=mysqli_fetch_array($selectData)){     
            // print_r($selectDataQry);       

            $queryName=mysqli_fetch_array(mysqli_query($connection,"SELECT `st_name`,`st_enquiry_id`,`st_phno`,`st_email` FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_id`=".$selectDataQry['enq_form_id']));

            if($selectDataQry['slot_book_email_link']==1){
                $link='Yes';
            }else{
                $link='No';
            }

            $tbody.='<tr>';
            $tbody.='<td>'.$queryName['st_name'].'</td>';
            $tbody.='<td>'.$queryName['st_enquiry_id'].'</td>';            
            $tbody.='<td>'.$queryName['st_phno'].'</td>';
            $tbody.='<td>'.$queryName['st_email'].'</td>';
            $tbody.='<td>'.$selectDataQry['slot_bk_purpose'].'</td>';
            $tbody.='<td>'.$selectDataQry['slot_book_by'].'</td>';
            $tbody.='<td>'.$link.'</td>';
            $tbody.='<td>'.date('Y-m-d H:i',strtotime($selectDataQry['slot_bk_datetime'])).'</td>';
            $tbody.='</tr>';
        }
    }

    echo $tbody;

}

if(@$_POST['formName']=='fetchCounsel'){    

    $where='';
    if(@$_POST['objFilter']){
        $objFilter=$_POST['objFilter'];        
        foreach($objFilter as $key=>$value){

        if($key=='counsil_mem_name'){
            $teamArray=explode(',',$value);
            array_walk($teamArray, function (&$item) {
                $item = '"%' . $item . '%"';
            });
            $team=implode(' OR counsil_mem_name LIKE ',$teamArray);
            $where.=' AND `counsil_mem_name` LIKE '.$team;
        }else if($key=='counsil_created_date'){
            $from_date=date('Y-m-d',strtotime(explode(' - ',$value)[0])).' 00:00:00';
            $to_date=date('Y-m-d',strtotime(explode(' - ',$value)[1])).' 23:59:59';
            $where.=' AND '.$key.' BETWEEN "'.$from_date.'" AND "'.$to_date.'"';    
        }else{
            $where.=' AND '.$key.' LIKE "%'.$value.'%"';
        }

        }
    }else{
        $where='';
    }    
    
// echo "SELECT * FROM `counseling_details` WHERE `counsil_enquiry_status`!=1".$where;
    $tbody='';
    $selectData=mysqli_query($connection,"SELECT * FROM `counseling_details` WHERE `counsil_enquiry_status`!=1".$where);
    // echo "SELECT * FROM `slot_book` WHERE `slot_bk_id`!=''".$where;
    if(mysqli_num_rows($selectData)!=0){
        while($selectDataQry=mysqli_fetch_array($selectData)){     
            // print_r($selectDataQry);       
            // echo "SELECT `st_name`,`st_enquiry_id`,`st_phno`,`st_email` FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_enquiry_id`=".$selectDataQry['st_enquiry_id'];
            $queryName=mysqli_fetch_array(mysqli_query($connection,"SELECT `st_name`,`st_enquiry_id`,`st_phno`,`st_email` FROM `student_enquiry` WHERE `st_enquiry_status`=0 AND `st_enquiry_id`='".$selectDataQry['st_enquiry_id']."'"));

            if($selectDataQry['counsil_type']==1){
                $type='Face to Face';
            }else{
                $type='Video';
            }

            if($selectDataQry['counsil_end_time']!=''){
                $endDate=date('Y-m-d H:i',strtotime($selectDataQry['counsil_end_time']));
            }else{
                $endDate='';
            }

            $tbody.='<tr>';
            $tbody.='<td>'.$queryName['st_name'].'</td>';
            $tbody.='<td>'.$queryName['st_enquiry_id'].'</td>';            
            $tbody.='<td>'.$queryName['st_phno'].'</td>';
            $tbody.='<td>'.$queryName['st_email'].'</td>';
            $tbody.='<td>'.$type.'</td>';
            $tbody.='<td>'.$selectDataQry['counsil_mem_name'].'</td>';
            $tbody.='<td>'.date('Y-m-d H:i',strtotime($selectDataQry['counsil_timing'])).'</td>';
            $tbody.='<td>'.$endDate.'</td>';
            $tbody.='<td><a class="btn btn-outline-primary btn-sm" href="counselling_form.php?eq='.base64_encode($selectDataQry['counsil_id']).'">Edit</a></td>';
            $tbody.='</tr>';
        }
    }

    echo $tbody;

}

if(@$_POST['formName']=='fetchFollowupList'){
    $where='';
    if(@$_POST['objFilter']){
        $objFilter=$_POST['objFilter'];
        foreach($objFilter as $key=>$value){
            if($key=='flw_contacted_time' || $key=='flw_date'){
                if(strpos($value,' - ')!==false){
                    $parts=explode(' - ',$value);
                    $from_date=date('Y-m-d',strtotime(trim($parts[0]))).' 00:00:00';
                    $to_date=date('Y-m-d',strtotime(trim($parts[1]))).' 23:59:59';
                    $where.=' AND `'.$key.'` BETWEEN "'.$from_date.'" AND "'.$to_date.'"';
                }
            }else{
                $where.=' AND `'.$key.'` LIKE "%'.mysqli_real_escape_string($connection,$value).'%"';
            }
        }
    }
    $tbody='';
    $selectData=mysqli_query($connection,"SELECT * FROM `followup_calls` WHERE `flw_enquiry_status`=0".$where);
    if(mysqli_num_rows($selectData)!=0){
        while($row=mysqli_fetch_array($selectData)){
            $contacted_time=$row['flw_contacted_time']!='' ? date('d M Y H:i',strtotime($row['flw_contacted_time'])) : '';
            $flw_date=$row['flw_date']!='' ? date('d M Y',strtotime($row['flw_date'])) : '';
            $tbody.='<tr>';
            $tbody.='<td>'.$row['enquiry_id'].'</td>';
            $tbody.='<td>'.$row['flw_name'].'</td>';
            $tbody.='<td>'.$row['flw_phone'].'</td>';
            $tbody.='<td>'.$row['flw_contacted_person'].'</td>';
            $tbody.='<td>'.$contacted_time.'</td>';
            $tbody.='<td>'.$flw_date.'</td>';
            $tbody.='<td>'.$row['flw_mode_contact'].'</td>';
            $tbody.='<td>'.($row['flw_comments']!='' ? $row['flw_comments'] : '-').'</td>';
            $tbody.='<td><a class="btn btn-outline-primary btn-sm" href="followup_call.php?flw_id='.base64_encode($row['flw_id']).'">Edit</a></td>';
            $tbody.='</tr>';
        }
    }
    echo $tbody;
}

// ==================== APPOINTMENT SYSTEM FUNCTIONS ====================

if (!function_exists('crm_seed_appointment_email_templates')) {
    function crm_seed_appointment_email_templates($connection) {
        @mysqli_query($connection, "CREATE TABLE IF NOT EXISTS appointment_email_templates (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            template_code VARCHAR(64) NOT NULL UNIQUE,
            template_name VARCHAR(128) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $defaults = array(
            'standard_booking' => array(
                'name' => 'Standard appointment confirmation',
                'subject' => 'Your appointment confirmation – National College Australia',
                'body' => "Hi {{FirstName}},\n\nThis email confirms your appointment with National College Australia.\n\nYour booking details:\n- Purpose: {{PurposeName}}\n- Date: {{AppointmentDate}}\n- Time: {{AppointmentTime}}\n- Format: {{MeetingType}}\n- Team member: {{StaffName}}\n- Enquiry reference: {{EnquiryID}}\n\nIf you have any questions, please contact us."
            ),
            'phone_call_booking' => array(
                'name' => 'Phone call booking confirmation',
                'subject' => 'Your scheduled call with us – National College Australia',
                'body' => "Hi {{FirstName}},\n\nThank you for your interest in studying with us. A member of our team will contact you at the time below.\n\nCall details:\n- Date: {{AppointmentDate}}\n- Time: {{AppointmentTime}}\n- Team member: {{StaffName}}\n- Contact number: {{StudentPhone}}\n- Enquiry reference: {{EnquiryID}}\n\nPlease keep your phone available. If this time no longer suits you, reply to this email and we will arrange another time."
            ),
            'counselling_rescheduled' => array(
                'name' => 'Counselling rescheduled confirmation',
                'subject' => 'Your rescheduled counselling session – National College Australia',
                'body' => "Hi {{FirstName}},\n\nYour counselling session has been rescheduled. Here are your confirmed details:\n\n- Purpose: {{PurposeName}}\n- Date: {{AppointmentDate}}\n- Time: {{AppointmentTime}}\n- Format: {{MeetingType}}\n- Team member: {{StaffName}}\n- Enquiry reference: {{EnquiryID}}\n\nWe look forward to meeting you at the scheduled time."
            ),
        );

        foreach ($defaults as $code => $cfg) {
            $codeEsc = mysqli_real_escape_string($connection, $code);
            $nameEsc = mysqli_real_escape_string($connection, $cfg['name']);
            $subjectEsc = mysqli_real_escape_string($connection, $cfg['subject']);
            $bodyEsc = mysqli_real_escape_string($connection, $cfg['body']);
            @mysqli_query($connection, "INSERT INTO appointment_email_templates(template_code,template_name,subject,body,updated_at) VALUES('$codeEsc','$nameEsc','$subjectEsc','$bodyEsc',NOW()) ON DUPLICATE KEY UPDATE template_name=VALUES(template_name)");
        }
    }
}

if (!function_exists('crm_get_appointment_email_template')) {
    function crm_get_appointment_email_template($connection, $template_code) {
        crm_seed_appointment_email_templates($connection);
        $code = mysqli_real_escape_string($connection, trim((string)$template_code));
        $q = @mysqli_query($connection, "SELECT subject, body FROM appointment_email_templates WHERE template_code='$code' LIMIT 1");
        if ($q && ($r = mysqli_fetch_assoc($q))) {
            return array('subject' => (string)$r['subject'], 'body' => (string)$r['body']);
        }
        return array('subject' => '', 'body' => '');
    }
}

/**
 * Lookups + HTML confirmation for student when booking from enquiry flows
 * (contact bar “phone call” or counselling “Rescheduled” calendar).
 */
if (!function_exists('crm_appointment_lookup_location_name')) {
    function crm_appointment_lookup_location_name($connection, $location_id) {
        $id = (int) $location_id;
        if ($id <= 0) {
            return '';
        }
        $q = mysqli_query($connection, 'SELECT location_name FROM appointment_locations WHERE location_id=' . $id . ' LIMIT 1');
        if ($q && ($r = mysqli_fetch_assoc($q))) {
            return (string) $r['location_name'];
        }
        return '';
    }
}
if (!function_exists('crm_appointment_lookup_platform_name')) {
    function crm_appointment_lookup_platform_name($connection, $platform_id) {
        $id = (int) $platform_id;
        if ($id <= 0) {
            return '';
        }
        $q = mysqli_query($connection, 'SELECT platform_name FROM appointment_platforms WHERE platform_id=' . $id . ' LIMIT 1');
        if ($q && ($r = mysqli_fetch_assoc($q))) {
            return (string) $r['platform_name'];
        }
        return '';
    }
}
if (!function_exists('crm_appointment_lookup_user_name')) {
    function crm_appointment_lookup_user_name($connection, $user_id) {
        $id = (int) $user_id;
        if ($id <= 0) {
            return '';
        }
        $q = mysqli_query($connection, 'SELECT user_name FROM users WHERE user_id=' . $id . ' LIMIT 1');
        if ($q && ($r = mysqli_fetch_assoc($q))) {
            return (string) $r['user_name'];
        }
        return '';
    }
}
if (!function_exists('crm_send_enquiry_flow_appointment_confirmation_email')) {
    function crm_send_enquiry_flow_appointment_confirmation_email($connection, $student_email, $is_phone_flow, $is_reschedule_flow, array $ctx) {
        $student_email = trim((string) $student_email);
        if ($student_email === '' || !filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        if (!function_exists('send_mail')) {
            require_once __DIR__ . '/mail_function.php';
        }
        $name = trim((string) ($ctx['student_name'] ?? ''));
        $greet = $name !== '' ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : 'there';
        $eq = trim((string) ($ctx['enquiry_id'] ?? ''));
        $purpose = trim((string) ($ctx['purpose_name'] ?? ''));
        if ($purpose === '') {
            $purpose = 'Appointment';
        }
        $dateYmd = trim((string) ($ctx['appointment_date'] ?? ''));
        $t1 = trim((string) ($ctx['appointment_time'] ?? ''));
        $t2 = trim((string) ($ctx['appointment_time_to'] ?? ''));
        if ($t2 === '') {
            $t2 = $t1;
        }
        $tz = trim((string) ($ctx['timezone_state'] ?? ''));
        $meet = trim((string) ($ctx['meeting_type'] ?? ''));
        $loc = trim((string) ($ctx['location_name'] ?? ''));
        $plat = trim((string) ($ctx['platform_name'] ?? ''));
        $link = trim((string) ($ctx['online_meeting_link'] ?? ''));
        $staff = trim((string) ($ctx['staff_name'] ?? ''));
        $booker = trim((string) ($ctx['booked_by_name'] ?? ''));
        $comments = trim((string) ($ctx['booking_comments'] ?? ''));
        $notes = trim((string) ($ctx['appointment_notes'] ?? ''));
        $phone = trim((string) ($ctx['student_phone'] ?? ''));

        $dateNice = '';
        if ($dateYmd !== '' && strtotime($dateYmd)) {
            $dateNice = date('l, j F Y', strtotime($dateYmd));
        }
        $timeRange = '';
        if ($dateYmd !== '' && $t1 !== '' && strtotime($dateYmd . ' ' . $t1)) {
            $ts = date('g:i A', strtotime($dateYmd . ' ' . $t1));
            $te = ($t2 !== '' && strtotime($dateYmd . ' ' . $t2)) ? date('g:i A', strtotime($dateYmd . ' ' . $t2)) : $ts;
            $timeRange = ($ts === $te) ? $ts : ($ts . ' – ' . $te);
            if ($tz !== '') {
                $timeRange .= ' (' . $tz . ')';
            }
        }

        if ($is_reschedule_flow) {
            $tpl_code = 'counselling_rescheduled';
        } elseif ($is_phone_flow) {
            $tpl_code = 'phone_call_booking';
        } else {
            $tpl_code = 'standard_booking';
        }
        $tpl = crm_get_appointment_email_template($connection, $tpl_code);

        $repl = array(
            '{{FirstName}}' => $name !== '' ? trim(strtok($name, ' ')) : 'there',
            '{{StudentName}}' => $name !== '' ? $name : 'there',
            '{{EnquiryID}}' => $eq,
            '{{PurposeName}}' => $purpose,
            '{{AppointmentDate}}' => $dateNice,
            '{{AppointmentTime}}' => $timeRange,
            '{{MeetingType}}' => $meet,
            '{{Location}}' => $loc,
            '{{Platform}}' => $plat,
            '{{MeetingLink}}' => $link,
            '{{StaffName}}' => $staff,
            '{{BookedBy}}' => $booker,
            '{{StudentPhone}}' => $phone,
            '{{BookingComments}}' => $comments,
            '{{AppointmentNotes}}' => $notes,
        );

        $subject_raw = trim((string)($tpl['subject'] ?? ''));
        $body_raw = trim((string)($tpl['body'] ?? ''));
        $subject = strtr($subject_raw !== '' ? $subject_raw : 'Appointment Confirmation – National College Australia', $repl);
        $body_main = strtr($body_raw, $repl);

        $brand = 'National College Australia';
        $html = '<div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;max-width:600px;margin:0 auto;color:#1a1a1a;background:#f1f5f4;padding:24px;">';
        $html .= '<div style="background:#ffffff;border-radius:10px;padding:28px 28px 24px;border:1px solid #dfe7e4;">';
        $html .= '<p style="margin:0 0 6px;font-size:11px;letter-spacing:0.06em;text-transform:uppercase;color:#158887;font-weight:600;">Appointment confirmation</p>';
        $html .= '<p style="margin:0 0 20px;font-size:18px;font-weight:600;color:#0f172a;">' . htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<p style="margin:0 0 18px;font-size:16px;line-height:1.6;color:#334155;">Hi ' . $greet . ',</p>';
        if ($body_main !== '') {
            $html .= '<p style="margin:0 0 22px;font-size:15px;line-height:1.65;color:#334155;">' . nl2br(htmlspecialchars($body_main, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        $html .= '<table cellpadding="0" cellspacing="0" role="presentation" style="width:100%;margin:0 0 22px;font-size:14px;line-height:1.55;color:#334155;">';
        $html .= '<tr><td style="padding:10px 12px;background:#f8fafc;border-radius:6px 6px 0 0;font-weight:600;color:#0f172a;">Your booking summary</td></tr>';
        $html .= '<tr><td style="padding:0;border:1px solid #e2e8f0;border-top:0;border-radius:0 0 6px 6px;background:#fff;">';
        $html .= '<table cellpadding="0" cellspacing="0" style="width:100%;">';

        $row = function ($label, $valHtml) {
            return '<tr><td style="padding:12px 16px;border-bottom:1px solid #f1f5f9;width:36%;vertical-align:top;color:#64748b;font-size:13px;">' . $label . '</td>'
                . '<td style="padding:12px 16px;border-bottom:1px solid #f1f5f9;vertical-align:top;font-weight:500;">' . $valHtml . '</td></tr>';
        };

        $html .= $row('Purpose', htmlspecialchars($purpose, ENT_QUOTES, 'UTF-8'));
        if ($dateNice !== '') {
            $html .= $row('Date', htmlspecialchars($dateNice, ENT_QUOTES, 'UTF-8'));
        }
        if ($timeRange !== '') {
            $html .= $row('Time', htmlspecialchars($timeRange, ENT_QUOTES, 'UTF-8'));
        }
        if ($meet !== '') {
            $html .= $row('Format', htmlspecialchars($meet, ENT_QUOTES, 'UTF-8'));
        }
        if ($staff !== '') {
            $html .= $row('Team member', htmlspecialchars($staff, ENT_QUOTES, 'UTF-8'));
        }
        if ($phone !== '') {
            $html .= $row('Your contact number', htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'));
        }
        if ($eq !== '' && preg_match('/^EQ\d+$/i', $eq)) {
            $html .= $row('Enquiry reference', htmlspecialchars($eq, ENT_QUOTES, 'UTF-8'));
        }
        if ($meet === 'Face to Face' && $loc !== '') {
            $html .= $row('Location', htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'));
        }
        if ($meet === 'Online') {
            if ($plat !== '') {
                $html .= $row('Platform', htmlspecialchars($plat, ENT_QUOTES, 'UTF-8'));
            }
            if ($link !== '') {
                $safe = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
                $html .= $row('Meeting link', '<a href="' . $safe . '" style="color:#0d9488;font-weight:600;">Open meeting link</a>');
            }
        }
        if ($comments !== '') {
            $html .= $row('Message from our team', nl2br(htmlspecialchars($comments, ENT_QUOTES, 'UTF-8')));
        }
        if ($notes !== '') {
            $html .= $row('Additional details', nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')));
        }

        $html .= '</table></td></tr></table>';
        if ($booker !== '') {
            $html .= '<p style="margin:0;font-size:12px;color:#94a3b8;">Arranged by ' . htmlspecialchars($booker, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        $html .= '</div><p style="margin:18px 8px 0;font-size:11px;line-height:1.5;color:#94a3b8;text-align:center;">You received this because an appointment was booked for you in our system. For questions, contact National College Australia using the same channel you used to reach us.</p></div>';

        $st_id_ctx = null;
        if ($eq !== '' && preg_match('/^EQ\d+$/i', $eq)) {
            $eesc = mysqli_real_escape_string($connection, $eq);
            $sr = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_id FROM student_enquiry WHERE st_enquiry_id='$eesc' AND st_enquiry_status!=1 LIMIT 1"));
            if ($sr && isset($sr['st_id'])) {
                $st_id_ctx = (int) $sr['st_id'];
            }
        }
        $mail_ctx_appt = array(
            'email_category' => 'appointment_confirmation',
            'st_enquiry_id' => $eq,
            'meta' => array('flow' => $is_reschedule_flow ? 'reschedule' : ($is_phone_flow ? 'phone' : 'standard')),
        );
        if ($st_id_ctx !== null && $st_id_ctx > 0) {
            $mail_ctx_appt['st_id'] = $st_id_ctx;
        }
        try {
            send_mail($student_email, $subject, $html, $mail_ctx_appt);
        } catch (Throwable $e) {
            // Booking must still succeed if mail transport fails
        }
    }
}

// Appointment Booking
if(@$_POST['formName']=='appointment_booking'){
    $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
    $appointment_date = isset($_POST['appointment_date']) ? mysqli_real_escape_string($connection, trim((string)$_POST['appointment_date'])) : '';
    $appointment_time = isset($_POST['appointment_time']) ? mysqli_real_escape_string($connection, trim((string)$_POST['appointment_time'])) : '';
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    $appointment_time_to = isset($_POST['appointment_time_to']) && $_POST['appointment_time_to'] !== '' ? mysqli_real_escape_string($connection, trim((string)$_POST['appointment_time_to'])) : $appointment_time;
    $appointment_end_datetime = $appointment_date . ' ' . $appointment_time_to;
    $booked_by = isset($_POST['created_by']) ? (int)$_POST['created_by'] : 0;
    $booked_by_name = mysqli_real_escape_string($connection, trim((string)($_POST['booked_by_name'] ?? '')));
    $booking_comments = mysqli_real_escape_string($connection, trim((string)($_POST['booking_comments'] ?? '')));
    $purpose_id = isset($_POST['purpose_id']) && $_POST['purpose_id'] !== '' ? (int)$_POST['purpose_id'] : 0;
    $appointment_to_see = isset($_POST['appointment_to_see']) && $_POST['appointment_to_see'] !== '' ? (int)$_POST['appointment_to_see'] : 0;
    $attendee_type_id = isset($_POST['attendee_type_id']) && $_POST['attendee_type_id'] !== '' ? (int)$_POST['attendee_type_id'] : 1;
    $student_name = mysqli_real_escape_string($connection, trim((string)($_POST['student_name'] ?? '')));
    $student_phone = mysqli_real_escape_string($connection, trim((string)($_POST['student_phone'] ?? '')));
    $student_email = mysqli_real_escape_string($connection, trim((string)($_POST['student_email'] ?? '')));
    $business_name = mysqli_real_escape_string($connection, trim((string)($_POST['business_name'] ?? '')));
    $business_contact = mysqli_real_escape_string($connection, trim((string)($_POST['business_contact'] ?? '')));
    $send_email = isset($_POST['send_email']) ? 1 : 0;
    $staff_member_type = mysqli_real_escape_string($connection, trim((string)($_POST['staff_member_type'] ?? '')));
    $meeting_type = mysqli_real_escape_string($connection, trim((string)($_POST['meeting_type'] ?? '')));
    $location_id = isset($_POST['location_id']) && $_POST['location_id'] != '' ? (int)$_POST['location_id'] : 'NULL';
    $platform_id = isset($_POST['platform_id']) && $_POST['platform_id'] != '' ? (int)$_POST['platform_id'] : 'NULL';
    $online_meeting_link = mysqli_real_escape_string($connection, trim((string)($_POST['online_meeting_link'] ?? '')));
    $timezone_state = mysqli_real_escape_string($connection, trim((string)($_POST['timezone_state'] ?? '')));
    $appointment_time_state = isset($_POST['appointment_time_state']) && $_POST['appointment_time_state'] != '' ? mysqli_real_escape_string($connection, trim((string)$_POST['appointment_time_state'])) : $appointment_datetime;
    $appointment_time_adelaide = isset($_POST['appointment_time_adelaide']) && $_POST['appointment_time_adelaide'] != '' ? mysqli_real_escape_string($connection, trim((string)$_POST['appointment_time_adelaide'])) : $appointment_datetime;
    $appointment_time_india = isset($_POST['appointment_time_india']) && $_POST['appointment_time_india'] != '' ? mysqli_real_escape_string($connection, trim((string)$_POST['appointment_time_india'])) : $appointment_datetime;
    $appointment_time_philippines = isset($_POST['appointment_time_philippines']) && $_POST['appointment_time_philippines'] != '' ? mysqli_real_escape_string($connection, trim((string)$_POST['appointment_time_philippines'])) : $appointment_datetime;
    $auto_phone_flow = isset($_POST['auto_create_enquiry_phone_flow']) && (string)$_POST['auto_create_enquiry_phone_flow'] === '1';
    $auto_couns_resched_flow = isset($_POST['auto_create_enquiry_counselling_reschedule_flow']) && (string)$_POST['auto_create_enquiry_counselling_reschedule_flow'] === '1';
    $set_book_couns = isset($_POST['set_flow_status_booked_counselling']) && (string)$_POST['set_flow_status_booked_counselling'] === '1';
    $set_couns_pending = isset($_POST['set_flow_status_counselling_pending']) && (string)$_POST['set_flow_status_counselling_pending'] === '1';
    $ce_raw_init = isset($_POST['connected_enquiry_id']) ? trim((string)$_POST['connected_enquiry_id']) : '';
    $appointment_return_new_st_id = null;
    if ($auto_phone_flow && $ce_raw_init === '') {
        $cb_email = trim((string)($_POST['cb_email'] ?? ''));
        if ($cb_email === '' || !filter_var($cb_email, FILTER_VALIDATE_EMAIL)) {
            echo 'invalid_email';
            exit;
        }
        $cb_staff = trim((string)($_POST['cb_responsible_staff'] ?? ''));
        if ($cb_staff === '') {
            echo 'contact_phone_staff_required';
            exit;
        }
        $_POST['emailAddress'] = $cb_email;
        $_POST['enquiryFor'] = isset($_POST['cb_enquiry_for']) && $_POST['cb_enquiry_for'] !== '' ? (string)$_POST['cb_enquiry_for'] : '1';
        $_POST['studentName'] = trim((string)($_POST['cb_student_name'] ?? ''));
        $_POST['memberName'] = trim((string)($_POST['cb_member_name'] ?? ''));
        $_POST['contactName'] = trim((string)($_POST['cb_contact_num'] ?? ''));
        $_POST['surname'] = trim((string)($_POST['cb_surname'] ?? ''));
        $admin_id_apt = (int)$_POST['created_by'];
        $ens = crm_ensure_enquiry_from_sidebar_contact($connection, $admin_id_apt);
        if (!$ens['ok']) {
            if (($ens['error'] ?? '') === 'invalid_email') {
                echo 'invalid_email';
                exit;
            }
            echo 0;
            exit;
        }
        $new_eq = (string)($ens['enquiry_id'] ?? '');
        if ($new_eq === '') {
            echo 0;
            exit;
        }
        $new_eq_esc = mysqli_real_escape_string($connection, $new_eq);
        $loc_esc = mysqli_real_escape_string($connection, trim((string)($_POST['cb_location'] ?? '')));
        $staff_esc = mysqli_real_escape_string($connection, $cb_staff);
        mysqli_query($connection, "UPDATE student_enquiry SET st_enquiry_source=2, st_enquiry_flow_status=9, st_location='$loc_esc', st_hearedby='$staff_esc' WHERE st_enquiry_id='$new_eq_esc' AND st_enquiry_status!=1 LIMIT 1");
        $_POST['connected_enquiry_id'] = $new_eq;
        $appointment_return_new_st_id = isset($ens['st_id']) ? (int)$ens['st_id'] : 0;
    }
    $ce_after_phone = isset($_POST['connected_enquiry_id']) ? trim((string)$_POST['connected_enquiry_id']) : '';
    if ($auto_couns_resched_flow && $ce_after_phone === '') {
        $cb_email = trim((string)($_POST['cb_email'] ?? ''));
        if ($cb_email === '' || !filter_var($cb_email, FILTER_VALIDATE_EMAIL)) {
            echo 'invalid_email';
            exit;
        }
        $_POST['emailAddress'] = $cb_email;
        $_POST['enquiryFor'] = isset($_POST['cb_enquiry_for']) && $_POST['cb_enquiry_for'] !== '' ? (string)$_POST['cb_enquiry_for'] : '1';
        $_POST['studentName'] = trim((string)($_POST['cb_student_name'] ?? ''));
        $_POST['memberName'] = trim((string)($_POST['cb_member_name'] ?? ''));
        $_POST['contactName'] = trim((string)($_POST['cb_contact_num'] ?? ''));
        $_POST['surname'] = trim((string)($_POST['cb_surname'] ?? ''));
        $admin_id_apt2 = (int)$_POST['created_by'];
        $ens2 = crm_ensure_enquiry_from_sidebar_contact($connection, $admin_id_apt2);
        if (!$ens2['ok']) {
            if (($ens2['error'] ?? '') === 'invalid_email') {
                echo 'invalid_email';
                exit;
            }
            echo 0;
            exit;
        }
        $new_eq2 = (string)($ens2['enquiry_id'] ?? '');
        if ($new_eq2 === '') {
            echo 0;
            exit;
        }
        $new_eq2_esc = mysqli_real_escape_string($connection, $new_eq2);
        mysqli_query($connection, "UPDATE student_enquiry SET st_enquiry_flow_status=11 WHERE st_enquiry_id='$new_eq2_esc' AND st_enquiry_status!=1 LIMIT 1");
        $_POST['connected_enquiry_id'] = $new_eq2;
        $appointment_return_new_st_id = isset($ens2['st_id']) ? (int)$ens2['st_id'] : 0;
    }
    $ce_for_sql = isset($_POST['connected_enquiry_id']) ? trim((string)$_POST['connected_enquiry_id']) : '';
    $connected_enquiry_id = $ce_for_sql !== '' ? "'" . mysqli_real_escape_string($connection, $ce_for_sql) . "'" : 'NULL';
    $connected_enrolment_id = isset($_POST['connected_enrolment_id']) && $_POST['connected_enrolment_id'] != '' ? "'" . mysqli_real_escape_string($connection, trim((string)$_POST['connected_enrolment_id'])) . "'" : 'NULL';
    $connected_counselling_id = isset($_POST['connected_counselling_id']) && $_POST['connected_counselling_id'] != '' ? (int)$_POST['connected_counselling_id'] : 'NULL';
    $appointment_notes = mysqli_real_escape_string($connection, trim((string)($_POST['appointment_notes'] ?? '')));
    // Share with (visibility)
    $share_with = isset($_POST['share_with']) ? $_POST['share_with'] : array();
    $appointment_shared_with = '';
    if(is_array($share_with) && count($share_with)){
        $ids = array();
        foreach($share_with as $sid){
            $ids[] = (int)$sid;
        }
        $appointment_shared_with = implode(',', $ids);
    }
    $created_by = isset($_POST['created_by']) ? (int)$_POST['created_by'] : 0;
    
    // Prevent double-booking: same attendee (student phone/email or business contact) at same start time
    if($appointment_id == '0'){
        $conflict_where = "appointment_datetime='$appointment_datetime' AND delete_status!=1";
    } else {
        $conflict_where = "appointment_datetime='$appointment_datetime' AND delete_status!=1 AND appointment_id!=$appointment_id";
    }
    $attendee_where = array();
    if($student_phone !== '') $attendee_where[] = "(student_phone='".mysqli_real_escape_string($connection,$student_phone)."')";
    if($student_email !== '') $attendee_where[] = "(student_email='".mysqli_real_escape_string($connection,$student_email)."')";
    if($business_contact !== '') $attendee_where[] = "(business_contact='".mysqli_real_escape_string($connection,$business_contact)."')";
    if(count($attendee_where)){
        $check_sql = "SELECT COUNT(*) FROM appointments WHERE ".$conflict_where." AND (".implode(' OR ',$attendee_where).")";
        $conf_res = mysqli_query($connection,$check_sql);
        if($conf_res && ($cr = mysqli_fetch_row($conf_res)) && (int)$cr[0] > 0){
            echo 2;
            exit;
        }
    }

    // Prevent booking inside blocked appointment slots for this staff (or all-staff blocks)
    // Rules:
    // - Rows with block_staff_member_id IS NULL are treated as "All Staff" blocks
    // - Rows with a specific block_staff_member_id only block that particular staff
    $staffId = (int)$appointment_to_see;
    $blockDateEsc = mysqli_real_escape_string($connection, $appointment_date);
    $startTimeEsc = mysqli_real_escape_string($connection, $appointment_time);
    $endTimeEsc = mysqli_real_escape_string($connection, $appointment_time_to);
    $blockCheckSql = "
        SELECT COUNT(*) 
        FROM appointment_blocks 
        WHERE block_status != 1
          AND block_date = '{$blockDateEsc}'
          AND (
                block_staff_member_id IS NULL
                OR block_staff_member_id = {$staffId}
              )
          AND NOT (
                block_end_time <= '{$startTimeEsc}'
                OR block_start_time >= '{$endTimeEsc}'
              )";
    $blk_res = mysqli_query($connection, $blockCheckSql);
    if($blk_res && ($br = mysqli_fetch_row($blk_res)) && (int)$br[0] > 0){
        // Code 3: time slot falls in a blocked period
        echo 3;
        exit;
    }

    // Check if end-time columns exist (for backward compatibility)
    $has_end_cols = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM appointments LIKE 'appointment_end_time'"));

    if($appointment_id == '0'){
        // Insert new appointment
        if($has_end_cols){
            $query = "INSERT INTO appointments (appointment_date, appointment_time, appointment_end_time, appointment_datetime, appointment_end_datetime, booked_by, booked_by_name, booking_comments, purpose_id, appointment_to_see, attendee_type_id, student_name, student_phone, student_email, business_name, business_contact, send_email, staff_member_type, meeting_type, location_id, platform_id, online_meeting_link, timezone_state, appointment_time_state, appointment_time_adelaide, appointment_time_india, appointment_time_philippines, connected_enquiry_id, connected_enrolment_id, connected_counselling_id, appointment_notes, appointment_shared_with, created_by) VALUES ('$appointment_date', '$appointment_time', '$appointment_time_to', '$appointment_datetime', '$appointment_end_datetime', $booked_by, '$booked_by_name', '$booking_comments', $purpose_id, $appointment_to_see, $attendee_type_id, '$student_name', '$student_phone', '$student_email', '$business_name', '$business_contact', $send_email, '$staff_member_type', '$meeting_type', $location_id, $platform_id, '$online_meeting_link', '$timezone_state', '$appointment_time_state', '$appointment_time_adelaide', '$appointment_time_india', '$appointment_time_philippines', $connected_enquiry_id, $connected_enrolment_id, $connected_counselling_id, '$appointment_notes', ".($appointment_shared_with==='' ? "NULL" : "'$appointment_shared_with'").", $created_by)";
        } else {
            $query = "INSERT INTO appointments (appointment_date, appointment_time, appointment_datetime, booked_by, booked_by_name, booking_comments, purpose_id, appointment_to_see, attendee_type_id, student_name, student_phone, student_email, business_name, business_contact, send_email, staff_member_type, meeting_type, location_id, platform_id, online_meeting_link, timezone_state, appointment_time_state, appointment_time_adelaide, appointment_time_india, appointment_time_philippines, connected_enquiry_id, connected_enrolment_id, connected_counselling_id, appointment_notes, created_by) VALUES ('$appointment_date', '$appointment_time', '$appointment_datetime', $booked_by, '$booked_by_name', '$booking_comments', $purpose_id, $appointment_to_see, $attendee_type_id, '$student_name', '$student_phone', '$student_email', '$business_name', '$business_contact', $send_email, '$staff_member_type', '$meeting_type', $location_id, $platform_id, '$online_meeting_link', '$timezone_state', '$appointment_time_state', '$appointment_time_adelaide', '$appointment_time_india', '$appointment_time_philippines', $connected_enquiry_id, $connected_enrolment_id, $connected_counselling_id, '$appointment_notes', $created_by)";
        }
    } else {
        // Update existing appointment
        if($has_end_cols){
            $query = "UPDATE appointments SET appointment_date='$appointment_date', appointment_time='$appointment_time', appointment_end_time='$appointment_time_to', appointment_datetime='$appointment_datetime', appointment_end_datetime='$appointment_end_datetime', booked_by_name='$booked_by_name', booking_comments='$booking_comments', purpose_id=$purpose_id, appointment_to_see=$appointment_to_see, attendee_type_id=$attendee_type_id, student_name='$student_name', student_phone='$student_phone', student_email='$student_email', business_name='$business_name', business_contact='$business_contact', send_email=$send_email, staff_member_type='$staff_member_type', meeting_type='$meeting_type', location_id=$location_id, platform_id=$platform_id, online_meeting_link='$online_meeting_link', timezone_state='$timezone_state', appointment_time_state='$appointment_time_state', appointment_time_adelaide='$appointment_time_adelaide', appointment_time_india='$appointment_time_india', appointment_time_philippines='$appointment_time_philippines', connected_enquiry_id=$connected_enquiry_id, connected_enrolment_id=$connected_enrolment_id, connected_counselling_id=$connected_counselling_id, appointment_notes='$appointment_notes', appointment_shared_with=".($appointment_shared_with==='' ? "NULL" : "'$appointment_shared_with'").", modified_date=NOW(), modified_by=$created_by WHERE appointment_id=$appointment_id";
        } else {
            $query = "UPDATE appointments SET appointment_date='$appointment_date', appointment_time='$appointment_time', appointment_datetime='$appointment_datetime', booked_by_name='$booked_by_name', booking_comments='$booking_comments', purpose_id=$purpose_id, appointment_to_see=$appointment_to_see, attendee_type_id=$attendee_type_id, student_name='$student_name', student_phone='$student_phone', student_email='$student_email', business_name='$business_name', business_contact='$business_contact', send_email=$send_email, staff_member_type='$staff_member_type', meeting_type='$meeting_type', location_id=$location_id, platform_id=$platform_id, online_meeting_link='$online_meeting_link', timezone_state='$timezone_state', appointment_time_state='$appointment_time_state', appointment_time_adelaide='$appointment_time_adelaide', appointment_time_india='$appointment_time_india', appointment_time_philippines='$appointment_time_philippines', connected_enquiry_id=$connected_enquiry_id, connected_enrolment_id=$connected_enrolment_id, connected_counselling_id=$connected_counselling_id, appointment_notes='$appointment_notes', modified_date=NOW(), modified_by=$created_by WHERE appointment_id=$appointment_id";
        }
    }
    
    $result = mysqli_query($connection, $query);
    $error = mysqli_error($connection);
    
    if($error != ''){
        echo 0;
    } else {
        $appt_id = $appointment_id == '0' ? mysqli_insert_id($connection) : $appointment_id;
        
        // Automatic polished student email for enquiry flows (phone call from contact bar / counselling reschedule)
        $auto_flow_mail = ($auto_phone_flow || $auto_couns_resched_flow) && $student_email !== '' && filter_var(trim($student_email), FILTER_VALIDATE_EMAIL);
        if ($auto_flow_mail) {
            $purpose_n = getPurposeName($connection, (int) $purpose_id);
            if (!is_string($purpose_n) || $purpose_n === '') {
                $purpose_n = 'Appointment';
            }
            $loc_n = ($location_id !== 'NULL' && $location_id !== '' && is_numeric($location_id)) ? crm_appointment_lookup_location_name($connection, $location_id) : '';
            $plat_n = ($platform_id !== 'NULL' && $platform_id !== '' && is_numeric($platform_id)) ? crm_appointment_lookup_platform_name($connection, $platform_id) : '';
            $staff_n = crm_appointment_lookup_user_name($connection, (int) $appointment_to_see);
            $eq_conn = isset($_POST['connected_enquiry_id']) ? trim((string) $_POST['connected_enquiry_id']) : '';
            crm_send_enquiry_flow_appointment_confirmation_email(
                $connection,
                $student_email,
                (bool) $auto_phone_flow,
                (bool) $auto_couns_resched_flow,
                array(
                    'student_name' => $student_name,
                    'student_phone' => $student_phone,
                    'enquiry_id' => $eq_conn,
                    'purpose_name' => $purpose_n,
                    'appointment_date' => $appointment_date,
                    'appointment_time' => $appointment_time,
                    'appointment_time_to' => $appointment_time_to,
                    'timezone_state' => $timezone_state,
                    'meeting_type' => $meeting_type,
                    'location_name' => $loc_n,
                    'platform_name' => $plat_n,
                    'online_meeting_link' => $online_meeting_link,
                    'staff_name' => $staff_n,
                    'booked_by_name' => $booked_by_name,
                    'booking_comments' => $booking_comments,
                    'appointment_notes' => $appointment_notes,
                )
            );
        }

        // Manual “send email” from appointment form: use the same detailed template.
        if ($send_email == 1 && $student_email !== '' && !$auto_flow_mail) {
            $pn = getPurposeName($connection, (int) $purpose_id);
            if (!is_string($pn) || $pn === '') {
                $pn = 'Appointment';
            }
            $loc_n = ($location_id !== 'NULL' && $location_id !== '' && is_numeric($location_id)) ? crm_appointment_lookup_location_name($connection, $location_id) : '';
            $plat_n = ($platform_id !== 'NULL' && $platform_id !== '' && is_numeric($platform_id)) ? crm_appointment_lookup_platform_name($connection, $platform_id) : '';
            $staff_n = crm_appointment_lookup_user_name($connection, (int) $appointment_to_see);
            $eq_manual = isset($_POST['connected_enquiry_id']) ? trim((string) $_POST['connected_enquiry_id']) : '';
            crm_send_enquiry_flow_appointment_confirmation_email(
                $connection,
                $student_email,
                false,
                false,
                array(
                    'student_name' => $student_name,
                    'student_phone' => $student_phone,
                    'enquiry_id' => $eq_manual,
                    'purpose_name' => $pn,
                    'appointment_date' => $appointment_date,
                    'appointment_time' => $appointment_time,
                    'appointment_time_to' => $appointment_time_to,
                    'timezone_state' => $timezone_state,
                    'meeting_type' => $meeting_type,
                    'location_name' => $loc_n,
                    'platform_name' => $plat_n,
                    'online_meeting_link' => $online_meeting_link,
                    'staff_name' => $staff_n,
                    'booked_by_name' => $booked_by_name,
                    'booking_comments' => $booking_comments,
                    'appointment_notes' => $appointment_notes,
                )
            );
        }

        if ($set_book_couns) {
            $eq_for_status = isset($_POST['connected_enquiry_id']) ? trim((string)$_POST['connected_enquiry_id']) : '';
            if ($eq_for_status !== '' && preg_match('/^EQ\d+$/i', $eq_for_status)) {
                $esc = mysqli_real_escape_string($connection, $eq_for_status);
                $loc_esc = mysqli_real_escape_string($connection, trim((string)($_POST['cb_location'] ?? '')));
                $staff_raw = trim((string)($_POST['cb_responsible_staff'] ?? ''));
                $staff_esc = mysqli_real_escape_string($connection, $staff_raw);
                $upd = "UPDATE student_enquiry SET st_enquiry_flow_status=9, st_enquiry_source=2, st_location='$loc_esc'";
                if ($staff_raw !== '') {
                    $upd .= ", st_hearedby='$staff_esc'";
                }
                $upd .= " WHERE st_enquiry_id='$esc' AND st_enquiry_status!=1 LIMIT 1";
                mysqli_query($connection, $upd);
            }
        } elseif ($set_couns_pending) {
            $eq_cp = isset($_POST['connected_enquiry_id']) ? trim((string)$_POST['connected_enquiry_id']) : '';
            if ($eq_cp !== '' && preg_match('/^EQ\d+$/i', $eq_cp)) {
                $escp = mysqli_real_escape_string($connection, $eq_cp);
                mysqli_query($connection, "UPDATE student_enquiry SET st_enquiry_flow_status=11 WHERE st_enquiry_id='$escp' AND st_enquiry_status!=1 LIMIT 1");
            }
        }

        if ($appointment_return_new_st_id !== null && $appointment_return_new_st_id > 0) {
            echo '1|' . $appointment_return_new_st_id;
        } else {
            echo 1;
        }
    }
}

// Get appointments for calendar
if(@$_POST['formName']=='get_appointments_calendar'){
    $start = $_POST['start'];
    $end = $_POST['end'];
    $staff_filter = isset($_POST['staff_filter']) ? (int)$_POST['staff_filter'] : 0;

    // Use appointment_date to ensure all appointments on those days are returned,
    // regardless of time or end-time, then pass precise start/end to FullCalendar.
    $startDate = date('Y-m-d', strtotime($start));
    // FullCalendar's end is exclusive, so subtract one day for inclusive date filter
    $endDate = date('Y-m-d', strtotime($end . ' -1 day'));

    $has_end_cols = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM appointments LIKE 'appointment_end_datetime'"));
    $query = "SELECT a.*, p.purpose_name, p.purpose_color FROM appointments a 
              LEFT JOIN appointment_purposes p ON a.purpose_id = p.purpose_id 
              WHERE a.delete_status != 1
              AND a.appointment_date >= '$startDate'
              AND a.appointment_date <= '$endDate'";

    // Restrict visibility for non-admin users:
    // allow appointments that are public/shared OR directly related to the logged-in user.
    $currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $currentUserType = isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 0;
    if($currentUserId && $currentUserType !== 1){
        $query .= " AND (a.appointment_shared_with IS NULL 
                         OR a.appointment_shared_with = '' 
                         OR a.appointment_shared_with = 'ALL' 
                         OR FIND_IN_SET($currentUserId, a.appointment_shared_with)
                         OR a.appointment_to_see = $currentUserId
                         OR a.created_by = $currentUserId
                         OR a.booked_by = $currentUserId)";
    }

    if($staff_filter > 0){
        $query .= " AND a.appointment_to_see = $staff_filter";
    }

            //   echo $query;
    
    $result = mysqli_query($connection, $query);
    $events = array();
    
    while($row = mysqli_fetch_array($result)){
        $title = $row['purpose_name'];
        if($row['student_name'] != ''){
            $title .= ' - ' . $row['student_name'];
        } else if($row['business_name'] != ''){
            $title .= ' - ' . $row['business_name'];
        }
        
        $event = array(
            'id' => $row['appointment_id'],
            'title' => $title,
            'start' => $row['appointment_datetime'],
            'color' => $row['purpose_color'],
            'extendedProps' => array(
                'status' => $row['appointment_status'],
                'purpose' => $row['purpose_name']
            )
        );
        if($has_end_cols && isset($row['appointment_end_datetime']) && !empty($row['appointment_end_datetime'])){
            $event['end'] = $row['appointment_end_datetime'];
        }
        $events[] = $event;
    }
    
    // Also include blocked slots in calendar
    $blockQuery = "SELECT b.*, u.user_name AS blocked_staff_name, ub.user_name AS blocked_by_name
                   FROM appointment_blocks b
                   LEFT JOIN users u ON b.block_staff_member_id = u.user_id
                   LEFT JOIN users ub ON b.created_by = ub.user_id
                   WHERE b.block_status != 1
                   AND b.block_date >= '$startDate'
                   AND b.block_date <= '$endDate'";

    // Non-admin users should only see all-staff blocks and blocks relevant to them
    if($currentUserId && $currentUserType !== 1){
        $blockQuery .= " AND (b.block_staff_member_id IS NULL OR b.block_staff_member_id = $currentUserId)";
    }

    if($staff_filter > 0){
        // For staff filter, include global(all staff) blocks and selected staff blocks
        $blockQuery .= " AND (b.block_staff_member_id IS NULL OR b.block_staff_member_id = $staff_filter)";
    }

    $blockResult = mysqli_query($connection, $blockQuery);
    if($blockResult){
        while($brow = mysqli_fetch_array($blockResult)){
            $blockStart = $brow['block_date'] . ' ' . $brow['block_start_time'];
            $blockEnd = $brow['block_date'] . ' ' . $brow['block_end_time'];
            $blockedFor = !empty($brow['blocked_staff_name']) ? $brow['blocked_staff_name'] : 'All Staff';
            $blockedBy = !empty($brow['blocked_by_name']) ? $brow['blocked_by_name'] : 'Unknown';
            $blockReason = trim((string)($brow['block_reason'] ?? ''));

            $title = 'BLOCKED - ' . $blockedFor . ' (By: ' . $blockedBy . ')';
            if($blockReason !== ''){
                $title .= ' - ' . $blockReason;
            }

            $events[] = array(
                'id' => 'block_' . $brow['block_id'],
                'title' => $title,
                'start' => $blockStart,
                'end' => $blockEnd,
                'color' => '#ff3d60',
                'extendedProps' => array(
                    'event_type' => 'blocked',
                    'block_id' => $brow['block_id'],
                    'blocked_for' => $blockedFor,
                    'blocked_by' => $blockedBy,
                    'reason' => $blockReason
                )
            );
        }
    }

    echo json_encode($events);
}

// Get appointment details
if(@$_POST['formName']=='get_appointment_details'){
    $appointment_id = $_POST['appointment_id'];
    
    $query = "SELECT a.*, p.purpose_name, p.purpose_color, at.type_name as attendee_type, l.location_name, pl.platform_name, u.user_name as staff_name 
              FROM appointments a 
              LEFT JOIN appointment_purposes p ON a.purpose_id = p.purpose_id 
              LEFT JOIN appointment_attendee_types at ON a.attendee_type_id = at.type_id 
              LEFT JOIN appointment_locations l ON a.location_id = l.location_id 
              LEFT JOIN appointment_platforms pl ON a.platform_id = pl.platform_id 
              LEFT JOIN users u ON a.appointment_to_see = u.user_id 
              WHERE a.appointment_id = $appointment_id";
    
    $result = mysqli_query($connection, $query);
    $appointment = mysqli_fetch_array($result);
    
    $html = '<input type="hidden" id="appointment_status_hidden" value="'.$appointment['appointment_status'].'">';
    $html .= '<div class="row">';
    $html .= '<div class="col-md-6"><strong>Date & Time:</strong></div><div class="col-md-6">' . date('d M Y h:i A', strtotime($appointment['appointment_datetime'])) . '</div>';
    $html .= '<div class="col-md-6"><strong>Purpose:</strong></div><div class="col-md-6"><span class="color-preview" style="background:'.$appointment['purpose_color'].'"></span>' . $appointment['purpose_name'] . '</div>';
    $html .= '<div class="col-md-6"><strong>Status:</strong></div><div class="col-md-6"><span class="status-badge status-'.$appointment['appointment_status'].'">' . ucfirst(str_replace('-', ' ', $appointment['appointment_status'])) . '</span></div>';
    $html .= '<div class="col-md-6"><strong>Booked By:</strong></div><div class="col-md-6">' . $appointment['booked_by_name'] . '</div>';
    $html .= '<div class="col-md-6"><strong>Staff Member:</strong></div><div class="col-md-6">' . $appointment['staff_name'] . ' (' . $appointment['staff_member_type'] . ')</div>';
    $html .= '<div class="col-md-6"><strong>Meeting Type:</strong></div><div class="col-md-6">' . $appointment['meeting_type'] . '</div>';
    
    if($appointment['location_name']){
        $html .= '<div class="col-md-6"><strong>Location:</strong></div><div class="col-md-6">' . $appointment['location_name'] . '</div>';
    }
    
    if($appointment['platform_name']){
        $html .= '<div class="col-md-6"><strong>Platform:</strong></div><div class="col-md-6">' . $appointment['platform_name'] . '</div>';
    }
    
    if($appointment['student_name']){
        $html .= '<div class="col-md-6"><strong>Student Name:</strong></div><div class="col-md-6">' . $appointment['student_name'] . '</div>';
        $html .= '<div class="col-md-6"><strong>Student Phone:</strong></div><div class="col-md-6">' . $appointment['student_phone'] . '</div>';
        $html .= '<div class="col-md-6"><strong>Student Email:</strong></div><div class="col-md-6">' . $appointment['student_email'] . '</div>';
    }
    
    if($appointment['business_name']){
        $html .= '<div class="col-md-6"><strong>Business Name:</strong></div><div class="col-md-6">' . $appointment['business_name'] . '</div>';
        $html .= '<div class="col-md-6"><strong>Business Contact:</strong></div><div class="col-md-6">' . $appointment['business_contact'] . '</div>';
    }
    
    if($appointment['time_in']){
        $html .= '<div class="col-md-6"><strong>Time In:</strong></div><div class="col-md-6">' . date('d M Y h:i A', strtotime($appointment['time_in'])) . '</div>';
    }
    
    if($appointment['time_out']){
        $html .= '<div class="col-md-6"><strong>Time Out:</strong></div><div class="col-md-6">' . date('d M Y h:i A', strtotime($appointment['time_out'])) . '</div>';
    }
    
    if($appointment['appointment_notes']){
        $html .= '<div class="col-md-12 mt-3"><strong>Notes:</strong><br>' . nl2br($appointment['appointment_notes']) . '</div>';
    }
    
    $html .= '</div>';
    
    echo $html;
}

// Update appointment status
if(@$_POST['formName']=='update_appointment_status'){
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    $meeting_happened = ($status == 'completed') ? 1 : 0;
    
    $query = "UPDATE appointments SET appointment_status='$status', meeting_happened=$meeting_happened, modified_date=NOW() WHERE appointment_id=$appointment_id";
    $result = mysqli_query($connection, $query);
    
    echo $result ? 1 : 0;
    exit;
}

// Record time in/out
if(@$_POST['formName']=='record_time_in_out'){
    $appointment_id = $_POST['appointment_id'];
    $type = $_POST['type'];
    $now = date('Y-m-d H:i:s');
    
    if($type == 'in'){
        $query = "UPDATE appointments SET time_in='$now', modified_date=NOW() WHERE appointment_id=$appointment_id";
    } else {
        $query = "UPDATE appointments SET time_out='$now', modified_date=NOW() WHERE appointment_id=$appointment_id";
    }
    
    $result = mysqli_query($connection, $query);
    echo $result ? 1 : 0;
    exit;
}

// Get appointment reports
if(@$_POST['formName']=='get_appointment_reports'){
    $date_range = $_POST['date_range'];
    $start_date = '';
    $end_date = '';
    $status_filter = isset($_POST['status_filter']) ? trim($_POST['status_filter']) : '';
    $staff_filter = isset($_POST['staff_filter']) ? (int)$_POST['staff_filter'] : 0;
    $purpose_filter = isset($_POST['purpose_filter']) ? (int)$_POST['purpose_filter'] : 0;
    
    if($date_range == 'today'){
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
    } else if($date_range == 'tomorrow'){
        $start_date = date('Y-m-d', strtotime('+1 day'));
        $end_date = $start_date;
    } else if($date_range == 'week'){
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d', strtotime('sunday this week'));
    } else if($date_range == 'month'){
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
    } else {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
    }
    
    $query = "SELECT a.*, p.purpose_name, at.type_name as attendee_type, u.user_name as staff_name 
              FROM appointments a 
              LEFT JOIN appointment_purposes p ON a.purpose_id = p.purpose_id 
              LEFT JOIN appointment_attendee_types at ON a.attendee_type_id = at.type_id 
              LEFT JOIN users u ON a.appointment_to_see = u.user_id 
              WHERE a.delete_status != 1 AND DATE(a.appointment_date) >= '$start_date' AND DATE(a.appointment_date) <= '$end_date'";

    if($status_filter !== ''){
        $status_filter_esc = mysqli_real_escape_string($connection, $status_filter);
        $query .= " AND a.appointment_status = '$status_filter_esc'";
    }
    if($staff_filter > 0){
        $query .= " AND a.appointment_to_see = $staff_filter";
    }
    if($purpose_filter > 0){
        $query .= " AND a.purpose_id = $purpose_filter";
    }

    // Reports page should show complete data across staff/admin (controlled by filters),
    // so do not apply Share With restrictions here.
    
    $result = mysqli_query($connection, $query);
    
    $summary = array('total' => 0, 'attended' => 0, 'missed' => 0, 'cancelled' => 0);
    $statusData = array('labels' => array(), 'values' => array());
    $purposeData = array('labels' => array(), 'values' => array());
    $staffData = array('labels' => array(), 'values' => array());
    $dailyData = array('labels' => array(), 'values' => array());
    $appointments = array();
    
    $statusCounts = array();
    $purposeCounts = array();
    $staffCounts = array();
    $dailyCounts = array();
    $has_end_cols = mysqli_fetch_assoc(mysqli_query($connection, "SHOW COLUMNS FROM appointments LIKE 'appointment_end_datetime'"));
    
    while($row = mysqli_fetch_array($result)){
        $summary['total']++;
        
        if($row['appointment_status'] == 'completed'){
            $summary['attended']++;
        } else if(in_array($row['appointment_status'], array('no-show', 'missed'))){
            $summary['missed']++;
        } else if($row['appointment_status'] == 'cancelled'){
            $summary['cancelled']++;
        }
        
        // Status counts
        $status = ucfirst(str_replace('-', ' ', $row['appointment_status']));
        if(!isset($statusCounts[$status])){
            $statusCounts[$status] = 0;
        }
        $statusCounts[$status]++;
        
        // Purpose counts
        $purpose = $row['purpose_name'];
        if(!isset($purposeCounts[$purpose])){
            $purposeCounts[$purpose] = 0;
        }
        $purposeCounts[$purpose]++;
        
        // Staff counts
        $staff = $row['staff_name'];
        if(!isset($staffCounts[$staff])){
            $staffCounts[$staff] = 0;
        }
        $staffCounts[$staff]++;
        
        // Daily counts
        $day = date('Y-m-d', strtotime($row['appointment_date']));
        if(!isset($dailyCounts[$day])){
            $dailyCounts[$day] = 0;
        }
        $dailyCounts[$day]++;
        
        // Appointment details
        $startTime = date('h:i A', strtotime($row['appointment_datetime']));
        $endTime = ($has_end_cols && !empty($row['appointment_end_datetime'])) ? date('h:i A', strtotime($row['appointment_end_datetime'])) : '';
        $timeSlot = $endTime ? $startTime . ' - ' . $endTime : $startTime;

        $appointments[] = array(
            'id' => $row['appointment_id'],
            'datetime' => date('d M Y h:i A', strtotime($row['appointment_datetime'])),
            'date_display' => date('d M Y', strtotime($row['appointment_datetime'])),
            'date_raw' => $day,
            'time_slot' => $timeSlot,
            'purpose' => $row['purpose_name'],
            'attendee' => $row['student_name'] ? $row['student_name'] : ($row['business_name'] ? $row['business_name'] : '-'),
            'staff' => $row['staff_name'],
            'status' => $row['appointment_status'],
            'meeting_type' => $row['meeting_type']
        );
    }
    
    // Format chart data
    foreach($statusCounts as $label => $value){
        $statusData['labels'][] = $label;
        $statusData['values'][] = $value;
    }
    
    foreach($purposeCounts as $label => $value){
        $purposeData['labels'][] = $label;
        $purposeData['values'][] = $value;
    }
    
    foreach($staffCounts as $label => $value){
        $staffData['labels'][] = $label;
        $staffData['values'][] = $value;
    }
    
    ksort($dailyCounts);
    foreach($dailyCounts as $label => $value){
        $dailyData['labels'][] = date('d M', strtotime($label));
        $dailyData['values'][] = $value;
    }
    
    $response = array(
        'summary' => $summary,
        'charts' => array(
            'status' => $statusData,
            'purpose' => $purposeData,
            'staff' => $staffData,
            'daily' => $dailyData
        ),
        'appointments' => $appointments
    );
    
    echo json_encode($response);
}

// Manage purposes
if(@$_POST['formName']=='get_purposes'){
    $query = "SELECT * FROM appointment_purposes WHERE purpose_status != 1 ORDER BY purpose_name";
    $result = mysqli_query($connection, $query);
    
    $html = '<table class="table table-sm"><thead><tr><th>Purpose</th><th>Color</th><th>Actions</th></tr></thead><tbody>';
    while($row = mysqli_fetch_array($result)){
        $html .= '<tr>';
        $html .= '<td>'.$row['purpose_name'].'</td>';
        $html .= '<td><span class="color-preview" style="background:'.$row['purpose_color'].'"></span></td>';
        $html .= '<td><button class="btn btn-sm btn-danger" onclick="deletePurpose('.$row['purpose_id'].')">Delete</button></td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    
    echo $html;
}

if(@$_POST['formName']=='add_purpose'){
    $purpose_name = $_POST['purpose_name'];
    $purpose_color = $_POST['purpose_color'];
    
    $query = "INSERT INTO appointment_purposes (purpose_name, purpose_color) VALUES ('$purpose_name', '$purpose_color')";
    $result = mysqli_query($connection, $query);
    
    echo $result ? 1 : 0;
}

// Manage attendee types
if(@$_POST['formName']=='get_attendee_types'){
    $query = "SELECT * FROM appointment_attendee_types WHERE type_status != 1 ORDER BY type_name";
    $result = mysqli_query($connection, $query);
    
    $html = '<table class="table table-sm"><thead><tr><th>Type</th><th>Actions</th></tr></thead><tbody>';
    while($row = mysqli_fetch_array($result)){
        $html .= '<tr>';
        $html .= '<td>'.$row['type_name'].'</td>';
        $html .= '<td><button class="btn btn-sm btn-danger" onclick="deleteAttendeeType('.$row['type_id'].')">Delete</button></td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    
    echo $html;
}
    
if(@$_POST['formName']=='add_attendee_type'){
    $type_name = $_POST['type_name'];
    
    $query = "INSERT INTO appointment_attendee_types (type_name) VALUES ('$type_name')";
    $result = mysqli_query($connection, $query);
    
    echo $result ? 1 : 0;
}

// Manage locations
if(@$_POST['formName']=='get_locations'){
    $query = "SELECT * FROM appointment_locations WHERE location_status != 1 ORDER BY location_name";
    $result = mysqli_query($connection, $query);
    
    $html = '<table class="table table-sm"><thead><tr><th>Location</th><th>Actions</th></tr></thead><tbody>';
    while($row = mysqli_fetch_array($result)){
        $html .= '<tr>';
        $html .= '<td>'.$row['location_name'].'</td>';
        $html .= '<td><button class="btn btn-sm btn-danger" onclick="deleteLocation('.$row['location_id'].')">Delete</button></td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    
    echo $html;
}

if(@$_POST['formName']=='add_location'){
    $location_name = $_POST['location_name'];
    
    $query = "INSERT INTO appointment_locations (location_name) VALUES ('$location_name')";
    $result = mysqli_query($connection, $query);
    
    echo $result ? 1 : 0;
}

// Manage platforms
if(@$_POST['formName']=='get_platforms'){
    $query = "SELECT * FROM appointment_platforms WHERE platform_status != 1 ORDER BY platform_name";
    $result = mysqli_query($connection, $query);
    
    $html = '<table class="table table-sm"><thead><tr><th>Platform</th><th>Actions</th></tr></thead><tbody>';
    while($row = mysqli_fetch_array($result)){
        $html .= '<tr>';
        $html .= '<td>'.$row['platform_name'].'</td>';
        $html .= '<td><button class="btn btn-sm btn-danger" onclick="deletePlatform('.$row['platform_id'].')">Delete</button></td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    
    echo $html;
}

if(@$_POST['formName']=='add_platform'){
    $platform_name = $_POST['platform_name'];
    
    $query = "INSERT INTO appointment_platforms (platform_name) VALUES ('$platform_name')";
    $result = mysqli_query($connection, $query);
    
    echo $result ? 1 : 0;
}

// Appointment blocks
if(@$_POST['formName']=='add_appointment_block'){
    $block_date = $_POST['block_date'];
    $block_start_time = $_POST['block_start_time'];
    $block_end_time = $_POST['block_end_time'];
    $block_staff_member_id = isset($_POST['block_staff_member_id']) && $_POST['block_staff_member_id'] != '' ? $_POST['block_staff_member_id'] : 'NULL';
    $block_reason = isset($_POST['block_reason']) ? $_POST['block_reason'] : '';
    $created_by = $_SESSION['user_id'];
    
    $query = "INSERT INTO appointment_blocks (block_date, block_start_time, block_end_time, block_staff_member_id, block_reason, created_by) VALUES ('$block_date', '$block_start_time', '$block_end_time', $block_staff_member_id, '$block_reason', $created_by)";
    $result = mysqli_query($connection, $query);
    
    echo $result ? 1 : 0;
}

if(@$_POST['formName']=='get_appointment_blocks'){
    $query = "SELECT b.*, u.user_name FROM appointment_blocks b LEFT JOIN users u ON b.block_staff_member_id = u.user_id WHERE b.block_status != 1 ORDER BY b.block_date DESC, b.block_start_time";
    $result = mysqli_query($connection, $query);
    
    $blocks = array();
    while($row = mysqli_fetch_array($result)){
        $blocks[] = array(
            'id' => $row['block_id'],
            'date' => date('d M Y', strtotime($row['block_date'])),
            'start_time' => date('h:i A', strtotime($row['block_start_time'])),
            'end_time' => date('h:i A', strtotime($row['block_end_time'])),
            'staff' => $row['user_name'],
            'reason' => $row['block_reason']
        );
    }
    
    echo json_encode($blocks);
}

if(@$_POST['formName']=='delete_appointment_block'){
    $block_id = $_POST['block_id'];
    
    $query = "UPDATE appointment_blocks SET block_status=1 WHERE block_id=$block_id";
    $result = mysqli_query($connection, $query);
    
    echo $result ? 1 : 0;
}

// Helper function
function getPurposeName($connection, $purpose_id){
    $pid = (int) $purpose_id;
    if ($pid <= 0) {
        return '';
    }
    $query = "SELECT purpose_name FROM appointment_purposes WHERE purpose_id = $pid LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        return '';
    }
    $row = mysqli_fetch_array($result);
    return ($row && isset($row['purpose_name'])) ? (string) $row['purpose_name'] : '';
}

// Course Cancellation Form Processing
if(@$_POST['formName']=='course_cancellation'){
    if(!function_exists('send_mail')){
        require_once(__DIR__ . '/mail_function.php');
    }
    
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $family_name = mysqli_real_escape_string($connection, $_POST['family_name']);
    $given_names = mysqli_real_escape_string($connection, $_POST['given_names']);
    $residential_address = mysqli_real_escape_string($connection, $_POST['residential_address']);
    $post_code = mysqli_real_escape_string($connection, $_POST['post_code']);
    $contact_number = mysqli_real_escape_string($connection, $_POST['contact_number']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $date_of_birth = !empty($_POST['date_of_birth']) ? mysqli_real_escape_string($connection, $_POST['date_of_birth']) : NULL;
    $gender = !empty($_POST['gender']) ? mysqli_real_escape_string($connection, $_POST['gender']) : NULL;
    $course_code = !empty($_POST['course_code']) ? mysqli_real_escape_string($connection, $_POST['course_code']) : NULL;
    $course_title = !empty($_POST['course_title']) ? mysqli_real_escape_string($connection, $_POST['course_title']) : NULL;
    $date_of_enrolment = !empty($_POST['date_of_enrolment']) ? mysqli_real_escape_string($connection, $_POST['date_of_enrolment']) : NULL;
    $reason_for_cancellation = mysqli_real_escape_string($connection, $_POST['reason_for_cancellation']);
    $reason_other_details = !empty($_POST['reason_other_details']) ? mysqli_real_escape_string($connection, $_POST['reason_other_details']) : NULL;
    $cancellation_effective_date = mysqli_real_escape_string($connection, $_POST['cancellation_effective_date']);
    $cooling_off_period = mysqli_real_escape_string($connection, $_POST['cooling_off_period']);
    $account_type = !empty($_POST['account_type']) ? mysqli_real_escape_string($connection, $_POST['account_type']) : NULL;
    $bank_name = !empty($_POST['bank_name']) ? mysqli_real_escape_string($connection, $_POST['bank_name']) : NULL;
    $bsb = !empty($_POST['bsb']) ? mysqli_real_escape_string($connection, $_POST['bsb']) : NULL;
    $account_number = !empty($_POST['account_number']) ? mysqli_real_escape_string($connection, $_POST['account_number']) : NULL;
    $full_name = mysqli_real_escape_string($connection, $_POST['full_name']);
    $signature = mysqli_real_escape_string($connection, $_POST['signature']);
    $submission_date = mysqli_real_escape_string($connection, $_POST['submission_date']);
    
    $query = "INSERT INTO course_cancellations (title, family_name, given_names, residential_address, post_code, contact_number, email, date_of_birth, gender, course_code, course_title, date_of_enrolment, reason_for_cancellation, reason_other_details, cancellation_effective_date, cooling_off_period, account_type, bank_name, bsb, account_number, full_name, signature, submission_date) 
              VALUES ('$title', '$family_name', '$given_names', '$residential_address', '$post_code', '$contact_number', '$email', " . ($date_of_birth ? "'$date_of_birth'" : "NULL") . ", " . ($gender ? "'$gender'" : "NULL") . ", " . ($course_code ? "'$course_code'" : "NULL") . ", " . ($course_title ? "'$course_title'" : "NULL") . ", " . ($date_of_enrolment ? "'$date_of_enrolment'" : "NULL") . ", '$reason_for_cancellation', " . ($reason_other_details ? "'$reason_other_details'" : "NULL") . ", '$cancellation_effective_date', '$cooling_off_period', " . ($account_type ? "'$account_type'" : "NULL") . ", " . ($bank_name ? "'$bank_name'" : "NULL") . ", " . ($bsb ? "'$bsb'" : "NULL") . ", " . ($account_number ? "'$account_number'" : "NULL") . ", '$full_name', '$signature', '$submission_date')";
    
    $result = mysqli_query($connection, $query);
    $error = mysqli_error($connection);
    
    if($error != '' || !$result){
        echo '0';
    } else {
        $lastId = mysqli_insert_id($connection);
        $uniqueId = sprintf('CC%05d', $lastId);
        $updateQuery = mysqli_query($connection, "UPDATE course_cancellations SET cancellation_unique_id='$uniqueId' WHERE cancellation_id=$lastId");
        
        if(mysqli_error($connection) == ''){
            echo $uniqueId;
            
            // Send email
            $mail_to = $email;
            $mail_subject = "Course Cancellation Form Submitted - National College Australia";
            $mail_body = "Dear $given_names $family_name,<br><br>";
            $mail_body .= "Thank you for submitting your Course Cancellation Form.<br><br>";
            $mail_body .= "<b>Reference ID:</b> $uniqueId<br>";
            $mail_body .= "<b>Submission Date:</b> $submission_date<br><br>";
            $mail_body .= "Your cancellation request is being processed. You will be contacted shortly regarding the status of your application.<br><br>";
            $mail_body .= "If you have any questions, please contact Client Services.<br><br>";
            $mail_body .= "Best regards,<br>National College Australia";
            
            send_mail($mail_to, $mail_subject, $mail_body, array('email_category' => 'course_cancellation_submit', 'meta' => array('ref' => $uniqueId)));
        } else {
            echo '0';
        }
    }
}

// Course Extension Form Processing
if(@$_POST['formName']=='course_extension'){
    if(!function_exists('send_mail')){
        require_once(__DIR__ . '/mail_function.php');
    }
    
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $family_name = mysqli_real_escape_string($connection, $_POST['family_name']);
    $given_names = mysqli_real_escape_string($connection, $_POST['given_names']);
    $residential_address = mysqli_real_escape_string($connection, $_POST['residential_address']);
    $post_code = mysqli_real_escape_string($connection, $_POST['post_code']);
    $contact_number = mysqli_real_escape_string($connection, $_POST['contact_number']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $course_code = !empty($_POST['course_code']) ? mysqli_real_escape_string($connection, $_POST['course_code']) : NULL;
    $course_title = !empty($_POST['course_title']) ? mysqli_real_escape_string($connection, $_POST['course_title']) : NULL;
    $enrolment_date = !empty($_POST['enrolment_date']) ? mysqli_real_escape_string($connection, $_POST['enrolment_date']) : NULL;
    $reason_for_extension = mysqli_real_escape_string($connection, $_POST['reason_for_extension']);
    $reason_other_details = !empty($_POST['reason_other_details']) ? mysqli_real_escape_string($connection, $_POST['reason_other_details']) : NULL;
    $full_name = mysqli_real_escape_string($connection, $_POST['full_name']);
    $signature = mysqli_real_escape_string($connection, $_POST['signature']);
    $submission_date = mysqli_real_escape_string($connection, $_POST['submission_date']);
    
    // Extract extension duration from reason_other_details if it contains duration info
    $extension_duration = NULL;
    if($reason_other_details && preg_match('/(\d+)\s*(month|months|week|weeks|day|days)/i', $reason_other_details, $matches)){
        $extension_duration = $matches[0];
    }
    
    $query = "INSERT INTO course_extensions (title, family_name, given_names, residential_address, post_code, contact_number, email, course_code, course_title, enrolment_date, reason_for_extension, reason_other_details, extension_duration, full_name, signature, submission_date) 
              VALUES ('$title', '$family_name', '$given_names', '$residential_address', '$post_code', '$contact_number', '$email', " . ($course_code ? "'$course_code'" : "NULL") . ", " . ($course_title ? "'$course_title'" : "NULL") . ", " . ($enrolment_date ? "'$enrolment_date'" : "NULL") . ", '$reason_for_extension', " . ($reason_other_details ? "'$reason_other_details'" : "NULL") . ", " . ($extension_duration ? "'$extension_duration'" : "NULL") . ", '$full_name', '$signature', '$submission_date')";
    
    $result = mysqli_query($connection, $query);
    $error = mysqli_error($connection);
    
    if($error != '' || !$result){
        echo '0';
    } else {
        $lastId = mysqli_insert_id($connection);
        $uniqueId = sprintf('CE%05d', $lastId);
        $updateQuery = mysqli_query($connection, "UPDATE course_extensions SET extension_unique_id='$uniqueId' WHERE extension_id=$lastId");
        
        if(mysqli_error($connection) == ''){
            echo $uniqueId;
            
            // Send email
            $mail_to = $email;
            $mail_subject = "Course Extension Application Submitted - National College Australia";
            $mail_body = "Dear $given_names $family_name,<br><br>";
            $mail_body .= "Thank you for submitting your Application for Course Extension Form.<br><br>";
            $mail_body .= "<b>Reference ID:</b> $uniqueId<br>";
            $mail_body .= "<b>Submission Date:</b> $submission_date<br>";
            $mail_body .= "<b>Reason:</b> $reason_for_extension<br><br>";
            $mail_body .= "Your extension request is being reviewed. You will be contacted shortly regarding the status of your application.<br><br>";
            $mail_body .= "<strong>Please note:</strong> Course extension rollover fees apply. Please refer to www.nationalcollege.edu.au for fee information.<br><br>";
            $mail_body .= "If you have any questions, please contact Client Services.<br><br>";
            $mail_body .= "Best regards,<br>National College Australia";
            
            send_mail($mail_to, $mail_subject, $mail_body, array('email_category' => 'course_extension_submit', 'meta' => array('ref' => $uniqueId)));
        } else {
            echo '0';
        }
    }
}

// Course Cancellations DataTable API
if(@$_GET['name']=='courseCancellations'){
    ob_clean();
    header('Content-Type: application/json');
    
    $query = "SELECT * FROM course_cancellations WHERE status = 0 ORDER BY created_date DESC";
    $result = mysqli_query($connection, $query);
    
    $data = array();
    while($row = mysqli_fetch_array($result)){
        $refundStatus = isset($row['refund_to_be_issued']) ? trim($row['refund_to_be_issued']) : '';
        $status = 'Pending';
        $statusClass = 'warning';
        
        if($refundStatus == 'Yes'){
            $status = 'Approved';
            $statusClass = 'success';
        } elseif($refundStatus == 'No' && $refundStatus != ''){
            $status = 'Processed';
            $statusClass = 'info';
        }
        
        // Show Process button only if not yet processed (refund_to_be_issued is empty or NULL)
        if($refundStatus == '' || $refundStatus == NULL){
            $action = '<button class="btn btn-sm btn-success btn-accept" data-id="'.$row['cancellation_id'].'"><i class="ti ti-check"></i> Process</button>';
        } else {
            $action = '<span class="badge bg-'.$statusClass.'">'.$status.'</span>';
        }
        
        $data[] = array(
            'reference_id' => $row['cancellation_unique_id'] ? $row['cancellation_unique_id'] : 'N/A',
            'name' => $row['given_names'] . ' ' . $row['family_name'],
            'email' => $row['email'],
            'contact_number' => $row['contact_number'],
            'course_code' => $row['course_code'] ? $row['course_code'] : '-',
            'course_title' => $row['course_title'] ? $row['course_title'] : '-',
            'reason' => $row['reason_for_cancellation'],
            'effective_date' => $row['cancellation_effective_date'] ? date('d M Y', strtotime($row['cancellation_effective_date'])) : '-',
            'cooling_off' => $row['cooling_off_period'],
            'status' => '<span class="badge bg-'.$statusClass.'">'.$status.'</span>',
            'submitted_date' => $row['submission_date'] ? date('d M Y', strtotime($row['submission_date'])) : '-',
            'action' => $action
        );
    }
    
    echo json_encode(array('data' => $data));
    exit;
}

// Course Extensions DataTable API
if(@$_GET['name']=='courseExtensions'){
    // Clear any previous output
    ob_clean();
    header('Content-Type: application/json');
    
    $query = "SELECT * FROM course_extensions WHERE status = 0 ORDER BY created_date DESC";
    $result = mysqli_query($connection, $query);
    
    $data = array();
    while($row = mysqli_fetch_array($result)){
        $extensionStatus = isset($row['extension_approved']) ? trim($row['extension_approved']) : '';
        $status = 'Pending';
        $statusClass = 'warning';
        
        if($extensionStatus == 'Yes'){
            $status = 'Approved';
            $statusClass = 'success';
        } elseif($extensionStatus == 'No' && $extensionStatus != ''){
            $status = 'Rejected';
            $statusClass = 'danger';
        }
        
        // Show Process button only if not yet processed (extension_approved is empty or NULL)
        if($extensionStatus == '' || $extensionStatus == NULL){
            $action = '<button class="btn btn-sm btn-success btn-accept" data-id="'.$row['extension_id'].'"><i class="ti ti-check"></i> Process</button>';
        } else {
            $action = '<span class="badge bg-'.$statusClass.'">'.$status.'</span>';
        }
        
        $data[] = array(
            'reference_id' => $row['extension_unique_id'] ? $row['extension_unique_id'] : 'N/A',
            'name' => $row['given_names'] . ' ' . $row['family_name'],
            'email' => $row['email'],
            'contact_number' => $row['contact_number'],
            'course_code' => $row['course_code'] ? $row['course_code'] : '-',
            'course_title' => $row['course_title'] ? $row['course_title'] : '-',
            'reason' => $row['reason_for_extension'],
            'enrolment_date' => $row['enrolment_date'] ? date('d M Y', strtotime($row['enrolment_date'])) : '-',
            'status' => '<span class="badge bg-'.$statusClass.'">'.$status.'</span>',
            'submitted_date' => $row['submission_date'] ? date('d M Y', strtotime($row['submission_date'])) : '-',
            'action' => $action
        );
    }
    
    echo json_encode(array('data' => $data));
    exit;
}

// Create New Question
if(@$_POST['formName']=='create_question'){
    ob_start(); ob_clean();

    $assessment_id = intval($_POST['assessment_id']);
    $question_type = isset($_POST['question_type']) ? intval($_POST['question_type']) : 1;
    $question_text = mysqli_real_escape_string($connection, $_POST['question_text'] ?? '');
    $question_marks = intval($_POST['question_marks'] ?? 0);
    
    if($assessment_id <= 0 || empty($question_text) || $question_marks <= 0){
        echo 'Invalid input data';
        exit;
    }
    
    // Check total marks don't exceed assessment marks
    $assessment_res = mysqli_query($connection, "SELECT marks FROM assessment WHERE assessment_id = $assessment_id LIMIT 1");
    if($assessment_res && mysqli_num_rows($assessment_res) > 0){
        $assessment = mysqli_fetch_assoc($assessment_res);
        $assessment_marks = intval($assessment['marks']);
        
        $current_marks_res = mysqli_query($connection, "SELECT SUM(marks) as total FROM assessment_questions WHERE assessment_id = $assessment_id");
        $current_marks = $current_marks_res ? intval(mysqli_fetch_assoc($current_marks_res)['total']) : 0;
        
        if($current_marks + $question_marks > $assessment_marks){
            echo 'Total questions marks (' . ($current_marks + $question_marks) . ') must be LESS than assessment marks (' . $assessment_marks . '). Available: ' . ($assessment_marks - $current_marks) . ' marks';
            exit;
        } 
        // elseif($current_marks + $question_marks == $assessment_marks) {
        //     echo 'Total questions marks have reached the assessment marks limit (' . $assessment_marks . '). No more questions can be added.';
        //     exit;
        // }
             $option_1 = isset($_POST['option_1']) ? mysqli_real_escape_string($connection, $_POST['option_1']) : null;
    $option_2 = isset($_POST['option_2']) ? mysqli_real_escape_string($connection, $_POST['option_2']) : null;
    $option_3 = isset($_POST['option_3']) ? mysqli_real_escape_string($connection, $_POST['option_3']) : null;
    $option_4 = isset($_POST['option_4']) ? mysqli_real_escape_string($connection, $_POST['option_4']) : null;
    $correct_option = '';
    $correct_options_multi = null;
    
    if($question_type == 1) {
        $correct_option = intval($_POST['correct_option']);
        if(empty($option_1) || empty($option_2) || empty($option_3) || empty($option_4) || $correct_option < 1 || $correct_option > 4){
            echo 'Invalid options for single choice';
            exit;
        }
    } elseif($question_type == 2) {
        $correct_option = mysqli_real_escape_string($connection, $_POST['correct_option']);
        // echo $correct_option."<br>";
        if($correct_option != 'True' && $correct_option != 'False'){
            echo 'Invalid True/False answer';
            exit;
        }
        if($correct_option == 'True')
        {
            $correct_option=1;
        }
        else
            {
                $correct_option=2;
            }
        //   echo $correct_option."<br>";
    } elseif($question_type == 3) {
        $correct_options_multi = isset($_POST['correct_options_multi']) ? mysqli_real_escape_string($connection, $_POST['correct_options_multi']) : '[]';
        if(empty($option_1) || empty($option_2) || empty($option_3) || empty($option_4)){
            echo 'Invalid options for multiple choice';
            exit;
        }
    } elseif($question_type == 4) {
        $correct_options_multi = mysqli_real_escape_string($connection, $_POST['correct_option']);
        if(empty($correct_options_multi)){
            echo 'Expected answer required';
            exit;
        }
    } elseif($question_type == 5) {
        // Image-based question – requires a file upload
        $upload_dir = __DIR__ . '/../uploads/question_images/';
        if(!is_dir($upload_dir)) { @mkdir($upload_dir, 0777, true); }
        if(!isset($_FILES['question_image'])) {
            echo 'No image received by the server. Please ensure the image was selected.';
            exit;
        }
        $upload_error_code = intval($_FILES['question_image']['error']);
        if($upload_error_code !== UPLOAD_ERR_OK) {
            $upload_error_msgs = [
                UPLOAD_ERR_INI_SIZE   => 'Image exceeds server upload size limit (upload_max_filesize).',
                UPLOAD_ERR_FORM_SIZE  => 'Image exceeds allowed form size.',
                UPLOAD_ERR_PARTIAL    => 'Image was only partially uploaded. Try again.',
                UPLOAD_ERR_NO_FILE    => 'No image file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server temp directory is missing.',
                UPLOAD_ERR_CANT_WRITE => 'Server could not write the uploaded file.',
                UPLOAD_ERR_EXTENSION  => 'Upload blocked by a server extension.',
            ];
            echo $upload_error_msgs[$upload_error_code] ?? 'Image upload failed (error code: ' . $upload_error_code . ').';
            exit;
        }
        $img_info = @getimagesize($_FILES['question_image']['tmp_name']);
        $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if(!$img_info || !in_array($img_info[2], $allowed_types)) {
            echo 'Invalid image type. Only JPG, PNG, GIF, and WEBP are allowed.';
            exit;
        }
        if($_FILES['question_image']['size'] > 2 * 1024 * 1024) {
            echo 'Image too large. Maximum allowed size is 2 MB.';
            exit;
        }
        $ext_map = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
        $ext = $ext_map[$img_info[2]] ?? strtolower(pathinfo($_FILES['question_image']['name'], PATHINFO_EXTENSION));
        $filename = 'qimg_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        if(!is_writable($upload_dir)) {
            echo 'Server upload directory is not writable. Contact administrator.';
            exit;
        }
        if(!move_uploaded_file($_FILES['question_image']['tmp_name'], $upload_dir . $filename)) {
            echo 'Failed to save the uploaded image to disk.';
            exit;
        }
        $correct_options_multi = mysqli_real_escape_string($connection, $filename);
        $correct_option = intval($_POST['correct_option'] ?? 0);
        if(empty($option_1) || empty($option_2) || empty($option_3) || empty($option_4) || $correct_option < 1 || $correct_option > 4) {
            echo 'Please fill all 4 option fields and select the correct answer.';
            exit;
        }
    }

    $query = "INSERT INTO assessment_questions (assessment_id, question_type, question_text, option_1, option_2, option_3, option_4, correct_option, correct_options_multi, marks, status)
              VALUES ($assessment_id, $question_type, '$question_text', " . ($option_1 ? "'$option_1'" : "NULL") . ", " . ($option_2 ? "'$option_2'" : "NULL") . ", " . ($option_3 ? "'$option_3'" : "NULL") . ", " . ($option_4 ? "'$option_4'" : "NULL") . ", " . ($correct_option !== '' ? "'$correct_option'" : "NULL") . ", " . ($correct_options_multi ? "'$correct_options_multi'" : "NULL") . ", $question_marks, 0)";

    $result = mysqli_query($connection, $query);

    if($result){
        echo '1';
    } else {
        echo mysqli_error($connection);
    }
    }
    exit;
}

// Delete Question
if(@$_POST['formName']=='delete_question'){
    ob_clean();
    
    $question_id = intval($_POST['question_id']);
    
    if($question_id <= 0){
        echo 'Invalid question ID';
        exit;
    }
    
    $query = "DELETE FROM assessment_questions WHERE question_id = $question_id";
    $result = mysqli_query($connection, $query);
    
    if($result){
        echo '1';
    } else {
        echo mysqli_error($connection);
    }
    exit;
}

// Update Question
if(@$_POST['formName']=='update_question'){
    ob_start(); ob_clean();

    $question_id = intval($_POST['question_id'] ?? 0);
    $assessment_id = intval($_POST['assessment_id'] ?? 0);
    $question_type = isset($_POST['question_type']) ? intval($_POST['question_type']) : 1;
    $question_text = mysqli_real_escape_string($connection, $_POST['question_text'] ?? '');
    $question_marks = intval($_POST['question_marks'] ?? 0);
    
    if($question_id <= 0 || $assessment_id <= 0 || empty($question_text) || $question_marks <= 0){
        echo 'Invalid input data';
        exit;
    }
    
    // Check total marks don't exceed assessment marks (for update)
    $assessment_res = mysqli_query($connection, "SELECT marks FROM assessment WHERE assessment_id = $assessment_id LIMIT 1");
    if($assessment_res && mysqli_num_rows($assessment_res) > 0){
        $assessment = mysqli_fetch_assoc($assessment_res);
        $assessment_marks = intval($assessment['marks']);
        
        // Get current total excluding this question
        $current_marks_res = mysqli_query($connection, "SELECT SUM(marks) as total FROM assessment_questions WHERE assessment_id = $assessment_id AND question_id != $question_id");
        $current_marks = $current_marks_res ? intval(mysqli_fetch_assoc($current_marks_res)['total']) : 0;
        
        if($current_marks + $question_marks > $assessment_marks){
            echo 'Total questions marks (' . ($current_marks + $question_marks) . ') must be LESS than assessment marks (' . $assessment_marks . '). marks: ';
            exit;
        }
    }
    
    $option_1 = isset($_POST['option_1']) ? mysqli_real_escape_string($connection, $_POST['option_1']) : null;
    $option_2 = isset($_POST['option_2']) ? mysqli_real_escape_string($connection, $_POST['option_2']) : null;
    $option_3 = isset($_POST['option_3']) ? mysqli_real_escape_string($connection, $_POST['option_3']) : null;
    $option_4 = isset($_POST['option_4']) ? mysqli_real_escape_string($connection, $_POST['option_4']) : null;
    $correct_option = '';
    $correct_options_multi = null;
    
    if($question_type == 1) {
        $correct_option = intval($_POST['correct_option']);
    } elseif($question_type == 2) {
        $correct_option = mysqli_real_escape_string($connection, $_POST['correct_option']);
    } elseif($question_type == 3) {
        $correct_options_multi = isset($_POST['correct_options_multi']) ? mysqli_real_escape_string($connection, $_POST['correct_options_multi']) : '[]';
    } elseif($question_type == 4) {
        $correct_options_multi = mysqli_real_escape_string($connection, $_POST['correct_option']);
    } elseif($question_type == 5) {
        // Image-based question update – new upload or keep existing
        $upload_dir = __DIR__ . '/../uploads/question_images/';
        if(!is_dir($upload_dir)) { @mkdir($upload_dir, 0777, true); }
        if(isset($_FILES['question_image']) && $_FILES['question_image']['error'] === UPLOAD_ERR_OK) {
            $img_info = @getimagesize($_FILES['question_image']['tmp_name']);
            $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
            if(!$img_info || !in_array($img_info[2], $allowed_types)) {
                echo 'Invalid image type. Only JPG, PNG, GIF, and WEBP are allowed.'; exit;
            }
            if($_FILES['question_image']['size'] > 2 * 1024 * 1024) { echo 'Image too large. Max 2 MB.'; exit; }
            $ext_map = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
            $ext = $ext_map[$img_info[2]] ?? strtolower(pathinfo($_FILES['question_image']['name'], PATHINFO_EXTENSION));
            $filename = 'qimg_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            if(!is_writable($upload_dir)) { echo 'Upload directory is not writable.'; exit; }
            if(!move_uploaded_file($_FILES['question_image']['tmp_name'], $upload_dir . $filename)) {
                echo 'Failed to save uploaded image.'; exit;
            }
            $correct_options_multi = mysqli_real_escape_string($connection, $filename);
        } elseif(!empty($_POST['existing_image'])) {
            $correct_options_multi = mysqli_real_escape_string($connection, $_POST['existing_image']);
        } else {
            echo 'An image is required for image-based questions.'; exit;
        }
        $correct_option = intval($_POST['correct_option'] ?? 0);
    }

    $o1 = $option_1 ? "'$option_1'" : "NULL";
    $o2 = $option_2 ? "'$option_2'" : "NULL";
    $o3 = $option_3 ? "'$option_3'" : "NULL";
    $o4 = $option_4 ? "'$option_4'" : "NULL";
    $co = $correct_option ? "'$correct_option'" : "NULL";
    $com = $correct_options_multi ? "'$correct_options_multi'" : "NULL";
    
    $query = "UPDATE assessment_questions SET 
              question_type = $question_type,
              question_text = '$question_text', 
              option_1 = $o1, 
              option_2 = $o2, 
              option_3 = $o3, 
              option_4 = $o4, 
              correct_option = $co, 
              correct_options_multi = $com,
              marks = $question_marks 
              WHERE question_id = $question_id AND assessment_id = $assessment_id";
    
    $result = mysqli_query($connection, $query);
    
    if($result){
        echo '1';
    } else {
        echo mysqli_error($connection);
    }
    exit;
}

// Question List DataTable API
if(@$_GET['name']=='questionList'){
    ob_clean();
    header('Content-Type: application/json');
    
    $assessment_id = intval($_GET['assessment_id']);
    
    $query = "SELECT * FROM assessment_questions WHERE assessment_id = $assessment_id AND status = 0 ORDER BY question_id ASC";
    $result = mysqli_query($connection, $query);
    
    $questions = array();
    while($row = mysqli_fetch_array($result)){
        $questions[] = array(
            'question_id' => $row['question_id'],
            'question_text' => htmlspecialchars($row['question_text']),
            'option_1' => htmlspecialchars($row['option_1']),
            'option_2' => htmlspecialchars($row['option_2']),
            'option_3' => htmlspecialchars($row['option_3']),
            'option_4' => htmlspecialchars($row['option_4']),
            'correct_option' => $row['correct_option'],
            'marks' => $row['marks']
        );
    }
    
    echo json_encode(array('questions' => $questions));
    exit;
}

// Create Assessment
if(@$_POST['formName']=='create_assessment'){
     ob_clean();

     $assessment_name = mysqli_real_escape_string($connection, $_POST['assessment_name']);
     $marks = intval($_POST['marks']);
     $passing_marks = intval($_POST['passing_marks']);
     $duration = intval($_POST['duration']);
     $status = intval($_POST['status']);

     $query = "INSERT INTO assessment (assessment_name, marks, passing_marks, duration, status, created_date)
               VALUES ('$assessment_name', $marks, $passing_marks, $duration, $status, NOW())";

     $result = mysqli_query($connection, $query);

     echo $result ? 1 : 0;
     exit;
}

// Update Assessment
if(@$_POST['formName']=='update_assessment'){
     ob_clean();

     $assessment_id = intval($_POST['assessment_id']);
     $assessment_name = mysqli_real_escape_string($connection, $_POST['assessment_name']);
     $marks = intval($_POST['marks']);
     $passing_marks = intval($_POST['passing_marks']);
     $duration = intval($_POST['duration']);
     $status = intval($_POST['status']);

     $query = "UPDATE assessment SET
               assessment_name = '$assessment_name',
               marks = $marks,
               passing_marks = $passing_marks,
               duration = $duration,
               status = $status
               WHERE assessment_id = $assessment_id";
    
    $result = mysqli_query($connection, $query);
    
    echo $result ? 1 : 0;
    exit;
}

// Assessment List DataTable API
if(@$_GET['name']=='assessmentList'){
    ob_clean();
    header('Content-Type: application/json');
    
     $query = "SELECT assessment_id, assessment_name, marks, passing_marks, duration, status, created_date FROM assessment ORDER BY created_date DESC";
    $result = mysqli_query($connection, $query);
    
    $data = array();
    $sr_no = 1;
    while($row = mysqli_fetch_array($result)){
        $status = $row['status'] == 0 ? 'Published' : 'Draft';
        $statusClass = $row['status'] == 0 ? 'success' : 'warning';
        $created_date = $row['created_date'] ? date('d M Y', strtotime($row['created_date'])) : '-';
        
        $action = '<a href="create_question.php?id='.$row['assessment_id'].'" class="btn btn-sm btn-primary btn-view me-1" data-id="'.$row['assessment_id'].'"><i class="ti ti-edit"></i> Edit</a>';
        $action .= '<a href="assign_student.php?id='.$row['assessment_id'].'" class="btn btn-sm btn-success"><i class="ti ti-user-plus"></i> Assign</a>';
        
$data[] = array(
             'sr_no' => $sr_no,
             'assessment_name' => $row['assessment_name'],
             'marks' => $row['marks'],
              'passing_marks' => $row['passing_marks'],
             'duration' => $row['duration'],
             'status' => '<span class="badge bg-'.$statusClass.'">'.$status.'</span>',
             'created_date' => $created_date,
             'action' => $action
         );
        $sr_no++;
    }
    
    echo json_encode(array('data' => $data));
    exit;
}


if(@$_POST['formName']=='assign_assessment'){
    ob_clean();
    $assessment_id = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;
    $student_ids_raw = isset($_POST['student_ids']) ? $_POST['student_ids'] : '';
    $student_ids = array();

    if(is_string($student_ids_raw) && $student_ids_raw !== ''){
        $decoded = json_decode($student_ids_raw, true);
        if(is_array($decoded)){
            foreach($decoded as $id){
                $id = trim((string)$id);
                if($id === '') continue;
                $student_ids[] = $id;
            }
        }
    }
    $student_ids = array_values(array_unique($student_ids));

    if($assessment_id <= 0){
        echo 'invalid_assessment';
        exit;
    }

    if(empty($student_ids)){
        echo 'no_students';
        exit;
    }

    $enrol_ids = array();
    foreach($student_ids as $student_id){
        if(substr($student_id, 0, 8) === 'enquiry:'){
            $enquiry_id = mysqli_real_escape_string($connection, substr($student_id, 8));
            $existing_enrol = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_enrol_id FROM student_enrolments WHERE st_enquiry_id='$enquiry_id' LIMIT 1"));
            if($existing_enrol && !empty($existing_enrol['st_enrol_id'])){
                $enrol_ids[] = intval($existing_enrol['st_enrol_id']);
            } else {
                $enquiry = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT st_name, st_surname, st_phno, st_email, st_course FROM student_enquiry WHERE st_enquiry_id='$enquiry_id' LIMIT 1"));
                if($enquiry){
                    $given_name = mysqli_real_escape_string($connection, trim($enquiry['st_name']));
                    $surname = mysqli_real_escape_string($connection, trim($enquiry['st_surname']));
                    $mobile = mysqli_real_escape_string($connection, trim($enquiry['st_phno']));
                    $email = mysqli_real_escape_string($connection, trim($enquiry['st_email']));
                    $courses = mysqli_real_escape_string($connection, trim($enquiry['st_course']));
                    $created_by = intval($_SESSION['user_id']);
                    $insertEnrol = "INSERT INTO student_enrolments (st_enquiry_id, st_rto_name, st_courses, st_given_name, st_surname, st_email, st_mobile, st_status, st_created_by) VALUES ('$enquiry_id', '', '$courses', '$given_name', '$surname', '$email', '$mobile', 0, $created_by)";
                    mysqli_query($connection, $insertEnrol);
                    $newEnrolId = mysqli_insert_id($connection);
                    if($newEnrolId > 0){
                        $enrol_ids[] = $newEnrolId;
                    }
                }
            }
        } else {
            $id_int = intval($student_id);
            if($id_int > 0){
                $enrol_ids[] = $id_int;
            }
        }
    }

    if(empty($enrol_ids)){
        echo 'no_students';
        exit;
    }

    mysqli_query($connection, "DELETE FROM assessment_assignments WHERE assessment_id = $assessment_id");
    $values = array();
    foreach(array_values(array_unique($enrol_ids)) as $student_id){
        $values[] = "($assessment_id, $student_id)";
    }
    if(!empty($values)){
        $insert_query = "INSERT INTO assessment_assignments (assessment_id, student_enrol_id) VALUES " . implode(',', $values);
        $insert_result = mysqli_query($connection, $insert_query);
        echo $insert_result ? '1' : '0';
    } else {
        echo '1';
    }
    exit;
}

// Save Student Answer
if(@$_POST['formName']=='save_answer'){
    ob_clean();
    
    $assessment_id = intval($_POST['assessment_id']);
    $student_enrol_id = intval($_POST['student_enrol_id']);
    $question_id = intval($_POST['question_id']);
    $answer_option = mysqli_real_escape_string($connection, $_POST['answer_option']);
    
    if($assessment_id <= 0 || $student_enrol_id <= 0 || $question_id <= 0){
        echo 'Invalid input data';
        exit;
    }
    
    // Check if answer already exists
    $check = mysqli_query($connection, "SELECT answer_id FROM assessment_answers WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id AND question_id = $question_id LIMIT 1");
    
    if($check && mysqli_num_rows($check) > 0){
        // Update existing answer
        $query = "UPDATE assessment_answers SET answer_option = '$answer_option', answered_at = NOW() WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id AND question_id = $question_id";
    } else {
        // Insert new answer
        $query = "INSERT INTO assessment_answers (assessment_id, student_enrol_id, question_id, answer_option) VALUES ($assessment_id, $student_enrol_id, $question_id, '$answer_option')";
    }
    
    $result = mysqli_query($connection, $query);
    
    if($result){
        echo '1';
    } else {
        echo mysqli_error($connection);
    }
    exit;
}

// Submit Assessment
if(@$_POST['formName']=='submit_assessment'){
    ob_clean();
    
    $assessment_id = intval($_POST['assessment_id']);
    $student_enrol_id = intval($_POST['student_enrol_id']);
    
    if($assessment_id <= 0 || $student_enrol_id <= 0){
        echo 'Invalid input data';
        exit;
    }
    
    // Get all questions for this assessment
    $questions_result = mysqli_query($connection, "SELECT * FROM assessment_questions WHERE assessment_id = $assessment_id");
    $total_marks = 0;
    $obtained_marks = 0;
    $attempted_questions = 0;
    $pending_questions = 0;
    
    $total_questions = mysqli_num_rows($questions_result);
    
    while($question = mysqli_fetch_assoc($questions_result)){
        $question_id = $question['question_id'];
        $question_marks = intval($question['marks']);
        $question_type = $question['question_type'] ?? 1;
        $total_marks += $question_marks;
        
        // Get student's answer
        $answer_result = mysqli_query($connection, "SELECT answer_option FROM assessment_answers WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id AND question_id = $question_id LIMIT 1");
        $student_answer = $answer_result ? mysqli_fetch_assoc($answer_result) : null;
        $student_answer_text = $student_answer['answer_option'] ?? '';
        
        $is_correct = 0;
        $marks_obtained = 0;
        
        if($student_answer_text){
            $attempted_questions++;
            if($question_type == 1){
                // Single Choice
                if($student_answer_text == $question['correct_option']){
                    $is_correct = 1;
                    $marks_obtained = $question_marks;
                }
            } elseif($question_type == 2){
                // True/False
                if($student_answer_text == $question['correct_option']){
                    $is_correct = 1;
                    $marks_obtained = $question_marks;
                }
            } elseif($question_type == 3){
                // Multiple Choice - check all correct options
                $correct_multi = json_decode($question['correct_options_multi'] ?? '[]', true);
                $student_multi = json_decode($student_answer_text, true);
                if(is_array($correct_multi) && is_array($student_multi)){
                    sort($correct_multi);
                    sort($student_multi);
                    if($correct_multi == $student_multi){
                        $is_correct = 1;
                        $marks_obtained = $question_marks;
                    }
                }
            } elseif($question_type == 4){
                // Text Answer - case insensitive check
                if(strtolower(trim($student_answer_text)) == strtolower(trim($question['correct_option']))){
                    $is_correct = 1;
                    $marks_obtained = $question_marks;
                }
            }
        } else {
            $pending_questions++;
        }
        
        $obtained_marks += $marks_obtained;
        
        // Update answer with marks
        mysqli_query($connection, "UPDATE assessment_answers SET is_correct = $is_correct, marks_obtained = $marks_obtained WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id AND question_id = $question_id");
    }
    
    $percentage = $total_marks > 0 ? ($obtained_marks / $total_marks) * 100 : 0;
    
     // Check if submission exists
     $sub_check = mysqli_query($connection, "SELECT submission_id FROM assessment_submissions WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id LIMIT 1");
     $assessment_count_result = mysqli_query($connection, "SELECT assessment_count FROM assessment_assignments WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id LIMIT 1");
     $assessment_count_row = mysqli_fetch_assoc($assessment_count_result);
     $assessment_count = ($assessment_count_row['assessment_count'] ?? 0) + 1;
    if($sub_check && mysqli_num_rows($sub_check) > 0){
        $query = "UPDATE assessment_submissions SET total_marks = $total_marks, obtained_marks = $obtained_marks, percentage = $percentage, status = 1, attempted_questions = $attempted_questions, pending_questions = $pending_questions, submitted_at = NOW() WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id";
        $query1 = "UPDATE `assessment_assignments` SET `assessment_count`= $assessment_count WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id"; 
        } else {
        $query = "INSERT INTO assessment_submissions (assessment_id, student_enrol_id, total_marks, obtained_marks, percentage, status, attempted_questions, pending_questions) VALUES ($assessment_id, $student_enrol_id, $total_marks, $obtained_marks, $percentage, 1, $attempted_questions, $pending_questions)";
    $query1 = "UPDATE `assessment_assignments` SET `assessment_count`= $assessment_count WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id"; 
       
        }
    $result1 = mysqli_query($connection, $query1);
    $result = mysqli_query($connection, $query);
    
    if($result){
        echo json_encode(array('success' => 1, 'obtained_marks' => $obtained_marks, 'total_marks' => $total_marks, 'percentage' => round($percentage, 2), 'attempted_questions' => $attempted_questions, 'pending_questions' => $pending_questions));
    } else {
        echo mysqli_error($connection);
    }
    exit;
}

// Process Course Cancellation (Office Use Only)
if(@$_POST['formName']=='process_cancellation'){
    // Clear any previous output
    ob_clean();
    
    if(!function_exists('send_mail')){
        require_once(__DIR__ . '/mail_function.php');
    }
    
    $cancellation_id = intval($_POST['cancellation_id']);
    $refund_to_be_issued = mysqli_real_escape_string($connection, $_POST['refund_to_be_issued']);
    $refund_approved_by = !empty($_POST['refund_approved_by']) ? mysqli_real_escape_string($connection, $_POST['refund_approved_by']) : NULL;
    $refund_approved_date = !empty($_POST['refund_approved_date']) ? mysqli_real_escape_string($connection, $_POST['refund_approved_date']) : NULL;
    $refund_amount = !empty($_POST['refund_amount']) ? floatval($_POST['refund_amount']) : NULL;
    $date_forwarded_to_finance = !empty($_POST['date_forwarded_to_finance']) ? mysqli_real_escape_string($connection, $_POST['date_forwarded_to_finance']) : NULL;
    $finance_initial = !empty($_POST['finance_initial']) ? mysqli_real_escape_string($connection, $_POST['finance_initial']) : NULL;
    $office_comments = !empty($_POST['office_comments']) ? mysqli_real_escape_string($connection, $_POST['office_comments']) : NULL;
    $processed_by = $_SESSION['user_id'];
    
    $updateQuery = "UPDATE course_cancellations SET 
        refund_to_be_issued = '$refund_to_be_issued',
        refund_approved_by = " . ($refund_approved_by ? "'$refund_approved_by'" : "NULL") . ",
        refund_approved_date = " . ($refund_approved_date ? "'$refund_approved_date'" : "NULL") . ",
        refund_amount = " . ($refund_amount ? $refund_amount : "NULL") . ",
        date_forwarded_to_finance = " . ($date_forwarded_to_finance ? "'$date_forwarded_to_finance'" : "NULL") . ",
        finance_initial = " . ($finance_initial ? "'$finance_initial'" : "NULL") . ",
        office_comments = " . ($office_comments ? "'$office_comments'" : "NULL") . ",
        modified_by = $processed_by,
        modified_date = CURDATE()
        WHERE cancellation_id = $cancellation_id";
    
    $result = mysqli_query($connection, $updateQuery);
    $error = mysqli_error($connection);
    
    if($error){
        echo '0';
        exit;
    }
    
    if($result && mysqli_affected_rows($connection) > 0){
        // Get student details for email
        $studentQuery = mysqli_query($connection, "SELECT * FROM course_cancellations WHERE cancellation_id = $cancellation_id");
        $student = mysqli_fetch_array($studentQuery);
        
        // Send email to student
        $mail_to = $student['email'];
        $mail_subject = "Course Cancellation Request - Update";
        $mail_body = "Dear " . $student['given_names'] . " " . $student['family_name'] . ",<br><br>";
        
        if($refund_to_be_issued == 'Yes'){
            $mail_body .= "Your Course Cancellation Request (Reference ID: " . $student['cancellation_unique_id'] . ") has been <strong>approved</strong>.<br><br>";
            if($refund_amount){
                $mail_body .= "<b>Refund Amount:</b> $" . number_format($refund_amount, 2) . "<br>";
            }
            $mail_body .= "Your refund will be processed according to our refund policy. You will be notified once the refund has been processed.<br><br>";
        } else {
            $mail_body .= "Your Course Cancellation Request (Reference ID: " . $student['cancellation_unique_id'] . ") has been <strong>processed</strong>.<br><br>";
            $mail_body .= "Please note that no refund will be issued as per our cancellation policy.<br><br>";
        }
        
        if($office_comments){
            $mail_body .= "<b>Comments:</b> " . $office_comments . "<br><br>";
        }
        
        $mail_body .= "If you have any questions, please contact Client Services.<br><br>";
        $mail_body .= "Best regards,<br>National College Australia";
        
        send_mail($mail_to, $mail_subject, $mail_body, array('email_category' => 'course_cancellation_update', 'meta' => array('cancellation_id' => $cancellation_id)));
        
        echo '1';
    } else {
        echo '0';
    }
    exit;
}

// Process Course Extension (Office Use Only)
if(@$_POST['formName']=='process_extension'){
    // Clear any previous output
    ob_clean();
    
    if(!function_exists('send_mail')){
        require_once(__DIR__ . '/mail_function.php');
    }
    
    $extension_id = intval($_POST['extension_id']);
    $extension_approved = mysqli_real_escape_string($connection, $_POST['extension_approved']);
    $application_approved_by = !empty($_POST['application_approved_by']) ? mysqli_real_escape_string($connection, $_POST['application_approved_by']) : NULL;
    $approval_initial = !empty($_POST['approval_initial']) ? mysqli_real_escape_string($connection, $_POST['approval_initial']) : NULL;
    $approval_date = !empty($_POST['approval_date']) ? mysqli_real_escape_string($connection, $_POST['approval_date']) : NULL;
    $rollover_fee = !empty($_POST['rollover_fee']) ? floatval($_POST['rollover_fee']) : NULL;
    $office_comments = !empty($_POST['office_comments']) ? mysqli_real_escape_string($connection, $_POST['office_comments']) : NULL;
    $processed_by = $_SESSION['user_id'];
    
    $updateQuery = "UPDATE course_extensions SET 
        extension_approved = '$extension_approved',
        application_approved_by = " . ($application_approved_by ? "'$application_approved_by'" : "NULL") . ",
        approval_initial = " . ($approval_initial ? "'$approval_initial'" : "NULL") . ",
        approval_date = " . ($approval_date ? "'$approval_date'" : "NULL") . ",
        rollover_fee = " . ($rollover_fee ? $rollover_fee : "NULL") . ",
        office_comments = " . ($office_comments ? "'$office_comments'" : "NULL") . ",
        modified_by = $processed_by,
        modified_date = CURDATE()
        WHERE extension_id = $extension_id";
    
    $result = mysqli_query($connection, $updateQuery);
    $error = mysqli_error($connection);
    
    if($error){
        echo '0';
        exit;
    }
    
    if($result && mysqli_affected_rows($connection) > 0){
        // Get student details for email
        $studentQuery = mysqli_query($connection, "SELECT * FROM course_extensions WHERE extension_id = $extension_id");
        $student = mysqli_fetch_array($studentQuery);
        
        // Send email to student
        $mail_to = $student['email'];
        $mail_subject = "Course Extension Application - Update";
        $mail_body = "Dear " . $student['given_names'] . " " . $student['family_name'] . ",<br><br>";
        
        if($extension_approved == 'Yes'){
            $mail_body .= "Your Application for Course Extension (Reference ID: " . $student['extension_unique_id'] . ") has been <strong>approved</strong>.<br><br>";
            if($rollover_fee){
                $mail_body .= "<b>Rollover Fee:</b> $" . number_format($rollover_fee, 2) . "<br>";
                $mail_body .= "Please arrange payment of the rollover fee. For payment details, please refer to www.nationalcollege.edu.au<br><br>";
            }
        } else {
            $mail_body .= "Your Application for Course Extension (Reference ID: " . $student['extension_unique_id'] . ") has been <strong>reviewed</strong>.<br><br>";
            $mail_body .= "Unfortunately, we are unable to offer an extension at this time.<br><br>";
        }
        
        if($office_comments){
            $mail_body .= "<b>Comments:</b> " . $office_comments . "<br><br>";
        }
        
        $mail_body .= "If you have any questions, please contact Client Services.<br><br>";
        $mail_body .= "Best regards,<br>National College Australia";
        
        send_mail($mail_to, $mail_subject, $mail_body, array('email_category' => 'course_extension_update', 'meta' => array('extension_id' => $extension_id)));

        echo '1';
    } else {
        echo '0';
    }
    exit;
}

// Complete Enrolment
if (@$_POST['action'] == 'complete_enrolment') {
    ob_clean();
    header('Content-Type: application/json');
    $enrolment_id = intval($_POST['enrolment_id'] ?? 0);
    if ($enrolment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid enrolment ID.']);
        exit;
    }
    $ok = mysqli_query($connection,
        "UPDATE enrolment_form_new SET status = 'complete', updated_status = 'completed' WHERE id = $enrolment_id LIMIT 1"
    );
    if ($ok && mysqli_affected_rows($connection) > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($connection) ?: 'Already completed or not found.']);
    }
    exit;
}

// Student Feedback
if (@$_POST['action'] == 'submit_student_feedback') {
    ob_clean();
    header('Content-Type: application/json');
    $sess_type = @$_SESSION['user_type'];
    if ($sess_type !== 0 && $sess_type !== 'student') {
        echo json_encode(['success' => false, 'message' => 'Unauthorised.']);
        exit;
    }
    $student_user_id = intval($_SESSION['user_id'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $subject  = trim($_POST['subject']  ?? '');
    $message  = trim($_POST['message']  ?? '');
    $rating   = min(5, max(0, intval($_POST['rating'] ?? 0)));
    if ($category === '' || $subject === '' || $message === '') {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    // Fetch student name and email
    $sn = mysqli_fetch_assoc(mysqli_query($connection, "SELECT full_name, email FROM student_users WHERE id = $student_user_id LIMIT 1"));
    $student_name  = mysqli_real_escape_string($connection, $sn['full_name'] ?? '');
    $student_email = mysqli_real_escape_string($connection, $sn['email']     ?? '');
    $category = mysqli_real_escape_string($connection, $category);
    $subject  = mysqli_real_escape_string($connection, $subject);
    $message  = mysqli_real_escape_string($connection, $message);
    $ok = mysqli_query($connection,
        "INSERT INTO student_feedback (student_user_id, student_name, student_email, category, subject, message, rating)
         VALUES ($student_user_id, '$student_name', '$student_email', '$category', '$subject', '$message', $rating)"
    );
    if ($ok) echo json_encode(['success' => true]);
    else     echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
    exit;
}

// Raise Enrolment Query
if (@$_POST['action'] == 'raise_enrolment_query') {
    ob_clean();
    header('Content-Type: application/json');
    $enrolment_id = intval($_POST['enrolment_id'] ?? 0);
    $subject      = trim($_POST['subject'] ?? '');
    $message      = trim($_POST['message'] ?? '');
    if ($enrolment_id <= 0 || $subject === '' || $message === '') {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    $sess_type = @$_SESSION['user_type'];
    $user_type = $sess_type === 1 ? 'admin' : ($sess_type === 2 ? 'staff' : 'student');
    $raised_by = mysqli_real_escape_string($connection, @$_SESSION['user_name'] ?? '');
    $user_type = mysqli_real_escape_string($connection, $user_type);
    $subject   = mysqli_real_escape_string($connection, $subject);
    $message   = mysqli_real_escape_string($connection, $message);
    $ok = mysqli_query($connection,
        "INSERT INTO enrolment_queries (enrolment_id, raised_by, user_type, subject, message)
         VALUES ($enrolment_id, '$raised_by', '$user_type', '$subject', '$message')"
    );
    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
        exit;
    }
    mysqli_query($connection,
        "UPDATE enrolment_form_new SET updated_status = 'raise_query' WHERE id = $enrolment_id LIMIT 1"
    );

    // Fetch student details to send notification email
    $enrol_row = mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT email_address, given_name, surname, office_student_id FROM enrolment_form_new WHERE id = $enrolment_id LIMIT 1"
    ));

    if ($enrol_row && !empty($enrol_row['email_address'])) {
        $student_name = trim($enrol_row['given_name'] . ' ' . $enrol_row['surname']) ?: 'Student';
        $student_id   = $enrol_row['office_student_id'] ?: "Enrolment #$enrolment_id";

        $mail_subject = 'Action Required: ' . stripslashes($subject);
        $mail_body = '
<p>Dear ' . htmlspecialchars($student_name) . ',</p>
<p>We have raised a query regarding your enrolment record (<strong>' . htmlspecialchars($student_id) . '</strong>).</p>
<p><strong>Subject:</strong> ' . htmlspecialchars(stripslashes($subject)) . '</p>
<p><strong>Details:</strong><br>' . nl2br(htmlspecialchars(stripslashes($message))) . '</p>
<p>Kindly log in to the <strong>Student Panel</strong> and review your enrolment details. If you have any questions or need to provide additional information, please contact us.</p>
<br>
<p>Regards,<br><strong>National College Australia</strong><br>RTO: 91000</p>
';
        send_mail($enrol_row['email_address'], $mail_subject, $mail_body);
    }

    echo json_encode(['success' => true]);
    exit;
}

// Resolve Enrolment Query (student)
if (@$_POST['action'] == 'resolve_enrolment_query') {
    ob_clean();
    header('Content-Type: application/json');
    $sess_type = @$_SESSION['user_type'];
    if ($sess_type !== 0 && $sess_type !== 'student') {
        echo json_encode(['success' => false, 'message' => 'Unauthorised.']);
        exit;
    }
    $query_id     = intval($_POST['query_id'] ?? 0);
    $enrolment_id = intval($_POST['enrolment_id'] ?? 0);
    if ($query_id <= 0 || $enrolment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }
    mysqli_query($connection,
        "UPDATE enrolment_queries SET status = 'resolved' WHERE id = $query_id LIMIT 1"
    );
    // Only move to resolve_query if no open queries remain
    $open = mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT COUNT(*) AS cnt FROM enrolment_queries WHERE enrolment_id = $enrolment_id AND status = 'open'"
    ));
    if (intval($open['cnt']) === 0) {
        mysqli_query($connection,
            "UPDATE enrolment_form_new SET updated_status = 'resolve_query' WHERE id = $enrolment_id LIMIT 1"
        );
    }
    echo json_encode(['success' => true]);
    exit;
}

// Assessment Pass
if(@$_POST['action'] == 'assessment_pass'){
    ob_clean();
    header('Content-Type: application/json');
    $submission_id    = intval($_POST['submission_id']);
    $assessment_id    = intval($_POST['assessment_id']);
    $student_enrol_id = intval($_POST['student_enrol_id']);
    if($submission_id <= 0 || $assessment_id <= 0 || $student_enrol_id <= 0){
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }
    $res = mysqli_query($connection,
        "UPDATE assessment_submissions SET status = 2
         WHERE submission_id = $submission_id
           AND assessment_id = $assessment_id
           AND student_enrol_id = $student_enrol_id LIMIT 1"
    );
    if($res && mysqli_affected_rows($connection) > 0){
        mysqli_query($connection,
            "UPDATE assessment_assignments
             SET enrolment_status = 1, passing_status = 'pass'
             WHERE assessment_id = $assessment_id
               AND student_enrol_id = $student_enrol_id
               AND assign_status = 'active' LIMIT 1"
        );
        echo json_encode(['status' => 'success', 'message' => 'Student marked as passed.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not update. Already graded or record not found.']);
    }
    exit;
}

// Assessment Reassign
if(@$_POST['action'] == 'assessment_reassign'){
    ob_clean();
    header('Content-Type: application/json');
    $submission_id    = intval($_POST['submission_id']);
    $assessment_id    = intval($_POST['assessment_id']);
    $student_enrol_id = intval($_POST['student_enrol_id']);
    $feedback         = trim($_POST['feedback'] ?? '');
    if($submission_id <= 0 || $assessment_id <= 0 || $student_enrol_id <= 0){
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }
    if($feedback === ''){
        echo json_encode(['status' => 'error', 'message' => 'Feedback is required.']);
        exit;
    }
    $feedback_esc = mysqli_real_escape_string($connection, $feedback);

    // Get current attempt_count from the active assignment
    $cur = mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT attempt_count FROM assessment_assignments
         WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id
           AND assign_status = 'active' ORDER BY id DESC LIMIT 1"
    ));
    $next_attempt = intval($cur['attempt_count'] ?? 0) + 1;

    // Delete answers then submission so the student can retake
    mysqli_query($connection,
        "DELETE FROM assessment_answers
         WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id"
    );
    $res = mysqli_query($connection,
        "DELETE FROM assessment_submissions
         WHERE submission_id = $submission_id
           AND assessment_id = $assessment_id
           AND student_enrol_id = $student_enrol_id LIMIT 1"
    );
    if($res && mysqli_affected_rows($connection) > 0){
        // Mark the previous assignment record as reassigned
        mysqli_query($connection,
            "UPDATE assessment_assignments SET assign_status = 'deleted'
             WHERE assessment_id = $assessment_id AND student_enrol_id = $student_enrol_id"
        );
        // Insert fresh assignment with incremented attempt_count and feedback
        mysqli_query($connection,
            "INSERT INTO assessment_assignments (assessment_id, student_enrol_id, attempt_count, feedback)
             VALUES ($assessment_id, $student_enrol_id, $next_attempt, '$feedback_esc')"
        );
        echo json_encode(['status' => 'success', 'message' => 'Assessment reassigned. Student can retake it.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not reassign. Record not found.']);
    }
    exit;
}

// Assessment Manual Pass
if(@$_POST['action'] == 'assessment_manual_pass'){
    ob_clean();
    header('Content-Type: application/json');
    $submission_id    = intval($_POST['submission_id']);
    $assessment_id    = intval($_POST['assessment_id']);
    $student_enrol_id = intval($_POST['student_enrol_id']);
    $manual_pass_reason = trim($_POST['manual_pass_reason'] ?? '');
    if($submission_id <= 0 || $assessment_id <= 0 || $student_enrol_id <= 0){
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }
    if($manual_pass_reason === ''){
        echo json_encode(['status' => 'error', 'message' => 'Reason for manual pass is required.']);
        exit;
    }
    $reason_esc = mysqli_real_escape_string($connection, $manual_pass_reason);
    $res = mysqli_query($connection,
        "UPDATE assessment_submissions SET status = 2
         WHERE submission_id = $submission_id
           AND assessment_id = $assessment_id
           AND student_enrol_id = $student_enrol_id LIMIT 1"
    );
    if($res && mysqli_affected_rows($connection) > 0){
        mysqli_query($connection,
            "UPDATE assessment_assignments
             SET enrolment_status = 1,
                 passing_status = 'manual_pass',
                 manual_pass_reason = '$reason_esc'
             WHERE assessment_id = $assessment_id
               AND student_enrol_id = $student_enrol_id
               AND assign_status = 'active' LIMIT 1"
        );
        echo json_encode(['status' => 'success', 'message' => 'Student manually passed.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not apply manual pass. Record not found.']);
    }
    exit;
}

// ─── Auto-create invoice tables ──────────────────────────────────────────────
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `invoices` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `invoice_number`  VARCHAR(30)  NOT NULL UNIQUE,
    `enrolment_id`    INT UNSIGNED NOT NULL,
    `student_name`    VARCHAR(200) NOT NULL DEFAULT '',
    `student_id`      VARCHAR(50)  NOT NULL DEFAULT '',
    `email_address`   VARCHAR(150) NOT NULL DEFAULT '',
    `status`          ENUM('pending','paid','overdue') NOT NULL DEFAULT 'pending',
    `created_by`      INT UNSIGNED DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_enrolment` (`enrolment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `invoice_installments` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `invoice_id`    INT UNSIGNED NOT NULL,
    `course_id`     INT UNSIGNED DEFAULT NULL,
    `invoice_type`  VARCHAR(60)  NOT NULL DEFAULT '',
    `funding_type`  VARCHAR(60)  NOT NULL DEFAULT '',
    `currency`      VARCHAR(10)  NOT NULL DEFAULT 'AUD',
    `amount`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `gst_amount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `issue_date`    DATE DEFAULT NULL,
    `due_date`      DATE DEFAULT NULL,
    `status`        ENUM('pending','paid') NOT NULL DEFAULT 'pending',
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_invoice` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
// ─────────────────────────────────────────────────────────────────────────────

// ─── Create Invoice handler ───────────────────────────────────────────────────
if (@$_POST['formName'] === 'create_invoice') {
    header('Content-Type: application/json; charset=UTF-8');

    $enrolment_id  = isset($_POST['enrolment_id']) ? intval($_POST['enrolment_id']) : 0;
    $installments  = isset($_POST['installments_json']) ? json_decode($_POST['installments_json'], true) : [];

    if ($enrolment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid enrolment.']);
        exit;
    }
    if (empty($installments) || !is_array($installments)) {
        echo json_encode(['success' => false, 'message' => 'No installments provided.']);
        exit;
    }

    // Fetch student details
    $enr = mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT given_name, surname, office_student_id, email_address FROM enrolment_form_new WHERE id = $enrolment_id LIMIT 1"
    ));
    if (!$enr) {
        echo json_encode(['success' => false, 'message' => 'Enrolment not found.']);
        exit;
    }

    $student_name = mysqli_real_escape_string($connection, trim($enr['given_name'] . ' ' . $enr['surname']));
    $student_id   = mysqli_real_escape_string($connection, $enr['office_student_id']);
    $email        = mysqli_real_escape_string($connection, $enr['email_address']);
    $created_by   = intval($_SESSION['user_id'] ?? 0);
    $inv_number   = 'INV-' . strtoupper(substr(md5(uniqid()), 0, 8));

    // Insert invoice header
    $ok = mysqli_query($connection,
        "INSERT INTO invoices (invoice_number, enrolment_id, student_name, student_id, email_address, created_by)
         VALUES ('$inv_number', $enrolment_id, '$student_name', '$student_id', '$email', $created_by)"
    );
    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'Could not create invoice: ' . mysqli_error($connection)]);
        exit;
    }
    $invoice_id = mysqli_insert_id($connection);

    // Insert installments
    foreach ($installments as $inst) {
        $course_id    = !empty($inst['course_id'])    ? intval($inst['course_id'])                                          : 'NULL';
        $inv_type     = mysqli_real_escape_string($connection, trim($inst['invoice_type']  ?? ''));
        $fund_type    = mysqli_real_escape_string($connection, trim($inst['funding_type']  ?? ''));
        $currency     = mysqli_real_escape_string($connection, trim($inst['currency']      ?? 'AUD'));
        $amount       = round(floatval($inst['amount']     ?? 0), 2);
        $gst_amount   = round(floatval($inst['gst_amount'] ?? 0), 2);
        $issue_date   = !empty($inst['issue_date']) ? "'" . mysqli_real_escape_string($connection, $inst['issue_date']) . "'" : 'NULL';
        $due_date     = !empty($inst['due_date'])   ? "'" . mysqli_real_escape_string($connection, $inst['due_date'])   . "'" : 'NULL';

        mysqli_query($connection,
            "INSERT INTO invoice_installments
                (invoice_id, course_id, invoice_type, funding_type, currency, amount, gst_amount, issue_date, due_date)
             VALUES
                ($invoice_id, $course_id, '$inv_type', '$fund_type', '$currency', $amount, $gst_amount, $issue_date, $due_date)"
        );
    }

    echo json_encode(['success' => true, 'invoice_number' => $inv_number, 'invoice_id' => $invoice_id]);
    exit;
}
// ─────────────────────────────────────────────────────────────────────────────

?>

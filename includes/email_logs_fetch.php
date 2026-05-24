<?php
/**
 * Server-side data for Email Logs (DataTables). Staff only (user_type 1 or 2).
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array(), 'error' => 'auth'));
    exit;
}
$ut = (int) @$_SESSION['user_type'];
if ($ut !== 1 && $ut !== 2) {
    echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array(), 'error' => 'forbidden'));
    exit;
}

require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/email_log_helper.php';

crm_email_ensure_log_table($connection);

$draw = isset($_POST['draw']) ? (int) $_POST['draw'] : 0;
$start = isset($_POST['start']) ? max(0, (int) $_POST['start']) : 0;
$length = isset($_POST['length']) ? (int) $_POST['length'] : 25;
if ($length < 1 || $length > 200) {
    $length = 25;
}

$filter_staff = isset($_POST['filter_staff']) ? (int) $_POST['filter_staff'] : 0;
$filter_category = isset($_POST['filter_category']) ? trim((string) $_POST['filter_category']) : '';
$filter_status = isset($_POST['filter_status']) ? trim((string) $_POST['filter_status']) : '';
$filter_enquiry = isset($_POST['filter_enquiry']) ? trim((string) $_POST['filter_enquiry']) : '';
$filter_to = isset($_POST['filter_to']) ? trim((string) $_POST['filter_to']) : '';
$filter_date_from = isset($_POST['filter_date_from']) ? trim((string) $_POST['filter_date_from']) : '';
$filter_date_to = isset($_POST['filter_date_to']) ? trim((string) $_POST['filter_date_to']) : '';
$search_q = isset($_POST['search']['value']) ? trim((string) $_POST['search']['value']) : '';

$where = array('1=1');
if ($filter_staff > 0) {
    $where[] = 'sent_by_user_id=' . (int) $filter_staff;
}
if ($filter_category !== '' && $filter_category !== '-1') {
    $where[] = "email_category='" . mysqli_real_escape_string($connection, $filter_category) . "'";
}
if ($filter_status === 'sent' || $filter_status === 'failed') {
    $where[] = "send_status='" . mysqli_real_escape_string($connection, $filter_status) . "'";
}
if ($filter_enquiry !== '') {
    $e = mysqli_real_escape_string($connection, $filter_enquiry);
    $digits = preg_replace('/\D/', '', $filter_enquiry);
    if ($digits !== '' && (int) $digits > 0) {
        $sid = (int) $digits;
        $where[] = "(st_enquiry_id LIKE '%$e%' OR st_id = $sid)";
    } else {
        $where[] = "st_enquiry_id LIKE '%$e%'";
    }
}
if ($filter_to !== '') {
    $t = mysqli_real_escape_string($connection, $filter_to);
    $where[] = "recipient_to LIKE '%$t%'";
}
if ($filter_date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date_from)) {
    $where[] = "created_at >= '" . mysqli_real_escape_string($connection, $filter_date_from) . " 00:00:00'";
}
if ($filter_date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date_to)) {
    $where[] = "created_at <= '" . mysqli_real_escape_string($connection, $filter_date_to) . " 23:59:59'";
}
if ($search_q !== '') {
    $s = mysqli_real_escape_string($connection, $search_q);
    $where[] = "(recipient_to LIKE '%$s%' OR subject LIKE '%$s%' OR st_enquiry_id LIKE '%$s%' OR sent_by_user_name LIKE '%$s%')";
}

$where_sql = implode(' AND ', $where);

$total_q = mysqli_query($connection, 'SELECT COUNT(*) AS c FROM crm_email_log');
$records_total = 0;
if ($total_q && ($tr = mysqli_fetch_assoc($total_q))) {
    $records_total = (int) $tr['c'];
}

$fil_q = mysqli_query($connection, "SELECT COUNT(*) AS c FROM crm_email_log WHERE $where_sql");
$records_filtered = 0;
if ($fil_q && ($fr = mysqli_fetch_assoc($fil_q))) {
    $records_filtered = (int) $fr['c'];
}

$sql = "SELECT id, created_at, send_status, recipient_to, subject, email_category, sent_by_user_id, sent_by_user_name, st_enquiry_id, st_id, meta_json, request_uri, ip_address,
        CHAR_LENGTH(body_html) AS body_len
        FROM crm_email_log WHERE $where_sql ORDER BY id DESC LIMIT " . (int) $start . ", " . (int) $length;
$q = mysqli_query($connection, $sql);

$cat_labels = array(
    'general' => 'General',
    'enquiry_status' => 'Enquiry status email',
    'appointment_confirmation' => 'Appointment confirmation',
    'appointment_manual' => 'Appointment (manual)',
    'course_cancellation_submit' => 'Course cancellation (submit)',
    'course_extension_submit' => 'Course extension (submit)',
    'course_cancellation_update' => 'Course cancellation (office)',
    'course_extension_update' => 'Course extension (office)',
    'login_otp' => 'Login OTP',
    'public_enquiry_form' => 'Public enquiry form',
);

$data = array();
if ($q) {
    while ($r = mysqli_fetch_assoc($q)) {
        $cat = $r['email_category'];
        $cat_disp = isset($cat_labels[$cat]) ? $cat_labels[$cat] : htmlspecialchars($cat, ENT_QUOTES, 'UTF-8');
        $st = $r['send_status'] === 'failed' ? '<span class="badge bg-danger">Failed</span>' : '<span class="badge bg-success">Sent</span>';
        $staff = $r['sent_by_user_name'] ? htmlspecialchars($r['sent_by_user_name'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted">—</span>';
        if ($r['sent_by_user_id']) {
            $staff .= ' <span class="text-muted small">(#' . (int) $r['sent_by_user_id'] . ')</span>';
        }
        $enq = '';
        $sid = isset($r['st_id']) ? (int) $r['st_id'] : 0;
        if ($sid > 0) {
            $eqb = base64_encode((string) $sid);
            $label = !empty($r['st_enquiry_id']) ? $r['st_enquiry_id'] : ('#' . $sid);
            $enq = '<a href="student_enquiry.php?eq=' . htmlspecialchars($eqb, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
        } elseif (!empty($r['st_enquiry_id'])) {
            $enq = htmlspecialchars($r['st_enquiry_id'], ENT_QUOTES, 'UTF-8');
        } else {
            $enq = '<span class="text-muted">—</span>';
        }
        $subj_raw = (string) $r['subject'];
        if (function_exists('mb_strimwidth')) {
            $subj_disp = mb_strimwidth($subj_raw, 0, 80, '…', 'UTF-8');
        } else {
            $subj_disp = strlen($subj_raw) > 80 ? substr($subj_raw, 0, 77) . '…' : $subj_raw;
        }
        $to_raw = (string) $r['recipient_to'];
        if (function_exists('mb_strimwidth')) {
            $to_disp = mb_strimwidth($to_raw, 0, 42, '…', 'UTF-8');
        } else {
            $to_disp = strlen($to_raw) > 42 ? substr($to_raw, 0, 39) . '…' : $to_raw;
        }
        $subj = htmlspecialchars($subj_disp, ENT_QUOTES, 'UTF-8');
        $to = htmlspecialchars($to_disp, ENT_QUOTES, 'UTF-8');
        $when = $r['created_at'] ? date('d/m/Y H:i', strtotime($r['created_at'])) : '—';
        $detail_btn = '<button type="button" class="btn btn-sm btn-outline-primary btn-email-log-detail" data-id="' . (int) $r['id'] . '">View</button>';
        $data[] = array(
            $when,
            $st,
            $cat_disp,
            $to,
            $subj,
            $staff,
            $enq,
            $detail_btn,
        );
    }
}

echo json_encode(array(
    'draw' => $draw,
    'recordsTotal' => $records_total,
    'recordsFiltered' => $records_filtered,
    'data' => $data,
));

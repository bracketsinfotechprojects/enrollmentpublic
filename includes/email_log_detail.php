<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    echo json_encode(array('ok' => false, 'error' => 'auth'));
    exit;
}
$ut = (int) @$_SESSION['user_type'];
if ($ut !== 1 && $ut !== 2) {
    echo json_encode(array('ok' => false, 'error' => 'forbidden'));
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(array('ok' => false, 'error' => 'bad id'));
    exit;
}

require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/email_log_helper.php';
crm_email_ensure_log_table($connection);

$id_esc = (int) $id;
$q = mysqli_query($connection, "SELECT * FROM crm_email_log WHERE id=$id_esc LIMIT 1");
if (!$q || !($r = mysqli_fetch_assoc($q))) {
    echo json_encode(array('ok' => false, 'error' => 'not found'));
    exit;
}

$meta_pretty = '';
if (!empty($r['meta_json'])) {
    $mj = json_decode($r['meta_json'], true);
    if (is_array($mj)) {
        $meta_pretty = json_encode($mj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        $meta_pretty = $r['meta_json'];
    }
}

echo json_encode(array(
    'ok' => true,
    'row' => array(
        'id' => (int) $r['id'],
        'created_at' => $r['created_at'],
        'send_status' => $r['send_status'],
        'error_message' => $r['error_message'],
        'recipient_to' => $r['recipient_to'],
        'subject' => $r['subject'],
        'body_html' => $r['body_html'],
        'email_category' => $r['email_category'],
        'sent_by_user_id' => $r['sent_by_user_id'] !== null ? (int) $r['sent_by_user_id'] : null,
        'sent_by_user_name' => $r['sent_by_user_name'],
        'st_enquiry_id' => $r['st_enquiry_id'],
        'st_id' => $r['st_id'] !== null ? (int) $r['st_id'] : null,
        'meta_json' => $meta_pretty,
        'request_uri' => $r['request_uri'],
        'ip_address' => $r['ip_address'],
    ),
));

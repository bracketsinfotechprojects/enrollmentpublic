<?php
/**
 * CRM outbound email audit log (all send_mail / enrol_send_mail paths).
 */

if (!function_exists('crm_email_ensure_log_table')) {
    function crm_email_ensure_log_table($connection) {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        @mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `crm_email_log` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `send_status` VARCHAR(16) NOT NULL DEFAULT 'sent',
            `error_message` TEXT NULL,
            `recipient_to` VARCHAR(512) NOT NULL,
            `subject` VARCHAR(998) NOT NULL,
            `body_html` MEDIUMTEXT NULL,
            `email_category` VARCHAR(64) NOT NULL DEFAULT 'general',
            `sent_by_user_id` INT NULL,
            `sent_by_user_name` VARCHAR(128) NULL,
            `st_enquiry_id` VARCHAR(32) NULL,
            `st_id` INT NULL,
            `meta_json` TEXT NULL,
            `request_uri` VARCHAR(512) NULL,
            `ip_address` VARCHAR(45) NULL,
            PRIMARY KEY (`id`),
            KEY `idx_created` (`created_at`),
            KEY `idx_staff` (`sent_by_user_id`),
            KEY `idx_enquiry` (`st_enquiry_id`),
            KEY `idx_st_id` (`st_id`),
            KEY `idx_category` (`email_category`),
            KEY `idx_status` (`send_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

if (!function_exists('crm_email_log_client_ip')) {
    function crm_email_log_client_ip() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $p = trim($parts[0]);
            if ($p !== '') {
                return substr($p, 0, 45);
            }
        }
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    }
}

if (!function_exists('crm_email_log_resolve_staff')) {
    function crm_email_log_resolve_staff($connection) {
        $uid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
        $name = '';
        if ($uid > 0 && $connection) {
            $q = @mysqli_query($connection, 'SELECT user_name FROM users WHERE user_id=' . $uid . ' LIMIT 1');
            if ($q && ($r = mysqli_fetch_assoc($q)) && !empty($r['user_name'])) {
                $name = (string) $r['user_name'];
            }
        }
        return array($uid > 0 ? $uid : null, $name !== '' ? $name : null);
    }
}

if (!function_exists('crm_email_log_record')) {
    /**
     * @param mysqli $connection
     * @param string $to
     * @param string $subject
     * @param string|null $body_html
     * @param string $send_status sent|failed
     * @param string|null $error_message
     * @param array $ctx email_category, st_enquiry_id, st_id, sent_by_user_id, sent_by_user_name, meta (array)
     */
    function crm_email_log_record($connection, $to, $subject, $body_html, $send_status, $error_message, array $ctx = array()) {
        if (!$connection || !($connection instanceof mysqli)) {
            return;
        }
        crm_email_ensure_log_table($connection);

        $cat = isset($ctx['email_category']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $ctx['email_category']) : 'general';
        if ($cat === '') {
            $cat = 'general';
        }
        if (strlen($cat) > 64) {
            $cat = substr($cat, 0, 64);
        }

        $st_eq = isset($ctx['st_enquiry_id']) ? trim((string) $ctx['st_enquiry_id']) : '';
        if (strlen($st_eq) > 32) {
            $st_eq = substr($st_eq, 0, 32);
        }
        $st_id = isset($ctx['st_id']) ? (int) $ctx['st_id'] : 0;
        if ($st_id <= 0) {
            $st_id = null;
        }

        $uid = isset($ctx['sent_by_user_id']) ? (int) $ctx['sent_by_user_id'] : null;
        $uname = isset($ctx['sent_by_user_name']) ? trim((string) $ctx['sent_by_user_name']) : '';
        if ($uid === null || $uid <= 0) {
            list($u2, $n2) = crm_email_log_resolve_staff($connection);
            if ($uid === null || $uid <= 0) {
                $uid = $u2;
            }
            if ($uname === '' && $n2) {
                $uname = $n2;
            }
        }
        if ($uname !== '' && strlen($uname) > 128) {
            $uname = substr($uname, 0, 128);
        }

        $meta_json = null;
        if (!empty($ctx['meta']) && is_array($ctx['meta'])) {
            $meta_json = json_encode($ctx['meta'], JSON_UNESCAPED_UNICODE);
            if (strlen($meta_json) > 65000) {
                $meta_json = substr($meta_json, 0, 65000);
            }
        }

        $req = isset($_SERVER['REQUEST_URI']) ? substr((string) $_SERVER['REQUEST_URI'], 0, 512) : null;
        $ip = crm_email_log_client_ip();

        $to_esc = mysqli_real_escape_string($connection, substr((string) $to, 0, 512));
        $sub_esc = mysqli_real_escape_string($connection, substr((string) $subject, 0, 998));
        $body_esc = $body_html !== null ? mysqli_real_escape_string($connection, (string) $body_html) : '';
        $stat_esc = ($send_status === 'failed') ? 'failed' : 'sent';
        $err_raw = $error_message !== null ? substr((string) $error_message, 0, 2000) : '';
        $err_sql = $err_raw !== '' ? "'" . mysqli_real_escape_string($connection, $err_raw) . "'" : 'NULL';
        $cat_esc = mysqli_real_escape_string($connection, $cat);
        $st_eq_sql = $st_eq !== '' ? "'" . mysqli_real_escape_string($connection, $st_eq) . "'" : 'NULL';
        $st_id_sql = $st_id !== null ? (string) (int) $st_id : 'NULL';
        $uid_sql = $uid !== null && $uid > 0 ? (string) (int) $uid : 'NULL';
        $uname_sql = $uname !== '' ? "'" . mysqli_real_escape_string($connection, $uname) . "'" : 'NULL';
        $meta_sql = $meta_json !== null ? "'" . mysqli_real_escape_string($connection, $meta_json) . "'" : 'NULL';
        $req_sql = $req !== null && $req !== '' ? "'" . mysqli_real_escape_string($connection, $req) . "'" : 'NULL';
        $ip_sql = $ip !== '' ? "'" . mysqli_real_escape_string($connection, $ip) . "'" : 'NULL';

        @mysqli_query(
            $connection,
            "INSERT INTO crm_email_log (`send_status`,`error_message`,`recipient_to`,`subject`,`body_html`,`email_category`,`sent_by_user_id`,`sent_by_user_name`,`st_enquiry_id`,`st_id`,`meta_json`,`request_uri`,`ip_address`)
             VALUES ('$stat_esc',$err_sql,'$to_esc','$sub_esc','$body_esc','$cat_esc',$uid_sql,$uname_sql,$st_eq_sql,$st_id_sql,$meta_sql,$req_sql,$ip_sql)"
        );
    }
}

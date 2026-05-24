<?php
/**
 * Counselling outcome email templates use enquiry_status_email_templates.status_code 12–14
 * (separate from enquiry flow codes 1–11).
 */

if (!function_exists('datacontrol_counselling_session_repl')) {
    function datacontrol_counselling_session_repl($connection, $enquiry_id, $post_date, $post_start, $post_end)
    {
        $dateLine = '[Counselling Date]';
        $timeLine = '[Counselling Start Time – Counselling End Time]';
        $ts_start = null;
        $ts_end = null;
        $post_date = trim((string) $post_date);
        $post_start = trim((string) $post_start);
        $post_end = trim((string) $post_end);
        if ($post_date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $post_date)) {
            if ($post_start !== '' && preg_match('/^\d{2}:\d{2}$/', $post_start)) {
                $ts_start = strtotime($post_date . ' ' . $post_start . ':00');
            } else {
                $ts_start = strtotime($post_date . ' 00:00:00');
            }
            if ($post_end !== '' && preg_match('/^\d{2}:\d{2}$/', $post_end)) {
                $ts_end = strtotime($post_date . ' ' . $post_end . ':00');
            } elseif ($ts_start) {
                $ts_end = $ts_start;
            }
        }
        if (!$ts_start && $enquiry_id !== '') {
            $eid = mysqli_real_escape_string($connection, (string) $enquiry_id);
            $cd = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT counsil_timing, counsil_end_time FROM counseling_details WHERE st_enquiry_id='$eid' AND counsil_enquiry_status=0 ORDER BY counsil_id DESC LIMIT 1"));
            if ($cd && !empty(trim((string) ($cd['counsil_timing'] ?? '')))) {
                $ts_start = strtotime((string) $cd['counsil_timing']);
                $endRaw = trim((string) ($cd['counsil_end_time'] ?? ''));
                $ts_end = ($endRaw !== '') ? strtotime($endRaw) : $ts_start;
            }
        }
        if ($ts_start) {
            $dateLine = date('l, j F Y', $ts_start);
            $st = date('g:i A', $ts_start);
            $en = ($ts_end && $ts_end > $ts_start) ? date('g:i A', $ts_end) : $st;
            $timeLine = $st . ' – ' . $en;
        }
        return array(
            '{{CounsellingDate}}' => $dateLine,
            '{{CounsellingTime}}' => $timeLine,
        );
    }
}

if (!function_exists('datacontrol_seed_counselling_outcome_email_templates')) {
    function datacontrol_seed_counselling_outcome_email_templates($connection)
    {
        $defs = array(
            12 => array('Counselling completed – {{CourseName}}', "Dear {{FirstName}},\n\nThank you for completing your counselling session regarding {{CourseName}} at National College Australia.\n\nSession date: {{CounsellingDate}}\nTime: {{CounsellingTime}}\n\nWe will be in touch with any next steps.\n\nKind regards,\n{{OfficerName}}\nNational College Australia"),
            13 => array('Counselling rescheduled – {{CourseName}}', "Dear {{FirstName}},\n\nRegarding your enquiry about {{CourseName}}, your counselling session has been rescheduled.\n\nSession date: {{CounsellingDate}}\nTime: {{CounsellingTime}}\n\nIf you have questions, contact us:\nPhone: 08 7119 6196\nEmail: info@nca.edu.au\n\nKind regards,\n{{OfficerName}}\nNational College Australia"),
            14 => array('Counselling outcome – {{CourseName}}', "Dear {{FirstName}},\n\nThank you for attending your counselling regarding {{CourseName}} at National College Australia.\nAs discussed, we are unable to proceed with an application at this time.\n\nSession date: {{CounsellingDate}}\nTime: {{CounsellingTime}}\n\nWe appreciate your interest. Should your circumstances change, you are welcome to contact us in the future.\n\nKind regards,\n{{OfficerName}}\nNational College Australia"),
        );
        foreach ($defs as $sc => $pair) {
            $sc = (int) $sc;
            $chk = mysqli_query($connection, "SELECT 1 FROM enquiry_status_email_templates WHERE status_code=$sc LIMIT 1");
            if ($chk && mysqli_num_rows($chk) > 0) {
                continue;
            }
            $sub = mysqli_real_escape_string($connection, $pair[0]);
            $body = mysqli_real_escape_string($connection, $pair[1]);
            mysqli_query($connection, "INSERT INTO enquiry_status_email_templates (status_code, subject, body) VALUES ($sc, '$sub', '$body')");
        }
    }
}

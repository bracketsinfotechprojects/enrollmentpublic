<?php
/**
 * Google Calendar: one shared admin-connected account. Events are created with all dashboard staff as attendees so everyone gets the event.
 * Requires: google_calendar_tokens (single row id=1), site_settings or google_calendar_config.php for client_id/client_secret.
 */

if (!defined('GOOGLE_CALENDAR_HELPER_LOADED')) {
    define('GOOGLE_CALENDAR_HELPER_LOADED', 1);
}

/**
 * Get config: from site_settings (DB) first, then from google_calendar_config.php
 */
function google_calendar_get_config($connection) {
    $config = ['client_id' => '', 'client_secret' => '', 'redirect_uri' => ''];
    if ($connection) {
        $tableCheck = @mysqli_query($connection, "SHOW TABLES LIKE 'site_settings'");
        if ($tableCheck && mysqli_num_rows($tableCheck)) {
            $q = @mysqli_query($connection, "SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('google_calendar_client_id','google_calendar_client_secret','google_calendar_redirect_uri')");
        } else {
            $q = false;
        }
        if ($q && mysqli_num_rows($q)) {
            while ($r = mysqli_fetch_assoc($q)) {
                if ($r['setting_key'] === 'google_calendar_client_id') $config['client_id'] = (string)$r['setting_value'];
                if ($r['setting_key'] === 'google_calendar_client_secret') $config['client_secret'] = (string)$r['setting_value'];
                if ($r['setting_key'] === 'google_calendar_redirect_uri') $config['redirect_uri'] = (string)$r['setting_value'];
            }
        }
    }
    if (is_file(__DIR__ . '/google_calendar_config.php')) {
        $file_config = include __DIR__ . '/google_calendar_config.php';
        if (is_array($file_config)) {
            if (!empty($file_config['client_id'])) $config['client_id'] = $file_config['client_id'];
            if (!empty($file_config['client_secret'])) $config['client_secret'] = $file_config['client_secret'];
            if (!empty($file_config['redirect_uri'])) $config['redirect_uri'] = $file_config['redirect_uri'];
        }
    }
    return $config;
}

/** Build redirect URI from current request if not in config (must match Google Console exactly) */
function google_calendar_build_redirect_uri() {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = $_SERVER['SCRIPT_NAME'] ?? '/google_calendar_settings.php';
    $uri = $scheme . '://' . $host . $path;
    $uri .= (strpos($path, '?') !== false ? '&' : '?') . 'action=callback';
    return $uri;
}

/**
 * Build Google OAuth authorization URL for calendar scope.
 */
function google_calendar_get_auth_url($connection, $redirect_uri = '') {
    $config = google_calendar_get_config($connection);
    if (empty($config['client_id'])) return '';
    if ($redirect_uri === '') {
        $redirect_uri = !empty(trim($config['redirect_uri'])) ? trim($config['redirect_uri']) : google_calendar_build_redirect_uri();
    }
    $params = [
        'client_id'     => $config['client_id'],
        'redirect_uri'  => $redirect_uri,
        'response_type' => 'code',
        'scope'         => 'https://www.googleapis.com/auth/calendar.events',
        'access_type'   => 'offline',
        'prompt'        => 'consent',
    ];
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

/**
 * Exchange authorization code for tokens; save to single shared row (admin account).
 * Returns ['success' => true] or ['success' => false, 'error' => '...'] so caller can show Google's error.
 */
function google_calendar_exchange_code($connection, $code, $redirect_uri = '') {
    $config = google_calendar_get_config($connection);
    if (empty($config['client_id']) || empty($config['client_secret'])) {
        return ['success' => false, 'error' => 'Client ID or Client Secret is missing.'];
    }
    if ($redirect_uri === '') {
        $redirect_uri = !empty(trim($config['redirect_uri'])) ? trim($config['redirect_uri']) : google_calendar_build_redirect_uri();
    }
    $post = [
        'code'          => $code,
        'client_id'     => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code',
    ];
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($post),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    $response = curl_exec($ch);
    $code_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);
    $data = $response ? json_decode($response, true) : null;
    if ($code_http === 0) {
        $msg = 'Connection to Google failed (no response). ';
        if ($curl_errno) {
            $msg .= 'cURL error ' . $curl_errno . ': ' . $curl_err;
            if (strpos(strtolower($curl_err), 'ssl') !== false || $curl_errno === 60) {
                $msg .= ' — Your server may be missing CA certificates for HTTPS. Try on live HTTPS hosting, or install/update CA bundle in PHP.';
            }
        } else {
            $msg .= 'Check firewall, proxy, or try from another network.';
        }
        return ['success' => false, 'error' => $msg];
    }
    if ($code_http !== 200) {
        $msg = isset($data['error_description']) ? $data['error_description'] : (isset($data['error']) ? $data['error'] : 'HTTP ' . $code_http);
        if (isset($data['error']) && $data['error'] === 'redirect_uri_mismatch') {
            $msg .= ' Use the exact Redirect URI shown below in Google Console (copy it, then add it under Authorized redirect URIs).';
        }
        return ['success' => false, 'error' => $msg];
    }
    if (empty($data['access_token'])) return ['success' => false, 'error' => 'Google did not return an access token.'];
    $access_token = $data['access_token'];
    $refresh_token = isset($data['refresh_token']) ? $data['refresh_token'] : '';
    $expires_in = isset($data['expires_in']) ? (int)$data['expires_in'] : 3600;
    $expires_at = date('Y-m-d H:i:s', time() + $expires_in);
    $access_esc = mysqli_real_escape_string($connection, $access_token);
    $refresh_esc = mysqli_real_escape_string($connection, $refresh_token);
    $tableCheck = @mysqli_query($connection, "SHOW TABLES LIKE 'google_calendar_tokens'");
    if (!$tableCheck || !mysqli_num_rows($tableCheck)) return false;
    $exists = mysqli_fetch_row(mysqli_query($connection, "SELECT 1 FROM google_calendar_tokens WHERE id=1"));
    if ($exists) {
        mysqli_query($connection, "UPDATE google_calendar_tokens SET access_token='$access_esc', refresh_token='$refresh_esc', token_expires_at='$expires_at', updated_at=NOW() WHERE id=1");
    } else {
        mysqli_query($connection, "INSERT INTO google_calendar_tokens (id, access_token, refresh_token, token_expires_at) VALUES (1, '$access_esc', '$refresh_esc', '$expires_at')");
    }
    return ['success' => true];
}

/**
 * Get valid access token for the shared account (refresh if expired). Returns access_token or null.
 */
function google_calendar_get_valid_token($connection) {
    $tableCheck = @mysqli_query($connection, "SHOW TABLES LIKE 'google_calendar_tokens'");
    if (!$tableCheck || !mysqli_num_rows($tableCheck)) return null;
    $row = @mysqli_fetch_assoc(mysqli_query($connection, "SELECT access_token, refresh_token, token_expires_at FROM google_calendar_tokens WHERE id=1"));
    if (!$row || empty($row['refresh_token'])) return null;
    $expires = strtotime($row['token_expires_at'] ?? '0');
    if ($expires > (time() + 60)) return $row['access_token'];
    $config = google_calendar_get_config($connection);
    if (empty($config['client_id']) || empty($config['client_secret'])) return null;
    $post = [
        'client_id'     => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'refresh_token'  => $row['refresh_token'],
        'grant_type'    => 'refresh_token',
    ];
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($post),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = $response ? json_decode($response, true) : null;
    if (empty($data['access_token'])) return null;
    $expires_in = isset($data['expires_in']) ? (int)$data['expires_in'] : 3600;
    $expires_at = date('Y-m-d H:i:s', time() + $expires_in);
    $access_esc = mysqli_real_escape_string($connection, $data['access_token']);
    mysqli_query($connection, "UPDATE google_calendar_tokens SET access_token='$access_esc', token_expires_at='$expires_at', updated_at=NOW() WHERE id=1");
    return $data['access_token'];
}

/**
 * Get all staff emails from users table (everyone who can log in gets the event as attendee).
 */
function google_calendar_get_staff_emails($connection) {
    $emails = [];
    $q = @mysqli_query($connection, "SELECT user_email FROM users WHERE user_status=0 AND user_email != ''");
    if ($q && mysqli_num_rows($q)) {
        while ($r = mysqli_fetch_assoc($q)) {
            $e = trim($r['user_email']);
            if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL)) $emails[] = $e;
        }
    }
    return $emails;
}

/**
 * Create a calendar event on the shared Google Calendar and add all staff as attendees (so everyone gets it on their calendar).
 * Returns event id or null on failure.
 */
function google_calendar_create_event($connection, $title, $start_datetime, $end_datetime, $description = '') {
    $token = google_calendar_get_valid_token($connection);
    if (!$token) return null;
    $start_ts = is_numeric($start_datetime) ? $start_datetime : strtotime($start_datetime);
    $end_ts   = is_numeric($end_datetime)   ? $end_datetime   : strtotime($end_datetime);
    if (!$start_ts || !$end_ts) return null;
    if ($end_ts <= $start_ts) $end_ts = $start_ts + 1800;
    $tz = 'Australia/Sydney';
    $start_rfc = date('Y-m-d\TH:i:s', $start_ts);
    $end_rfc   = date('Y-m-d\TH:i:s', $end_ts);
    $attendees = [];
    foreach (google_calendar_get_staff_emails($connection) as $email) {
        $attendees[] = ['email' => $email];
    }
    $body = [
        'summary' => $title,
        'description' => $description,
        'start' => ['dateTime' => $start_rfc, 'timeZone' => $tz],
        'end'   => ['dateTime' => $end_rfc,   'timeZone' => $tz],
        'reminders' => [
            'useDefault' => false,
            'overrides' => [
                ['method' => 'popup', 'minutes' => 15],
                ['method' => 'email', 'minutes' => 60],
            ],
        ],
    ];
    if (!empty($attendees)) {
        $body['attendees'] = $attendees;
    }
    $json = json_encode($body);
    $ch = curl_init('https://www.googleapis.com/calendar/v3/calendars/primary/events?sendUpdates=all');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) return null;
    $data = json_decode($response, true);
    return isset($data['id']) ? $data['id'] : null;
}

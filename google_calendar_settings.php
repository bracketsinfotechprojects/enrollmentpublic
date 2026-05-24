<?php
/**
 * Google Calendar: OAuth callback, disconnect, and legacy form POST handler.
 * All UI lives in Profile & Settings → Google Calendar tab.
 * This script only processes actions and redirects back to profile_settings.php?tab=google
 */
include('includes/dbconnect.php');
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 1) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/google_calendar_helper.php';

$base_redirect = 'profile_settings.php?tab=google';

// OAuth callback: exchange code for tokens
if (isset($_GET['action']) && $_GET['action'] === 'callback' && !empty($_GET['code'])) {
    $result = google_calendar_exchange_code($connection, $_GET['code']);
    if (!empty($result['success'])) {
        header('Location: ' . $base_redirect . '&connected=1');
        exit;
    }
    $err = isset($result['error']) ? $result['error'] : 'Could not connect. Check your Client ID and Secret, and that the redirect URI matches exactly.';
    header('Location: ' . $base_redirect . '&google_error=' . rawurlencode($err));
    exit;
}

// Disconnect shared account
if (isset($_GET['action']) && $_GET['action'] === 'disconnect') {
    $tableCheck = mysqli_fetch_row(mysqli_query($connection, "SHOW TABLES LIKE 'google_calendar_tokens'"));
    if ($tableCheck) mysqli_query($connection, "DELETE FROM google_calendar_tokens WHERE id=1");
    header('Location: ' . $base_redirect . '&disconnected=1');
    exit;
}

// Legacy: save API credentials (form may still POST here from old bookmarks)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_google_config'])) {
    $cid = isset($_POST['google_client_id']) ? trim($_POST['google_client_id']) : '';
    $csec = isset($_POST['google_client_secret']) ? trim($_POST['google_client_secret']) : '';
    $redirect_uri = isset($_POST['google_redirect_uri']) ? trim($_POST['google_redirect_uri']) : '';
    $cid_esc = mysqli_real_escape_string($connection, $cid);
    $csec_esc = mysqli_real_escape_string($connection, $csec);
    $redirect_esc = mysqli_real_escape_string($connection, $redirect_uri);
    $tableExists = mysqli_fetch_row(mysqli_query($connection, "SHOW TABLES LIKE 'site_settings'"));
    if ($tableExists) {
        mysqli_query($connection, "INSERT INTO site_settings (setting_key, setting_value) VALUES ('google_calendar_client_id','$cid_esc'), ('google_calendar_client_secret','$csec_esc'), ('google_calendar_redirect_uri','$redirect_esc') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        header('Location: ' . $base_redirect . '&saved_google=1');
        exit;
    }
    header('Location: ' . $base_redirect . '&google_error=' . rawurlencode('Run the database migration first: sql/google_calendar_migration.sql'));
    exit;
}

// No action: redirect to Settings → Google Calendar tab
header('Location: ' . $base_redirect);
exit;

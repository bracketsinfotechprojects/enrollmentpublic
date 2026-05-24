<?php
/**
 * Google Calendar OAuth – copy to google_calendar_config.php and fill in.
 * Get credentials: https://console.cloud.google.com/ → APIs & Services → Credentials
 * Create OAuth 2.0 Client ID (Web application), add Authorized redirect URI:
 *   https://yourdomain.com/google_calendar_settings.php?action=callback
 *   (or http://localhost/auztraining/google_calendar_settings.php?action=callback for local)
 * Enable Google Calendar API for the project.
 */
return [
    'client_id'     => 'YOUR_CLIENT_ID.apps.googleusercontent.com',
    'client_secret' => 'YOUR_CLIENT_SECRET',
    'redirect_uri'  => '', // leave empty to use auto (current page with ?action=callback)
];

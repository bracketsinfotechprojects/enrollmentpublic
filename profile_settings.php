<?php
include('includes/dbconnect.php');
require_once __DIR__ . '/includes/enquiry_status_counselling_email_helper.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: index.php');
    exit;
}

$session_user_type = $_SESSION['user_type'] ?? null;
$user_id = (int)$_SESSION['user_id'];
$user = null;
$name = '';
$email = '';
$phone = '';
$address = '';
$roleLabel = 'User';
$is_admin = false;

if ($session_user_type === 'student') {
    // Self-registered student – use student_users table
    $stuRes = mysqli_query($connection, "SELECT * FROM student_users WHERE id = $user_id LIMIT 1");
    $stu = $stuRes ? mysqli_fetch_assoc($stuRes) : null;
    if ($stu) {
        $user = $stu;
        $name = $stu['full_name'];
        $email = $stu['email'];
        $phone = isset($stu['phone']) ? $stu['phone'] : '';
        $roleLabel = 'Student';
    }
} else {
    // Admin / staff / admin-created student (users table)
    $userRes = mysqli_query($connection, "SELECT * FROM users WHERE user_id = $user_id LIMIT 1");
    $user = $userRes ? mysqli_fetch_assoc($userRes) : null;
    if ($user) {
        $name = $user['user_name'];
        $email = $user['user_email'];
        $phone = isset($user['user_phone']) ? $user['user_phone'] : '';
        $address = isset($user['user_address']) ? $user['user_address'] : '';
        if ((int)$user['user_type'] === 1) {
            $roleLabel = 'Admin';
            $is_admin = true;
        } elseif ((int)$user['user_type'] === 2) {
            $roleLabel = 'Staff';
        } elseif ((int)$user['user_type'] === 0) {
            $roleLabel = 'Student';
        }
    }
}

// Load enquiry email templates data for admin users (for Settings → Enquiry Email Templates)
$status_labels = array(
    1 => 'New',
    2 => 'Contacted',
    3 => 'Follow-up Required',
    4 => 'Interested',
    5 => 'Documents Collected',
    6 => 'Enrolled',
    7 => 'Not Interested',
    8 => 'Invalid / Duplicate',
    9 => 'Booked Counselling',
    10 => 'Re-enquired',
    11 => 'Counselling Pending',
    12 => 'Counselling Done',
    13 => 'Rescheduling',
    14 => 'Rejected',
);
$email_template_codes = array_merge(range(1, 11), array(12, 13, 14));
$templates = array();
$appt_template_defaults = array(
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
$appt_templates = array();
if ($is_admin) {
    datacontrol_seed_counselling_outcome_email_templates($connection);
    $q = mysqli_query($connection, "SELECT id, status_code, subject, body, updated_at FROM enquiry_status_email_templates ORDER BY status_code");
    if ($q && mysqli_num_rows($q)) {
        while ($row = mysqli_fetch_assoc($q)) {
            $templates[$row['status_code']] = $row;
        }
    }
    foreach ($email_template_codes as $i) {
        if (!isset($templates[$i])) {
            $templates[$i] = array('id' => '', 'status_code' => $i, 'subject' => '', 'body' => '', 'updated_at' => null);
        }
    }
    @mysqli_query($connection, "CREATE TABLE IF NOT EXISTS appointment_email_templates (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        template_code VARCHAR(64) NOT NULL UNIQUE,
        template_name VARCHAR(128) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        updated_at DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    foreach ($appt_template_defaults as $code => $cfg) {
        $codeEsc = mysqli_real_escape_string($connection, $code);
        $nameEsc = mysqli_real_escape_string($connection, $cfg['name']);
        $subjectEsc = mysqli_real_escape_string($connection, $cfg['subject']);
        $bodyEsc = mysqli_real_escape_string($connection, $cfg['body']);
        @mysqli_query($connection, "INSERT INTO appointment_email_templates(template_code,template_name,subject,body,updated_at) VALUES('$codeEsc','$nameEsc','$subjectEsc','$bodyEsc',NOW()) ON DUPLICATE KEY UPDATE template_name=VALUES(template_name)");
    }
    $aq = mysqli_query($connection, "SELECT template_code, template_name, subject, body, updated_at FROM appointment_email_templates ORDER BY id");
    if ($aq && mysqli_num_rows($aq)) {
        while ($row = mysqli_fetch_assoc($aq)) {
            $appt_templates[$row['template_code']] = $row;
        }
    }
    foreach ($appt_template_defaults as $code => $cfg) {
        if (!isset($appt_templates[$code])) {
            $appt_templates[$code] = array('template_code' => $code, 'template_name' => $cfg['name'], 'subject' => '', 'body' => '', 'updated_at' => null);
        }
    }
}

// Google Calendar (admin only): save credentials from form POST
$google_message = '';
$google_error = '';
$google_config = array('client_id' => '', 'client_secret' => '', 'redirect_uri' => '');
$google_auth_url = '';
$google_redirect_uri_used = '';
$google_connected = false;

if ($is_admin) {
    require_once __DIR__ . '/includes/google_calendar_helper.php';
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
            header('Location: profile_settings.php?tab=google&saved_google=1');
            exit;
        }
        $google_error = 'Run the database migration first: sql/google_calendar_migration.sql (creates site_settings and google_calendar_tokens tables).';
    }
    $google_config = google_calendar_get_config($connection);
    $google_auth_url = google_calendar_get_auth_url($connection);
    $google_redirect_uri_used = !empty(trim($google_config['redirect_uri'])) ? trim($google_config['redirect_uri']) : google_calendar_build_redirect_uri();
    $tbl = @mysqli_query($connection, "SHOW TABLES LIKE 'google_calendar_tokens'");
    $google_connected = $tbl && mysqli_num_rows($tbl) && mysqli_fetch_row(mysqli_query($connection, "SELECT 1 FROM google_calendar_tokens WHERE id=1 AND refresh_token IS NOT NULL AND refresh_token != ''"));
    if (!empty($_GET['saved_google'])) $google_message = 'Credentials saved. Copy the Redirect URI below into Google Console if you haven\'t already, then connect your Google account.';
    if (!empty($_GET['connected'])) $google_message = 'Google Calendar connected. When any staff sets a Next Follow-Up Date, an event is created and all dashboard staff are added as attendees so everyone gets it on their calendar.';
    if (!empty($_GET['disconnected'])) $google_message = 'Google Calendar disconnected.';
    if (!empty($_GET['google_error'])) $google_error = trim((string) $_GET['google_error']);
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Profile & Settings</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include('includes/app_includes.php'); ?>
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
                                    <h4 class="mb-sm-0">Settings</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                            <li class="breadcrumb-item active">Settings</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 mb-3">
                            <div class="card-body pb-0 pt-0 px-2">
                                <ul class="nav nav-tabs nav-bordered nav-bordered-primary">
                                    <li class="nav-item me-3">
                                        <a href="javascript:void(0);" class="nav-link p-2 active settings-main-tab" data-target="#settings_profile_section">
                                            <i class="ti ti-settings-cog me-2"></i>Profile
                                        </a>
                                    </li>
                                    <li class="nav-item me-3">
                                        <a href="javascript:void(0);" class="nav-link p-2 settings-main-tab" data-target="#settings_security_section">
                                            <i class="ti ti-lock me-2"></i>Security
                                        </a>
                                    </li>
                                    <?php if($is_admin){ ?>
                                    <li class="nav-item me-3">
                                        <a href="javascript:void(0);" class="nav-link p-2 settings-main-tab" data-target="#settings_email_section">
                                            <i class="ti ti-mail me-2"></i>Enquiry Email Templates
                                        </a>
                                    </li>
                                    <li class="nav-item me-3">
                                        <a href="javascript:void(0);" class="nav-link p-2 settings-main-tab" data-target="#settings_appt_email_section">
                                            <i class="ti ti-calendar-event me-2"></i>Appointment Email Templates
                                        </a>
                                    </li>
                                    <li class="nav-item me-3">
                                        <a href="javascript:void(0);" class="nav-link p-2 settings-main-tab" data-target="#settings_google_section">
                                            <i class="ti ti-calendar me-2"></i>Google Calendar
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-3 col-lg-12">
                                <div class="card mb-3 mb-xl-0">
                                    <div class="card-body">
                                            <div class="settings-sidebar">
                                                <h5 class="mb-3 fs-17">Profile Settings</h5>
                                                <div class="list-group list-group-flush settings-sidebar">
                                                    <a href="javascript:void(0);" class="d-block p-2 fw-medium settings-side-link active" data-target="#settings_profile_section">Profile</a>
                                                    <a href="javascript:void(0);" class="d-block p-2 fw-medium settings-side-link" data-target="#settings_security_section">Change Password</a>
                                                    <?php if($is_admin){ ?>
                                                    <a href="javascript:void(0);" class="d-block p-2 fw-medium settings-side-link" data-target="#settings_email_section">Enquiry Email Templates</a>
                                                    <a href="javascript:void(0);" class="d-block p-2 fw-medium settings-side-link" data-target="#settings_appt_email_section">Appointment Email Templates</a>
                                                    <a href="javascript:void(0);" class="d-block p-2 fw-medium settings-side-link" data-target="#settings_google_section">Google Calendar</a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-9 col-lg-12">
                                <div class="card mb-0">
                                    <div class="card-body">

                                        <div id="settings_profile_section">
                                            <div class="border-bottom mb-3 pb-3 d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0 fs-17">Profile</h5>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($roleLabel); ?></span>
                                            </div>
                                            <div class="mb-3">
                                                <h6 class="mb-1">Account Information</h6>
                                                <p class="mb-0 text-muted">Update your basic details below.</p>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                                        <input type="text" id="profile_name" class="form-control" value="<?php echo htmlspecialchars($name); ?>">
                                                        <div class="error-feedback">Please enter your name.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                                        <input type="email" id="profile_email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                                                        <div class="error-feedback">Please enter a valid email.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Mobile</label>
                                                        <input type="text" id="profile_phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                                                        <div class="error-feedback">Please enter a valid mobile number.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Address</label>
                                                        <textarea id="profile_address" class="form-control" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-primary" id="save_profile_btn">Save Changes</button>
                                            </div>
                                        </div>

                                        <div id="settings_security_section" class="d-none">
                                            <div class="border-bottom mb-3 pb-3">
                                                <h5 class="mb-0 fs-17">Security</h5>
                                            </div>
                                            <div class="mb-3">
                                                <h6 class="mb-1">Change Password</h6>
                                                <p class="mb-0 text-muted">Use a strong password that you do not use elsewhere.</p>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                                        <input type="password" id="current_password" class="form-control">
                                                        <div class="error-feedback" id="current_password_error">Please enter your current password.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                                                        <input type="password" id="new_password" class="form-control">
                                                        <div class="error-feedback" id="new_password_error">Password must be at least 6 characters.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                                        <input type="password" id="confirm_password" class="form-control">
                                                        <div class="error-feedback" id="confirm_password_error">New password and confirm password do not match.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end align-items-center gap-2">
                                                <span class="text-success d-none" id="password_success_message">Password updated successfully.</span>
                                                <button type="button" class="btn btn-sm btn-primary" id="change_password_btn">Update Password</button>
                                            </div>
                                        </div>

                                        <div id="settings_email_section" class="d-none">
                                            <div class="border-bottom mb-3 pb-3">
                                                <h5 class="mb-0 fs-17">Enquiry Email Templates</h5>
                                            </div>
                                            <?php if($is_admin){ ?>
                                            <p class="text-muted">
                                                Manage the default email templates used for each enquiry status (New, Contacted, Follow-up Required, etc.) and for <strong>counselling outcomes</strong> (Counselling Done, Rescheduling, Rejected).
                                                Follow-up uses status templates 1–11; the counselling accordion uses codes 12–14.
                                                You can use placeholders: <code>{{FirstName}}</code>, <code>{{CourseName}}</code>, <code>{{OfficerName}}</code> (and legacy <code>{{student_name}}</code>). For <strong>Booked Counselling</strong> (code 9): <code>{{CounsellingDate}}</code> and <code>{{CounsellingTime}}</code> come from the linked appointment. For <strong>counselling emails</strong> (12–14): the same placeholders are filled from the counselling session date and times on the form (or the latest saved counselling record).
                                            </p>
                                            <div class="accordion" id="templatesAccordion">
                                                <?php
                                                $first_acc = true;
                                                foreach ($email_template_codes as $idx => $i) {
                                                    $t = $templates[$i];
                                                    $sid = (int)$t['status_code'];
                                                    $subj = htmlspecialchars($t['subject'] ?? '');
                                                    $body = htmlspecialchars($t['body'] ?? '');
                                                    $updated = !empty($t['updated_at']) ? date('d M Y H:i', strtotime($t['updated_at'])) : '—';
                                                    $label = isset($status_labels[$i]) ? $status_labels[$i] : ('Template ' . $i);
                                                    $badge = ($i >= 12 && $i <= 14)
                                                        ? '<span class="badge bg-info ms-2">Counselling email</span>'
                                                        : '<span class="badge bg-secondary ms-2">Code ' . $i . '</span>';
                                                    $show = $first_acc;
                                                    $first_acc = false;
                                                ?>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="head<?php echo $sid; ?>">
                                                        <button class="accordion-button <?php echo $show ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $sid; ?>" aria-expanded="<?php echo $show ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $sid; ?>">
                                                            <?php echo htmlspecialchars($label); ?> <?php echo $badge; ?>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse<?php echo $sid; ?>" class="accordion-collapse collapse <?php echo $show ? 'show' : ''; ?>" aria-labelledby="head<?php echo $sid; ?>" data-bs-parent="#templatesAccordion">
                                                        <div class="accordion-body">
                                                            <div class="mb-2 small text-muted">Last updated: <?php echo $updated; ?></div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Subject</label>
                                                                <input type="text" class="form-control template-subject" data-status="<?php echo $sid; ?>" value="<?php echo $subj; ?>" placeholder="Email subject">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Body</label>
                                                                <textarea class="form-control template-body" data-status="<?php echo $sid; ?>" rows="8" placeholder="Email body. Use {{FirstName}}, {{CourseName}}, {{OfficerName}} as needed."><?php echo $body; ?></textarea>
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm save-template-btn" data-status="<?php echo $sid; ?>">Save template</button>
                                                            <span class="ms-2 save-feedback" data-status="<?php echo $sid; ?>"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } else { ?>
                                            <p class="text-muted">
                                                Only admin users can manage enquiry email templates.
                                                Please contact an administrator if you need a template updated.
                                            </p>
                                            <?php } ?>
                                        </div>

                                        <div id="settings_appt_email_section" class="d-none">
                                            <div class="border-bottom mb-3 pb-3">
                                                <h5 class="mb-0 fs-17">Appointment Email Templates</h5>
                                            </div>
                                            <?php if($is_admin){ ?>
                                            <p class="text-muted">
                                                Manage templates used when appointments are created from the portal.
                                                Placeholders:
                                                <code>{{FirstName}}</code>, <code>{{StudentName}}</code>, <code>{{EnquiryID}}</code>, <code>{{PurposeName}}</code>,
                                                <code>{{AppointmentDate}}</code>, <code>{{AppointmentTime}}</code>, <code>{{MeetingType}}</code>,
                                                <code>{{Location}}</code>, <code>{{Platform}}</code>, <code>{{MeetingLink}}</code>, <code>{{StaffName}}</code>,
                                                <code>{{BookedBy}}</code>, <code>{{StudentPhone}}</code>, <code>{{BookingComments}}</code>, <code>{{AppointmentNotes}}</code>.
                                            </p>
                                            <div class="accordion" id="apptTemplatesAccordionInSettings">
                                                <?php
                                                $first_appt_acc = true;
                                                foreach ($appt_template_defaults as $code => $cfg) {
                                                    $t = $appt_templates[$code];
                                                    $show = $first_appt_acc;
                                                    $first_appt_acc = false;
                                                    $sid = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
                                                    $title = htmlspecialchars($t['template_name'] ?? $cfg['name'], ENT_QUOTES, 'UTF-8');
                                                    $subject = htmlspecialchars($t['subject'] ?? '', ENT_QUOTES, 'UTF-8');
                                                    $body = htmlspecialchars($t['body'] ?? '', ENT_QUOTES, 'UTF-8');
                                                    $updated = !empty($t['updated_at']) ? date('d M Y H:i', strtotime($t['updated_at'])) : '—';
                                                ?>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="apptHead_<?php echo $sid; ?>">
                                                        <button class="accordion-button <?php echo $show ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#apptCollapse_<?php echo $sid; ?>">
                                                            <?php echo $title; ?>
                                                        </button>
                                                    </h2>
                                                    <div id="apptCollapse_<?php echo $sid; ?>" class="accordion-collapse collapse <?php echo $show ? 'show' : ''; ?>" data-bs-parent="#apptTemplatesAccordionInSettings">
                                                        <div class="accordion-body">
                                                            <div class="mb-2 small text-muted">Last updated: <?php echo $updated; ?></div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Subject</label>
                                                                <input type="text" class="form-control appt-template-subject" data-code="<?php echo $sid; ?>" value="<?php echo $subject; ?>" placeholder="Email subject">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Body</label>
                                                                <textarea class="form-control appt-template-body" data-code="<?php echo $sid; ?>" rows="8" placeholder="Email body"><?php echo $body; ?></textarea>
                                                            </div>
                                                            <button type="button" class="btn btn-primary btn-sm save-appt-template-btn" data-code="<?php echo $sid; ?>">Save template</button>
                                                            <span class="ms-2 appt-save-feedback" data-code="<?php echo $sid; ?>"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } else { ?>
                                            <p class="text-muted">
                                                Only admin users can manage appointment email templates.
                                            </p>
                                            <?php } ?>
                                        </div>

                                        <?php if ($is_admin) { ?>
                                        <div id="settings_google_section" class="d-none">
                                            <div class="border-bottom mb-3 pb-3">
                                                <h5 class="mb-0 fs-17">Google Calendar – Follow-up reminders</h5>
                                            </div>
                                            <?php if ($google_message): ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($google_message); ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($google_error): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($google_error); ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                            <?php endif; ?>
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">1. Prerequisites (one-time setup)</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted small">One admin connects a single Google account here. When <strong>any</strong> staff sets a <strong>Next Follow-Up Date</strong>, an event is created and <strong>all dashboard staff</strong> are added as attendees. Create OAuth credentials in Google Cloud and save them below.</p>
                                                    <ol class="small mb-3">
                                                        <li>Go to <a href="https://console.cloud.google.com/" target="_blank" rel="noopener">Google Cloud Console</a> and create/select a project.</li>
                                                        <li>Enable <strong>Google Calendar API</strong>: APIs &amp; Services → Library → search “Calendar API” → Enable.</li>
                                                        <li>Create <strong>OAuth 2.0 Client ID</strong>: Web application. Under <strong>Authorized redirect URIs</strong>, add the exact URL shown in “Redirect URI” below.</li>
                                                        <li>Copy <strong>Client ID</strong> and <strong>Client Secret</strong> from Google and paste below.</li>
                                                    </ol>
                                                    <form method="post" action="profile_settings.php" class="mb-0">
                                                        <div class="mb-2">
                                                            <label class="form-label">Redirect URI <span class="text-muted">(copy into Google Console → Authorized redirect URIs)</span></label>
                                                            <input type="text" name="google_redirect_uri" class="form-control font-monospace small" placeholder="Leave blank to use auto-detected URL" value="<?php echo htmlspecialchars($google_config['redirect_uri']); ?>">
                                                            <small class="text-muted">If blank we use: <code><?php echo htmlspecialchars($google_redirect_uri_used); ?></code></small>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Client ID</label>
                                                            <input type="text" name="google_client_id" class="form-control" placeholder="xxxxx.apps.googleusercontent.com" value="<?php echo htmlspecialchars($google_config['client_id']); ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Client Secret</label>
                                                            <input type="password" name="google_client_secret" class="form-control" placeholder="GOCSPX-..." value="<?php echo htmlspecialchars($google_config['client_secret']); ?>">
                                                        </div>
                                                        <button type="submit" name="save_google_config" class="btn btn-outline-primary btn-sm">Save credentials</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="card mb-0">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">2. Connect one Google account (admin)</h6>
                                                </div>
                                                <div class="card-body">
                                                    <?php if ($google_connected): ?>
                                                        <p class="text-success mb-2">A Google account is connected. When any staff sets a Next Follow-Up Date, an event is created and all dashboard users are added as attendees.</p>
                                                        <a href="google_calendar_settings.php?action=disconnect" class="btn btn-outline-danger btn-sm">Disconnect Google Calendar</a>
                                                    <?php else: ?>
                                                        <?php if (empty($google_config['client_id']) || empty($google_config['client_secret'])): ?>
                                                            <p class="text-warning mb-0">Save your Client ID and Client Secret above first, then click the button below.</p>
                                                        <?php else: ?>
                                                            <p class="mb-2">Connect <strong>one</strong> Google account. Events will be created from this account and all staff added as attendees.</p>
                                                            <a href="<?php echo htmlspecialchars($google_auth_url); ?>" class="btn btn-primary btn-sm"><i class="ti ti-calendar"></i> Connect Google Calendar</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/footer_includes.php'); ?>
        <script>
        $(function(){
            function showSection(target){
                $('#settings_profile_section, #settings_security_section, #settings_email_section, #settings_appt_email_section, #settings_google_section').addClass('d-none');
                $(target).removeClass('d-none');
                $('.settings-main-tab, .settings-side-link').removeClass('active');
                $('.settings-main-tab[data-target="'+target+'"]').addClass('active');
                $('.settings-side-link[data-target="'+target+'"]').addClass('active');
            }

            var tab = (new URLSearchParams(window.location.search)).get('tab');
            if (tab === 'google') {
                showSection('#settings_google_section');
            } else if (tab === 'appointment_email') {
                showSection('#settings_appt_email_section');
            }

            $(document).on('click','.settings-main-tab, .settings-side-link',function(e){
                e.preventDefault();
                var target = $(this).data('target');
                if(target){
                    showSection(target);
                }
            });

            $('#save_profile_btn').on('click', function(){
                var name = $('#profile_name').val().trim();
                var email = $('#profile_email').val().trim();
                var phone = $('#profile_phone').val().trim();
                var address = $('#profile_address').val().trim();

                // Clear previous validation states
                $('.error-feedback').hide();
                $('#profile_name, #profile_email, #profile_phone').removeClass('is-invalid');

                var hasError = false;
                if(!name){
                    $('#profile_name').addClass('is-invalid')
                        .closest('.mb-3').find('.error-feedback').show();
                    hasError = true;
                }

                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!email || !emailPattern.test(email)){
                    $('#profile_email').addClass('is-invalid')
                        .closest('.mb-3').find('.error-feedback').show();
                    hasError = true;
                }

                if(phone){
                    var phonePattern = /^[0-9]{8,15}$/;
                    if(!phonePattern.test(phone)){
                        $('#profile_phone').addClass('is-invalid')
                            .closest('.mb-3').find('.error-feedback').show();
                        hasError = true;
                    }
                }

                if(hasError){
                    return;
                }
                $.post('includes/datacontrol', {
                    formName: 'update_profile',
                    user_name: name,
                    user_email: email,
                    user_phone: phone,
                    user_address: address
                }, function(resp){
                    if(resp == 1){
                        $('#toast-text').html('Profile updated successfully');
                        $('#borderedToast1Btn').trigger('click');
                    }else{
                        $('.toast-text2').html('Unable to update profile. Please try again.');
                        $('#borderedToast2Btn').trigger('click');
                    }
                });
            });

            $('#change_password_btn').on('click', function(){
                var current = $('#current_password').val().trim();
                var nw = $('#new_password').val().trim();
                var confirm = $('#confirm_password').val().trim();

                // Clear previous validation states
                $('#current_password, #new_password, #confirm_password').removeClass('is-invalid');
                $('#current_password_error, #new_password_error, #confirm_password_error')
                    .hide()
                    .text(function(i, t){ return t; }); // reset to default text
                $('#password_success_message').addClass('d-none');

                var hasError = false;
                if(!current){
                    $('#current_password').addClass('is-invalid');
                    $('#current_password_error').text('Please enter your current password.').show();
                    hasError = true;
                }
                if(!nw || nw.length < 6){
                    $('#new_password').addClass('is-invalid');
                    $('#new_password_error').show();
                    hasError = true;
                }
                if(!confirm || nw !== confirm){
                    $('#confirm_password').addClass('is-invalid');
                    $('#confirm_password_error').show();
                    hasError = true;
                }
                if(hasError){
                    return;
                }
                $.post('includes/datacontrol', {
                    formName: 'change_password',
                    current_password: current,
                    new_password: nw
                }, function(resp){
                    if(resp == 1){
                        $('#toast-text').html('Password updated successfully');
                        $('#borderedToast1Btn').trigger('click');
                        $('#current_password, #new_password, #confirm_password').val('');
                        $('#password_success_message').removeClass('d-none');
                    }else if(resp === 'INVALID'){
                        $('#current_password').addClass('is-invalid');
                        $('#current_password_error').text('Current password is incorrect.').show();
                    }else{
                        $('.toast-text2').html('Unable to update password. Please try again.');
                        $('#borderedToast2Btn').trigger('click');
                    }
                });
            });

            // Save enquiry email templates (admin only)
            $(document).on('click', '.save-template-btn', function(){
                var status = $(this).data('status');
                var subject = $('.template-subject[data-status="'+status+'"]').val().trim();
                var body = $('.template-body[data-status="'+status+'"]').val().trim();
                var $btn = $(this).prop('disabled', true).text('Saving...');
                var $fb = $('.save-feedback[data-status="'+status+'"]').removeClass('text-success text-danger').text('');

                if(!subject || !body){
                    $btn.prop('disabled', false).text('Save template');
                    $fb.addClass('text-danger').text('Subject and body are required.');
                    return;
                }
                $.post('includes/datacontrol', { save_enquiry_status_template: 1, status_code: status, subject: subject, body: body }, function(data){
                    $btn.prop('disabled', false).text('Save template');
                    if (data == '1') {
                        $fb.addClass('text-success').text('Saved.');
                        $('#toast-text').html('Email template updated successfully');
                        $('#borderedToast1Btn').trigger('click');
                        setTimeout(function(){ $fb.text(''); }, 3000);
                    } else {
                        $fb.addClass('text-danger').text('Failed to save.');
                        $('.toast-text2').html('Unable to save email template. Please try again.');
                        $('#borderedToast2Btn').trigger('click');
                    }
                });
            });

            $(document).on('click', '.save-appt-template-btn', function(){
                var code = $(this).data('code');
                var subject = $('.appt-template-subject[data-code="'+code+'"]').val().trim();
                var body = $('.appt-template-body[data-code="'+code+'"]').val().trim();
                var $btn = $(this).prop('disabled', true).text('Saving...');
                var $fb = $('.appt-save-feedback[data-code="'+code+'"]').removeClass('text-success text-danger').text('');
                if(!subject || !body){
                    $btn.prop('disabled', false).text('Save template');
                    $fb.addClass('text-danger').text('Subject and body are required.');
                    return;
                }
                $.post('includes/datacontrol', { save_appointment_email_template: 1, template_code: code, subject: subject, body: body }, function(data){
                    $btn.prop('disabled', false).text('Save template');
                    if (String(data).trim() === '1') {
                        $fb.addClass('text-success').text('Saved.');
                        $('#toast-text').html('Appointment email template updated successfully');
                        $('#borderedToast1Btn').trigger('click');
                        setTimeout(function(){ $fb.text(''); }, 3000);
                    } else {
                        $fb.addClass('text-danger').text('Failed to save.');
                        $('.toast-text2').html('Unable to save appointment email template. Please try again.');
                        $('#borderedToast2Btn').trigger('click');
                    }
                });
            });
        });
        </script>
    </body>
</html>


<?php
/**
 * STANDALONE Enrol / Course Application Form
 * ------------------------------------------
 * Deploy this ONE file on your public website (any server). No other includes or CRM files needed.
 * 1. Edit the CONFIG block below: set your CRM database host/user/pass/name (can be remote).
 * 2. Set CRM_REGISTER_URL and CRM_LOGIN_URL to your CRM domain so users can register/log in after submitting.
 * 3. Set FROM_EMAIL and mail method (mail or smtp). Form submits to this same file; data is saved to CRM DB and a confirmation email is sent.
 */
// ============== CONFIG – EDIT THESE FOR YOUR SETUP ==============
define('DB_HOST', 'localhost');           // CRM database host (or IP if DB is on another server)
define('DB_USER', 'root');                // CRM database username
define('DB_PASS', '');                    // CRM database password
define('DB_NAME', 'auztraining');         // CRM database name
define('SITE_NAME', 'National College Australia');
define('FROM_EMAIL', 'noreply@yoursite.com');  // From address for confirmation email
define('ADMIN_EMAIL', 'info@nca.edu.au');      // Optional: notify this email on new enquiry
define('CRM_REGISTER_URL', 'https://your-crm-domain.com/student_register.php');  // Link shown after submit
define('CRM_LOGIN_URL', 'https://your-crm-domain.com/student_login.php');
// Mail: set to 'smtp' to use SMTP below; otherwise uses PHP mail()
define('MAIL_METHOD', 'mail');
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_SECURE', 'ssl');
// ============== END CONFIG ==============

$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    $conn = null;
}
$form_error = '';
$form_success = false;
$enquiry_id = '';

// ---------- Form submit handler (minimal fields: name, surname, email, mobile) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $studentName = trim((string)($_POST['studentName'] ?? ''));
    $surname = trim((string)($_POST['surname'] ?? ''));
    $emailAddress = trim((string)($_POST['emailAddress'] ?? ''));
    $contactName = preg_replace('/[^0-9]/', '', (string)($_POST['contactName'] ?? ''));

    $valid = true;
    if ($studentName === '' || $surname === '' || $emailAddress === '') {
        $valid = false;
        $form_error = 'Please fill in your name, surname and email.';
    }
    if ($valid && strlen($contactName) !== 10) {
        $valid = false;
        $form_error = 'Please enter a valid 10-digit mobile number.';
    }
    if ($valid && !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
        $valid = false;
        $form_error = 'Please enter a valid email address.';
    }

    if ($valid) {
        $st_name = mysqli_real_escape_string($conn, $studentName);
        $st_member_name = mysqli_real_escape_string($conn, $studentName);
        $contactName = mysqli_real_escape_string($conn, $contactName);
        $emailAddress = mysqli_real_escape_string($conn, $emailAddress);
        $surname_esc = mysqli_real_escape_string($conn, $surname);
        $enquiry_date = mysqli_real_escape_string($conn, date('Y-m-d')) . ' 00:00:00';

        $sql = "INSERT INTO student_enquiry (st_name, st_member_name, st_phno, st_email, st_course, st_fee, st_visa_status, st_visa_condition, st_visa_note, st_surname, st_suburb, st_state, st_post_code, st_visited, st_heared, st_hearedby, st_startplan_date, st_refered, st_refer_name, st_refer_alumni, st_comments, st_pref_comments, st_appoint_book, st_remarks, st_street_details, st_enquiry_for, st_enquiry_date, st_course_type, st_shore, st_ethnicity, st_created_by, st_gen_enq_type) VALUES (
            '$st_name','$st_member_name','$contactName','$emailAddress','[]','',0,1,'','$surname_esc','','0','0',0,'','','0000-00-00 00:00:00',0,'',0,'','',0,'','',1,'$enquiry_date',0,0,'',0,2)";
        if (mysqli_query($conn, $sql)) {
            $lastId = (int)mysqli_insert_id($conn);
            $enquiry_id = sprintf('EQ%05d', $lastId);
            mysqli_query($conn, "UPDATE student_enquiry SET st_enquiry_id='$enquiry_id' WHERE st_id=$lastId");
            $form_success = true;

            // Send confirmation email to applicant
            $subject = 'Your Enquiry – ' . SITE_NAME;
            $body = "Thank you for your interest.<br><br>Your Enquiry ID: <strong>$enquiry_id</strong> (keep this for reference).<br><br><strong>Next step:</strong> Register or log in to complete your full enrolment application and submit all form details.<br><br>Register: " . CRM_REGISTER_URL . "<br>Log in: " . CRM_LOGIN_URL;
            enrol_send_mail($emailAddress, $subject, $body, array('email_category' => 'public_enquiry_form', 'st_enquiry_id' => $enquiry_id, 'st_id' => $lastId, 'meta' => array('recipient_role' => 'applicant')));

            // Optional: notify admin
            if (ADMIN_EMAIL !== '') {
                enrol_send_mail(ADMIN_EMAIL, 'New Enquiry ' . $enquiry_id . ' – ' . $studentName . ' ' . $surname, 'A new course application was submitted. Enquiry ID: ' . $enquiry_id, array('email_category' => 'public_enquiry_form', 'st_enquiry_id' => $enquiry_id, 'st_id' => $lastId, 'meta' => array('recipient_role' => 'admin_notify')));
            }
        } else {
            $form_error = 'Sorry, we could not save your application. Please try again or contact us.';
        }
    }
}

function enrol_send_mail($to, $subject, $body, array $ctx = array()) {
    $from = FROM_EMAIL;
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: " . SITE_NAME . " <$from>\r\n";
    $ok = true;
    $err = null;
    if (MAIL_METHOD === 'smtp' && defined('SMTP_USER') && SMTP_USER !== '') {
        enrol_smtp_mail($to, $subject, $body, $from);
    } else {
        $ok = @mail($to, $subject, $body, $headers);
        if (!$ok) {
            $err = 'mail() returned false';
        }
    }
    global $conn;
    if (!empty($conn) && $conn instanceof mysqli && file_exists(__DIR__ . '/includes/email_log_helper.php')) {
        require_once __DIR__ . '/includes/email_log_helper.php';
        crm_email_ensure_log_table($conn);
        crm_email_log_record($conn, $to, $subject, $body, $ok ? 'sent' : 'failed', $err, array_merge(array('email_category' => 'public_enquiry_form'), $ctx));
    }
}

function enrol_smtp_mail($to, $subject, $body, $from) {
    $host = defined('SMTP_HOST') ? SMTP_HOST : '';
    $port = (int)(defined('SMTP_PORT') ? SMTP_PORT : 465);
    $user = defined('SMTP_USER') ? SMTP_USER : '';
    $pass = defined('SMTP_PASS') ? SMTP_PASS : '';
    $secure = (defined('SMTP_SECURE') && SMTP_SECURE === 'ssl');
    $errno = $errstr = null;
    $sock = @stream_socket_client(($secure ? 'ssl://' : '') . $host . ':' . $port, $errno, $errstr, 15);
    if (!$sock) return;
    $get = function() use ($sock) { $r = fgets($sock, 512); return $r; };
    $put = function($s) use ($sock) { fwrite($sock, $s . "\r\n"); };
    $get();
    $put('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    while ($line = $get()) { if (substr($line, 3, 1) === ' ') break; }
    $put('AUTH LOGIN');
    $put(base64_encode($user));
    $put(base64_encode($pass));
    $get();
    $put('MAIL FROM:<' . $from . '>');
    $put('RCPT TO:<' . $to . '>');
    $put('DATA');
    $put('Subject: ' . $subject);
    $put('MIME-Version: 1.0');
    $put('Content-Type: text/html; charset=UTF-8');
    $put('');
    $put($body);
    $put('.');
    $get();
    $put('QUIT');
    fclose($sock);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enrol | <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --nca-primary: #0d6efd; --nca-dark: #1a1a2e; --nca-light: #f8f9fa; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--nca-light); color: #333; }
        .enrol-header { background: linear-gradient(135deg, var(--nca-dark) 0%, #16213e 100%); color: #fff; padding: 1.5rem 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .enrol-header .logo { font-size: 1.5rem; font-weight: 700; }
        .enrol-header a { color: #fff; text-decoration: none; }
        .breadcrumb { background: transparent; padding: 0; margin: 0; }
        .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.7); }
        .breadcrumb-item a { color: rgba(255,255,255,0.9); }
        .page-hero { background: linear-gradient(180deg, #fff 0%, var(--nca-light) 100%); padding: 2rem 0 1rem; border-bottom: 1px solid #dee2e6; }
        .page-hero h1 { font-size: 2rem; font-weight: 600; color: var(--nca-dark); }
        .form-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 2rem; margin-bottom: 2rem; }
        .form-card h2 { font-size: 1.35rem; margin-bottom: 1.5rem; color: var(--nca-dark); }
        .asterisk { color: #dc3545; }
        .btn-enrol { background: var(--nca-primary); color: #fff; padding: 0.6rem 2rem; font-weight: 600; border-radius: 8px; }
        .btn-enrol:hover { color: #fff; }
        .success-box { background: #d1e7dd; border: 1px solid #badbcc; border-radius: 12px; padding: 2rem; text-align: center; }
        .success-box .enquiry-id { font-size: 1.5rem; font-weight: 700; color: var(--nca-dark); }
        .success-box .register-cta { margin-top: 1.5rem; padding: 1rem; background: #fff; border-radius: 8px; border: 1px solid #0d6efd; }
        .enrol-footer { background: var(--nca-dark); color: rgba(255,255,255,0.85); padding: 2rem 0; margin-top: 3rem; }
        .enrol-footer a { color: rgba(255,255,255,0.9); }
    </style>
</head>
<body>
    <header class="enrol-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <span class="logo"><?php echo htmlspecialchars(SITE_NAME); ?></span>
            </div>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Enrol</li>
                </ol>
            </nav>
        </div>
    </header>

    <div class="page-hero">
        <div class="container">
            <h1>Course Application Form</h1>
            <p class="text-muted mb-0">Start your journey with us. Fill in your details below.</p>
        </div>
    </div>

    <div class="container py-4">
        <?php if ($form_success): ?>
            <div class="form-card success-box">
                <h3 class="mt-2">Thanks for your interest</h3>
                <p class="mb-0">Your Enquiry ID: <span class="enquiry-id"><?php echo htmlspecialchars($enquiry_id); ?></span></p>
                <div class="register-cta">
                    <p class="mb-2"><strong>To complete your enrolment and submit the full application form, you need to register and log in.</strong></p>
                    <p class="mb-3 text-muted small">Use the same email you just entered. After logging in you can fill in the remaining details and submit your complete enquiry.</p>
                    <a href="<?php echo htmlspecialchars(CRM_REGISTER_URL); ?>" class="btn btn-primary me-2">Register &amp; continue</a>
                    <a href="<?php echo htmlspecialchars(CRM_LOGIN_URL); ?>" class="btn btn-outline-primary">I already have an account – Log in</a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($form_error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($form_error); ?></div>
            <?php endif; ?>
            <?php if (!$conn): ?>
                <div class="alert alert-warning">We are temporarily unable to load the form. Please try again later or contact us.</div>
            <?php endif; ?>

            <div class="form-card">
                <h2>Get started – leave your details</h2>
                <p class="text-muted small mb-3">Submit this short form, then register or log in to complete your full enrolment application.</p>
                <form method="post" action="" id="enrol_form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="asterisk">*</span></label>
                            <input type="text" class="form-control" name="studentName" value="<?php echo htmlspecialchars($_POST['studentName'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Surname <span class="asterisk">*</span></label>
                            <input type="text" class="form-control" name="surname" value="<?php echo htmlspecialchars($_POST['surname'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="asterisk">*</span></label>
                            <input type="email" class="form-control" name="emailAddress" value="<?php echo htmlspecialchars($_POST['emailAddress'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile <span class="asterisk">*</span></label>
                            <input type="tel" class="form-control" name="contactName" maxlength="10" pattern="[0-9]{10}" placeholder="10 digits" value="<?php echo htmlspecialchars($_POST['contactName'] ?? ''); ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-enrol">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <footer class="enrol-footer">
        <div class="container text-center">
            <p class="mb-1">Level 1/118 King William Street Adelaide SA 5000</p>
            <p class="mb-1">08 7119 6196 | <a href="mailto:info@nca.edu.au">info@nca.edu.au</a></p>
            <p class="mb-0 small">© <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?></p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

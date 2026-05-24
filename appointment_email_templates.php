<?php
include('includes/dbconnect.php');
session_start();
if (!isset($_SESSION['user_type']) || (int)$_SESSION['user_type'] !== 1) {
    header('Location: index.php');
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

$templates = array();
$q = mysqli_query($connection, "SELECT template_code, template_name, subject, body, updated_at FROM appointment_email_templates ORDER BY id");
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $templates[$row['template_code']] = $row;
    }
}
foreach ($defaults as $code => $cfg) {
    if (!isset($templates[$code])) {
        $templates[$code] = array(
            'template_code' => $code,
            'template_name' => $cfg['name'],
            'subject' => '',
            'body' => '',
            'updated_at' => null
        );
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Appointment Email Templates</title>
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
                        <h4 class="mb-sm-0">Appointment Email Templates</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="enquiry_email_templates.php">Settings</a></li>
                                <li class="breadcrumb-item active">Appointment Email Templates</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="text-muted">
                        These templates are used for appointment-created emails from the portal.
                        Placeholders: <code>{{FirstName}}</code>, <code>{{StudentName}}</code>, <code>{{EnquiryID}}</code>, <code>{{PurposeName}}</code>, <code>{{AppointmentDate}}</code>, <code>{{AppointmentTime}}</code>, <code>{{MeetingType}}</code>, <code>{{Location}}</code>, <code>{{Platform}}</code>, <code>{{MeetingLink}}</code>, <code>{{StaffName}}</code>, <code>{{BookedBy}}</code>, <code>{{StudentPhone}}</code>, <code>{{BookingComments}}</code>, <code>{{AppointmentNotes}}</code>.
                    </p>
                    <div class="accordion" id="apptTemplatesAccordion">
                        <?php
                        $first = true;
                        foreach ($defaults as $code => $cfg) {
                            $t = $templates[$code];
                            $show = $first;
                            $first = false;
                            $sid = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
                            $title = htmlspecialchars($t['template_name'] ?? $cfg['name'], ENT_QUOTES, 'UTF-8');
                            $subject = htmlspecialchars($t['subject'] ?? '', ENT_QUOTES, 'UTF-8');
                            $body = htmlspecialchars($t['body'] ?? '', ENT_QUOTES, 'UTF-8');
                            $updated = !empty($t['updated_at']) ? date('d M Y H:i', strtotime($t['updated_at'])) : '—';
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="head_<?php echo $sid; ?>">
                                <button class="accordion-button <?php echo $show ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?php echo $sid; ?>">
                                    <?php echo $title; ?>
                                </button>
                            </h2>
                            <div id="collapse_<?php echo $sid; ?>" class="accordion-collapse collapse <?php echo $show ? 'show' : ''; ?>" data-bs-parent="#apptTemplatesAccordion">
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
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
$(function(){
    $(document).on('click', '.save-appt-template-btn', function(){
        var code = $(this).data('code');
        var subject = $('.appt-template-subject[data-code="'+code+'"]').val().trim();
        var body = $('.appt-template-body[data-code="'+code+'"]').val().trim();
        var $btn = $(this).prop('disabled', true).text('Saving...');
        var $fb = $('.appt-save-feedback[data-code="'+code+'"]').removeClass('text-success text-danger').text('');
        $.post('includes/datacontrol', { save_appointment_email_template: 1, template_code: code, subject: subject, body: body }, function(data){
            $btn.prop('disabled', false).text('Save template');
            if (String(data).trim() === '1') {
                $fb.addClass('text-success').text('Saved.');
                setTimeout(function(){ $fb.text(''); }, 2500);
            } else {
                $fb.addClass('text-danger').text('Failed to save.');
            }
        });
    });
});
</script>
<?php include('includes/footer_includes.php'); ?>
</body>
</html>

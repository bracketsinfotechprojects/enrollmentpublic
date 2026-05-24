<?php
include('includes/dbconnect.php');
require_once __DIR__ . '/includes/enquiry_status_counselling_email_helper.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 1) {
    header('Location: index.php');
    exit;
}

// One-time migration: replace old default subjects with new recommended templates (only if still using original subject)
@mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='New Enquiry – Auto Acknowledgement', body='Dear {{FirstName}},\n\nThank you for your interest in studying {{CourseName}} at National College Australia.\nWe have received your enquiry and one of our admissions team members will contact you shortly to discuss your study options, entry requirements, and upcoming intakes.\n\nIf you would like immediate assistance, please feel free to contact us:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to assisting you with your study journey.\n\nKind regards,\nAdmissions Team\nNational College Australia' WHERE status_code=1 AND subject='Thank you for your enquiry'");
@mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='Contacted – Follow-Up After Initial Contact', body='Dear {{FirstName}},\n\nIt was a pleasure speaking with you regarding {{CourseName}}.\nAs discussed, please let us know if you require any additional information about course structure, fees, entry requirements, or intake dates.\nIf you are ready to proceed, we can guide you through the application process.\n\nFor any questions, feel free to contact us:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to supporting you.\n\nKind regards,\n{{OfficerName}}\nNational College Australia' WHERE status_code=2 AND subject='Thank you for your time'");
@mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='Follow-Up Required – Gentle Reminder', body='Dear {{FirstName}},\n\nWe hope you are doing well.\nWe are following up regarding your enquiry about {{CourseName}}. We would be happy to assist you further and answer any questions you may have.\nPlease let us know a suitable time to contact you, or feel free to reach out directly using the details below.\n\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to hearing from you.\n\nKind regards,\nAdmissions Team\nNational College Australia' WHERE status_code=3 AND subject='Follow-up – National College Australia'");
@mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='Interested – Application Invitation', body='Dear {{FirstName}},\n\nThank you for confirming your interest in {{CourseName}}.\nThe next step is to submit your application along with the required supporting documents. Our admissions team is ready to assist you through the process.\nPlease reply to this email if you would like us to send the application form or guide you through the submission process.\n\nFor assistance:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to welcoming you to National College Australia.\n\nKind regards,\n{{OfficerName}}\nAdmissions Team' WHERE status_code=4 AND subject='We are glad you are interested'");
@mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='Documents Collected – Under Assessment', body='Dear {{FirstName}},\n\nThank you for submitting your documents for {{CourseName}}.\nWe confirm that your application is currently under review. Our admissions team will assess your documents and contact you shortly regarding the outcome.\nIf any additional documents are required, we will inform you promptly.\n\nFor enquiries:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nThank you for choosing National College Australia.\n\nKind regards,\nAdmissions Team\nNational College Australia' WHERE status_code=5 AND subject='Documents received'");
@mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='Enrolled – Welcome Email', body='Dear {{FirstName}},\n\nCongratulations on your successful enrolment in {{CourseName}} at National College Australia!\nWe are excited to welcome you to our college community. You will soon receive further information regarding orientation, timetable, and commencement details.\n\nIf you have any questions before your course begins, please contact us:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to supporting you throughout your studies.\n\nKind regards,\nAdmissions & Student Support Team\nNational College Australia' WHERE status_code=6 AND subject='Welcome – Enrolment confirmed'");
@mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='Not Interested – Polite Closure', body='Dear {{FirstName}},\n\nThank you for considering National College Australia for your studies.\nWe understand that you have decided not to proceed at this time. Should you reconsider in the future, we would be happy to assist you.\n\nPlease feel free to contact us anytime:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe wish you all the best in your future endeavours.\n\nKind regards,\nAdmissions Team\nNational College Australia' WHERE status_code=7 AND subject='Thank you for your interest'");
@mysqli_query($connection, "UPDATE enquiry_status_email_templates SET subject='Invalid / Duplicate Enquiry', body='Dear {{FirstName}},\n\nThank you for your recent enquiry.\nIt appears that we may already have your details in our system or that some information provided was incomplete. If this was submitted in error, no further action is required.\nIf you would still like assistance, please contact us directly so we can help you promptly.\n\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nKind regards,\nAdmissions Team\nNational College Australia' WHERE status_code=8 AND subject='Enquiry update'");

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

datacontrol_seed_counselling_outcome_email_templates($connection);

$email_template_codes = array_merge(range(1, 11), array(12, 13, 14));
$templates = array();
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
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Enquiry Status Email Templates</title>
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
                        <h4 class="mb-sm-0">Enquiry Status Email Templates</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="student_enquiry.php">Create Enquiry</a></li>
                                <li class="breadcrumb-item"><a href="student_enquiry.php?view=list">View Enquiry</a></li>
                                <li class="breadcrumb-item active">Email Templates</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="text-muted">
                        Edit templates for the Follow-up section (codes 1–11) and the Counselling section (codes 12–14: Counselling Done, Rescheduling, Rejected).
                        Placeholders: <code>{{FirstName}}</code>, <code>{{CourseName}}</code>, <code>{{OfficerName}}</code> (and legacy <code>{{student_name}}</code>).
                        For <strong>Booked Counselling</strong> (code 9): <code>{{CounsellingDate}}</code> and <code>{{CounsellingTime}}</code> come from the linked appointment.
                        For <strong>counselling emails</strong> (12–14): those placeholders use the counselling session date and times on the form (or the latest saved counselling record).
                    </p>
                    <div class="accordion" id="templatesAccordion">
                        <?php
                        $first_acc = true;
                        foreach ($email_template_codes as $i) {
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
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
$(function(){
    $(document).on('click', '.save-template-btn', function(){
        var status = $(this).data('status');
        var subject = $('.template-subject[data-status="'+status+'"]').val().trim();
        var body = $('.template-body[data-status="'+status+'"]').val().trim();
        var $btn = $(this).prop('disabled', true).text('Saving...');
        var $fb = $('.save-feedback[data-status="'+status+'"]').removeClass('text-success text-danger').text('');
        $.post('includes/datacontrol', { save_enquiry_status_template: 1, status_code: status, subject: subject, body: body }, function(data){
            $btn.prop('disabled', false).text('Save template');
            if (data == '1') {
                $fb.addClass('text-success').text('Saved.');
                setTimeout(function(){ $fb.text(''); }, 3000);
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

<?php

// Include Composer's autoloader (works when called from auztraining or nca)
// Prefer the local includes/vendor (where Symfony Mailer lives), then fall back to project root vendor.
$autoloadPaths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
];
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
    }
}

require_once __DIR__ . '/email_log_helper.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

$email = '';

/**
 * Send HTML email via Symfony Mailer and append to crm_email_log.
 *
 * @param string $to Recipient address
 * @param string $subject
 * @param string $body HTML body
 * @param array $context Optional: email_category, st_enquiry_id, st_id, sent_by_user_id, sent_by_user_name, meta (array)
 */
if (!function_exists('send_mail')) {
function send_mail($to, $subject, $body, array $context = array()) {
    global $connection;

    $conn = (isset($connection) && $connection instanceof mysqli) ? $connection : null;

    $transport = Transport::fromDsn('smtp://noreply%40nationalcollege.edu.au:Noreply%402026mail@smtp.hostinger.com:465?encryption=ssl');
    $mailer = new Mailer($transport);
    $email = (new Email())
        ->from('National College of Australia <noreply@nationalcollege.edu.au>')
        ->to($to)
        ->subject($subject)
        ->html($body);

    try {
        $mailer->send($email);
        if ($conn) {
            crm_email_log_record($conn, $to, $subject, $body, 'sent', null, $context);
        }
    } catch (Throwable $e) {
        if ($conn) {
            crm_email_log_record($conn, $to, $subject, $body, 'failed', $e->getMessage(), $context);
        }
        throw $e;
    }
}
}

/**
 * Send HTML email with a single PDF attachment via Symfony Mailer.
 *
 * @param string $to            Recipient address
 * @param string $subject
 * @param string $body          HTML body
 * @param string $attachment    Absolute filesystem path to the PDF file
 * @param string $attach_name   Filename the recipient sees (e.g. "Invoice-NCA-001.pdf")
 * @param array  $context       Optional log context (same keys as send_mail)
 */
if (!function_exists('send_mail_with_attachment')) {
function send_mail_with_attachment(string $to, string $subject, string $body, string $attachment, string $attach_name, array $context = []): void
{
    global $connection;

    $conn = (isset($connection) && $connection instanceof mysqli) ? $connection : null;

    $transport = Transport::fromDsn('smtp://noreply%40nationalcollege.edu.au:Noreply%402026mail@smtp.hostinger.com:465?encryption=ssl');
    $mailer    = new Mailer($transport);
    $email     = (new Email())
        ->from('National College of Australia <noreply@nationalcollege.edu.au>')
        ->to($to)
        ->subject($subject)
        ->html($body)
        ->attachFromPath($attachment, $attach_name, 'application/pdf');

    try {
        $mailer->send($email);
        if ($conn) {
            crm_email_log_record($conn, $to, $subject, $body, 'sent', null, $context);
        }
    } catch (Throwable $e) {
        if ($conn) {
            crm_email_log_record($conn, $to, $subject, $body, 'failed', $e->getMessage(), $context);
        }
        throw $e;
    }
}
}

<?php
/**
 * Cron: Installment Due Date Reminder
 * Runs daily — sends email to students whose installment due date has passed
 * and marks overdue invoices accordingly.
 *
 * Recommended cron schedule (Linux/cPanel):
 *   0 8 * * * php /path/to/enrollmentpublic/cron_installment_reminder.php >> /path/to/cron.log 2>&1
 *
 * For XAMPP local testing, run via browser:
 *   http://localhost/assessment/enrollmentpublic/cron_installment_reminder.php?key=cron_secret_2025
 */

// ── Security: allow CLI always, browser only with correct key ──
$is_cli = (php_sapi_name() === 'cli');
if (!$is_cli) {
    $allowed_key = 'cron_secret_2025';
    if (($_GET['key'] ?? '') !== $allowed_key) {
        http_response_code(403);
        die('Forbidden.');
    }
}

define('CRON_START', microtime(true));

require_once __DIR__ . '/includes/dbconnect.php';
require_once __DIR__ . '/includes/stripe_config.php';

$today     = date('Y-m-d');
$log_lines = [];

function cron_log(string $msg): void {
    global $log_lines;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    $log_lines[] = $line;
    echo $line . PHP_EOL;
}

cron_log("Installment reminder cron started.");
cron_log("Today: $today");

// ── Step 1: Mark invoices as overdue where all pending installments are past due ──
$mark_overdue = mysqli_query($connection,
    "UPDATE enrolment_invoices ei
     SET ei.status = 'overdue'
     WHERE ei.status = 'pending'
       AND ei.id IN (
           SELECT invoice_id FROM enrolment_invoice_installments
           WHERE status = 'pending' AND due_date < '$today'
       )"
);
cron_log("Invoices marked overdue: " . mysqli_affected_rows($connection));

// ── Step 1b: Ensure reminder_sent column exists ──
$col_check = mysqli_query($connection, "SHOW COLUMNS FROM enrolment_invoice_installments LIKE 'reminder_sent'");
if ($col_check && mysqli_num_rows($col_check) === 0) {
    mysqli_query($connection,
        "ALTER TABLE enrolment_invoice_installments
         ADD COLUMN reminder_sent ENUM('Yes','No') NOT NULL DEFAULT 'No'"
    );
    cron_log("Column reminder_sent added to enrolment_invoice_installments.");
}

// ── Step 2: Fetch pending installments past due date where reminder not yet sent ──
$res = mysqli_query($connection,
    "SELECT eis.id          AS inst_id,
            eis.invoice_id,
            eis.amount,
            eis.gst_amount,
            eis.due_date,
            eis.issue_date,
            eis.currency,
            ei.invoice_number,
            ei.student_name,
            ei.email_address,
            ei.student_id,
            c.course_sname,
            c.course_name,
            DATEDIFF('$today', eis.due_date) AS days_overdue
     FROM enrolment_invoice_installments eis
     LEFT JOIN enrolment_invoices ei ON ei.id = eis.invoice_id
     LEFT JOIN courses c ON c.course_id = eis.course_id
     WHERE eis.status       = 'pending'
       AND eis.reminder_sent = 'No'
       AND eis.due_date < '$today'
       AND ei.email_address IS NOT NULL
       AND ei.email_address != ''
     ORDER BY eis.due_date ASC"
);

if (!$res) {
    cron_log("DB error: " . mysqli_error($connection));
    exit(1);
}

$total     = mysqli_num_rows($res);
$sent      = 0;
$failed    = 0;
$skipped   = 0;

cron_log("Overdue installments found: $total");

// ── Step 3: Send reminder email for each overdue installment ──
while ($row = mysqli_fetch_assoc($res)) {
    $email_to    = trim($row['email_address']);
    $inst_id     = intval($row['inst_id']);
    $inv_number  = $row['invoice_number'];
    $student     = $row['student_name'] ?: 'Student';
    $days_over   = intval($row['days_overdue']);
    $total_due   = floatval($row['amount']) + floatval($row['gst_amount']);
    $currency    = strtoupper(preg_replace('/[^a-zA-Z]/', '', $row['currency'] ?: 'AUD'));
    $due_date    = date('d M Y', strtotime($row['due_date']));
    $course      = ($row['course_sname'] && $row['course_name'])
                   ? $row['course_sname'] . ' – ' . $row['course_name'] : 'your enrolled course';

    if (!filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
        cron_log("  SKIP inst#$inst_id — invalid email: $email_to");
        $skipped++;
        continue;
    }

    $subject = "Payment Reminder – Invoice $inv_number (Overdue $days_over days)";

    $body = '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
.wrap { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.header { background: #1e293b; color: #fff; padding: 24px 32px; }
.header h2 { margin: 0; font-size: 1.2rem; }
.header p  { margin: 4px 0 0; font-size: .85rem; color: #94a3b8; }
.body { padding: 28px 32px; }
.alert { background: #fee2e2; border-left: 4px solid #dc2626; padding: 14px 18px; border-radius: 4px; margin-bottom: 22px; }
.alert strong { color: #991b1b; }
.detail-table { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
.detail-table td { padding: 9px 12px; border-bottom: 1px solid #f0f0f0; font-size: .9rem; }
.detail-table td:first-child { color: #6b7280; width: 40%; }
.detail-table td:last-child { font-weight: 600; }
.amount { font-size: 1.4rem; font-weight: 700; color: #dc2626; }
.btn { display: inline-block; background: #635bff; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: 600; margin-top: 8px; }
.footer { background: #f8fafc; padding: 18px 32px; font-size: .78rem; color: #9ca3af; border-top: 1px solid #e5e7eb; }
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <h2>Payment Reminder</h2>
    <p>National College Australia</p>
  </div>
  <div class="body">
    <p>Dear <strong>' . htmlspecialchars($student) . '</strong>,</p>
    <div class="alert">
      <strong>Your payment is overdue by ' . $days_over . ' day' . ($days_over !== 1 ? 's' : '') . '.</strong><br>
      Invoice <strong>' . htmlspecialchars($inv_number) . '</strong> has an outstanding balance that was due on ' . $due_date . '.
    </div>
    <table class="detail-table">
      <tr><td>Invoice Number</td><td>' . htmlspecialchars($inv_number) . '</td></tr>
      <tr><td>Course</td><td>' . htmlspecialchars($course) . '</td></tr>
      <tr><td>Due Date</td><td>' . $due_date . '</td></tr>
      <tr><td>Days Overdue</td><td style="color:#dc2626;">' . $days_over . ' days</td></tr>
      <tr><td>Amount Due</td><td><span class="amount">$' . number_format($total_due, 2) . ' ' . $currency . '</span></td></tr>
    </table>
    <p>Please arrange payment immediately to avoid any disruption to your enrolment.</p>
    <p>If you have already made a payment, please disregard this notice or contact us to confirm receipt.</p>
    <a href="' . APP_BASE_URL . '/installment_view.php?id=' . $inst_id . '" class="btn">View </a>
    <p style="margin-top:24px;font-size:.85rem;color:#6b7280;">
      If you have any questions, please contact us at
      <a href="mailto:accounts@nationalcollege.edu.au">accounts@nationalcollege.edu.au</a>.
    </p>
  </div>
  <div class="footer">
    &copy; ' . date('Y') . ' National College Australia &nbsp;|&nbsp; RTO: 91000<br>
    You are receiving this email because you have an outstanding invoice with National College Australia.
  </div>
</div>
</body></html>';

    try {
        send_mail($email_to, $subject, $body, [
            'email_category' => 'installment_reminder',
            'meta'           => [
                'installment_id' => $inst_id,
                'invoice_number' => $inv_number,
                'days_overdue'   => $days_over,
                'amount_due'     => $total_due,
            ],
        ]);
        mysqli_query($connection,
            "UPDATE enrolment_invoice_installments SET reminder_sent = 'Yes' WHERE id = $inst_id"
        );
        cron_log("  SENT inst#$inst_id → $email_to ($inv_number, \$$total_due, {$days_over}d overdue)");
        $sent++;
    } catch (\Throwable $e) {
        cron_log("  FAIL inst#$inst_id → $email_to — " . $e->getMessage());
        $failed++;
    }
}

// ── Summary ────────────────────────────────────────────────────────────────────
$elapsed = round(microtime(true) - CRON_START, 2);
cron_log("Done. Sent: $sent | Failed: $failed | Skipped: $skipped | Time: {$elapsed}s");

// Write log to file
$log_dir  = __DIR__ . '/logs';
if (!is_dir($log_dir)) @mkdir($log_dir, 0755, true);
$log_file = $log_dir . '/installment_reminder_' . date('Y-m') . '.log';
file_put_contents($log_file, implode(PHP_EOL, $log_lines) . PHP_EOL, FILE_APPEND | LOCK_EX);

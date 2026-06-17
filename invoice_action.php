<?php
ob_start();
include('includes/dbconnect.php');
session_start();

ob_clean();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' || ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorised.']);
    exit;
}

$action     = $_POST['action']     ?? '';
$invoice_id = intval($_POST['invoice_id'] ?? 0);

if ($action === 'mark_paid') {
    if ($invoice_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid invoice.']); exit; }
    mysqli_query($connection, "UPDATE enrolment_invoices SET status='paid' WHERE id=$invoice_id");
    mysqli_query($connection, "UPDATE enrolment_invoice_installments SET status='paid' WHERE invoice_id=$invoice_id");
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'send_reminder') {
    if ($invoice_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid invoice.']); exit; }
    $res = mysqli_query($connection,
        "SELECT ei.invoice_number, ei.student_name, ei.email_address,
                COALESCE(SUM(eis.amount + eis.gst_amount), 0) AS balance_due,
                MIN(eis.due_date) AS due_date
         FROM enrolment_invoices ei
         LEFT JOIN enrolment_invoice_installments eis ON eis.invoice_id = ei.id AND eis.status = 'pending'
         WHERE ei.id = $invoice_id
         GROUP BY ei.id LIMIT 1"
    );
    $inv = $res ? mysqli_fetch_assoc($res) : null;
    if (!$inv || empty($inv['email_address'])) {
        echo json_encode(['success' => false, 'message' => 'No email address on file.']);
        exit;
    }

    $to      = $inv['email_address'];
    $name    = $inv['student_name'];
    $inv_num = $inv['invoice_number'];
    $balance = '$' . number_format($inv['balance_due'], 2);
    $due     = $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : 'overdue';

    $subject = "Payment Reminder – Invoice $inv_num";
    $body    = "Dear $name,\n\nThis is a reminder that your invoice $inv_num has a balance due of $balance (due $due).\n\nPlease arrange payment at your earliest convenience.\n\nRegards,\nNational College Australia";
    $headers = "From: noreply@nationalcollege.edu.au\r\nContent-Type: text/plain; charset=UTF-8";

    $sent = mail($to, $subject, $body, $headers);
    echo json_encode(['success' => true, 'emailed' => $sent]);
    exit;
}

if ($action === 'mark_installment_paid') {
    $inst_id = intval($_POST['installment_id'] ?? 0);
    if ($inst_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid installment.']); exit; }

    mysqli_query($connection, "UPDATE enrolment_invoice_installments SET status='paid' WHERE id=$inst_id");

    // Look up invoice_id from the installment row
    $inv_row = mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT invoice_id FROM enrolment_invoice_installments WHERE id=$inst_id LIMIT 1"
    ));
    $real_inv_id = intval($inv_row['invoice_id'] ?? 0);
    if ($real_inv_id > 0) {
        $chk = mysqli_query($connection,
            "SELECT COUNT(*) AS total, SUM(status='paid') AS paid_count
             FROM enrolment_invoice_installments WHERE invoice_id=$real_inv_id"
        );
        $counts = mysqli_fetch_assoc($chk);
        if ($counts && $counts['total'] > 0 && $counts['total'] == $counts['paid_count']) {
            mysqli_query($connection, "UPDATE enrolment_invoices SET status='paid' WHERE id=$real_inv_id");
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'pay_offline') {
    $inst_id = intval($_POST['installment_id'] ?? 0);
    if ($inst_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid installment.']); exit; }

    $pay_method    = mysqli_real_escape_string($connection, $_POST['payment_method'] ?? 'offline');
    $pay_date      = mysqli_real_escape_string($connection, $_POST['payment_date']   ?? '');
    $receiver_name = mysqli_real_escape_string($connection, $_POST['receiver_name']  ?? '');
    $notes         = mysqli_real_escape_string($connection, $_POST['notes']          ?? '');
    $pay_date_sql  = $pay_date ? "'$pay_date'" : 'NULL';

    // Ensure all offline-payment columns exist
    $offline_cols = [
        'payment_method' => "VARCHAR(30)  DEFAULT NULL",
        'payment_date'   => "DATE         DEFAULT NULL",
        'receiver_name'  => "VARCHAR(150) DEFAULT NULL",
        'payment_notes'  => "TEXT         DEFAULT NULL",
        'proof_image'    => "VARCHAR(255) DEFAULT NULL",
    ];
    foreach ($offline_cols as $col => $def) {
        $chk = mysqli_query($connection, "SHOW COLUMNS FROM `enrolment_invoice_installments` LIKE '$col'");
        if ($chk && mysqli_num_rows($chk) === 0) {
            mysqli_query($connection, "ALTER TABLE `enrolment_invoice_installments` ADD COLUMN `$col` $def");
        }
    }

    // Proof image upload
    $proof_image = '';
    if (!empty($_FILES['proof_image']['tmp_name']) && is_uploaded_file($_FILES['proof_image']['tmp_name'])) {
        $uploadDir = __DIR__ . '/uploads/payment_proofs/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        $ext        = strtolower(pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION));
        $proof_name = 'proof_' . $inst_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $uploadDir . $proof_name)) {
            $proof_image = mysqli_real_escape_string($connection, $proof_name);
        }
    }
    $proof_sql = $proof_image ? ", proof_image='$proof_image'" : '';

    mysqli_query($connection,
        "UPDATE enrolment_invoice_installments
         SET status='paid', payment_method='$pay_method', payment_date=$pay_date_sql,
             receiver_name='$receiver_name', payment_notes='$notes' $proof_sql
         WHERE id=$inst_id"
    );

    // Look up invoice_id and auto-close invoice if all installments paid
    $inv_row = mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT invoice_id FROM enrolment_invoice_installments WHERE id=$inst_id LIMIT 1"
    ));
    $real_inv_id = intval($inv_row['invoice_id'] ?? 0);
    if ($real_inv_id > 0) {
        $chk = mysqli_query($connection,
            "SELECT COUNT(*) AS total, SUM(status='paid') AS paid_count
             FROM enrolment_invoice_installments WHERE invoice_id=$real_inv_id"
        );
        $counts = mysqli_fetch_assoc($chk);
        if ($counts && $counts['total'] > 0 && $counts['total'] == $counts['paid_count']) {
            mysqli_query($connection, "UPDATE enrolment_invoices SET status='paid' WHERE id=$real_inv_id");
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'send_installment_reminder') {
    $inst_id = intval($_POST['installment_id'] ?? 0);
    if ($inst_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid installment.']); exit; }

    $row = mysqli_fetch_assoc(mysqli_query($connection,
        "SELECT eis.*, ei.invoice_number, ei.student_name, ei.email_address,
                c.course_sname, c.course_name,
                DATEDIFF(CURDATE(), eis.due_date) AS days_overdue
         FROM enrolment_invoice_installments eis
         LEFT JOIN enrolment_invoices ei ON ei.id = eis.invoice_id
         LEFT JOIN courses c ON c.course_id = eis.course_id
         WHERE eis.id = $inst_id LIMIT 1"
    ));

    if (!$row || empty($row['email_address'])) {
        echo json_encode(['success' => false, 'message' => 'No email address found for this installment.']); exit;
    }

    $email_to   = trim($row['email_address']);
    $student    = $row['student_name'] ?: 'Student';
    $inv_number = $row['invoice_number'];
    $total_due  = floatval($row['amount']) + floatval($row['gst_amount']);
    $days_over  = max(0, intval($row['days_overdue']));
    $due_date   = $row['due_date'] ? date('d M Y', strtotime($row['due_date'])) : '—';
    $course     = ($row['course_sname'] && $row['course_name'])
                  ? $row['course_sname'] . ' – ' . $row['course_name'] : 'your enrolled course';

    if (!filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address on file.']); exit;
    }

    $subject = "Payment Reminder – Invoice $inv_number" . ($days_over > 0 ? " (Overdue $days_over days)" : '');
    $body = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
body{font-family:Arial,sans-serif;color:#333;margin:0;padding:0;background:#f4f4f4}
.wrap{max-width:600px;margin:30px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.header{background:#1e293b;color:#fff;padding:24px 32px}.header h2{margin:0;font-size:1.2rem}
.header p{margin:4px 0 0;font-size:.85rem;color:#94a3b8}
.body{padding:28px 32px}
.alert{background:#fee2e2;border-left:4px solid #dc2626;padding:14px 18px;border-radius:4px;margin-bottom:22px}
.alert strong{color:#991b1b}
.detail-table{width:100%;border-collapse:collapse;margin-bottom:22px}
.detail-table td{padding:9px 12px;border-bottom:1px solid #f0f0f0;font-size:.9rem}
.detail-table td:first-child{color:#6b7280;width:40%}.detail-table td:last-child{font-weight:600}
.amount{font-size:1.3rem;font-weight:700;color:#dc2626}
.footer{background:#f8fafc;padding:18px 32px;font-size:.78rem;color:#9ca3af;border-top:1px solid #e5e7eb}
</style></head><body><div class="wrap">
<div class="header"><h2>Payment Reminder</h2><p>National College Australia</p></div>
<div class="body">
<p>Dear <strong>' . htmlspecialchars($student) . '</strong>,</p>
<div class="alert"><strong>This is a reminder that your payment is due.</strong><br>
Invoice <strong>' . htmlspecialchars($inv_number) . '</strong> has an outstanding balance' .
($days_over > 0 ? ' that was due on ' . $due_date . ' (' . $days_over . ' day' . ($days_over !== 1 ? 's' : '') . ' overdue).' : ' due on ' . $due_date . '.') . '</div>
<table class="detail-table">
<tr><td>Invoice Number</td><td>' . htmlspecialchars($inv_number) . '</td></tr>
<tr><td>Course</td><td>' . htmlspecialchars($course) . '</td></tr>
<tr><td>Due Date</td><td>' . $due_date . '</td></tr>
<tr><td>Amount Due</td><td><span class="amount">$' . number_format($total_due, 2) . ' AUD</span></td></tr>
</table>
<p>Please arrange payment at your earliest convenience to avoid any disruption to your enrolment.</p>
<p style="margin-top:24px;font-size:.85rem;color:#6b7280;">
If you have already made a payment, please disregard this notice or contact us at
<a href="mailto:accounts@nationalcollege.edu.au">accounts@nationalcollege.edu.au</a>.
</p></div>
<div class="footer">&copy; ' . date('Y') . ' National College Australia &nbsp;|&nbsp; RTO: 91000</div>
</div></body></html>';

    try {
        send_mail($email_to, $subject, $body, ['email_category' => 'installment_reminder']);
        echo json_encode(['success' => true]);
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Email send failed: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);

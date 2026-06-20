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

    // ── Ensure pdf_path column exists ─────────────────────────────────────────
    $chk_col = mysqli_query($connection, "SHOW COLUMNS FROM `enrolment_invoice_installments` LIKE 'pdf_path'");
    if ($chk_col && mysqli_num_rows($chk_col) === 0) {
        mysqli_query($connection, "ALTER TABLE `enrolment_invoice_installments` ADD COLUMN `pdf_path` VARCHAR(255) DEFAULT NULL");
    }

    // ── Generate PDF, store file path, send email ────────────────────────────
    $pdf_generated = false;
    $email_sent    = false;
    $pdf_error     = '';

    try {
        require_once __DIR__ . '/includes/invoice_pdf_helper.php';
        require_once __DIR__ . '/includes/mail_function.php';

        $pdfResult = generate_installment_pdf_file($inst_id, $connection);

        if ($pdfResult['success']) {
            $pdf_generated = true;
            $rel_esc = mysqli_real_escape_string($connection, $pdfResult['rel_path']);
            mysqli_query($connection,
                "UPDATE enrolment_invoice_installments SET pdf_path='$rel_esc' WHERE id=$inst_id"
            );

            // ── Send email with PDF attachment ────────────────────────────────
            $student_email = trim($pdfResult['email'] ?? '');
            if ($student_email && filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
                $student_name = $pdfResult['student']   ?: 'Student';
                $inv_number   = $pdfResult['inv_number']?: '';
                $paid_total   = '$' . number_format($pdfResult['total'], 2);
                $pay_method   = ucfirst(str_replace('_', ' ', $pdfResult['method'] ?: 'offline'));
                $pay_date     = $pdfResult['pay_date'] ? date('d M Y', strtotime($pdfResult['pay_date'])) : date('d M Y');
                $course_lbl   = $pdfResult['course'] ?: '—';
                $year         = date('Y');

                $email_subject = "Payment Confirmation – Invoice $inv_number";
                $email_body = '<!DOCTYPE html><html><head><meta charset="utf-8">
<style>
body{margin:0;padding:0;background:#f2f5fa;font-family:Arial,sans-serif;color:#1e2d45}
.wrap{max-width:600px;margin:30px auto;background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 4px 24px rgba(26,43,74,.10)}
.hdr{background:linear-gradient(135deg,#1a2b4a 0%,#0d3d54 55%,#005f56 100%);padding:28px 36px}
.hdr-name{font-size:20px;font-weight:700;color:#fff;letter-spacing:.2px}
.hdr-sub{font-size:12px;color:rgba(255,255,255,.55);margin-top:4px}
.body{padding:32px 36px}
.greeting{font-size:16px;font-weight:600;color:#1a2b4a;margin-bottom:8px}
.intro{font-size:13.5px;color:#374662;line-height:1.6;margin-bottom:22px}
.confirm-badge{display:inline-block;background:#d1fae5;color:#065f46;font-size:12px;font-weight:700;padding:5px 14px;border-radius:20px;margin-bottom:20px;letter-spacing:.3px}
.detail-table{width:100%;border-collapse:collapse;border-radius:8px;overflow:hidden;margin-bottom:22px}
.detail-table td{padding:11px 14px;font-size:13px;border-bottom:1px solid #e8ecf4}
.detail-table tr:last-child td{border-bottom:none}
.detail-table .lbl{color:#6b7a95;width:42%}
.detail-table .val{font-weight:600;color:#1e2d45}
.amount{color:#059669;font-size:16px;font-weight:700}
.callout{background:#e6f4f3;border-left:4px solid #00857a;border-radius:4px;padding:13px 16px;font-size:12.5px;color:#1a5c58;margin-bottom:24px;line-height:1.6}
.footer-strip{background:#f8fafc;border-top:1px solid #dde3ed;padding:18px 36px;font-size:11px;color:#9ba3b8;text-align:center;line-height:1.7}
</style></head><body>
<div class="wrap">
  <div class="hdr">
    <div class="hdr-name">National College Australia</div>
    <div class="hdr-sub">RTO ID: 91000 &nbsp;&middot;&nbsp; ABN: 78 097 149 598</div>
  </div>
  <div class="body">
    <div class="confirm-badge">&#10003; Payment Confirmed</div>
    <div class="greeting">Dear ' . htmlspecialchars($student_name) . ',</div>
    <p class="intro">Thank you for your payment. Your tax invoice is attached to this email as a PDF. Please keep a copy for your records.</p>
    <table class="detail-table">
      <tr><td class="lbl">Invoice Number</td><td class="val">' . htmlspecialchars($inv_number) . '</td></tr>
      <tr><td class="lbl">Course</td><td class="val">' . htmlspecialchars($course_lbl) . '</td></tr>
      <tr><td class="lbl">Amount Paid (inc. GST)</td><td class="val"><span class="amount">' . $paid_total . ' AUD</span></td></tr>
      <tr><td class="lbl">Payment Method</td><td class="val">' . htmlspecialchars($pay_method) . '</td></tr>
      <tr><td class="lbl">Payment Date</td><td class="val">' . htmlspecialchars($pay_date) . '</td></tr>
    </table>
    <div class="callout">
      &#128196;&nbsp; Your tax invoice (<strong>' . htmlspecialchars($inv_number) . '.pdf</strong>) is attached to this email. If you have any questions, please contact <a href="mailto:accounts@nationalcollege.edu.au" style="color:#00857a">accounts@nationalcollege.edu.au</a> or call <strong>08 7119 6196</strong>.
    </div>
  </div>
  <div class="footer-strip">
    &copy; ' . $year . ' National College Australia &nbsp;&middot;&nbsp; RTO: 91000 &nbsp;&middot;&nbsp; ABN: 78 097 149 598<br>
    Level 1/118 King William Street, Adelaide SA 5000
  </div>
</div>
</body></html>';

                $attach_name = 'NCA-Invoice-' . preg_replace('/[^A-Za-z0-9\-]/', '', $inv_number) . '.pdf';
                send_mail_with_attachment(
                    $student_email,
                    $email_subject,
                    $email_body,
                    $pdfResult['file_path'],
                    $attach_name,
                    ['email_category' => 'invoice_payment_confirmation']
                );
                $email_sent = true;
            }
        } else {
            $pdf_error = $pdfResult['error'] ?? 'PDF generation failed.';
        }
    } catch (\Throwable $e) {
        $pdf_error = $e->getMessage();
    }

    echo json_encode([
        'success'       => true,
        'pdf_generated' => $pdf_generated,
        'email_sent'    => $email_sent,
        'pdf_error'     => $pdf_error,
    ]);
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

<?php
include('includes/dbconnect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: student_login.php'); exit;
}
$ut         = @$_SESSION['user_type'];
$is_admin   = ($ut == 1 || $ut == 2);
$is_student = ($ut === 0 || $ut === 'student');
if (!$is_admin && !$is_student) {
    header('Location: student_login.php'); exit;
}

$inst_id = intval($_GET['id'] ?? 0);
$action  = $_GET['action'] ?? 'view'; // 'view' | 'download'
if ($inst_id <= 0) {
    header('Location: student_invoice.php'); exit;
}

// Fetch pdf_path and basic access info from DB
$res = mysqli_query($connection,
    "SELECT eis.pdf_path, eis.invoice_id, ei.student_user_id, ei.invoice_number
     FROM enrolment_invoice_installments eis
     LEFT JOIN enrolment_invoices ei ON ei.id = eis.invoice_id
     WHERE eis.id = $inst_id LIMIT 1"
);
if (!$res || mysqli_num_rows($res) === 0) {
    http_response_code(404); exit('Installment not found.');
}
$row = mysqli_fetch_assoc($res);

// Students may only access their own invoice
if ($is_student) {
    $user_id  = intval($_SESSION['user_id']);
    $owner_id = intval($row['student_user_id'] ?? 0);
    if ($owner_id > 0 && $owner_id !== $user_id) {
        http_response_code(403); exit('Access denied.');
    }
}

$pdf_rel  = $row['pdf_path'] ?? '';
$inv_num  = preg_replace('/[^A-Za-z0-9\-]/', '', $row['invoice_number'] ?? $inst_id);
$filename = 'NCA-Invoice-' . $inv_num . '.pdf';

// ── Try stored file first ─────────────────────────────────────────────────────
if ($pdf_rel !== '') {
    $abs_path = __DIR__ . '/' . $pdf_rel;
    if (file_exists($abs_path)) {
        $disposition = ($action === 'download') ? 'attachment' : 'inline';
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($abs_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($abs_path);
        exit;
    }
}

// ── Fallback: generate on-demand and serve inline ────────────────────────────
require_once __DIR__ . '/includes/invoice_pdf_helper.php';

$pdfResult = generate_installment_pdf_file($inst_id, $connection);

if (!$pdfResult['success']) {
    http_response_code(500);
    exit('Could not generate PDF: ' . ($pdfResult['error'] ?? 'Unknown error.'));
}

// Store the newly generated file path for next time
$rel_esc = mysqli_real_escape_string($connection, $pdfResult['rel_path']);
mysqli_query($connection,
    "UPDATE enrolment_invoice_installments SET pdf_path='$rel_esc' WHERE id=$inst_id"
);

$disposition = ($action === 'download') ? 'attachment' : 'inline';
header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
header('Content-Length: ' . filesize($pdfResult['file_path']));
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($pdfResult['file_path']);
exit;

<?php
include('includes/dbconnect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' ||
    ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    header('Location: student_login.php'); exit;
}

$inst_id = intval($_GET['id'] ?? 0);
if ($inst_id <= 0) { header('Location: invoices_list.php'); exit; }

// ── Fetch installment + invoice + course ──────────────────────────────────────
$res = mysqli_query($connection, "
    SELECT eis.*,
           ei.invoice_number, ei.student_name, ei.student_id AS stu_id,
           ei.email_address, ei.enrolment_id,
           c.course_sname, c.course_name
    FROM enrolment_invoice_installments eis
    LEFT JOIN enrolment_invoices ei ON ei.id = eis.invoice_id
    LEFT JOIN courses            c  ON c.course_id = eis.course_id
    WHERE eis.id = $inst_id LIMIT 1
");
if (!$res || mysqli_num_rows($res) === 0) { header('Location: invoices_list.php'); exit; }
$d = mysqli_fetch_assoc($res);

require_once(__DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php');

// ── Extend TCPDF to suppress default header/footer ───────────────────────────
class NCA_PDF extends TCPDF {
    public function Header() {}
    public function Footer() {}
}

$pdf = new NCA_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('National College Australia');
$pdf->SetAuthor('NCA Admin');
$pdf->SetTitle('Invoice ' . ($d['invoice_number'] ?? ''));
$pdf->SetMargins(15, 5, 15);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// ── Helper to safely output text ──────────────────────────────────────────────
function hsc($v) { return htmlspecialchars_decode(htmlspecialchars((string)($v ?? ''), ENT_QUOTES)); }

// ── Derived values ────────────────────────────────────────────────────────────
$amount      = floatval($d['amount']);
$gst         = floatval($d['gst_amount']);
$total       = $amount + $gst;
$is_paid     = ($d['status'] === 'paid');
$course_lbl  = trim(($d['course_sname'] ?? '') . ($d['course_name'] ? ($d['course_sname'] ? ' - ' : '') . $d['course_name'] : ''));

$desc_parts = [];
if (!empty($d['invoice_type']))  $desc_parts[] = $d['invoice_type'];
if ($course_lbl)                 $desc_parts[] = $course_lbl;
if (!empty($d['funding_type']))  $desc_parts[] = 'Funding: ' . $d['funding_type'];
$desc = $desc_parts ? implode("\n", $desc_parts) : 'Invoice Line Item';

// ═══════════════════════════════════════════════════════════════════════════════
//  HEADER BLOCK
// ═══════════════════════════════════════════════════════════════════════════════

// Main navy background
$pdf->SetFillColor(26, 43, 74);
$pdf->Rect(0, 0, 210, 52, 'F');

// Sub-header darker strip
$pdf->SetFillColor(16, 28, 52);
$pdf->Rect(0, 52, 210, 22, 'F');

// Left teal accent bar
$pdf->SetFillColor(0, 133, 122);
$pdf->Rect(0, 0, 4, 74, 'F');

// ── Logo + College name & contact (left) ──────────────────────────────────────
$logo_path = __DIR__ . '/assets/images/logo-light.png';
if (file_exists($logo_path) && (extension_loaded('gd') || extension_loaded('imagick'))) {
    try { $pdf->Image($logo_path, 9, 7, 32, 0, 'PNG'); } catch (\Exception $e) { /* skip logo */ }
}

$pdf->SetXY(9, 22);
$pdf->SetFont('helvetica', 'B', 15);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(95, 8, 'National College Australia', 0, 1, 'L');

$pdf->SetXY(9, 30);
$pdf->SetFont('helvetica', '', 7.5);
$pdf->SetTextColor(155, 180, 215);
$pdf->MultiCell(95, 4.2,
    "RTO ID: 91000  \xC2\xB7  ABN: 78 097 149 598\n" .
    "Level 1/118 King William St, Adelaide SA 5000\n" .
    "08 7119 6196  \xC2\xB7  info@nca.edu.au  \xC2\xB7  nationalcollege.edu.au",
    0, 'L');

// ── Invoice number block (right) ──────────────────────────────────────────────
$pdf->SetXY(115, 9);
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(140, 168, 208);
$pdf->Cell(80, 4.5, 'TAX INVOICE', 0, 1, 'R');

$pdf->SetXY(115, 13.5);
$pdf->SetFont('helvetica', 'B', 15);
$pdf->SetTextColor(93, 232, 216);
$pdf->Cell(80, 9, hsc($d['invoice_number']), 0, 1, 'R');

$dateText = '';
if (!empty($d['issue_date'])) $dateText .= 'Issue Date: ' . date('d M Y', strtotime($d['issue_date'])) . "\n";
if (!empty($d['due_date']))   $dateText .= 'Due Date:   ' . date('d M Y', strtotime($d['due_date']));
if ($dateText) {
    $pdf->SetXY(115, 23.5);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetTextColor(155, 180, 215);
    $pdf->MultiCell(80, 4.2, $dateText, 0, 'R');
}

// ── Sub-header meta blocks ────────────────────────────────────────────────────
// BILL TO
$pdf->SetXY(9, 54);
$pdf->SetFont('helvetica', '', 6.5);
$pdf->SetTextColor(120, 150, 190);
$pdf->Cell(60, 3.5, 'BILL TO', 0, 1, 'L');

$pdf->SetXY(9, 57.5);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(240, 245, 255);
$pdf->Cell(70, 5, hsc($d['student_name'] ?? '—'), 0, 1, 'L');

$pdf->SetXY(9, 62.5);
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(155, 180, 215);
$sid_str = ($d['stu_id'] ?? '') . ($d['email_address'] ? ($d['stu_id'] ? '  \xC2\xB7  ' : '') . $d['email_address'] : '');
$pdf->Cell(90, 3.5, hsc($sid_str), 0, 1, 'L');

// COURSE
$pdf->SetXY(95, 54);
$pdf->SetFont('helvetica', '', 6.5);
$pdf->SetTextColor(120, 150, 190);
$pdf->Cell(60, 3.5, 'COURSE', 0, 1, 'L');

$pdf->SetXY(95, 57.5);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(240, 245, 255);
$pdf->MultiCell(60, 4.2, $course_lbl ?: '—', 0, 'L');

// ENROLMENT REF
$pdf->SetXY(157, 54);
$pdf->SetFont('helvetica', '', 6.5);
$pdf->SetTextColor(120, 150, 190);
$pdf->Cell(38, 3.5, 'ENROLMENT REF', 0, 1, 'R');

$pdf->SetXY(157, 57.5);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(240, 245, 255);
$pdf->Cell(38, 5, hsc($d['enrolment_id'] ?? '—'), 0, 1, 'R');

// ═══════════════════════════════════════════════════════════════════════════════
//  LINE ITEMS TABLE
// ═══════════════════════════════════════════════════════════════════════════════
$pdf->SetDrawColor(220, 225, 237);
$pdf->SetLineWidth(0.25);
$tY = 84;
$pdf->SetY($tY);

// Column widths (total = 180mm)
$cw = [8, 86, 14, 26, 20, 26];
$ch = ['#', 'DESCRIPTION', 'QTY', 'UNIT PRICE', 'GST', 'AMOUNT'];
$ca = ['C', 'L', 'C', 'R', 'R', 'R'];
$rh = 7;

// Table header row
$pdf->SetFillColor(248, 250, 253);
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->SetTextColor(55, 70, 98);
$x = 15;
foreach ($cw as $k => $w) {
    $pdf->SetXY($x, $tY);
    $pdf->Cell($w, $rh, $ch[$k], 'B', 0, $ca[$k], true);
    $x += $w;
}
$pdf->Ln($rh);

// Row height based on description lines
$desc_lines = substr_count($desc, "\n") + 1;
$row_h = max(10, $desc_lines * 5 + 3);

$yRow = $pdf->GetY();
$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('helvetica', '', 8.5);
$pdf->SetTextColor(30, 45, 69);

// Draw each data cell manually to keep row aligned
$x = 15;
$pdf->SetXY($x, $yRow);
$pdf->Cell($cw[0], $row_h, '1', 'B', 0, 'C', true);
$x += $cw[0];

$pdf->SetXY($x, $yRow);
$pdf->MultiCell($cw[1], $row_h, $desc, 'B', 'L', true, 0, '', '', true, 0, false, true, $row_h, 'M');
$x += $cw[1];

$pdf->SetXY($x, $yRow);
$pdf->Cell($cw[2], $row_h, '1', 'B', 0, 'C', true);
$x += $cw[2];

$pdf->SetXY($x, $yRow);
$pdf->Cell($cw[3], $row_h, '$' . number_format($amount, 2), 'B', 0, 'R', true);
$x += $cw[3];

$pdf->SetXY($x, $yRow);
$pdf->Cell($cw[4], $row_h, $gst > 0 ? '$' . number_format($gst, 2) : chr(226).chr(128).chr(148), 'B', 0, 'R', true);
$x += $cw[4];

$pdf->SetFont('helvetica', 'B', 8.5);
$pdf->SetXY($x, $yRow);
$pdf->Cell($cw[5], $row_h, '$' . number_format($total, 2), 'B', 0, 'R', true);
$pdf->Ln($row_h);

// ═══════════════════════════════════════════════════════════════════════════════
//  TOTALS
// ═══════════════════════════════════════════════════════════════════════════════
$pdf->SetY($pdf->GetY() + 6);

$lW = 140; $rW = 40;

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(107, 122, 149);
$pdf->SetX(15); $pdf->Cell($lW, 7, 'Subtotal', 0, 0, 'R');
$pdf->SetTextColor(30, 45, 69);
$pdf->Cell($rW, 7, '$' . number_format($amount, 2), 0, 1, 'R');

$pdf->SetTextColor(107, 122, 149);
$pdf->SetX(15); $pdf->Cell($lW, 7, 'GST (10%)', 0, 0, 'R');
$pdf->SetTextColor(30, 45, 69);
$pdf->Cell($rW, 7, '$' . number_format($gst, 2), 0, 1, 'R');

$pdf->SetDrawColor(220, 225, 237);
$pdf->SetLineWidth(0.4);
$pdf->Line(105, $pdf->GetY(), 195, $pdf->GetY());

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(26, 43, 74);
$pdf->SetX(15); $pdf->Cell($lW, 11, 'Total Due (AUD)', 0, 0, 'R');
$pdf->SetTextColor(0, 133, 122);
$pdf->Cell($rW, 11, '$' . number_format($total, 2), 0, 1, 'R');

// ── PAID watermark + payment details (if paid) ───────────────────────────────
if ($is_paid) {
    // Diagonal watermark only
    $pdf->SetFont('helvetica', 'B', 55);
    $pdf->SetTextColor(210, 245, 240);
    $pdf->StartTransform();
    $pdf->Rotate(32, 105, 148);
    $pdf->Text(52, 148, 'PAID');
    $pdf->StopTransform();

    // Payment details (method + date if recorded offline)
    if (!empty($d['payment_method'])) {
        $pdf->SetY($pdf->GetY() + 3);
        $pdf->SetX(15);
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(107, 122, 149);
        $pdetail = 'Paid via: ' . ucfirst(str_replace('_', ' ', $d['payment_method']));
        if (!empty($d['payment_date'])) $pdetail .= '  on ' . date('d M Y', strtotime($d['payment_date']));
        if (!empty($d['receiver_name'])) $pdetail .= '  \xC2\xB7  Received by: ' . $d['receiver_name'];
        $pdf->Cell(180, 5, $pdetail, 0, 1, 'L');
        $pdf->SetY($pdf->GetY() + 3);
    } else {
        $pdf->SetY($pdf->GetY() + 6);
    }
} else {
    $pdf->SetY($pdf->GetY() + 8);
}

// ═══════════════════════════════════════════════════════════════════════════════
//  PAYMENT INSTRUCTIONS BOX
// ═══════════════════════════════════════════════════════════════════════════════
$piY = $pdf->GetY();
$pdf->SetFillColor(230, 244, 243);
$pdf->SetDrawColor(160, 212, 208);
$pdf->SetLineWidth(0.4);
$pdf->RoundedRect(15, $piY, 180, 44, 3, '1111', 'DF');

$pdf->SetXY(21, $piY + 6);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(0, 95, 86);
$pdf->Cell(0, 5, 'Payment Instructions', 0, 1, 'L');

// Left: bank details
$pdf->SetXY(21, $piY + 13);
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->SetTextColor(25, 60, 58);
$pdf->Cell(85, 4, 'Bank Transfer (EFT)', 0, 1, 'L');
$pdf->SetXY(21, $piY + 17);
$pdf->SetFont('helvetica', '', 7.5);
$pdf->SetTextColor(30, 50, 60);
$pdf->MultiCell(84, 4.2,
    "Bank: Commonwealth Bank\n" .
    "BSB: 085-000  \xC2\xB7  Account: 12 345 678\n" .
    "Account Name: National College Australia\n" .
    "Reference: " . hsc($d['invoice_number']),
    0, 'L');

// Right: other
$pdf->SetXY(110, $piY + 13);
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->SetTextColor(25, 60, 58);
$pdf->Cell(80, 4, 'Other Payment Methods', 0, 1, 'L');
$pdf->SetXY(110, $piY + 17);
$pdf->SetFont('helvetica', '', 7.5);
$pdf->SetTextColor(30, 50, 60);
$pdf->MultiCell(80, 4.2,
    "Credit/Debit Card: via student portal\n" .
    "ncalearnerhub.com\n\n" .
    "Enquiries: accounts@nca.edu.au\n" .
    "Phone: 08 7119 6196",
    0, 'L');

// ═══════════════════════════════════════════════════════════════════════════════
//  FOOTER
// ═══════════════════════════════════════════════════════════════════════════════
$fY = 256;
$pdf->SetFillColor(248, 250, 252);
$pdf->Rect(0, $fY, 210, 32, 'F');
$pdf->SetDrawColor(229, 231, 235);
$pdf->SetLineWidth(0.3);
$pdf->Line(0, $fY, 210, $fY);

// 3-col footer blocks
$fcols = [
    ['Terms & Conditions', "Payment due within 30 days of issue.\nLate payments may incur a \$50 admin fee.\nRefund policy: NCA Refund Policy v1.0."],
    ['Privacy',            "Personal info collected under the\nPrivacy Act 1988 (Cth) & NCA Privacy Policy.\nContact info@nca.edu.au for queries."],
    ['Contact Us',         "National College Australia\nLevel 1/118 King William St\nAdelaide SA 5000\n08 7119 6196  \xC2\xB7  info@nca.edu.au"],
];
$fx = 15;
foreach ($fcols as $fc) {
    $pdf->SetXY($fx, $fY + 5);
    $pdf->SetFont('helvetica', 'B', 6.5);
    $pdf->SetTextColor(55, 70, 98);
    $pdf->Cell(55, 4, $fc[0], 0, 1, 'L');
    $pdf->SetXY($fx, $fY + 9.5);
    $pdf->SetFont('helvetica', '', 6);
    $pdf->SetTextColor(107, 122, 149);
    $pdf->MultiCell(55, 3.3, $fc[1], 0, 'L');
    $fx += 60;
}

// Bottom navy strip
$pdf->SetFillColor(26, 43, 74);
$pdf->Rect(0, 289, 210, 8, 'F');
$pdf->SetXY(15, 292);
$pdf->SetFont('helvetica', '', 5.5);
$pdf->SetTextColor(150, 172, 208);
$pdf->Cell(180, 3,
    'This is a computer-generated tax invoice. No signature required.  \xC2\xB7  National College Australia  \xC2\xB7  RTO ID 91000  \xC2\xB7  ABN 78 097 149 598',
    0, 0, 'C');

// ── Output ───────────────────────────────────────────────────────────────────
$filename = 'NCA-Invoice-' . preg_replace('/[^A-Za-z0-9\-]/', '', $d['invoice_number'] ?? $inst_id) . '.pdf';
$pdf->Output($filename, 'I');

<?php
include('includes/dbconnect.php');
include('includes/stripe_config.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: student_login.php'); exit;
}

$inst_id = intval($_POST['installment_id'] ?? 0);
if ($inst_id <= 0) {
    header('Location: student_invoice.php'); exit;
}

// Fetch installment
$res = mysqli_query($connection,
    "SELECT eis.*, ei.invoice_number, ei.student_name, ei.email_address
     FROM enrolment_invoice_installments eis
     LEFT JOIN enrolment_invoices ei ON ei.id = eis.invoice_id
     WHERE eis.id = $inst_id AND eis.status = 'pending'
     LIMIT 1"
);
if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['stripe_flash'] = ['type' => 'warning', 'msg' => 'Installment not found or already paid.'];
    header("Location: installment_view.php?id=$inst_id"); exit;
}
$inst = mysqli_fetch_assoc($res);

$amount_cents = (int) round((floatval($inst['amount']) + floatval($inst['gst_amount'])) * 100);
// Strip everything except letters (handles values like "AUD $", "AUD$", "aud ")
$currency     = strtolower(preg_replace('/[^a-zA-Z]/', '', $inst['currency'] ?: 'AUD')) ?: 'aud';
$description  = 'Invoice ' . $inst['invoice_number'] . ' — Installment #' . $inst_id;
$success_url  = APP_BASE_URL . '/stripe_success.php?session_id={CHECKOUT_SESSION_ID}&inst=' . $inst_id;
$cancel_url   = APP_BASE_URL . '/installment_view.php?id=' . $inst_id . '&cancelled=1';

// Build Stripe Checkout Session via cURL
$payload = http_build_query([
    'payment_method_types[0]'              => 'card',
    'mode'                                 => 'payment',
    'line_items[0][price_data][currency]'  => $currency,
    'line_items[0][price_data][unit_amount]' => $amount_cents,
    'line_items[0][price_data][product_data][name]' => $description,
    'line_items[0][price_data][product_data][description]' => 'Student: ' . $inst['student_name'],
    'line_items[0][quantity]'              => 1,
    'customer_email'                       => $inst['email_address'],
    'success_url'                          => $success_url,
    'cancel_url'                           => $cancel_url,
    'metadata[installment_id]'             => $inst_id,
    'metadata[invoice_number]'             => $inst['invoice_number'],
]);

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$response = curl_exec($ch);
$err      = curl_error($ch);
curl_close($ch);

if ($err) {
    $_SESSION['stripe_flash'] = ['type' => 'danger', 'msg' => 'Connection error: ' . $err];
    header("Location: installment_view.php?id=$inst_id"); exit;
}

$data = json_decode($response, true);

if (!empty($data['error'])) {
    $_SESSION['stripe_flash'] = ['type' => 'danger', 'msg' => 'Stripe error: ' . ($data['error']['message'] ?? 'Unknown error.')];
    header("Location: installment_view.php?id=$inst_id"); exit;
}

if (empty($data['url'])) {
    $_SESSION['stripe_flash'] = ['type' => 'danger', 'msg' => 'Could not create payment session. Please try again.'];
    header("Location: installment_view.php?id=$inst_id"); exit;
}

// Store session ID for verification on success
$_SESSION['stripe_session_' . $inst_id] = $data['id'];

// Create transactions table if not exists
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `online_payment_transactions` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `installment_id`   INT UNSIGNED NOT NULL,
    `invoice_id`       INT UNSIGNED DEFAULT NULL,
    `invoice_number`   VARCHAR(30)  DEFAULT NULL,
    `student_name`     VARCHAR(200) DEFAULT NULL,
    `email_address`    VARCHAR(200) DEFAULT NULL,
    `stripe_session_id`   VARCHAR(120) DEFAULT NULL,
    `stripe_payment_intent` VARCHAR(120) DEFAULT NULL,
    `amount`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency`         VARCHAR(10)   NOT NULL DEFAULT 'aud',
    `status`           ENUM('initiated','success','failed','cancelled') NOT NULL DEFAULT 'initiated',
    `payment_status`   VARCHAR(30)  DEFAULT NULL,
    `stripe_response`  TEXT         DEFAULT NULL,
    `initiated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at`     TIMESTAMP    NULL DEFAULT NULL,
    INDEX `idx_installment` (`installment_id`),
    INDEX `idx_session`     (`stripe_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Log the initiated transaction
$esc_session  = mysqli_real_escape_string($connection, $data['id']);
$esc_inv_num  = mysqli_real_escape_string($connection, $inst['invoice_number']);
$esc_student  = mysqli_real_escape_string($connection, $inst['student_name']);
$esc_email    = mysqli_real_escape_string($connection, $inst['email_address']);
$inv_id_log   = intval($inst['invoice_id'] ?? 0);
$amount_log   = floatval($inst['amount']) + floatval($inst['gst_amount']);
$currency_log = mysqli_real_escape_string($connection, $currency);

mysqli_query($connection,
    "INSERT INTO online_payment_transactions
        (installment_id, invoice_id, invoice_number, student_name, email_address,
         stripe_session_id, amount, currency, status)
     VALUES
        ($inst_id, $inv_id_log, '$esc_inv_num', '$esc_student', '$esc_email',
         '$esc_session', $amount_log, '$currency_log', 'initiated')"
);

header('Location: ' . $data['url']);
exit;

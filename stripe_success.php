<?php
include('includes/dbconnect.php');
include('includes/stripe_config.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: student_login.php'); exit;
}

$session_id = $_GET['session_id'] ?? '';
$inst_id    = intval($_GET['inst'] ?? 0);

if (!$session_id || $inst_id <= 0) {
    header('Location: student_invoice.php'); exit;
}

// Verify session with Stripe
$ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . urlencode($session_id));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

$stripe_payment_status = $data['payment_status']         ?? 'unknown';
$stripe_payment_intent = $data['payment_intent']         ?? '';
$stripe_response_esc   = mysqli_real_escape_string($connection, $response);
$esc_session           = mysqli_real_escape_string($connection, $session_id);
$esc_pi                = mysqli_real_escape_string($connection, $stripe_payment_intent);
$esc_pstatus           = mysqli_real_escape_string($connection, $stripe_payment_status);

if ($stripe_payment_status === 'paid') {
    $tx_status = 'success';

    // Mark installment as paid
    mysqli_query($connection,
        "UPDATE enrolment_invoice_installments SET status='paid' WHERE id=$inst_id"
    );

    // Fetch invoice_id for this installment
    $inv_res = mysqli_query($connection, "SELECT invoice_id FROM enrolment_invoice_installments WHERE id=$inst_id LIMIT 1");
    $inv_row = $inv_res ? mysqli_fetch_assoc($inv_res) : null;
    $inv_id  = $inv_row ? intval($inv_row['invoice_id']) : 0;

    // If all installments for this invoice are paid, mark invoice paid too
    if ($inv_id > 0) {
        $chk = mysqli_query($connection,
            "SELECT COUNT(*) AS total, SUM(status='paid') AS paid_count
             FROM enrolment_invoice_installments WHERE invoice_id=$inv_id"
        );
        $counts = mysqli_fetch_assoc($chk);
        if ($counts && $counts['total'] > 0 && $counts['total'] == $counts['paid_count']) {
            mysqli_query($connection, "UPDATE enrolment_invoices SET status='paid' WHERE id=$inv_id");
        }
    }

    $_SESSION['stripe_flash'] = ['type' => 'success', 'msg' => 'Payment successful! Your installment has been marked as paid.'];
} else {
    $tx_status = 'failed';
    $_SESSION['stripe_flash'] = ['type' => 'warning', 'msg' => 'Payment is pending or could not be verified. Please contact support if payment was deducted.'];
}

// Update transaction log
mysqli_query($connection,
    "UPDATE online_payment_transactions
     SET status='$tx_status',
         payment_status='$esc_pstatus',
         stripe_payment_intent='$esc_pi',
         stripe_response='$stripe_response_esc',
         completed_at=NOW()
     WHERE stripe_session_id='$esc_session'
     ORDER BY id DESC LIMIT 1"
);

header("Location: installment_view.php?id=$inst_id");
exit;

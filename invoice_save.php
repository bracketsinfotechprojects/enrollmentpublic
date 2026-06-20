<?php
include('includes/dbconnect.php');
session_start();

header('Content-Type: application/json; charset=UTF-8');

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' || ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorised.']);
    exit;
}

// Auto-create tables if not exists
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `enrolment_invoices` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `invoice_number`  VARCHAR(30)  NOT NULL UNIQUE,
    `enrolment_id`    INT UNSIGNED NOT NULL,
    `student_user_id` INT UNSIGNED DEFAULT NULL,
    `student_name`    VARCHAR(200) NOT NULL DEFAULT '',
    `student_id`      VARCHAR(50)  NOT NULL DEFAULT '',
    `email_address`   VARCHAR(150) NOT NULL DEFAULT '',
    `status`          ENUM('pending','paid','overdue') NOT NULL DEFAULT 'pending',
    `created_by`      INT UNSIGNED DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_enrolment` (`enrolment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Add student_user_id column to existing table if missing
$col_check = mysqli_query($connection, "SHOW COLUMNS FROM `enrolment_invoices` LIKE 'student_user_id'");
if ($col_check && mysqli_num_rows($col_check) === 0) {
    mysqli_query($connection, "ALTER TABLE `enrolment_invoices` ADD COLUMN `student_user_id` INT UNSIGNED DEFAULT NULL AFTER `enrolment_id`");
}

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `enrolment_invoice_installments` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `invoice_id`     INT UNSIGNED NOT NULL,
    `invoice_number` VARCHAR(30)  NOT NULL DEFAULT '',
    `course_id`      INT UNSIGNED DEFAULT NULL,
    `invoice_type`   VARCHAR(60)  NOT NULL DEFAULT '',
    `funding_type`   VARCHAR(60)  NOT NULL DEFAULT '',
    `currency`       VARCHAR(10)  NOT NULL DEFAULT 'AUD',
    `amount`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `gst_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `issue_date`     DATE DEFAULT NULL,
    `due_date`       DATE DEFAULT NULL,
    `status`         ENUM('pending','paid') NOT NULL DEFAULT 'pending',
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_invoice` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Add invoice_number column to existing table if missing
$col_inst = mysqli_query($connection, "SHOW COLUMNS FROM `enrolment_invoice_installments` LIKE 'invoice_number'");
if ($col_inst && mysqli_num_rows($col_inst) === 0) {
    mysqli_query($connection, "ALTER TABLE `enrolment_invoice_installments` ADD COLUMN `invoice_number` VARCHAR(30) NOT NULL DEFAULT '' AFTER `invoice_id`");
}

// Add offline payment columns if missing
$offline_cols = [
    'payment_method' => "VARCHAR(30) DEFAULT NULL",
    'payment_date'   => "DATE DEFAULT NULL",
    'receiver_name'  => "VARCHAR(150) DEFAULT NULL",
    'payment_notes'  => "TEXT DEFAULT NULL",
    'proof_image'    => "VARCHAR(255) DEFAULT NULL",
];
foreach ($offline_cols as $col_name => $col_def) {
    $chk = mysqli_query($connection, "SHOW COLUMNS FROM `enrolment_invoice_installments` LIKE '$col_name'");
    if ($chk && mysqli_num_rows($chk) === 0) {
        mysqli_query($connection, "ALTER TABLE `enrolment_invoice_installments` ADD COLUMN `$col_name` $col_def");
    }
}

$enrolment_id    = isset($_POST['enrolment_id'])    ? intval($_POST['enrolment_id'])    : 0;
$student_user_id = isset($_POST['student_user_id']) ? intval($_POST['student_user_id']) : 0;
$installments    = isset($_POST['installments_json']) ? json_decode($_POST['installments_json'], true) : [];

if ($enrolment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid enrolment.']);
    exit;
}
if (empty($installments) || !is_array($installments)) {
    echo json_encode(['success' => false, 'message' => 'No installments provided.']);
    exit;
}

// Fetch student info
$res = mysqli_query($connection,
    "SELECT given_name, surname, office_student_id, email_address FROM enrolment_form_new WHERE id = $enrolment_id LIMIT 1"
);
$enr = $res ? mysqli_fetch_assoc($res) : null;
if (!$enr) {
    echo json_encode(['success' => false, 'message' => 'Enrolment not found.']);
    exit;
}

$student_name = mysqli_real_escape_string($connection, trim($enr['given_name'] . ' ' . $enr['surname']));
$student_id   = mysqli_real_escape_string($connection, $enr['office_student_id'] ?? '');
$email        = mysqli_real_escape_string($connection, $enr['email_address'] ?? '');
$created_by   = intval($_SESSION['user_id']);
$inv_number   = 'NCA-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

// Insert invoice header
$suid_val = $student_user_id > 0 ? $student_user_id : 'NULL';
$ok = mysqli_query($connection,
    "INSERT INTO enrolment_invoices (invoice_number, enrolment_id, student_user_id, student_name, student_id, email_address, created_by)
     VALUES ('$inv_number', $enrolment_id, $suid_val, '$student_name', '$student_id', '$email', $created_by)"
);
if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'Could not save invoice: ' . mysqli_error($connection)]);
    exit;
}
$invoice_id = (int) mysqli_insert_id($connection);

// Insert installments
$failed = 0;
foreach ($installments as $inst) {
    $course_id  = !empty($inst['course_id']) ? intval($inst['course_id']) : 'NULL';
    $inv_type   = mysqli_real_escape_string($connection, trim($inst['invoice_type']  ?? ''));
    $fund_type  = mysqli_real_escape_string($connection, trim($inst['funding_type']  ?? ''));
    $currency   = mysqli_real_escape_string($connection, trim($inst['currency']      ?? 'AUD'));
    $amount     = round(floatval($inst['amount']     ?? 0), 2);
    $gst        = round(floatval($inst['gst_amount'] ?? 0), 2);
    $issue_date = !empty($inst['issue_date']) ? "'" . mysqli_real_escape_string($connection, $inst['issue_date']) . "'" : 'NULL';
    $due_date   = !empty($inst['due_date'])   ? "'" . mysqli_real_escape_string($connection, $inst['due_date'])   . "'" : 'NULL';

    $q = mysqli_query($connection,
        "INSERT INTO enrolment_invoice_installments
             (invoice_id, invoice_number, course_id, invoice_type, funding_type, currency, amount, gst_amount, issue_date, due_date)
         VALUES
             ($invoice_id, '$inv_number', $course_id, '$inv_type', '$fund_type', '$currency', $amount, $gst, $issue_date, $due_date)"
    );
    if (!$q) $failed++;
}

if ($failed > 0) {
    echo json_encode(['success' => false, 'message' => "Invoice header saved but $failed installment(s) failed: " . mysqli_error($connection)]);
    exit;
}

echo json_encode(['success' => true, 'invoice_number' => $inv_number, 'invoice_id' => $invoice_id]);

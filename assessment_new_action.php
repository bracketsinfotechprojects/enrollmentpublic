<?php
ob_start();
include('includes/dbconnect.php');
session_start();
ob_clean();

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' || ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorised.']); exit;
}

$action        = $_POST['action']        ?? '';
$form_id       = intval($_POST['form_id']       ?? 0);
$assessment_id = intval($_POST['assessment_id'] ?? 0);

if ($action === 'delete_form') {
    if ($form_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid form.']); exit; }
    mysqli_query($connection, "DELETE FROM submissions WHERE form_id = $form_id");
    mysqli_query($connection, "DELETE FROM form_fields WHERE form_id = $form_id");
    mysqli_query($connection, "DELETE FROM forms WHERE id = $form_id");
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'delete_assessment') {
    if ($assessment_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid assessment.']); exit; }
    mysqli_query($connection, "DELETE FROM `assessment` WHERE assessment_id = $assessment_id");
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'activate_assessment') {
    if ($assessment_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid assessment.']); exit; }
    // Set all other assessments to Draft (status = 0)
    mysqli_query($connection, "UPDATE `assessment` SET status = 0 WHERE assessment_id != $assessment_id");
    // Set this assessment to Active (status = 1)
    mysqli_query($connection, "UPDATE `assessment` SET status = 1 WHERE assessment_id = $assessment_id");
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);

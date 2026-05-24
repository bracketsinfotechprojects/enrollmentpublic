<?php
session_start();

// Remember if this was a student session (in case we ever need it later)
$was_student = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student');

// Clear session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Always send students back to the student login page
header('Location: student_login.php');
exit;

?>

<?php
session_start();
if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student'){
    unset($_SESSION['user_id'], $_SESSION['user_type'], $_SESSION['user_name']);
}
header('Location: student_login.php');
exit;

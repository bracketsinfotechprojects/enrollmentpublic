<?php
// includes/auth.php

include('includes/dbconnect.php');

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // session_name(SESSION_NAME);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $db = DB::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE user_id = ?", [$_SESSION['user_id']]);
    }
    return $user;
}

function isAdmin(): bool {
    $user = currentUser();
    return $user && (int)$user['user_type'] === 1;
}

function login(string $username, string $password): bool {
    $db = DB::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE username = ? OR email = ?", [$username, $username]);
    if ($user && password_verify($password, $user['password'])) {
        startSession();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout(): void {
    startSession();
    session_destroy();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT);
}

function csrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function canAccessForm(int $formId): bool {
    if (!isLoggedIn()) return false;
    if (isAdmin()) return true;
    $db = DB::getInstance();
    $form = $db->fetch("SELECT user_id FROM forms WHERE id = ?", [$formId]);
    return $form && $form['user_id'] == $_SESSION['user_id'];
}

<?php
function requirePatientLogin() {
    if (!isset($_SESSION['patient_id'])) {
        header('Location: login.php');
        exit;
    }
}

function requireAdminLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function isPatientLoggedIn() {
    return isset($_SESSION['patient_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function sanitizeInput($data) {
    global $conn;
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateAppointmentId() {
    return 'APT-' . strtoupper(uniqid());
}
?>

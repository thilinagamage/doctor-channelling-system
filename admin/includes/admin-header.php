<?php
session_start();
$base_url = '/';
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

$page_title = isset($page_title) ? $page_title . ' - Admin' : 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-page">
    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <h2><i class="fas fa-stethoscope"></i> DoctorChannelling</h2>
            </div>
            <nav class="admin-nav">
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="<?php echo $base_url; ?>admin/doctors.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'doctors.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-md"></i> Doctors
                </a>
                <a href="<?php echo $base_url; ?>admin/centers.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'centers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hospital"></i> Centers
                </a>
                <a href="<?php echo $base_url; ?>admin/slots.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'slots.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Time Slots
                </a>
                <a href="<?php echo $base_url; ?>admin/appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'appointments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Appointments
                </a>
                <a href="<?php echo $base_url; ?>admin/patients.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'patients.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Patients
                </a>
                <a href="<?php echo $base_url; ?>admin/payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
                <a href="<?php echo $base_url; ?>logout.php" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header">
                <button class="mobile-sidebar-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('active')">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <div class="admin-user">
                    <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($admin_name); ?></span>
                    <span class="status-badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.7rem;"><?php echo ucfirst($admin_role); ?></span>
                </div>
            </header>
            <div class="admin-content">
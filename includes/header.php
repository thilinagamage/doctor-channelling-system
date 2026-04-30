<?php
$base_url = '/';
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

$patient_logged_in = isset($_SESSION['patient_id']);
$patient_name = $patient_logged_in ? $_SESSION['patient_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Online Doctor Channelling</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo $base_url; ?>index.php" class="logo">
                    <i class="fas fa-stethoscope"></i>
                    <span>DoctorChannelling</span>
                </a>
                <nav class="main-nav">
                    <button class="mobile-menu-btn" aria-label="Toggle menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="nav-list">
                        <li><a href="<?php echo $base_url; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $base_url; ?>doctors.php">Doctors</a></li>
                        <?php if ($patient_logged_in): ?>
                        <li><a href="<?php echo $base_url; ?>my-appointments.php">My Appointments</a></li>
                        <li class="user-menu">
                            <a href="#" class="user-toggle">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($patient_name); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo $base_url; ?>profile.php"><i class="fas fa-user"></i> Profile</a></li>
                                <li><a href="<?php echo $base_url; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li><a href="<?php echo $base_url; ?>login.php" class="btn-text">Login</a></li>
                        <li><a href="<?php echo $base_url; ?>register.php" class="btn-primary">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main class="main-content">
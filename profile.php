<?php
$page_title = 'My Profile';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

requirePatientLogin();

$patient_id = $_SESSION['patient_id'];
$error = '';
$success = '';

$stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');
    
    if (empty($full_name) || empty($phone)) {
        $error = 'Please fill in all required fields';
    } else {
        $stmt = $conn->prepare("UPDATE patients SET full_name = ?, phone = ?, date_of_birth = ?, gender = ?, address = ? WHERE patient_id = ?");
        $stmt->bind_param("sssssi", $full_name, $phone, $date_of_birth, $gender, $address, $patient_id);
        
        if ($stmt->execute()) {
            $_SESSION['patient_name'] = $full_name;
            $success = 'Profile updated successfully';
            $patient['full_name'] = $full_name;
            $patient['phone'] = $phone;
            $patient['date_of_birth'] = $date_of_birth;
            $patient['gender'] = $gender;
            $patient['address'] = $address;
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all password fields';
    } elseif (!password_verify($current_password, $patient['password_hash'])) {
        $error = 'Current password is incorrect';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE patients SET password_hash = ? WHERE patient_id = ?");
        $stmt->bind_param("si", $new_hash, $patient_id);
        
        if ($stmt->execute()) {
            $success = 'Password changed successfully';
        } else {
            $error = 'Failed to change password';
        }
    }
}

$flash = getFlashMessage();
?>

<section class="main-content">
    <div class="container">
        <div class="page-title">
            <h1>My Profile</h1>
            <p>View and update your personal information</p>
        </div>
        
        <?php if ($error): ?>
        <div class="flash-message error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="flash-message success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($flash): ?>
        <div class="flash-message <?php echo $flash['type']; ?>">
            <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($flash['message']); ?></span>
        </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
            <div>
                <div class="form-container" style="max-width: 100%;">
                    <h2>Personal Information</h2>
                    
                    <form action="profile.php" method="POST">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($patient['full_name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" disabled value="<?php echo htmlspecialchars($patient['email']); ?>" style="background: var(--bg-gray);">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($patient['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($patient['date_of_birth'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo ($patient['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($patient['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($patient['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($patient['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <div>
                <div class="form-container" style="max-width: 100%;">
                    <h2>Change Password</h2>
                    
                    <form action="profile.php" method="POST">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn-outline">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
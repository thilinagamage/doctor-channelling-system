<?php
$page_title = 'Register';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

if (isset($_SESSION['patient_id'])) {
    header('Location: my-appointments.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');
    
    if (empty($full_name) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO patients (full_name, email, password_hash, phone, date_of_birth, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $full_name, $email, $password_hash, $phone, $date_of_birth, $gender, $address);
            
            if ($stmt->execute()) {
                $_SESSION['patient_id'] = $conn->insert_id;
                $_SESSION['patient_name'] = $full_name;
                $_SESSION['patient_email'] = $email;
                
                setFlashMessage('Registration successful! Welcome to DoctorChannelling.', 'success');
                header('Location: my-appointments.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<section class="main-content">
    <div class="container">
        <div class="form-container">
            <h2>Create Account</h2>
            
            <?php if ($error): ?>
            <div class="flash-message error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <form action="register.php" method="POST" class="validate-form">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="+94 77 123 4567">
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo ($gender ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($gender ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo ($gender ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; display: block;">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; display: block;">
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
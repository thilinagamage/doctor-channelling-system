<?php
$page_title = 'Login';
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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $stmt = $conn->prepare("SELECT patient_id, full_name, email, password_hash FROM patients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['patient_id'] = $row['patient_id'];
                $_SESSION['patient_name'] = $row['full_name'];
                $_SESSION['patient_email'] = $row['email'];
                
                $redirect = $_GET['redirect'] ?? 'my-appointments.php';
                header("Location: $redirect");
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

$flash = getFlashMessage();
?>

<section class="main-content">
    <div class="container">
        <div class="form-container">
            <h2>Welcome Back</h2>
            
            <?php if ($error): ?>
            <div class="flash-message error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($flash): ?>
            <div class="flash-message <?php echo $flash['type']; ?>">
                <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($flash['message']); ?></span>
            </div>
            <?php endif; ?>
            
            <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" method="POST" class="validate-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
            

        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
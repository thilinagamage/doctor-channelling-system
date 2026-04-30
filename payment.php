<?php
$page_title = 'Payment';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

requirePatientLogin();

$patient_id = $_SESSION['patient_id'];
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

if (!$appointment_id) {
    header('Location: my-appointments.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT a.*, ts.slot_date, ts.start_time, ts.end_time,
           d.full_name as doctor_name, d.specialization,
           cc.center_name, cc.address as center_address,
           p.amount as payment_amount, p.status as payment_status, p.method as payment_method
    FROM appointments a
    JOIN time_slots ts ON a.slot_id = ts.slot_id
    JOIN doctors d ON ts.doctor_id = d.doctor_id
    JOIN channelling_centers cc ON ts.center_id = cc.center_id
    LEFT JOIN payments p ON a.appointment_id = p.appointment_id
    WHERE a.appointment_id = ? AND a.patient_id = ?
");
$stmt->bind_param("ii", $appointment_id, $patient_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    header('Location: my-appointments.php');
    exit;
}

if ($appointment['payment_status'] === 'paid') {
    header('Location: my-appointments.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'] ?? 'cash';
    
    $conn->begin_transaction();
    
    try {
        if ($method === 'cash') {
            $stmt = $conn->prepare("UPDATE payments SET method = ?, status = 'unpaid' WHERE appointment_id = ?");
            $stmt->bind_param("si", $method, $appointment_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            
            $conn->commit();
            
            setFlashMessage('Appointment confirmed! Please pay at the center when you visit.', 'success');
            header('Location: my-appointments.php');
            exit;
        } else {
            $stmt = $conn->prepare("UPDATE payments SET method = ?, status = 'paid', paid_at = NOW() WHERE appointment_id = ?");
            $stmt->bind_param("si", $method, $appointment_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            
            $conn->commit();
            
            setFlashMessage('Payment successful! Your appointment has been confirmed.', 'success');
            header('Location: my-appointments.php');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Payment failed. Please try again.';
    }
}
?>

<section class="main-content">
    <div class="container">
        <div class="page-title">
            <h1>Payment</h1>
            <p>Complete your payment to confirm the appointment</p>
        </div>
        
        <?php if ($error): ?>
        <div class="flash-message error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
            <div>
                <div class="booking-summary">
                    <h3>Select Payment Method</h3>
                    
                    <form action="payment.php?appointment_id=<?php echo $appointment_id; ?>" method="POST">
                        <div class="form-group">
                            <label class="center-card" style="cursor: pointer;">
                                <input type="radio" name="method" value="cash" checked>
                                <span class="radio-indicator"></span>
                                <div class="center-info">
                                    <h4><i class="fas fa-money-bill-wave"></i> Pay at Center</h4>
                                    <p>Pay in cash when you visit the doctor</p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="center-card" style="cursor: pointer;">
                                <input type="radio" name="method" value="card">
                                <span class="radio-indicator"></span>
                                <div class="center-info">
                                    <h4><i class="fas fa-credit-card"></i> Debit/Credit Card</h4>
                                    <p>Pay securely online with your card</p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="center-card" style="cursor: pointer;">
                                <input type="radio" name="method" value="online">
                                <span class="radio-indicator"></span>
                                <div class="center-info">
                                    <h4><i class="fas fa-globe"></i> Online Banking</h4>
                                    <p>Pay via your bank's online portal</p>
                                </div>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px;">
                            <i class="fas fa-lock"></i> Confirm Payment
                        </button>
                    </form>
                </div>
            </div>
            
            <div>
                <div class="booking-summary">
                    <h3>Appointment Details</h3>
                    
                    <div class="summary-item">
                        <span class="label">Doctor</span>
                        <span class="value"><?php echo htmlspecialchars($appointment['doctor_name']); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="label">Specialization</span>
                        <span class="value"><?php echo htmlspecialchars($appointment['specialization']); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="label">Center</span>
                        <span class="value"><?php echo htmlspecialchars($appointment['center_name']); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="label">Date</span>
                        <span class="value"><?php echo date('d M Y', strtotime($appointment['slot_date'])); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="label">Time</span>
                        <span class="value"><?php echo date('h:i A', strtotime($appointment['start_time'])); ?></span>
                    </div>
                    
                    <div class="summary-item total-amount">
                        <span class="label">Total Amount</span>
                        <span class="value">LKR <?php echo number_format($appointment['payment_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
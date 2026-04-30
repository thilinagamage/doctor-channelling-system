<?php
$page_title = 'My Appointments';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

requirePatientLogin();

$patient_id = $_SESSION['patient_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';

$appointments = [];

if ($filter === 'upcoming') {
    $stmt = $conn->prepare("
        SELECT a.*, ts.slot_date, ts.start_time, ts.end_time, ts.status as slot_status,
               d.full_name as doctor_name, d.specialization, d.qualification,
               cc.center_name, cc.address as center_address,
               p.amount as payment_amount, p.status as payment_status
        FROM appointments a
        JOIN time_slots ts ON a.slot_id = ts.slot_id
        JOIN doctors d ON ts.doctor_id = d.doctor_id
        JOIN channelling_centers cc ON ts.center_id = cc.center_id
        LEFT JOIN payments p ON a.appointment_id = p.appointment_id
        WHERE a.patient_id = ? AND a.status != 'cancelled' AND ts.slot_date >= CURDATE()
        ORDER BY ts.slot_date ASC, ts.start_time ASC
    ");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} elseif ($filter === 'past') {
    $stmt = $conn->prepare("
        SELECT a.*, ts.slot_date, ts.start_time, ts.end_time, ts.status as slot_status,
               d.full_name as doctor_name, d.specialization, d.qualification,
               cc.center_name, cc.address as center_address,
               p.amount as payment_amount, p.status as payment_status
        FROM appointments a
        JOIN time_slots ts ON a.slot_id = ts.slot_id
        JOIN doctors d ON ts.doctor_id = d.doctor_id
        JOIN channelling_centers cc ON ts.center_id = cc.center_id
        LEFT JOIN payments p ON a.appointment_id = p.appointment_id
        WHERE a.patient_id = ? AND ts.slot_date < CURDATE()
        ORDER BY ts.slot_date DESC, ts.start_time DESC
    ");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} elseif ($filter === 'cancelled') {
    $stmt = $conn->prepare("
        SELECT a.*, ts.slot_date, ts.start_time, ts.end_time, ts.status as slot_status,
               d.full_name as doctor_name, d.specialization, d.qualification,
               cc.center_name, cc.address as center_address,
               p.amount as payment_amount, p.status as payment_status
        FROM appointments a
        JOIN time_slots ts ON a.slot_id = ts.slot_id
        JOIN doctors d ON ts.doctor_id = d.doctor_id
        JOIN channelling_centers cc ON ts.center_id = cc.center_id
        LEFT JOIN payments p ON a.appointment_id = p.appointment_id
        WHERE a.patient_id = ? AND a.status = 'cancelled'
        ORDER BY ts.slot_date DESC, ts.start_time DESC
    ");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $stmt = $conn->prepare("
        SELECT a.*, ts.slot_date, ts.start_time, ts.end_time, ts.status as slot_status,
               d.full_name as doctor_name, d.specialization, d.qualification,
               cc.center_name, cc.address as center_address,
               p.amount as payment_amount, p.status as payment_status
        FROM appointments a
        JOIN time_slots ts ON a.slot_id = ts.slot_id
        JOIN doctors d ON ts.doctor_id = d.doctor_id
        JOIN channelling_centers cc ON ts.center_id = cc.center_id
        LEFT JOIN payments p ON a.appointment_id = p.appointment_id
        WHERE a.patient_id = ?
        ORDER BY ts.slot_date DESC, ts.start_time DESC
    ");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if (isset($_GET['cancel']) && isset($_GET['appointment_id'])) {
    $appointment_id = intval($_GET['appointment_id']);
    
    $stmt = $conn->prepare("SELECT a.*, ts.slot_id FROM appointments a JOIN time_slots ts ON a.slot_id = ts.slot_id WHERE a.appointment_id = ? AND a.patient_id = ?");
    $stmt->bind_param("ii", $appointment_id, $patient_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    
    if ($appointment && $appointment['status'] !== 'cancelled') {
        $slot_id = $appointment['slot_id'];
        
        $conn->begin_transaction();
        
        try {
            $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE time_slots SET status = 'available' WHERE slot_id = ?");
            $stmt->bind_param("i", $slot_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE payments SET status = 'unpaid' WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            
            $conn->commit();
            
            setFlashMessage('Appointment cancelled successfully', 'success');
            header('Location: my-appointments.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            setFlashMessage('Failed to cancel appointment', 'error');
        }
    }
}

$flash = getFlashMessage();
?>

<section class="main-content">
    <div class="container">
        <div class="page-title">
            <h1>My Appointments</h1>
            <p>View and manage your appointments</p>
        </div>
        
        <?php if ($flash): ?>
        <div class="flash-message <?php echo $flash['type']; ?>">
            <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($flash['message']); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="filters-section">
            <form action="my-appointments.php" method="GET" class="filters-form">
                <label style="font-weight: 500;">Filter:</label>
                <select name="filter" onchange="this.form.submit()">
                    <option value="upcoming" <?php echo $filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="past" <?php echo $filter === 'past' ? 'selected' : ''; ?>>Past</option>
                    <option value="cancelled" <?php echo $filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </form>
        </div>
        
        <?php if (empty($appointments)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No Appointments</h3>
            <p>You don't have any <?php echo $filter; ?> appointments</p>
            <a href="doctors.php" class="btn-primary">Book an Appointment</a>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Center</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $apt): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($apt['doctor_name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($apt['specialization']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($apt['center_name']); ?><br>
                            <small style="color: var(--text-gray);"><?php echo htmlspecialchars($apt['center_address']); ?></small>
                        </td>
                        <td>
                            <?php echo date('d M Y', strtotime($apt['slot_date'])); ?><br>
                            <small><?php echo date('h:i A', strtotime($apt['start_time'])); ?> - <?php echo date('h:i A', strtotime($apt['end_time'])); ?></small>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $apt['status']; ?>">
                                <?php echo ucfirst($apt['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $apt['payment_status']; ?>">
                                <?php echo ucfirst($apt['payment_status']); ?>
                            </span>
                            <?php if ($apt['payment_amount']): ?>
                            <small style="display: block; margin-top: 5px;">LKR <?php echo number_format($apt['payment_amount'], 2); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <?php if ($apt['status'] === 'pending' && $apt['payment_status'] === 'unpaid'): ?>
                                <a href="payment.php?appointment_id=<?php echo $apt['appointment_id']; ?>" class="action-btn view">Pay</a>
                                <?php endif; ?>
                                
                                <?php if ($apt['status'] !== 'cancelled' && strtotime($apt['slot_date']) > time()): ?>
                                <a href="my-appointments.php?cancel=1&appointment_id=<?php echo $apt['appointment_id']; ?>" class="action-btn cancel" data-confirm="Are you sure you want to cancel this appointment?">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
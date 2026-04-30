<?php
$page_title = 'Manage Appointments';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$query = "
    SELECT a.*, ts.slot_date, ts.start_time, ts.end_time, ts.status as slot_status,
           d.full_name as doctor_name, d.specialization,
           cc.center_name,
           p.full_name as patient_name, p.phone as patient_phone, p.email as patient_email
    FROM appointments a
    JOIN time_slots ts ON a.slot_id = ts.slot_id
    JOIN doctors d ON ts.doctor_id = d.doctor_id
    JOIN channelling_centers cc ON ts.center_id = cc.center_id
    JOIN patients p ON a.patient_id = p.patient_id
    WHERE 1=1
";

if ($filter === 'today') {
    $query .= " AND ts.slot_date = CURDATE()";
} elseif ($filter === 'pending') {
    $query .= " AND a.status = 'pending'";
} elseif ($filter === 'confirmed') {
    $query .= " AND a.status = 'confirmed'";
} elseif ($filter === 'cancelled') {
    $query .= " AND a.status = 'cancelled'";
}

$query .= " ORDER BY ts.slot_date DESC, ts.start_time DESC";

$stmt = $conn->prepare($query);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['update_status'])) {
    $appointment_id = intval($_GET['update_status']);
    $new_status = $_GET['status'];
    
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
    $stmt->bind_param("si", $new_status, $appointment_id);
    
    if ($stmt->execute()) {
        if ($new_status === 'cancelled') {
            $stmt = $conn->prepare("SELECT slot_id FROM appointments WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $apt = $result->fetch_assoc();
            
            if ($apt) {
                $stmt = $conn->prepare("UPDATE time_slots SET status = 'available' WHERE slot_id = ?");
                $stmt->bind_param("i", $apt['slot_id']);
                $stmt->execute();
            }
        }
        
        $success = 'Appointment status updated successfully';
        header('Location: appointments.php');
        exit;
    } else {
        $error = 'Failed to update appointment status';
    }
}

if (isset($_GET['delete'])) {
    $appointment_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("SELECT slot_id FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $apt = $result->fetch_assoc();
    
    if ($apt) {
        $stmt = $conn->prepare("UPDATE time_slots SET status = 'available' WHERE slot_id = ?");
        $stmt->bind_param("i", $apt['slot_id']);
        $stmt->execute();
    }
    
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        $success = 'Appointment deleted successfully';
    } else {
        $error = 'Failed to delete appointment';
    }
}
?>

<?php if ($error): ?>
<div class="flash-message error" style="padding: 16px; border-radius: 8px; margin-bottom: 20px; background: #fee2e2; color: #b91c1c;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="flash-message success" style="padding: 16px; border-radius: 8px; margin-bottom: 20px; background: #d1fae5; color: #047857;">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<div class="filters-section" style="margin-top: 0; background: transparent; padding: 0; margin-bottom: 20px;">
    <form action="appointments.php" method="GET" class="filters-form">
        <label style="font-weight: 500;">Filter:</label>
        <select name="filter" onchange="this.form.submit()">
            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
            <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
            <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="confirmed" <?php echo $filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
            <option value="cancelled" <?php echo $filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
    </form>
</div>

<div class="admin-table-container">
    <div class="admin-table-header">
        <h3>Appointments (<?php echo count($appointments); ?>)</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Center</th>
                <th>Date & Time</th>
                <th>Booked</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $apt): ?>
            <tr>
                <td>#<?php echo $apt['appointment_id']; ?></td>
                <td>
                    <?php echo htmlspecialchars($apt['patient_name']); ?><br>
                    <small style="color: var(--text-gray);"><?php echo htmlspecialchars($apt['patient_phone']); ?></small>
                </td>
                <td>
                    <?php echo htmlspecialchars($apt['doctor_name']); ?><br>
                    <small style="color: var(--text-gray);"><?php echo htmlspecialchars($apt['specialization']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($apt['center_name']); ?></td>
                <td>
                    <?php echo date('d M Y', strtotime($apt['slot_date'])); ?><br>
                    <small><?php echo date('h:i A', strtotime($apt['start_time'])); ?></small>
                </td>
                <td><?php echo date('d M h:i A', strtotime($apt['booked_at'])); ?></td>
                <td>
                    <span class="status-badge <?php echo $apt['status']; ?>">
                        <?php echo ucfirst($apt['status']); ?>
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <?php if ($apt['status'] === 'pending'): ?>
                        <a href="appointments.php?update_status=<?php echo $apt['appointment_id']; ?>&status=confirmed" class="action-btn view" style="background: #d1fae5; color: #047857;">
                            <i class="fas fa-check"></i> Confirm
                        </a>
                        <?php endif; ?>
                        <?php if ($apt['status'] !== 'cancelled'): ?>
                        <a href="appointments.php?update_status=<?php echo $apt['appointment_id']; ?>&status=cancelled" class="action-btn cancel">
                            <i class="fas fa-times"></i>
                        </a>
                        <?php endif; ?>
                        <a href="appointments.php?delete=<?php echo $apt['appointment_id']; ?>" class="action-btn cancel" data-confirm="Delete this appointment?">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($appointments)): ?>
            <tr>
                <td colspan="8" style="text-align: center;">No appointments found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div></main></div></body></html>
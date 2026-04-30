<?php
$page_title = 'Payment Records';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$query = "
    SELECT p.*, 
           d.full_name as doctor_name,
           pt.full_name as patient_name,
           ts.slot_date
    FROM payments p
    JOIN appointments a ON p.appointment_id = a.appointment_id
    JOIN time_slots ts ON a.slot_id = ts.slot_id
    JOIN doctors d ON ts.doctor_id = d.doctor_id
    JOIN patients pt ON a.patient_id = pt.patient_id
    WHERE 1=1
";

if ($filter === 'paid') {
    $query .= " AND p.status = 'paid'";
} elseif ($filter === 'unpaid') {
    $query .= " AND p.status = 'unpaid'";
}

$query .= " ORDER BY p.paid_at DESC, ts.slot_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalPaid = 0;
$totalUnpaid = 0;
foreach ($payments as $pay) {
    if ($pay['status'] === 'paid') {
        $totalPaid += $pay['amount'];
    } else {
        $totalUnpaid += $pay['amount'];
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

<div class="admin-stats-grid" style="margin-bottom: 30px;">
    <div class="admin-stat-card">
        <div class="admin-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="admin-stat-info">
            <h3>LKR <?php echo number_format($totalPaid, 2); ?></h3>
            <p>Total Paid</p>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="admin-stat-info">
            <h3>LKR <?php echo number_format($totalUnpaid, 2); ?></h3>
            <p>Total Unpaid</p>
        </div>
    </div>
</div>

<div class="filters-section" style="margin-top: 0; background: transparent; padding: 0; margin-bottom: 20px;">
    <form action="payments.php" method="GET" class="filters-form">
        <label style="font-weight: 500;">Filter:</label>
        <select name="filter" onchange="this.form.submit()">
            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
            <option value="paid" <?php echo $filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="unpaid" <?php echo $filter === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
        </select>
    </form>
</div>

<div class="admin-table-container">
    <div class="admin-table-header">
        <h3>Payment Records (<?php echo count($payments); ?>)</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Appointment Date</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Paid At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
            <tr>
                <td>#<?php echo $payment['payment_id']; ?></td>
                <td><?php echo htmlspecialchars($payment['patient_name']); ?></td>
                <td><?php echo htmlspecialchars($payment['doctor_name']); ?></td>
                <td><?php echo date('d M Y', strtotime($payment['slot_date'])); ?></td>
                <td><strong>LKR <?php echo number_format($payment['amount'], 2); ?></strong></td>
                <td><?php echo ucfirst($payment['method']); ?></td>
                <td>
                    <span class="status-badge <?php echo $payment['status']; ?>">
                        <?php echo ucfirst($payment['status']); ?>
                    </span>
                </td>
                <td><?php echo $payment['paid_at'] ? date('d M Y h:i A', strtotime($payment['paid_at'])) : '-'; ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($payments)): ?>
            <tr>
                <td colspan="8" style="text-align: center;">No payments found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div></main></div></body></html>
<?php
$page_title = 'Manage Time Slots';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

$doctors = [];
$result = $conn->query("SELECT * FROM doctors ORDER BY full_name");
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

$centers = [];
$result = $conn->query("SELECT * FROM channelling_centers ORDER BY center_name");
while ($row = $result->fetch_assoc()) {
    $centers[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_slots'])) {
        $doctor_id = intval($_POST['doctor_id']);
        $center_id = intval($_POST['center_id']);
        $slots_data = $_POST['slots'] ?? [];
        
        if (empty($doctor_id) || empty($center_id)) {
            $error = 'Please select doctor and center';
        } elseif (empty($slots_data)) {
            $error = 'Please add at least one time slot';
        } else {
            $inserted = 0;
            foreach ($slots_data as $slot) {
                if (!empty($slot['date']) && !empty($slot['start_time']) && !empty($slot['end_time'])) {
                    $stmt = $conn->prepare("INSERT INTO time_slots (doctor_id, center_id, slot_date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, 'available')");
                    $stmt->bind_param("iisss", $doctor_id, $center_id, $slot['date'], $slot['start_time'], $slot['end_time']);
                    if ($stmt->execute()) {
                        $inserted++;
                    }
                }
            }
            if ($inserted > 0) {
                $success = "$inserted time slot(s) added successfully";
            } else {
                $error = 'Failed to add time slots';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $slot_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("SELECT status FROM time_slots WHERE slot_id = ?");
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $slot = $result->fetch_assoc();
    
    if ($slot && $slot['status'] !== 'booked') {
        $stmt = $conn->prepare("DELETE FROM time_slots WHERE slot_id = ?");
        $stmt->bind_param("i", $slot_id);
        
        if ($stmt->execute()) {
            $success = 'Time slot deleted successfully';
        } else {
            $error = 'Failed to delete time slot';
        }
    } else {
        $error = 'Cannot delete a booked time slot';
    }
}

if (isset($_POST['update_slot'])) {
    $slot_id = intval($_POST['edit_slot_id']);
    $edit_date = $_POST['edit_date'];
    $edit_start_time = $_POST['edit_start_time'];
    $edit_end_time = $_POST['edit_end_time'];
    
    $stmt = $conn->prepare("SELECT status FROM time_slots WHERE slot_id = ?");
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $slot = $result->fetch_assoc();
    
    if ($slot && $slot['status'] !== 'booked') {
        $stmt = $conn->prepare("UPDATE time_slots SET slot_date = ?, start_time = ?, end_time = ? WHERE slot_id = ?");
        $stmt->bind_param("sssi", $edit_date, $edit_start_time, $edit_end_time, $slot_id);
        
        if ($stmt->execute()) {
            $success = 'Time slot updated successfully';
        } else {
            $error = 'Failed to update time slot';
        }
    } else {
        $error = 'Cannot edit a booked time slot';
    }
}

if (isset($_GET['doctor_filter'])) {
    $doctor_filter = intval($_GET['doctor_filter']);
    $slots = [];
    $stmt = $conn->prepare("
        SELECT ts.*, d.full_name as doctor_name, d.specialization, cc.center_name
        FROM time_slots ts
        JOIN doctors d ON ts.doctor_id = d.doctor_id
        JOIN channelling_centers cc ON ts.center_id = cc.center_id
        WHERE ts.doctor_id = ?
        ORDER BY ts.slot_date DESC, ts.start_time ASC
    ");
    $stmt->bind_param("i", $doctor_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }
} else {
    $slots = [];
    $result = $conn->query("
        SELECT ts.*, d.full_name as doctor_name, d.specialization, cc.center_name
        FROM time_slots ts
        JOIN doctors d ON ts.doctor_id = d.doctor_id
        JOIN channelling_centers cc ON ts.center_id = cc.center_id
        ORDER BY ts.slot_date DESC, ts.start_time ASC
        LIMIT 50
    ");
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
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

<div class="admin-table-container">
    <div class="admin-table-header">
        <h3>Add Time Slot(s)</h3>
    </div>
    <div style="padding: 20px;">
        <form id="slots-form" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label>Doctor *</label>
                    <select name="doctor_id" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $doc): ?>
                        <option value="<?php echo $doc['doctor_id']; ?>"><?php echo htmlspecialchars($doc['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Center *</label>
                    <select name="center_id" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                        <option value="">Select Center</option>
                        <?php foreach ($centers as $cen): ?>
                        <option value="<?php echo $cen['center_id']; ?>"><?php echo htmlspecialchars($cen['center_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div id="slots-container" style="background: #f9fafb; padding: 15px; border-radius: 8px;"></div>
            
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" id="add-slot-btn" class="btn-secondary">
                    <i class="fas fa-plus"></i> Add Slot
                </button>
                <button type="submit" name="add_slots" class="btn-primary">
                    <i class="fas fa-save"></i> Save All Slots
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let slotIndex = 0;

function addSlotRow() {
    const container = document.getElementById('slots-container');
    const row = document.createElement('div');
    row.className = 'slot-row';
    row.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: center; flex-wrap: wrap;';
    row.innerHTML = 
        '<input type="date" name="slots[' + slotIndex + '][date]" class="future-date" required min="<?php echo date('Y-m-d'); ?>" style="flex: 1; min-width: 140px; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">' +
        '<input type="time" name="slots[' + slotIndex + '][start_time]" required style="width: 110px; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">' +
        '<span style="font-weight: 500;">to</span>' +
        '<input type="time" name="slots[' + slotIndex + '][end_time]" required style="width: 110px; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">' +
        '<button type="button" onclick="this.parentElement.remove()" style="background: #fee2e2; color: #dc2626; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer;"><i class="fas fa-times"></i> Remove</button>';
    container.appendChild(row);
    slotIndex++;
}

document.getElementById('add-slot-btn').addEventListener('click', addSlotRow);
addSlotRow();
</script>

<div class="filters-section" style="margin-top: 30px; background: transparent; padding: 0;">
    <form action="slots.php" method="GET" class="filters-form">
        <label style="font-weight: 500;">Filter by Doctor:</label>
        <select name="doctor_filter" onchange="this.form.submit()">
            <option value="">All Doctors</option>
            <?php foreach ($doctors as $doc): ?>
            <option value="<?php echo $doc['doctor_id']; ?>" <?php echo (isset($doctor_filter) && $doctor_filter == $doc['doctor_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($doc['full_name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="admin-table-container" style="margin-top: 20px;">
    <div class="admin-table-header">
        <h3>Time Slots (<?php echo count($slots); ?>)</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Doctor</th>
                <th>Center</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($slots as $slot): ?>
            <tr>
                <td>#<?php echo $slot['slot_id']; ?></td>
                <td><?php echo htmlspecialchars($slot['doctor_name']); ?></td>
                <td><?php echo htmlspecialchars($slot['center_name']); ?></td>
                <td><?php echo date('d M Y', strtotime($slot['slot_date'])); ?></td>
                <td><?php echo date('h:i A', strtotime($slot['start_time'])); ?> - <?php echo date('h:i A', strtotime($slot['end_time'])); ?></td>
                <td>
                    <span class="status-badge <?php echo $slot['status']; ?>">
                        <?php echo ucfirst($slot['status']); ?>
                    </span>
                </td>
                <td>
                    <?php if ($slot['status'] !== 'booked'): ?>
                    <button type="button" onclick="editSlot(<?php echo $slot['slot_id']; ?>, '<?php echo $slot['slot_date']; ?>', '<?php echo $slot['start_time']; ?>', '<?php echo $slot['end_time']; ?>')" style="background: #e8f0fe; color: #1a73e8; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="slots.php?delete=<?php echo $slot['slot_id']; ?>" class="action-btn cancel" data-confirm="Are you sure you want to delete this slot?">
                        <i class="fas fa-trash"></i>
                    </a>
                    <?php else: ?>
                    <span style="color: var(--text-light);">Booked</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($slots)): ?>
            <tr>
                <td colspan="7" style="text-align: center;">No time slots found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 10px; width: 100%; max-width: 400px;">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Edit Time Slot</h3>
        <form method="POST" action="slots.php">
            <input type="hidden" name="edit_slot_id" id="edit_slot_id">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="edit_date" id="edit_date" required style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px;">
            </div>
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="edit_start_time" id="edit_start_time" required style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px;">
            </div>
            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="edit_end_time" id="edit_end_time" required style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px;">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="update_slot" class="btn-primary" style="flex: 1;">Save Changes</button>
                <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn-secondary" style="flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSlot(id, date, start, end) {
    document.getElementById('edit_slot_id').value = id;
    document.getElementById('edit_date').value = date;
    document.getElementById('edit_start_time').value = start;
    document.getElementById('edit_end_time').value = end;
    document.getElementById('editModal').style.display = 'flex';
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
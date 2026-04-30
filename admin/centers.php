<?php
$page_title = 'Manage Centers';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_center'])) {
        $center_name = trim($_POST['center_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($center_name) || empty($address) || empty($phone)) {
            $error = 'Please fill in all required fields';
        } else {
            $stmt = $conn->prepare("INSERT INTO channelling_centers (center_name, address, phone, email) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $center_name, $address, $phone, $email);
            
            if ($stmt->execute()) {
                $success = 'Center added successfully';
            } else {
                $error = 'Failed to add center';
            }
        }
    } elseif (isset($_POST['update_center'])) {
        $center_id = intval($_POST['center_id']);
        $center_name = trim($_POST['center_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $stmt = $conn->prepare("UPDATE channelling_centers SET center_name = ?, address = ?, phone = ?, email = ? WHERE center_id = ?");
        $stmt->bind_param("ssssi", $center_name, $address, $phone, $email, $center_id);
        
        if ($stmt->execute()) {
            $success = 'Center updated successfully';
        } else {
            $error = 'Failed to update center';
        }
    }
}

if (isset($_GET['delete'])) {
    $center_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM channelling_centers WHERE center_id = ?");
    $stmt->bind_param("i", $center_id);
    
    if ($stmt->execute()) {
        $success = 'Center deleted successfully';
    } else {
        $error = 'Failed to delete center';
    }
}

$centers = [];
$result = $conn->query("SELECT * FROM channelling_centers ORDER BY center_name");
while ($row = $result->fetch_assoc()) {
    $centers[] = $row;
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
        <h3>Add New Center</h3>
    </div>
    <div style="padding: 20px;">
        <form action="centers.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="form-group">
                <label>Center Name *</label>
                <input type="text" name="center_name" required>
            </div>
            <div class="form-group">
                <label>Address *</label>
                <input type="text" name="address" required>
            </div>
            <div class="form-group">
                <label>Phone *</label>
                <input type="tel" name="phone" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <div style="grid-column: 1 / -1;">
                <button type="submit" name="add_center" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Center
                </button>
            </div>
        </form>
    </div>
</div>

<div class="admin-table-container" style="margin-top: 30px;">
    <div class="admin-table-header">
        <h3>All Centers (<?php echo count($centers); ?>)</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($centers as $center): ?>
            <tr>
                <td>#<?php echo $center['center_id']; ?></td>
                <td><?php echo htmlspecialchars($center['center_name']); ?></td>
                <td><?php echo htmlspecialchars($center['address']); ?></td>
                <td><?php echo htmlspecialchars($center['phone']); ?></td>
                <td><?php echo htmlspecialchars($center['email']); ?></td>
                <td>
                    <div class="action-btns">
                        <button class="action-btn view" onclick="editCenter(<?php echo $center['center_id']; ?>, '<?php echo htmlspecialchars($center['center_name']); ?>', '<?php echo htmlspecialchars($center['address']); ?>', '<?php echo htmlspecialchars($center['phone']); ?>', '<?php echo htmlspecialchars($center['email']); ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="centers.php?delete=<?php echo $center['center_id']; ?>" class="action-btn cancel" data-confirm="Are you sure you want to delete this center?">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($centers)): ?>
            <tr>
                <td colspan="6" style="text-align: center;">No centers found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal" id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; border-radius: 12px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #e5e7eb;">
            <h3 style="font-size: 1.25rem; margin: 0;">Edit Center</h3>
            <button class="modal-close" onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
        </div>
        <form action="centers.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="center_id" id="edit_center_id">
                <input type="hidden" name="update_center" value="1">
                
                <div class="form-group">
                    <label>Center Name *</label>
                    <input type="text" name="center_name" id="edit_center_name" required>
                </div>
                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" name="address" id="edit_address" required>
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" id="edit_phone" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCenter(id, name, address, phone, email) {
    document.getElementById('edit_center_id').value = id;
    document.getElementById('edit_center_name').value = name;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
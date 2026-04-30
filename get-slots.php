<?php
require_once 'config/db.php';

header('Content-Type: application/json');

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$center_id = isset($_GET['center_id']) ? intval($_GET['center_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (!$doctor_id || !$center_id || !$date) {
    echo json_encode(['success' => false, 'html' => '<p class="error">Invalid parameters</p>']);
    exit;
}

$stmt = $conn->prepare("
    SELECT ts.*, cc.center_name 
    FROM time_slots ts 
    JOIN channelling_centers cc ON ts.center_id = cc.center_id 
    WHERE ts.doctor_id = ? AND ts.center_id = ? AND ts.slot_date = ? AND ts.status = 'available'
    ORDER BY ts.start_time ASC
");
$stmt->bind_param("iis", $doctor_id, $center_id, $date);
$stmt->execute();
$slots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($slots)) {
    echo json_encode(['success' => false, 'html' => '<p>No available slots for this date</p>']);
    exit;
}

$html = '';
foreach ($slots as $slot) {
    $html .= '<button type="button" class="slot-btn" onclick="selectSlot(' . $slot['slot_id'] . ', this)">';
    $html .= '<span class="time">' . date('h:i A', strtotime($slot['start_time'])) . ' - ' . date('h:i A', strtotime($slot['end_time'])) . '</span>';
    $html .= '<span class="status">Available</span>';
    $html .= '</button>';
}

echo json_encode(['success' => true, 'html' => $html]);
exit;

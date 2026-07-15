<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_token']) || $_SESSION['admin_token'] !== 'authenticated_success_token') {
    header('Location: login.php');
    exit;
}

include_once '../config/db.php';

$filter_from = $_GET['from'] ?? date('Y-m-01');
$filter_to = $_GET['to'] ?? date('Y-m-d');
$filter_doctor = $_GET['doctor'] ?? '';
$filter_treatment = $_GET['treatment'] ?? '';
$filter_status = $_GET['status'] ?? '';

$apt_where = "WHERE DATE(a.created_at) BETWEEN ? AND ?";
$apt_params = [$filter_from, $filter_to];
$apt_types = "ss";

if ($filter_doctor !== '') {
    $apt_where .= " AND s.doctor_id = ?";
    $apt_params[] = $filter_doctor;
    $apt_types .= "i";
}
if ($filter_treatment !== '') {
    $apt_where .= " AND a.treatment_id = ?";
    $apt_params[] = $filter_treatment;
    $apt_types .= "i";
}
if ($filter_status !== '') {
    $apt_where .= " AND a.status = ?";
    $apt_params[] = $filter_status;
    $apt_types .= "s";
}

$stmt = $conn->prepare("SELECT a.id, a.status, a.created_at, u.name AS patient_name, t.treatment_name, d.name AS doctor_name, s.available_date, s.start_time FROM appointments a JOIN users u ON u.id = a.user_id JOIN treatments t ON t.id = a.treatment_id JOIN schedules s ON s.id = a.schedule_id JOIN doctors d ON d.id = s.doctor_id $apt_where ORDER BY a.created_at DESC");
$stmt->bind_param($apt_types, ...$apt_params);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

// Build Excel XML (SpreadsheetML)
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="Appointment_Details_' . $filter_from . '_to_' . $filter_to . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";

echo '<Styles>' . "\n";
echo '<Style ss:ID="header"><Font ss:Bold="1" ss:Size="11" ss:Color="#FFFFFF"/><Interior ss:Color="#FF6584" ss:Pattern="Solid"/></Style>' . "\n";
echo '<Style ss:ID="date"><NumberFormat ss:Format="dd MMM yyyy"/></Style>' . "\n";
echo '<Style ss:ID="time"><NumberFormat ss:Format="hh:mm AM/PM"/></Style>' . "\n";
echo '</Styles>' . "\n";

echo '<Worksheet ss:Name="Appointment Details">' . "\n";
echo '<Table>' . "\n";

echo '<Column ss:Width="40"/>';
echo '<Column ss:Width="150"/>';
echo '<Column ss:Width="150"/>';
echo '<Column ss:Width="150"/>';
echo '<Column ss:Width="100"/>';
echo '<Column ss:Width="80"/>';
echo '<Column ss:Width="100"/>';
echo "\n";

echo '<Row ss:StyleID="header">' . "\n";
echo '<Cell><Data ss:Type="String">#</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Patient</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Treatment</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Doctor</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Date</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Time</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Status</Data></Cell>' . "\n";
echo '</Row>' . "\n";

foreach ($rows as $i => $r) {
    echo '<Row>' . "\n";
    echo '<Cell><Data ss:Type="Number">' . ($i + 1) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['patient_name']) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['treatment_name']) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['doctor_name']) . '</Data></Cell>' . "\n";
    echo '<Cell ss:StyleID="date"><Data ss:Type="DateTime">' . date('Y-m-d\T00:00:00', strtotime($r['available_date'])) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . date('h:i A', strtotime($r['start_time'])) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . ucfirst($r['status']) . '</Data></Cell>' . "\n";
    echo '</Row>' . "\n";
}

echo '</Table>' . "\n";
echo '</Worksheet>' . "\n";
echo '</Workbook>';

$conn->close();

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
$filter_payment = $_GET['payment'] ?? '';

$rev_where = "WHERE DATE(a.created_at) BETWEEN ? AND ? AND a.status IN ('completed','confirmed')";
$rev_params = [$filter_from, $filter_to];
$rev_types = "ss";

if ($filter_payment !== '') {
    $rev_where .= " AND a.payment_method_id = ?";
    $rev_params[] = $filter_payment;
    $rev_types .= "i";
}

$stmt = $conn->prepare("SELECT a.created_at, u.name AS patient_name, t.treatment_name, t.price, COALESCE(pm.method_name, 'N/A') AS payment_method FROM appointments a JOIN users u ON u.id = a.user_id JOIN treatments t ON t.id = a.treatment_id LEFT JOIN payment_methods pm ON pm.id = a.payment_method_id $rev_where ORDER BY a.created_at DESC");
$stmt->bind_param($rev_types, ...$rev_params);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

$rev_total = 0;
foreach ($rows as $r) $rev_total += $r['price'];

// Build Excel XML (SpreadsheetML)
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="Revenue_Details_' . $filter_from . '_to_' . $filter_to . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";

// Styles
echo '<Styles>' . "\n";
echo '<Style ss:ID="header"><Font ss:Bold="1" ss:Size="11" ss:Color="#FFFFFF"/><Interior ss:Color="#FF6584" ss:Pattern="Solid"/></Style>' . "\n";
echo '<Style ss:ID="currency"><NumberFormat ss:Format="#,##0"/></Style>' . "\n";
echo '<Style ss:ID="date"><NumberFormat ss:Format="dd MMM yyyy"/></Style>' . "\n";
echo '</Styles>' . "\n";

echo '<Worksheet ss:Name="Revenue Details">' . "\n";
echo '<Table>' . "\n";

// Column widths
echo '<Column ss:Width="40"/>';
echo '<Column ss:Width="100"/>';
echo '<Column ss:Width="160"/>';
echo '<Column ss:Width="160"/>';
echo '<Column ss:Width="120"/>';
echo '<Column ss:Width="100"/>';
echo "\n";

// Header row
echo '<Row ss:StyleID="header">' . "\n";
echo '<Cell><Data ss:Type="String">#</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Date</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Patient</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Treatment</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Payment Method</Data></Cell>' . "\n";
echo '<Cell><Data ss:Type="String">Amount (MMK)</Data></Cell>' . "\n";
echo '</Row>' . "\n";

// Data rows
foreach ($rows as $i => $r) {
    echo '<Row>' . "\n";
    echo '<Cell><Data ss:Type="Number">' . ($i + 1) . '</Data></Cell>' . "\n";
    echo '<Cell ss:StyleID="date"><Data ss:Type="DateTime">' . date('Y-m-d\T00:00:00', strtotime($r['created_at'])) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['patient_name']) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['treatment_name']) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['payment_method']) . '</Data></Cell>' . "\n";
    echo '<Cell ss:StyleID="currency"><Data ss:Type="Number">' . $r['price'] . '</Data></Cell>' . "\n";
    echo '</Row>' . "\n";
}

// Total row
echo '<Row>' . "\n";
echo '<Cell/>' . "\n";
echo '<Cell/>' . "\n";
echo '<Cell/>' . "\n";
echo '<Cell/>' . "\n";
echo '<Cell/>' . "\n";
echo '<Cell><Data ss:Type="String">TOTAL</Data></Cell>' . "\n";
echo '<Cell ss:StyleID="currency"><Data ss:Type="Number">' . $rev_total . '</Data></Cell>' . "\n";
echo '</Row>' . "\n";

echo '</Table>' . "\n";
echo '</Worksheet>' . "\n";
echo '</Workbook>';

$conn->close();

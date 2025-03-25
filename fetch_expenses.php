<?php
include 'db.php';

$sql = "SELECT * FROM expenses";
$stmt = $conn->prepare($sql);
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($expenses);
?>
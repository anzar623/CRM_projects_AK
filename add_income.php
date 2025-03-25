<?php
include 'db.php';

// Get form data
$amount = $_POST['amount'];
$income_from_id = $_POST['income_from_id'];
$date = $_POST['date'];
$notes = $_POST['notes'];

// Insert into database
$sql = "INSERT INTO income (amount, income_from_id, date, notes) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$amount, $income_from_id, $date, $notes]);

// Redirect back to income page
header("Location: income.php");
exit();
?>
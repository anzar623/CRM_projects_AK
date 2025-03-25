<?php
include 'db.php';

// Get form data
$amount = $_POST['amount'];
$expense_to_id = $_POST['expense_to_id'];
$date = $_POST['date'];
$notes = $_POST['notes'];

// Insert into database
$sql = "INSERT INTO expenses (amount, expense_to_id, date, notes) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$amount, $expense_to_id, $date, $notes]);

// Redirect back to expense page
header("Location: expense.php");
exit();
?>
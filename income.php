<?php
include 'db.php';

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM income WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$delete_id])) {
        header("Location: income.php");
        exit;
    }
}

// Handle Add & Edit Requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['source_name'])) {
        // Add new income source
        $source_name = $_POST['source_name'];
        $sql = "INSERT INTO income_from (source_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$source_name]);
        header("Location: income.php");
        exit;
    } else {
        // Add/Edit Income
        $amount = $_POST['amount'];
        $income_from_id = $_POST['income_from_id'];
        $date = $_POST['date'];
        $notes = $_POST['notes'];

        if (isset($_POST['income_id']) && !empty($_POST['income_id'])) {
            $income_id = $_POST['income_id'];
            $sql = "UPDATE income SET amount = ?, income_from_id = ?, date = ?, notes = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$amount, $income_from_id, $date, $notes, $income_id]);
        } else {
            $sql = "INSERT INTO income (amount, income_from_id, date, notes) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$amount, $income_from_id, $date, $notes]);
        }

        header("Location: income.php");
        exit;
    }
}

// Fetch all income
$sql = "SELECT i.id, i.amount, f.source_name, i.date, i.notes 
        FROM income i 
        JOIN income_from f ON i.income_from_id = f.id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$income = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sources for dropdown
$sql = "SELECT * FROM income_from";
$stmt = $conn->prepare($sql);
$stmt->execute();
$sources = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Income</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function editIncome(id, amount, source_id, date, notes) {
      document.getElementById('income_id').value = id;
      document.getElementById('amount').value = amount;
      document.getElementById('income_from_id').value = source_id;
      document.getElementById('date').value = date;
      document.getElementById('notes').value = notes;
      document.getElementById('incomeModal').style.display = 'block';
    }

    function confirmDelete(id) {
      if (confirm("Are you sure you want to delete this income?")) {
        window.location.href = 'income.php?delete_id=' + id;
      }
    }

    function openModal(modalId) {
      document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }
  </script>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
    <div class="logo" style="color:rgb(194, 194, 194);">Khan's Finance</div>
      <ul class="menu">
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="expense.php">Expense</a></li>
        <li><a href="income.php" class="active">Income</a></li>
        <li><a href="analytics.php">Analytics</a></li>
        <li><a href="summary.php">Summary</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="header">
        <div class="header-title">Income</div>
        <div class="user-profile">
          <span>John Doe</span>
          <img src="profile.jpg" alt="Profile">
        </div>
      </div>

      <div class="content">
        <h1>Income Tracker</h1>
        <button onclick="openModal('incomeModal')">Add Income</button>
        <button onclick="openModal('sourceModal')">Add Source</button>
        <table>
          <thead>
            <tr>
              <th>Amount</th>
              <th>Source</th>
              <th>Date</th>
              <th>Notes</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($income as $entry): ?>
              <tr>
                <td><?= $entry['amount'] ?></td>
                <td><?= $entry['source_name'] ?></td>
                <td><?= $entry['date'] ?></td>
                <td><?= $entry['notes'] ?></td>
                <td>
                <button onclick="editIncome(<?= $entry['id'] ?>, '<?= $entry['amount'] ?>', '<?= $entry['source_name'] ?>', '<?= $entry['date'] ?>', '<?= $entry['notes'] ?>')">Edit</button>
                  <button onclick="confirmDelete(<?= $entry['id'] ?>)">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add/Edit Income Modal -->
  <div id="incomeModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('incomeModal')">&times;</span>
      <h2>Add / Edit Income</h2>
      <form method="POST">
        <input type="hidden" id="income_id" name="income_id">
        
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required>

        <label for="income_from_id">Source:</label>
        <select id="income_from_id" name="income_from_id" required>
          <?php foreach ($sources as $source): ?>
            <option value="<?= $source['id'] ?>"><?= $source['source_name'] ?></option>
          <?php endforeach; ?>
        </select>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>

        <button type="submit">Save</button>
      </form>
    </div>
  </div>

  <!-- Add Source Modal -->
  <div id="sourceModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('sourceModal')">&times;</span>
      <h2>Add New Income Source</h2>
      <form method="POST">
        <label for="source_name">Source Name:</label>
        <input type="text" id="source_name" name="source_name" required>
        <button type="submit">Add Source</button>
      </form>
    </div>
  </div>

</body>
</html>

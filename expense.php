<?php
include 'db.php';

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM expenses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$delete_id])) {
        header("Location: expense.php");
        exit;
    }
}

// Handle Add & Edit Expense Requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    $expense_to_id = $_POST['expense_to_id'];
    $date = $_POST['date'];
    $notes = $_POST['notes'];

    if (!empty($_POST['expense_id'])) {
        // Edit existing expense
        $expense_id = $_POST['expense_id'];
        $sql = "UPDATE expenses SET amount = ?, expense_to_id = ?, date = ?, notes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$amount, $expense_to_id, $date, $notes, $expense_id]);
    } else {
        // Add new expense
        $sql = "INSERT INTO expenses (amount, expense_to_id, date, notes) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$amount, $expense_to_id, $date, $notes]);
    }

    header("Location: expense.php");
    exit;
}

// Handle Add Category Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category_name'])) {
    $category_name = $_POST['category_name'];

    $sql = "INSERT INTO expense_to (category_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$category_name])) {
        header("Location: expense.php");
        exit;
    }
}

// Fetch all expenses
$sql = "SELECT e.id, e.amount, t.category_name, e.date, e.notes 
        FROM expenses e 
        JOIN expense_to t ON e.expense_to_id = t.id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories for dropdown
$sql = "SELECT * FROM expense_to";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle filters
$dateFilter = $_GET['date_filter'] ?? 'all';
$categoryFilter = $_GET['category_filter'] ?? 'all';
$customStart = $_GET['custom_start'] ?? '';
$customEnd = $_GET['custom_end'] ?? '';

// Base SQL for expenses
$sql = "SELECT e.id, e.amount, t.category_name, e.date, e.notes 
        FROM expenses e 
        JOIN expense_to t ON e.expense_to_id = t.id";
$params = [];

// Apply date filters
if ($dateFilter !== 'all') {
    $today = date('Y-m-d');
    switch ($dateFilter) {
        case 'weekly':
            $sql .= " AND e.date >= DATE_SUB(?, INTERVAL 1 WEEK)";
            $params[] = $today;
            break;
        case 'monthly':
            $sql .= " AND e.date >= DATE_SUB(?, INTERVAL 1 MONTH)";
            $params[] = $today;
            break;
        case 'custom':
            if (!empty($customStart) && !empty($customEnd)) {
                $sql .= " AND e.date BETWEEN ? AND ?";
                $params[] = $customStart;
                $params[] = $customEnd;
            }
            break;
    }
}

// Apply category filter
if ($categoryFilter !== 'all') {
    $sql .= " AND e.expense_to_id = ?";
    $params[] = $categoryFilter;
}

// Fetch filtered expenses
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Expense</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function editExpense(id, amount, category_id, date, notes) {
      document.getElementById('expense_id').value = id;
      document.getElementById('amount').value = amount;
      document.getElementById('expense_to_id').value = category_id;
      document.getElementById('date').value = date;
      document.getElementById('notes').value = notes;
      document.getElementById('expenseModal').style.display = 'block';
    }

    function confirmDelete(id) {
      if (confirm("Are you sure you want to delete this expense?")) {
        window.location.href = 'expense.php?delete_id=' + id;
      }
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
        <li><a href="expense.php" class="active">Expense</a></li>
        <li><a href="income.php">Income</a></li>
        <li><a href="analytics.php">Analytics</a></li>
        <li><a href="summary.php">Summary</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="header">
        <div class="header-title">Expense</div>
        <div class="user-profile">
          <span>John Doe</span>
          <img src="profile.jpg" alt="Profile">
        </div>
      </div>

      <!-- Content Area -->
     <div class="content">
    <h1>Expense Tracker</h1>

    <div class="summary-chart">
      <h3>Expenses by Category</h3>
      <div class="chart-container">
        <canvas id="categoryChart"></canvas>
      </div>
    </div>
    
    <!-- Compact Filter Controls -->
    <div class="filters">
      <select id="date_filter" name="date_filter" onchange="toggleCustomDates(); window.location.href='expense.php?date_filter='+this.value+'&category_filter=<?= $categoryFilter ?>'">
        <option value="all" <?= $dateFilter === 'all' ? 'selected' : '' ?>>All Dates</option>
        <option value="weekly" <?= $dateFilter === 'weekly' ? 'selected' : '' ?>>Last Week</option>
        <option value="monthly" <?= $dateFilter === 'monthly' ? 'selected' : '' ?>>Last Month</option>
        <option value="custom" <?= $dateFilter === 'custom' ? 'selected' : '' ?>>Custom Range</option>
      </select>
      
      <div class="custom-dates <?= $dateFilter === 'custom' ? 'active' : '' ?>">
        <input type="date" name="custom_start" value="<?= $customStart ?>" 
               onchange="window.location.href='expense.php?date_filter=custom&category_filter=<?= $categoryFilter ?>&custom_start='+this.value+'&custom_end=<?= $customEnd ?>'">
        <span>to</span>
        <input type="date" name="custom_end" value="<?= $customEnd ?>" 
               onchange="window.location.href='expense.php?date_filter=custom&category_filter=<?= $categoryFilter ?>&custom_start=<?= $customStart ?>&custom_end='+this.value">
      </div>
      
      <select name="category_filter" onchange="window.location.href='expense.php?date_filter=<?= $dateFilter ?>&category_filter='+this.value+'&custom_start=<?= $customStart ?>&custom_end=<?= $customEnd ?>'">
        <option value="all" <?= $categoryFilter === 'all' ? 'selected' : '' ?>>All Categories</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($category['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      
      <button onclick="document.getElementById('expenseModal').style.display='block'">Add Expense</button>
      <button onclick="document.getElementById('categoryModal').style.display='block'">Add Category</button>
    </div>
        
        <table>
          <thead>
            <tr>
              <th>Amount</th>
              <th>Category</th>
              <th>Date</th>
              <th>Notes</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($expenses as $expense): ?>
              <tr>
                <td><?= htmlspecialchars($expense['amount']) ?></td>
                <td><?= htmlspecialchars($expense['category_name']) ?></td>
                <td><?= htmlspecialchars($expense['date']) ?></td>
                <td><?= htmlspecialchars($expense['notes']) ?></td>
                <td>
                  <button onclick="editExpense(<?= $expense['id'] ?>, '<?= $expense['amount'] ?>', '<?= $expense['category_name'] ?>', '<?= $expense['date'] ?>', '<?= $expense['notes'] ?>')">Edit</button>
                  <button onclick="confirmDelete(<?= $expense['id'] ?>)">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add/Edit Expense Modal -->
  <div id="expenseModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('expenseModal').style.display='none'">&times;</span>
      <h2>Add / Edit Expense</h2>
      <form method="POST">
        <input type="hidden" id="expense_id" name="expense_id">
        
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required>

        <label for="expense_to_id">Category:</label>
        <select id="expense_to_id" name="expense_to_id" required>
          <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
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

  <!-- Add Category Modal -->
  <div id="categoryModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('categoryModal').style.display='none'">&times;</span>
      <h2>Add Category</h2>
      <form method="POST">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" required>
        <button type="submit">Add</button>
      </form>
    </div>
  </div>

</body>
</html>
<?php
include 'db.php';

// Fetch top 5 incomes
$sql_income = "SELECT i.id, i.amount, f.source_name, i.date, i.notes 
               FROM income i 
               JOIN income_from f ON i.income_from_id = f.id 
               ORDER BY i.amount DESC 
               LIMIT 5";
$stmt_income = $conn->prepare($sql_income);
$stmt_income->execute();
$top_income = $stmt_income->fetchAll(PDO::FETCH_ASSOC);

// Fetch top 5 expenses
$sql_expenses = "SELECT e.id, e.amount, t.category_name, e.date, e.notes 
                 FROM expenses e 
                 JOIN expense_to t ON e.expense_to_id = t.id 
                 ORDER BY e.amount DESC 
                 LIMIT 5";
$stmt_expenses = $conn->prepare($sql_expenses);
$stmt_expenses->execute();
$top_expenses = $stmt_expenses->fetchAll(PDO::FETCH_ASSOC);

// Fetch total income for the current month
$sql_current_month_income = "SELECT SUM(amount) AS total_income 
                             FROM income 
                             WHERE MONTH(date) = MONTH(CURRENT_DATE()) 
                             AND YEAR(date) = YEAR(CURRENT_DATE())";
$stmt_current_month_income = $conn->prepare($sql_current_month_income);
$stmt_current_month_income->execute();
$current_month_income = $stmt_current_month_income->fetch(PDO::FETCH_ASSOC);

// Fetch total income for the previous month
$sql_previous_month_income = "SELECT SUM(amount) AS total_income 
                              FROM income 
                              WHERE MONTH(date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
                              AND YEAR(date) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
$stmt_previous_month_income = $conn->prepare($sql_previous_month_income);
$stmt_previous_month_income->execute();
$previous_month_income = $stmt_previous_month_income->fetch(PDO::FETCH_ASSOC);

// Fetch total expenses for the current month
$sql_current_month_expenses = "SELECT SUM(amount) AS total_expenses 
                               FROM expenses 
                               WHERE MONTH(date) = MONTH(CURRENT_DATE()) 
                               AND YEAR(date) = YEAR(CURRENT_DATE())";
$stmt_current_month_expenses = $conn->prepare($sql_current_month_expenses);
$stmt_current_month_expenses->execute();
$current_month_expenses = $stmt_current_month_expenses->fetch(PDO::FETCH_ASSOC);

// Fetch total expenses for the previous month
$sql_previous_month_expenses = "SELECT SUM(amount) AS total_expenses 
                                FROM expenses 
                                WHERE MONTH(date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
                                AND YEAR(date) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
$stmt_previous_month_expenses = $conn->prepare($sql_previous_month_expenses);
$stmt_previous_month_expenses->execute();
$previous_month_expenses = $stmt_previous_month_expenses->fetch(PDO::FETCH_ASSOC);

// Calculate comparison for income
$income_comparison = $current_month_income['total_income'] - $previous_month_income['total_income'];
$income_color = ($income_comparison >= 0) ? 'green' : 'red';
$income_arrow = ($income_comparison >= 0) ? '⬆' : '⬇';

// Calculate comparison for expenses
$expenses_comparison = $current_month_expenses['total_expenses'] - $previous_month_expenses['total_expenses'];
$expenses_color = ($expenses_comparison >= 0) ? 'green' : 'red';
$expenses_arrow = ($expenses_comparison >= 0) ? '⬆' : '⬇';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="logo" style="color:rgb(194, 194, 194);">Khan's Finance</div>
      <ul class="menu">
        <li><a href="index.php" class="active">Dashboard</a></li>
        <li><a href="expense.php">Expense</a></li>
        <li><a href="income.php">Income</a></li>
        <li><a href="analytics.php">Analytics</a></li>
        <li><a href="summary.php">Summary</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <div class="header">
        <div class="header-title">Dashboard</div>
        <div class="user-profile">
          <span>John Doe</span>
          <img src="profile.jpg" alt="Profile">
        </div>
      </div>

      <!-- Content Area -->
      <div class="content">
        <h1>Welcome to the Dashboard</h1>

        <!-- Summary Boxes -->
        <div class="summary-boxes">
          <!-- Total Income Summary -->
          <div class="summary-box" style="background-color: <?= $income_color ?>;">
            <h2>Total Income (This Month)</h2>
            <p>$<?= number_format($current_month_income['total_income'], 2) ?></p>
            <p>Compared to last month: <?= $income_arrow ?> $<?= number_format(abs($income_comparison), 2) ?></p>
          </div>

          <!-- Total Expenses Summary -->
          <div class="summary-box" style="background-color: <?= $expenses_color ?>;">
            <h2>Total Expenses (This Month)</h2>
            <p>$<?= number_format($current_month_expenses['total_expenses'], 2) ?></p>
            <p>Compared to last month: <?= $expenses_arrow ?> $<?= number_format(abs($expenses_comparison), 2) ?></p>
          </div>
        </div>

        <!-- Top 5 Income and Expenses -->
        <div class="dashboard-stats">
          <!-- Top 5 Income -->
          <div class="stat-box">
            <h2>Top 5 Incomes</h2>
            <?php if ($top_income): ?>
              <table>
                <thead>
                  <tr>
                    <th>Amount</th>
                    <th>Source</th>
                    <th>Date</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($top_income as $income): ?>
                    <tr>
                      <td>$<?= number_format($income['amount'], 2) ?></td>
                      <td><?= $income['source_name'] ?></td>
                      <td><?= $income['date'] ?></td>
                      <td><?= $income['notes'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p>No income data available.</p>
            <?php endif; ?>
          </div>

          <!-- Top 5 Expenses -->
          <div class="stat-box">
            <h2>Top 5 Expenses</h2>
            <?php if ($top_expenses): ?>
              <table>
                <thead>
                  <tr>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($top_expenses as $expense): ?>
                    <tr>
                      <td>$<?= number_format($expense['amount'], 2) ?></td>
                      <td><?= $expense['category_name'] ?></td>
                      <td><?= $expense['date'] ?></td>
                      <td><?= $expense['notes'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p>No expense data available.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Buttons to Add Data -->
        <div class="action-buttons">
          <button id="addIncomeBtn">Add Income</button>
          <button id="addExpenseBtn">Add Expense</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Income Modal -->
  <div id="incomeModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Add Income</h2>
      <form action="add_income.php" method="POST">
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required>

        <label for="income_from_id">Source:</label>
        <select id="income_from_id" name="income_from_id" required>
          <?php
          include 'db.php';
          $sql = "SELECT * FROM income_from";
          $stmt = $conn->prepare($sql);
          $stmt->execute();
          $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);
          foreach ($sources as $source): ?>
            <option value="<?= $source['id'] ?>"><?= $source['source_name'] ?></option>
          <?php endforeach; ?>
        </select>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>

        <button type="submit">Add</button>
      </form>
    </div>
  </div>

  <!-- Add Expense Modal -->
  <div id="expenseModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Add Expense</h2>
      <form action="add_expense.php" method="POST">
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required>

        <label for="expense_to_id">Category:</label>
        <select id="expense_to_id" name="expense_to_id" required>
          <?php
          include 'db.php';
          $sql = "SELECT * FROM expense_to";
          $stmt = $conn->prepare($sql);
          $stmt->execute();
          $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
          foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>"><?= $category['category_name'] ?></option>
          <?php endforeach; ?>
        </select>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>

        <button type="submit">Add</button>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
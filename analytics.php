<?php
include 'db.php';

// Fetch top 5 income
$sql_top_income = "SELECT i.amount, f.source_name, i.date 
                   FROM income i 
                   JOIN income_from f ON i.income_from_id = f.id 
                   ORDER BY i.amount DESC 
                   LIMIT 5";
$stmt_top_income = $conn->prepare($sql_top_income);
$stmt_top_income->execute();
$top_income = $stmt_top_income->fetchAll(PDO::FETCH_ASSOC);

// Fetch top 5 expenses
$sql_top_expenses = "SELECT e.amount, t.category_name, e.date 
                     FROM expenses e 
                     JOIN expense_to t ON e.expense_to_id = t.id 
                     ORDER BY e.amount DESC 
                     LIMIT 5";
$stmt_top_expenses = $conn->prepare($sql_top_expenses);
$stmt_top_expenses->execute();
$top_expenses = $stmt_top_expenses->fetchAll(PDO::FETCH_ASSOC);

// Fetch income and expense data for graphs
$sql_income_graph = "SELECT f.source_name, SUM(i.amount) AS total 
                     FROM income i 
                     JOIN income_from f ON i.income_from_id = f.id 
                     GROUP BY f.source_name";
$stmt_income_graph = $conn->prepare($sql_income_graph);
$stmt_income_graph->execute();
$income_graph_data = $stmt_income_graph->fetchAll(PDO::FETCH_ASSOC);

$sql_expense_graph = "SELECT t.category_name, SUM(e.amount) AS total 
                      FROM expenses e 
                      JOIN expense_to t ON e.expense_to_id = t.id 
                      GROUP BY t.category_name";
$stmt_expense_graph = $conn->prepare($sql_expense_graph);
$stmt_expense_graph->execute();
$expense_graph_data = $stmt_expense_graph->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
    <div class="logo" style="color:rgb(194, 194, 194);">Khan's Finance</div>
      <ul class="menu">
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="expense.php">Expense</a></li>
        <li><a href="income.php">Income</a></li>
        <li><a href="analytics.php" class="active">Analytics</a></li>
        <li><a href="summary.php">Summary</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <div class="header">
        <div class="header-title">Analytics</div>
        <div class="user-profile">
          <span>John Doe</span>
          <img src="profile.jpg" alt="Profile">
        </div>
      </div>

      <!-- Content Area -->
      <div class="content">
        <h1>Analytics</h1>

        <!-- Analytics Section -->
        <div class="analytics-section">
          <!-- Left Side: Income -->
          <div class="income-analytics">
            <h2>Income Analytics</h2>

            <!-- Top 5 Income Table -->
            <div class="table-container">
              <h3>Top 5 Income</h3>
              <table>
                <thead>
                  <tr>
                    <th>Amount</th>
                    <th>Source</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($top_income as $income): ?>
                    <tr>
                      <td>$<?= number_format($income['amount'], 2) ?></td>
                      <td><?= $income['source_name'] ?></td>
                      <td><?= $income['date'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Income Graph -->
            <div class="graph-container">
              <h3>Income by Source</h3>
              <canvas id="incomeChart"></canvas>
            </div>
          </div>

          <!-- Right Side: Expenses -->
          <div class="expense-analytics">
            <h2>Expense Analytics</h2>

            <!-- Top 5 Expenses Table -->
            <div class="table-container">
              <h3>Top 5 Expenses</h3>
              <table>
                <thead>
                  <tr>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($top_expenses as $expense): ?>
                    <tr>
                      <td>$<?= number_format($expense['amount'], 2) ?></td>
                      <td><?= $expense['category_name'] ?></td>
                      <td><?= $expense['date'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Expense Graph -->
            <div class="graph-container">
              <h3>Expenses by Category</h3>
              <canvas id="expenseChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Income Chart
    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    const incomeChart = new Chart(incomeCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($income_graph_data, 'source_name')) ?>,
        datasets: [{
          label: 'Income by Source',
          data: <?= json_encode(array_column($income_graph_data, 'total')) ?>,
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Expense Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(expenseCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($expense_graph_data, 'category_name')) ?>,
        datasets: [{
          label: 'Expenses by Category',
          data: <?= json_encode(array_column($expense_graph_data, 'total')) ?>,
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
</body>
</html>
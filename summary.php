<?php
include 'db.php';

// Fetch all income sources and expense categories
$sql_income_sources = "SELECT * FROM income_from";
$stmt_income_sources = $conn->prepare($sql_income_sources);
$stmt_income_sources->execute();
$income_sources = $stmt_income_sources->fetchAll(PDO::FETCH_ASSOC);

$sql_expense_categories = "SELECT * FROM expense_to";
$stmt_expense_categories = $conn->prepare($sql_expense_categories);
$stmt_expense_categories->execute();
$expense_categories = $stmt_expense_categories->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for filtering
$filter_type = $_GET['filter_type'] ?? 'monthly';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$income_source_id = $_GET['income_source_id'] ?? '';
$expense_category_id = $_GET['expense_category_id'] ?? '';

// Build SQL query based on filters
$sql_income = "SELECT i.amount, f.source_name, i.date, i.notes 
               FROM income i 
               JOIN income_from f ON i.income_from_id = f.id 
               WHERE 1=1";

$sql_expenses = "SELECT e.amount, t.category_name, e.date, e.notes 
                 FROM expenses e 
                 JOIN expense_to t ON e.expense_to_id = t.id 
                 WHERE 1=1";

// Apply date filters
if ($filter_type === 'weekly') {
  $sql_income .= " AND YEARWEEK(i.date, 1) = YEARWEEK(CURDATE(), 1)";
  $sql_expenses .= " AND YEARWEEK(e.date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter_type === 'monthly') {
  $sql_income .= " AND MONTH(i.date) = MONTH(CURDATE()) AND YEAR(i.date) = YEAR(CURDATE())";
  $sql_expenses .= " AND MONTH(e.date) = MONTH(CURDATE()) AND YEAR(e.date) = YEAR(CURDATE())";
} elseif ($filter_type === 'custom' && $start_date && $end_date) {
  $sql_income .= " AND i.date BETWEEN :start_date AND :end_date";
  $sql_expenses .= " AND e.date BETWEEN :start_date AND :end_date";
}

// Apply income source filter
if ($income_source_id) {
  $sql_income .= " AND i.income_from_id = :income_source_id";
}

// Apply expense category filter
if ($expense_category_id) {
  $sql_expenses .= " AND e.expense_to_id = :expense_category_id";
}

// Prepare and execute income query
$stmt_income = $conn->prepare($sql_income);
if ($filter_type === 'custom' && $start_date && $end_date) {
  $stmt_income->bindParam(':start_date', $start_date);
  $stmt_income->bindParam(':end_date', $end_date);
}
if ($income_source_id) {
  $stmt_income->bindParam(':income_source_id', $income_source_id);
}
$stmt_income->execute();
$filtered_income = $stmt_income->fetchAll(PDO::FETCH_ASSOC);

// Prepare and execute expenses query
$stmt_expenses = $conn->prepare($sql_expenses);
if ($filter_type === 'custom' && $start_date && $end_date) {
  $stmt_expenses->bindParam(':start_date', $start_date);
  $stmt_expenses->bindParam(':end_date', $end_date);
}
if ($expense_category_id) {
  $stmt_expenses->bindParam(':expense_category_id', $expense_category_id);
}
$stmt_expenses->execute();
$filtered_expenses = $stmt_expenses->fetchAll(PDO::FETCH_ASSOC);

// Prepare income data for PDF export
$income_data = array_map(function($income) {
  return [
    '$' . number_format($income['amount'], 2),
    $income['source_name'],
    $income['date'],
    $income['notes']
  ];
}, $filtered_income);

// Prepare expense data for PDF export
$expense_data = array_map(function($expense) {
  return [
    '$' . number_format($expense['amount'], 2),
    $expense['category_name'],
    $expense['date'],
    $expense['notes']
  ];
}, $filtered_expenses);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Summary</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="logo">My App</div>
      <ul class="menu">
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="expense.php">Expense</a></li>
        <li><a href="income.php">Income</a></li>
        <li><a href="analytics.php">Analytics</a></li>
        <li><a href="summary.php" class="active">Summary</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <div class="header">
        <div class="header-title">Summary</div>
        <div class="user-profile">
          <span>John Doe</span>
          <img src="profile.jpg" alt="Profile">
        </div>
      </div>

      <!-- Content Area -->
      <div class="content">
        <h1>Summary</h1>

        <!-- Filters -->
        <div class="filters">
          <form method="GET" action="summary.php">
            <label for="filter_type">Filter By:</label>
            <select id="filter_type" name="filter_type">
              <option value="monthly" <?= $filter_type === 'monthly' ? 'selected' : '' ?>>Monthly</option>
              <option value="weekly" <?= $filter_type === 'weekly' ? 'selected' : '' ?>>Weekly</option>
              <option value="custom" <?= $filter_type === 'custom' ? 'selected' : '' ?>>Custom Date Range</option>
            </select>

            <!-- Custom Date Range -->
            <div id="custom-date-range" style="display: <?= $filter_type === 'custom' ? 'block' : 'none' ?>;">
              <label for="start_date">Start Date:</label>
              <input type="date" id="start_date" name="start_date" value="<?= $start_date ?>">

              <label for="end_date">End Date:</label>
              <input type="date" id="end_date" name="end_date" value="<?= $end_date ?>">
            </div>

            <!-- Income Source Filter -->
            <label for="income_source_id">Income Source:</label>
            <select id="income_source_id" name="income_source_id">
              <option value="">All</option>
              <?php foreach ($income_sources as $source): ?>
                <option value="<?= $source['id'] ?>" <?= $income_source_id == $source['id'] ? 'selected' : '' ?>>
                  <?= $source['source_name'] ?>
                </option>
              <?php endforeach; ?>
            </select>

            <!-- Expense Category Filter -->
            <label for="expense_category_id">Expense Category:</label>
            <select id="expense_category_id" name="expense_category_id">
              <option value="">All</option>
              <?php foreach ($expense_categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= $expense_category_id == $category['id'] ? 'selected' : '' ?>>
                  <?= $category['category_name'] ?>
                </option>
              <?php endforeach; ?>
            </select>

            <button type="submit">Apply Filters</button>
          </form>
        </div>

        <!-- Filtered Data -->
        <div class="filtered-data">
          <!-- Income Table -->
          <div class="income-table">
            <h2>Income</h2>
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
                <?php foreach ($filtered_income as $income): ?>
                  <tr>
                    <td>$<?= number_format($income['amount'], 2) ?></td>
                    <td><?= $income['source_name'] ?></td>
                    <td><?= $income['date'] ?></td>
                    <td><?= $income['notes'] ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Expenses Table -->
          <div class="expenses-table">
            <h2>Expenses</h2>
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
                <?php foreach ($filtered_expenses as $expense): ?>
                  <tr>
                    <td>$<?= number_format($expense['amount'], 2) ?></td>
                    <td><?= $expense['category_name'] ?></td>
                    <td><?= $expense['date'] ?></td>
                    <td><?= $expense['notes'] ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Export to PDF Button -->
        <button id="exportPdf">Export to PDF</button>
      </div>
    </div>
  </div>

  <script>
    
    // Show/hide custom date range based on filter type
    const filterType = document.getElementById('filter_type');
    const customDateRange = document.getElementById('custom-date-range');

    filterType.addEventListener('change', () => {
      if (filterType.value === 'custom') {
        customDateRange.style.display = 'block';
      } else {
        customDateRange.style.display = 'none';
      }
    });

    document.getElementById('exportPdf').addEventListener('click', () => {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  // Check if income data exists
  const incomeData = <?= json_encode($income_data) ?>;
  if (incomeData.length > 0) {
    doc.text("Income Summary", 10, 10);
    doc.autoTable({
      head: [['Amount', 'Source', 'Date', 'Notes']],
      body: incomeData,
      startY: 20,
    });
  } else {
    doc.text("No Income Data Found", 10, 10);
  }

  // Check if expense data exists
  const expenseData = <?= json_encode($expense_data) ?>;
  if (expenseData.length > 0) {
    doc.text("Expenses Summary", 10, doc.autoTable.previous ? doc.autoTable.previous.finalY + 10 : 20);
    doc.autoTable({
      head: [['Amount', 'Category', 'Date', 'Notes']],
      body: expenseData,
      startY: doc.autoTable.previous ? doc.autoTable.previous.finalY + 20 : 30,
    });
  } else {
    doc.text("No Expense Data Found", 10, doc.autoTable.previous ? doc.autoTable.previous.finalY + 10 : 20);
  }

  // Save the PDF
  doc.save('summary.pdf');
});

  </script>
</body>
</html>
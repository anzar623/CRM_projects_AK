// Modal functionality for both Income and Expense pages

// Get modal elements
const incomeModal = document.getElementById('incomeModal');
const expenseModal = document.getElementById('expenseModal');

// Get buttons to open modals
const addIncomeBtn = document.getElementById('addIncomeBtn');
const addExpenseBtn = document.getElementById('addExpenseBtn');

// Get close buttons for modals
const closeBtns = document.querySelectorAll('.close');

// Open Income Modal
if (addIncomeBtn) {
  addIncomeBtn.onclick = () => {
    incomeModal.style.display = 'block';
  };
}

// Open Expense Modal
if (addExpenseBtn) {
  addExpenseBtn.onclick = () => {
    expenseModal.style.display = 'block';
  };
}

// Close modals when close button is clicked
closeBtns.forEach(btn => {
  btn.onclick = () => {
    incomeModal.style.display = 'none';
    expenseModal.style.display = 'none';
  };
});

// Close modals when clicking outside the modal
window.onclick = (event) => {
  if (event.target === incomeModal) {
    incomeModal.style.display = 'none';
  }
  if (event.target === expenseModal) {
    expenseModal.style.display = 'none';
  }
};

// Get summary boxes
const incomeSummaryBox = document.querySelector('.summary-box:nth-child(1)');
const expenseSummaryBox = document.querySelector('.summary-box:nth-child(2)');

// Example data (replace with actual data from PHP)
const incomeComparison = 500; // Example: Income increased by $500
const expenseComparison = -200; // Example: Expenses decreased by $200

// Apply styles based on comparison
if (incomeComparison >= 0) {
  incomeSummaryBox.classList.add('green');
} else {
  incomeSummaryBox.classList.add('red');
}

if (expenseComparison >= 0) {
  expenseSummaryBox.classList.add('green');
} else {
  expenseSummaryBox.classList.add('red');
}


function toggleCustomDates() {
  const customDates = document.querySelector('.custom-dates');
  if (document.getElementById('date_filter').value === 'custom') {
    customDates.classList.add('active');
  } else {
    customDates.classList.remove('active');
  }
}



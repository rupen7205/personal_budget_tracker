<?php
session_start();

$startDate = '00-00-00';
$endDate = '00-00-00';

if(isset($_SESSION['loggedUserId'])) {
    
    require_once 'database.php';
    
    // Handle delete requests
    if(isset($_GET['delete_income'])) {
        $incomeId = $_GET['delete_income'];
        $deleteQuery = $db->prepare("DELETE FROM incomes WHERE income_id = :incomeId AND user_id = :userId");
        $deleteQuery->execute([':incomeId' => $incomeId, ':userId' => $_SESSION['loggedUserId']]);
        $_SESSION['successMessage'] = "Income entry deleted successfully.";
        
        // Redirect to avoid resubmission on refresh
        header("Location: balance.php?userStartDate=" . $_GET['userStartDate'] . "&userEndDate=" . $_GET['userEndDate']);
        exit();
    }
    
    if(isset($_GET['delete_expense'])) {
        $expenseId = $_GET['delete_expense'];
        $deleteQuery = $db->prepare("DELETE FROM expenses WHERE expense_id = :expenseId AND user_id = :userId");
        $deleteQuery->execute([':expenseId' => $expenseId, ':userId' => $_SESSION['loggedUserId']]);
        $_SESSION['successMessage'] = "Expense entry deleted successfully.";
        
        // Redirect to avoid resubmission on refresh
        header("Location: balance.php?userStartDate=" . $_GET['userStartDate'] . "&userEndDate=" . $_GET['userEndDate']);
        exit();
    }
    
    // Handle update requests
    if(isset($_POST['update_income'])) {
        $incomeId = $_POST['income_id'];
        $incomeAmount = number_format($_POST['income_amount'], 2, '.', '');
        $incomeDate = $_POST['income_date'];
        $incomeComment = $_POST['income_comment'];
        
        $updateQuery = $db->prepare("UPDATE incomes SET income_amount = :amount, income_date = :date, income_comment = :comment WHERE income_id = :id AND user_id = :userId");
        $updateQuery->execute([
            ':amount' => $incomeAmount,
            ':date' => $incomeDate,
            ':comment' => $incomeComment,
            ':id' => $incomeId,
            ':userId' => $_SESSION['loggedUserId']
        ]);
        
        $_SESSION['successMessage'] = "Income entry updated successfully.";
        header("Location: balance.php?userStartDate=" . $_GET['userStartDate'] . "&userEndDate=" . $_GET['userEndDate']);
        exit();
    }
    
    if(isset($_POST['update_expense'])) {
        $expenseId = $_POST['expense_id'];
        $expenseAmount = number_format($_POST['expense_amount'], 2, '.', '');
        $expenseDate = $_POST['expense_date'];
        $expenseComment = $_POST['expense_comment'];
        
        $updateQuery = $db->prepare("UPDATE expenses SET expense_amount = :amount, expense_date = :date, expense_comment = :comment WHERE expense_id = :id AND user_id = :userId");
        $updateQuery->execute([
            ':amount' => $expenseAmount,
            ':date' => $expenseDate,
            ':comment' => $expenseComment,
            ':id' => $expenseId,
            ':userId' => $_SESSION['loggedUserId']
        ]);
        
        $_SESSION['successMessage'] = "Expense entry updated successfully.";
        header("Location: balance.php?userStartDate=" . $_GET['userStartDate'] . "&userEndDate=" . $_GET['userEndDate']);
        exit();
    }
    
    if(isset($_GET['userStartDate'])) {
        
        if($_GET['userStartDate'] > $_GET['userEndDate']) {
            
            $startDate = $_GET['userEndDate'];
            $endDate = $_GET['userStartDate'];
        } else {
            
            $startDate = $_GET['userStartDate'];
            $endDate = $_GET['userEndDate'];
        }
        
        $expensesQuery = $db -> prepare(
        "SELECT e.category_id, ec.expense_category, SUM(e.expense_amount) AS expense_amount
        FROM expenses e NATURAL JOIN expense_categories ec
        WHERE e.user_id=:loggedUserId AND e.expense_date BETWEEN :startDate AND :endDate
        GROUP BY e.category_id
        ORDER BY expense_amount DESC");
        $expensesQuery -> execute([':loggedUserId'=> $_SESSION['loggedUserId'], ':startDate'=> $startDate, ':endDate'=> $endDate]);
        
        $expensesOfLoggedUser = $expensesQuery -> fetchAll();
        
        $incomesQuery = $db -> prepare(
        "SELECT i.category_id, ic.income_category, SUM(i.income_amount) AS income_amount
        FROM incomes i NATURAL JOIN income_categories ic
        WHERE i.user_id=:loggedUserId AND i.income_date BETWEEN :startDate AND :endDate
        GROUP BY i.category_id
        ORDER BY income_amount DESC");
        $incomesQuery -> execute([':loggedUserId'=> $_SESSION['loggedUserId'], ':startDate'=> $startDate, ':endDate'=> $endDate]);
        
        $incomesOfLoggedUser = $incomesQuery -> fetchAll();
        
        // Get detailed income transactions for the table - FIXED: Include category_id
        $incomeDetailsQuery = $db -> prepare(
        "SELECT i.income_id, i.income_amount, i.income_date, i.category_id, ic.income_category, i.income_comment
        FROM incomes i 
        JOIN income_categories ic ON i.category_id = ic.category_id
        WHERE i.user_id=:loggedUserId AND i.income_date BETWEEN :startDate AND :endDate
        ORDER BY i.income_date ASC, ic.income_category");
        $incomeDetailsQuery -> execute([':loggedUserId'=> $_SESSION['loggedUserId'], ':startDate'=> $startDate, ':endDate'=> $endDate]);
        
        $incomeDetails = $incomeDetailsQuery -> fetchAll();
        
        // Get detailed expense transactions for the table - FIXED: Include category_id
        $expenseDetailsQuery = $db -> prepare(
        "SELECT e.expense_id, e.expense_amount, e.expense_date, e.category_id, ec.expense_category, pm.payment_method, e.expense_comment
        FROM expenses e 
        JOIN expense_categories ec ON e.category_id = ec.category_id
        JOIN payment_methods pm ON e.payment_method_id = pm.payment_method_id
        WHERE e.user_id=:loggedUserId AND e.expense_date BETWEEN :startDate AND :endDate
        ORDER BY e.expense_date ASC, ec.expense_category");
        $expenseDetailsQuery -> execute([':loggedUserId'=> $_SESSION['loggedUserId'], ':startDate'=> $startDate, ':endDate'=> $endDate]);
        
        $expenseDetails = $expenseDetailsQuery -> fetchAll();
        
        echo "<script>
                var incomes = ".json_encode($incomesOfLoggedUser).";
                var expenses = ".json_encode($expensesOfLoggedUser)."
            </script>";
        
    } else {
        
        header ("Location: menu.php");
        exit();
    }
} else {

    header ("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>

<html lang="pl">

<head>

    <meta charset="utf-8">
    <title>MyBudget - Your Personal Finance Manager</title>
    <meta name="description" content="Track your income and expenses - avoid overspending!">
    <meta name="keywords" content="expense manager, budget planner, expense tracker, budgeting app, money manager, money management, personal finance management software, finance manager, saving planner">
    <meta name="author" content="Magdalena Słomiany">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <meta http-equiv="X-Ua-Compatible" content="IE=edge">
    
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/fontello.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Paaji+2:wght@400;500;700&family=Fredoka+One&family=Roboto:wght@400;700;900&family=Varela+Round&display=swap" rel="stylesheet">
    
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        .edit-form {
            display: none;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
        }
        .edit-btn {
            margin-right: 5px;
        }
    </style>

</head>

<body onload="drawChart(incomes, expenses)" onresize="drawChart(incomes, expenses)">
    
    <header>
    
        <h1 class="mt-3 mb-1" id="title">
            <a id="homeButton" href="index.php" role="button"><span id="logo">myBudget</span>.com</a>
        </h1>
        
        <p id="subtitle">Your Personal Finance Manager</p>
        
    </header>
    
    <main>
        
        <section class="container-fluid square my-4 py-2">
            
            <nav class="navbar navbar-dark navbar-expand-lg">
            
                <button class="navbar-toggler bg-primary" type="button" data-toggle="collapse" data-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Navigation toggler">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="mainMenu">
            
                    <ul class="navbar-nav mx-auto">
                    
                        <li class="col-lg-2 nav-item">
                            <a class="nav-link" href="menu.php"><i class="icon-home"></i> Home</a>
                        </li>
                        
                        <li class="col-lg-2 nav-item">
                            <a class="nav-link" href="income.php"><i class="icon-money-1"></i> Add Income</a>
                        </li>
                        
                        <li class="col-lg-2 nav-item">
                            <a class="nav-link" href="expense.php"><i class="icon-dollar"></i> Add Expense</a>
                        </li>
                        
                        <li class="col-lg-2 nav-item dropdown disabled">
                            <a class="nav-link" href="#" role="button"><i class="icon-chart-pie"></i> View Balance</a>
                            <div class="dropdown-menu bg-transparent border-0 m-0 p-0">
                            
                                <?php
                                $userStartDate = date('Y-m-01');
                                $userEndDate = date('Y-m-t');
                                
                                echo '<a class="dropdown-item" href="balance.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Current Month</a>';
                                ?>
                                <?php
                                    $userStartDate = date('Y-m-01', strtotime("last month"));
                                    $userEndDate = date('Y-m-t', strtotime("last month"));
                                    
                                    echo '<a class="dropdown-item" href="balance.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Last Month</a>';
                                ?>
                                <?php
                                    $userStartDate = date('Y-01-01');
                                    $userEndDate = date('Y-12-31');
                                    
                                    echo '<a class="dropdown-item" href="balance.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Current Year</a>';
                                ?>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#dateModal">Custom</a>
                            
                            </div>
                        </li>
                        
                        <li class="col-lg-2 nav-item dropdown">
                            <a class="nav-link" href="#" role="button"><i class="icon-cog-alt"></i> Settings</a>
                            <div class="dropdown-menu bg-transparent border-0 m-0 p-0">
                            
                                <h6 class="dropdown-header">Profile settings</h6>
                                <a class="dropdown-item" href="#">Name</a>
                                <a class="dropdown-item" href="#">Password</a>
                                <a class="dropdown-item" href="#">E-mail Adress</a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Category settings</h6>
                                <a class="dropdown-item" href="#">Income</a>
                                <a class="dropdown-item" href="#">Expense</a>
                                <a class="dropdown-item" href="#">Payment Methods</a>
                            
                            </div>
                        </li>
                        
                        <li class="col-lg-2 nav-item">
                            <a class="nav-link" href="logout.php"><i class="icon-logout"></i> Sign out</a>
                        </li>
                        
                    </ul>
                    
                </div>
            
            </nav>
        
        </section>
        
        <section class="container-fluid square mb-4 py-3">
            
            <div class="row py-3">
            
                <div class="col-12 timePeriod pt-3 pb-2">
                    
                    <?php
                        echo "<h5>TIME PERIOD:&emsp;<span class='text-nowrap'>".$startDate."</span>  -  <span class='text-nowrap'>".$endDate."</span></h5>";
                    ?>
                    
                    <div class="btn-group m-2 mr-4 dateButton">
                        <button type="button" class="btn"><i class="icon-calendar"></i> Choose Date</button>
                        <button type="button" class="btn dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">Expand the list</span>
                        </button>
                        <div class="dropdown-menu bg-transparent border-0 m-0 p-0 dropdown-menu-right">
                        
                            <?php
                                $userStartDate = date('Y-m-01');
                                $userEndDate = date('Y-m-t');
                                
                                echo '<a class="dropdown-item" href="balance.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Current Month</a>';
                            ?>
                            <?php
                                $userStartDate = date('Y-m-01', strtotime("last month"));
                                $userEndDate = date('Y-m-t', strtotime("last month"));
                                
                                echo '<a class="dropdown-item" href="balance.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Last Month</a>';
                            ?>
                            <?php
                                $userStartDate = date('Y-01-01');
                                $userEndDate = date('Y-12-31');
                                
                                echo '<a class="dropdown-item" href="balance.php?userStartDate='.$userStartDate.'&userEndDate='.$userEndDate.'">Current Year</a>';
                            ?>
                            <a class="dropdown-item" data-toggle="modal" data-target="#dateModal">Custom</a>
                        </div>
                    </div>
                    
                </div>
                
            </div>
            
            <?php
                if(isset($_SESSION['successMessage'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                    echo $_SESSION['successMessage'];
                    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    echo '<span aria-hidden="true">&times;</span>';
                    echo '</button>';
                    echo '</div>';
                    unset($_SESSION['successMessage']);
                }
            ?>
            
            <div class="row justify-content-center" id="tables">
                <div class="table-responsive col-md-6" id="tableIncomes">
                    <table class="table-sm col-lg-10 mx-auto my-2">
                        <tbody>
                            <thead class="thead-dark">
                                <caption>Incomes</caption>
                                <tr>
                                    <th class="category">Category</th>
                                    <th class="amount">Amount</th>
                                    <th class="action">Actions</th>
                                </tr>
                            </thead>
                            
                            <?php
                                $totalIncomes = 0;
                                
                                foreach ($incomesOfLoggedUser as $incomeCategory) {
                                    
                                    echo "<tr class=\"summary\"><td class=\"category\">{$incomeCategory['income_category']}</td><td class=\"sum\">{$incomeCategory['income_amount']} PLN</td><td></td></tr>";
                                    
                                    $totalIncomes += $incomeCategory['income_amount'];
                                    
                                    foreach ($incomeDetails as $income) {
                                        if ($income['category_id'] == $incomeCategory['category_id']) {
                                            echo "<tr>";
                                            echo "<td class=\"date\">{$income['income_date']}</td>";
                                            echo "<td class=\"amount\">{$income['income_amount']} PLN</td>";
                                            echo "<td class=\"action\">";
                                            echo "<button class=\"btn btn-sm btn-primary mr-1 edit-btn\" onclick=\"toggleEditForm('income-form-{$income['income_id']}')\"><i class=\"icon-pencil\"></i></button>";
                                            echo "<a href=\"balance.php?userStartDate={$startDate}&userEndDate={$endDate}&delete_income={$income['income_id']}\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Are you sure you want to delete this income entry?')\"><i class=\"icon-trash\"></i></a>";
                                            echo "</td>";
                                            echo "</tr>";
                                            
                                            // Edit form for this income entry
                                            echo "<tr id=\"income-form-{$income['income_id']}\" class=\"edit-form\">";
                                            echo "<td colspan=\"3\">";
                                            echo "<form method=\"post\" action=\"balance.php?userStartDate={$startDate}&userEndDate={$endDate}\">";
                                            echo "<div class=\"form-row\">";
                                            echo "<div class=\"col\">";
                                            echo "<label>Amount:</label>";
                                            echo "<input type=\"number\" step=\"0.01\" class=\"form-control\" name=\"income_amount\" value=\"{$income['income_amount']}\" required>";
                                            echo "</div>";
                                            echo "<div class=\"col\">";
                                            echo "<label>Date:</label>";
                                            echo "<input type=\"date\" class=\"form-control\" name=\"income_date\" value=\"{$income['income_date']}\" required>";
                                            echo "</div>";
                                            echo "</div>";
                                            echo "<div class=\"form-group mt-2\">";
                                            echo "<label>Comment:</label>";
                                            echo "<textarea class=\"form-control\" name=\"income_comment\">{$income['income_comment']}</textarea>";
                                            echo "</div>";
                                            echo "<input type=\"hidden\" name=\"income_id\" value=\"{$income['income_id']}\">";
                                            echo "<button type=\"submit\" name=\"update_income\" class=\"btn btn-success btn-sm mr-2\">Save</button>";
                                            echo "<button type=\"button\" class=\"btn btn-secondary btn-sm\" onclick=\"toggleEditForm('income-form-{$income['income_id']}')\">Cancel</button>";
                                            echo "</form>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    }
                                }
                                
                                echo "<tr class=\"summary\"><td class=\"total\">TOTAL</td><td class=\"sum\">{$totalIncomes} PLN</td><td></td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="table-responsive col-md-6" id="tableExpenses">
                
                    <table class="table-sm col-lg-10 mx-auto my-2">
                        <tbody>
                            <thead class="thead-dark">
                                <caption>Expenses</caption>
                                <tr>
                                    <th class="category">Category</th>
                                    <th class="amount">Amount</th>
                                    <th class="action">Actions</th>
                                </tr>
                            </thead>
                            
                            <?php
                                $totalExpenses = 0;
                                
                                foreach ($expensesOfLoggedUser as $expenseCategory) {
                                    
                                    echo "<tr class=\"summary\"><td class=\"category\">{$expenseCategory['expense_category']}</td><td class=\"sum\">{$expenseCategory['expense_amount']} PLN</td><td></td></tr>";
                                    
                                    $totalExpenses += $expenseCategory['expense_amount'];
                                    
                                    foreach ($expenseDetails as $expense) {
                                        if ($expense['category_id'] == $expenseCategory['category_id']) {
                                            echo "<tr>";
                                            echo "<td class=\"date\">{$expense['expense_date']}</td>";
                                            echo "<td class=\"amount\">{$expense['expense_amount']} PLN</td>";
                                            echo "<td class=\"action\">";
                                            echo "<button class=\"btn btn-sm btn-primary mr-1 edit-btn\" onclick=\"toggleEditForm('expense-form-{$expense['expense_id']}')\"><i class=\"icon-pencil\"></i></button>";
                                            echo "<a href=\"balance.php?userStartDate={$startDate}&userEndDate={$endDate}&delete_expense={$expense['expense_id']}\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Are you sure you want to delete this expense entry?')\"><i class=\"icon-trash\"></i></a>";
                                            echo "</td>";
                                            echo "</tr>";
                                            
                                            // Edit form for this expense entry
                                            echo "<tr id=\"expense-form-{$expense['expense_id']}\" class=\"edit-form\">";
                                            echo "<td colspan=\"3\">";
                                            echo "<form method=\"post\" action=\"balance.php?userStartDate={$startDate}&userEndDate={$endDate}\">";
                                            echo "<div class=\"form-row\">";
                                            echo "<div class=\"col\">";
                                            echo "<label>Amount:</label>";
                                            echo "<input type=\"number\" step=\"0.01\" class=\"form-control\" name=\"expense_amount\" value=\"{$expense['expense_amount']}\" required>";
                                            echo "</div>";
                                            echo "<div class=\"col\">";
                                            echo "<label>Date:</label>";
                                            echo "<input type=\"date\" class=\"form-control\" name=\"expense_date\" value=\"{$expense['expense_date']}\" required>";
                                            echo "</div>";
                                            echo "</div>";
                                            echo "<div class=\"form-group mt-2\">";
                                            echo "<label>Comment:</label>";
                                            echo "<textarea class=\"form-control\" name=\"expense_comment\">{$expense['expense_comment']}</textarea>";
                                            echo "</div>";
                                            echo "<input type=\"hidden\" name=\"expense_id\" value=\"{$expense['expense_id']}\">";
                                            echo "<button type=\"submit\" name=\"update_expense\" class=\"btn btn-success btn-sm mr-2\">Save</button>";
                                            echo "<button type=\"button\" class=\"btn btn-secondary btn-sm\" onclick=\"toggleEditForm('expense-form-{$expense['expense_id']}')\">Cancel</button>";
                                            echo "</form>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    }
                                }
                                
                                echo "<tr class=\"summary\"><td class=\"total\">TOTAL</td><td class=\"sum\">{$totalExpenses} PLN</td><td></td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="row col-sm-6 col-lg-4 justify-content-center mt-5 mb-2 mx-auto box">
                
                <?php
                    $balance = $totalIncomes - $totalExpenses;
                    $balanceClass = $balance > 0 ? 'text-success' : ($balance < 0 ? 'text-danger' : '');
                    echo '<div id="balance" class="'.$balanceClass.'">BALANCE:&emsp;'.$balance.' PLN</div>';
                ?>
                
            </div>
            

            <?php
                if($balance > 0) {
                    
                    echo '<div class="ml-3 text-success text-center" id="result">Great! You Manage Your Finances Very Well!</div>';
                }
                if ($balance < 0){
                    
                    echo '<div class="ml-3 text-danger text-center" id="result">Watch Out! You Are Getting Into Debt!!</div>';
                }
            ?>
            
            <?php
                if(!empty($incomesOfLoggedUser)) {
                    
                    echo '<div class="col-sm-8 col-lg-6 mt-4 mb-2 pt-2 pb-4 mx-auto box"><div id="piechart1"></div></div>';
                }
            
                if(!empty($expensesOfLoggedUser)) {
                    
                    echo '<div class="col-sm-8 col-lg-6 my-3 pt-2 pb-4 mx-auto box"><div id="piechart2"></div></div>';
                }
            ?>
        </section>
        
        <div class="modal fade" role='dialog' id="dateModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h3 class="modal-title">Selecting time period</h3>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <form class="col py-3 mx-auto" action="balance.php" method="get">
                    
                        <div class="modal-body">
                        
                            <h5>Enter a start date and an end date of period that you want to review</h5>
                                
                            <div class="row justify-content-around py-2">
                                
                                <div class="form-group my-2">
                                    <label for="startDate">Enter start date</label>
                                    <input class="form-control  userInput labeledInput" type="date" name="userStartDate" required>
                                </div>
                                    
                                <div class="form-group my-2">
                                    <label for="endDate">Enter end date</label>
                                    <input class="form-control userInput labeledInput" type="date" name="userEndDate" required>
                                </div>
                                    
                            </div>
                                
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                            
                    </form>

                </div>
            </div>
        </div>
        
    </main>
    
    <footer>
    
        <div class="col my-2 footer">
            personal_budget_tracker
        </div>
        
    </footer>
    
    <script src="js/budget.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-3.4.1.min.js"></script>
    
    <script>
        function toggleEditForm(formId) {
            var form = document.getElementById(formId);
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'table-row';
            } else {
                form.style.display = 'none';
            }
        }
        
        function drawChart(incomes, expenses) {
            // Your existing chart drawing code
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(function() {
                drawIncomeChart(incomes);
                drawExpenseChart(expenses);
            });
            
            function drawIncomeChart(incomes) {
                if (incomes.length > 0) {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Category');
                    data.addColumn('number', 'Amount');
                    
                    incomes.forEach(function(income) {
                        data.addRow([income.income_category, parseFloat(income.income_amount)]);
                    });
                    
                    var options = {
                        title: 'Income by Category',
                        is3D: true,
                        pieSliceText: 'value',
                        legend: {position: 'labeled'}
                    };
                    
                    var chart = new google.visualization.PieChart(document.getElementById('piechart1'));
                    chart.draw(data, options);
                }
            }
            
            function drawExpenseChart(expenses) {
                if (expenses.length > 0) {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Category');
                    data.addColumn('number', 'Amount');
                    
                    expenses.forEach(function(expense) {
                        data.addRow([expense.expense_category, parseFloat(expense.expense_amount)]);
                    });
                    
                    var options = {
                        title: 'Expenses by Category',
                        is3D: true,
                        pieSliceText: 'value',
                        legend: {position: 'labeled'}
                    };
                    
                    var chart = new google.visualization.PieChart(document.getElementById('piechart2'));
                    chart.draw(data, options);
                }
            }
        }
    </script>
    
</body>

</html>
<?php
	session_start();

	if(isset($_SESSION['loggedUserId'])) {
		
		require_once 'database.php';
		
		$incomeCategoryQuery = $db -> prepare(
		"SELECT ic.income_category
		FROM income_categories ic NATURAL JOIN user_income_category uic
		WHERE uic.user_id = :loggedUserId");
		$incomeCategoryQuery -> execute([':loggedUserId'=> $_SESSION['loggedUserId']]);
		
		$incomeCategoriesOfLoggedUser = $incomeCategoryQuery -> fetchAll();
		
		$_SESSION['incomeAdded'] = false;
		
		if(isset($_POST['incomeAmount'])) {
			
			if(!empty($_POST['incomeAmount'])) {
					
				$positiveValidation = true;
				
				$incomeAmount = number_format($_POST['incomeAmount'], 2, '.', '');
				$amount = explode('.', $incomeAmount);
					
				if(!is_numeric($incomeAmount) || strlen($incomeAmount) > 9 || $incomeAmount < 0 || !(isset($amount[1]) && strlen($amount[1]) == 2)) {
						
					$_SESSION['incomeAmountError'] = "Enter valid positive amount - maximum 6 integer digits and 2 decimal places.";
					$positiveValidation = false;
				}
				
				$incomeComment = $_POST['incomeComment'];
				
				if(!empty($incomeComment) && !preg_match('/^[A-ZĄĘÓŁŚŻŹĆŃa-ząęółśżźćń 0-9]+$/', $incomeComment)) {
					
					$_SESSION['commentError'] = "Comment can contain up to 100 characters - only letters and numbers allowed.";
					$positiveValidation = false;
				}
				
				// Handle custom category input
				$incomeCategory = $_POST['incomeCategory'];
				$customCategory = trim($_POST['customCategory'] ?? '');
				
				if ($incomeCategory === 'Other' && !empty($customCategory)) {
					// Validate custom category
					if(!preg_match('/^[A-ZĄĘÓŁŚŻŹĆŃa-ząęółśżźćń 0-9\s]+$/', $customCategory) || strlen($customCategory) > 50) {
						$_SESSION['categoryError'] = "Custom category can contain up to 50 characters - only letters, numbers and spaces allowed.";
						$positiveValidation = false;
					} else {
						$incomeCategory = $customCategory;
					}
				} elseif ($incomeCategory === 'Other' && empty($customCategory)) {
					$_SESSION['categoryError'] = "Please enter a custom category name.";
					$positiveValidation = false;
				}
				
				// NEW VALIDATION: Check if the income date belongs to previous month
				$incomeDate = $_POST['incomeDate'];
				$selectedMonth = date('Y-m', strtotime($incomeDate));
				$currentMonth = date('Y-m');
				$previousMonth = date('Y-m', strtotime('first day of previous month'));
				
				// Prevent adding income from previous month
				if ($selectedMonth == $previousMonth) {
					$positiveValidation = false;
					$_SESSION['dateError'] = "Cannot add income for previous month. Please select a date from the current month or future.";
				}
				
				// Also prevent adding income from months older than previous month
				if (strtotime($selectedMonth) < strtotime($previousMonth)) {
					$positiveValidation = false;
					$_SESSION['dateError'] = "Cannot add income for past months. Please select a date from the current month or future.";
				}
				
				$_SESSION['formIncomeAmount'] = $incomeAmount;
				$_SESSION['formIncomeDate'] = $incomeDate;
				$_SESSION['formIncomeCategory'] = $_POST['incomeCategory'];
				$_SESSION['formCustomCategory'] = $customCategory;
				$_SESSION['formIncomeComment'] = $incomeComment;
			
				if($positiveValidation == true) {
					// Check if custom category exists or needs to be created
					if (!in_array($incomeCategory, array_column($incomeCategoriesOfLoggedUser, 'income_category'))) {
						// Add new category to income_categories
						$addCategoryQuery = $db->prepare(
							"INSERT INTO income_categories (income_category) VALUES (:categoryName)"
						);
						$addCategoryQuery->execute([':categoryName' => $incomeCategory]);
						
						// Get the new category ID
						$newCategoryId = $db->lastInsertId();
						
						// Add user association with the new category
						$assignCategoryQuery = $db->prepare(
							"INSERT INTO user_income_category (user_id, category_id) VALUES (:userId, :categoryId)"
						);
						$assignCategoryQuery->execute([':userId' => $_SESSION['loggedUserId'], ':categoryId' => $newCategoryId]);
					}

					$addIncomeQuery = $db -> prepare(
					"INSERT INTO incomes
					VALUES (NULL, :userId, :incomeAmount, :incomeDate,
					(SELECT category_id FROM income_categories
					WHERE income_category=:incomeCategory),
					:incomeComment)");
					$addIncomeQuery -> execute([':userId' => $_SESSION['loggedUserId'], ':incomeAmount' => $incomeAmount, ':incomeDate' => $incomeDate, ':incomeCategory' => $incomeCategory, ':incomeComment' => $incomeComment]);
					
					$_SESSION['incomeAdded'] = true;
				}
			} else {
				
					$_SESSION['emptyFieldError'] = "Please fill in all required fields.";
					$_SESSION['incomeAmountError'] = "Amount of an income required.";
			}
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
	
	<script src="js/budget.js"></script>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/fontello.css">
	<link href="https://fonts.googleapis.com/css2?family=Baloo+Paaji+2:wght@400;500;700&family=Fredoka+One&family=Roboto:wght@400;700;900&family=Varela+Round&display=swap" rel="stylesheet">
	
	<script>
		function toggleCustomCategory() {
			var categorySelect = document.getElementById('incomeCategory');
			var customCategoryDiv = document.getElementById('customCategoryDiv');
			
			if (categorySelect.value === 'Other') {
				customCategoryDiv.style.display = 'block';
			} else {
				customCategoryDiv.style.display = 'none';
			}
		}
	</script>
	
</head>

<body>
	
	<header>
	
		<h1 class="mt-3 mb-1" id="title">
			<a id="homeButton" href="index.php" role="button"><span id="logo">myBudget</span>.com</a>
		</h1>
		
		<p id="subtitle">Your Personal Finance Manager</p>
		
	</header>
	
	<main>
		
		<section class="container-fluid square my-4 py-2">
			
			<nav class="navbar navbar-dark navbar-expand-lg">
			
				<button class="navbar-toggler bg-primary" type="button" data-toggle="collapse" data-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Navigation Toggler">
					<span class="navbar-toggler-icon"></span>
				</button>
				
				<div class="collapse navbar-collapse" id="mainMenu">
			
					<ul class="navbar-nav mx-auto">
					
						<li class="col-lg-2 nav-item">
							<a class="nav-link" href="menu.php"><i class="icon-home"></i> Home</a>
						</li>
						
						<li class="col-lg-2 nav-item disabled">
							<a class="nav-link" href="income.php"><i class="icon-money-1"></i> Add Income</a>
						</li>
						
						<li class="col-lg-2 nav-item">
							<a class="nav-link" href="expense.php"><i class="icon-dollar"></i> Add Expense</a>
						</li>
						
						<li class="col-lg-2 nav-item dropdown">
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
								<a class='dropdown-item' href="#">Payment Methods</a>
							
							</div>
						</li>
						
						<li class="col-lg-2 nav-item">
							<a class="nav-link" href="logout.php"><i class="icon-logout"></i> Sign out</a>
						</li>
						
					</ul>
					
				</div>
			
			</nav>
			
		</section>
		
		<section class="container-fluid square my-4 py-4">
		
			<form class="col-sm-10 col-md-8 col-lg-6 py-3 mx-auto" method="post">
				
				<h3>ADDING AN INCOME</h3>
				
				<div class="row justify-content-around">
				
					<div class="col-sm-10 col-lg-8">
					
						<?php
							if(isset($_SESSION['emptyFieldError'])) {
								
								echo '<div class="text-danger">'.$_SESSION['emptyFieldError'].'</div>';
								unset($_SESSION['emptyFieldError']);
							}
						?>
						
						<div class="input-group mt-3">
							<div class="input-group-prepend px-1">
								<span class="input-group-text">Amount</span>
							</div>
							<input class="form-control userInput labeledInput" type="number" name="incomeAmount" step="0.01" value="<?php
								if(isset($_SESSION['formIncomeAmount'])) {
									
									echo $_SESSION['formIncomeAmount'];
									unset($_SESSION['formIncomeAmount']);
								}
							?>">
						</div>
						
						<?php
							if(isset($_SESSION['incomeAmountError'])) {
								
								echo '<div class="text-danger">'.$_SESSION['incomeAmountError'].'</div>';
								unset($_SESSION['incomeAmountError']);
							}
						?>
						
						<div class="input-group mt-3">
							<div class="input-group-prepend px-1">
								<span class="input-group-text">Date</span>
							</div>
							<?php
							if(!isset($_SESSION['formIncomeDate'])) {
								
									echo "<script>$(document).ready(function(){getCurrentDate();})</script>";
								}
							?>
							<input class="form-control  userInput labeledInput" type="date" id="dateInput" name="incomeDate" value="<?php
								if(isset($_SESSION['formIncomeDate'])) {
									
									echo $_SESSION['formIncomeDate'];
									unset($_SESSION['formIncomeDate']);
								}
							?>" required>
						</div>
						
						<?php
							if(isset($_SESSION['dateError'])) {
								
								echo '<div class="text-danger">'.$_SESSION['dateError'].'</div>';
								unset($_SESSION['dateError']);
							}
						?>
						
						<div class="input-group mt-3">
							<div class="input-group-prepend px-1">
								<span class="input-group-text">Category</span>
							</div>
							<select class="form-control userInput labeledInput" name="incomeCategory" id="incomeCategory" onchange="toggleCustomCategory()">
								<?php
									foreach ($incomeCategoriesOfLoggedUser as $category) {
									
										if(isset($_SESSION['formIncomeCategory']) && $_SESSION['formIncomeCategory'] == $category['income_category']) {
											
											echo '<option selected>'.$category['income_category']."</option>";
											unset($_SESSION['formIncomeCategory']);
										} else {
											
											echo "<option>".$category['income_category']."</option>";
										}
									}
								?>
								<option value="Other" <?php echo (isset($_SESSION['formIncomeCategory']) && $_SESSION['formIncomeCategory'] == 'Other') ? 'selected' : ''; ?>>Other</option>
							</select>
						</div>
						
						<div id="customCategoryDiv" style="display: <?php echo (isset($_SESSION['formIncomeCategory']) && $_SESSION['formIncomeCategory'] == 'Other') ? 'block' : 'none'; ?>;">
							<div class="input-group mt-3">
								<div class="input-group-prepend px-1">
									<span class="input-group-text">Custom Category</span>
								</div>
								<input class="form-control userInput labeledInput" type="text" name="customCategory" placeholder="Enter custom category name" value="<?php
									if(isset($_SESSION['formCustomCategory'])) {
										echo $_SESSION['formCustomCategory'];
										unset($_SESSION['formCustomCategory']);
									}
								?>">
							</div>
						</div>
						
						<?php
							if(isset($_SESSION['categoryError'])) {
								echo '<div class="text-danger">'.$_SESSION['categoryError'].'</div>';
								unset($_SESSION['categoryError']);
							}
						?>
						
						<div class="input-group mt-3">
							<div class="input-group-prepend px-1">
								<span class="input-group-text">Commments<br />(optional)</span>
							</div>
							<textarea class="form-control userInput labeledInput" name="incomeComment" maxlength="100" rows="5"><?php
									if(isset($_SESSION['formIncomeComment'])) {
										
										echo $_SESSION['formIncomeComment'];
										unset($_SESSION['formIncomeComment']);
									}
								?></textarea>
						</div>
						
						<?php
							if(isset($_SESSION['commentError'])) {
								
								echo '<div class="text-danger">'.$_SESSION['commentError'].'</div>';
								unset($_SESSION['commentError']);
							}
						?>
						
					</div>
					
					<div class="col-md-11">
						<button class="btn-lg mt-3 mb-2 mx-1 signButton bg-primary" type="submit">
							<i class="icon-floppy"></i> Save
						</button>
						<a data-toggle="modal" data-target="#discardIncomeModal">
							<button class="btn-lg mt-3 mb-2 mx-1 signButton bg-danger">
								<i class="icon-cancel-circled"></i> Cancel
							</button>
						</a>
					</div>
					
				</div>
				
			</form>
		
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
		
		<?php
			if($_SESSION['incomeAdded']){
				
				echo "<script>$(document).ready(function(){ $('#incomeAdded').modal('show'); });</script>

				<div class='modal fade' id='incomeAdded' role='dialog'>
					<div class='modal-dialog col'>
						<div class='modal-content'>
							<div class='modal-header'>
								<h3 class='modal-title'>New Income Added</h3>
								<a href='income.php'>
								<button type='button' class='close'>&times;</button>
								</a>
							</div>
											
							<div class='modal-body'>
								<p>Your income has been successfully added.</p>
							</div>
							<div class='modal-footer'>
								<a href='menu.php'>
									<button type='button' class='btn btn-success'>OK</button>
								</a>
							</div>
						</div>
					</div>
				</div>"; 
			}
		?>
		
		<div class="modal hide fade in" data-backdrop="static" id="discardIncomeModal">
			<div class='modal-dialog col'>
				<div class='modal-content'>
					<div class='modal-header'>
						<h3 class='modal-title'>Quit Adding Income?</h3>
						<a href='income.php'>
						<button type='button' class='close'>&times;</button>
						</a>
					</div>
											
					<div class='modal-body'>
						<p>Data you have entered so far will not be saved.</p>
					</div>
					<div class='modal-footer'>
						<a href='menu.php'>
							<button type='button' class='btn btn-success'>YES</button>
						</a>
						<button type="button" class="btn btn-danger" data-dismiss="modal">NO</button>
					</div>
				</div>
			</div>
		</div>
		
	</main>
	
	<footer>
	
		<div class="col my-2 footer">
			personal_budget_tracker
		</div>
		
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="js/bootstrap.min.js"></script>
	
	<script>
		// Initialize the custom category field visibility on page load
		document.addEventListener('DOMContentLoaded', function() {
			toggleCustomCategory();
		});
	</script>
	
</body>

</html>
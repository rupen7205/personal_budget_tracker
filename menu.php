<?php
	session_start();

	require_once 'database.php';
	
	if(!isset($_SESSION['loggedUserId'])) {
		
		if(isset($_POST['email'])) {
		
			$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			$password = filter_input(INPUT_POST, 'password');
		
			$userQuery = $db -> prepare(
			"SELECT user_id, password, username
			FROM users
			WHERE email = :email");
			$userQuery->execute([':email'=> $email]);
			
			$user = $userQuery -> fetch();

			if($user && password_verify($password, $user['password'])) {
				
				$_SESSION['loggedUserId'] = $user['user_id'];
				$_SESSION['username'] = $user['username'];
				unset($_SESSION['badAttempt']);
				
			} else {
				
				$_SESSION['badAttempt'] = "";
				header ('Location: login.php');
				exit();
			}
		}
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
	
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/fontello.css">
	<link href="https://fonts.googleapis.com/css2?family=Baloo+Paaji+2:wght@400;500;700&family=Fredoka+One&family=Roboto:wght@400;700;900&family=Varela+Round&display=swap" rel="stylesheet">
	
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
					
						<li class="col-lg-2 nav-item disabled">
							<a class="nav-link" href="menu.php"><i class="icon-home"></i> Home</a>
						</li>
						
						<li class="col-lg-2 nav-item">
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
		
		<section class="container-fluid square">
		
			<h2 class="pt-4 mx-2">Hello <?php echo $_SESSION['username']; ?> !</h2>
		
			<img class="img-fluid" src="css/img/menuBG.png" alt="" />
		
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
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="js/bootstrap.min.js"></script>
	
</body>

</html>
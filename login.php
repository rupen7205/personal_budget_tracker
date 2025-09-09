<?php
	session_start();
		
	if(isset($_SESSION['loggedUserId'])) {
		
		header('Location: menu.php');
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
	
</head>

<body>

	<header>
	
		<h1 class="mt-3 mb-1" id="title">
			<a id="homeButton" href="index.php" role="button"><span id="logo">myBudget</span>.com</a>
		</h1>
		
		<p id="subtitle">Your Personal Finance Manager</p>
		
	</header>
	
	<main>
		
		<section class="container-fluid square my-4 py-4">
			
			<form class="col-sm-10 col-md-8 col-lg-6 mx-auto my-2 py-3" method="post" action="menu.php">
				
				<div class="row justify-content-around">
					
					<?php
						if(isset($_SESSION['badAttempt'])) {
							
							echo '<div class="text-danger px-2">The name or password you have entered is incorrect.</div>';
							unset($_SESSION['badAttempt']);
						}
					?>
					
					<div class="col-sm-8">
						
						<div class="input-group mt-3">
							<div class="input-group-prepend px-1 pt-1 inputIcon">
								<i class="icon-mail-alt"></i>
							</div>
							<input class="form-control  userInput" type="email" id="loginInput" name="email" placeholder="email@address.com" required>
						</div>
							
						<div class="input-group mt-3">
							<div class="input-group-prepend px-1 pt-1 inputIcon">
								<i class="icon-lock"></i>
							</div>
							<input class="form-control  userInput" type="password" id="password1" placeholder="password" name="password" required>
						</div>
							
						<div>
							<input class="mt-3" type="checkbox" onclick="showPassword()"> Show password
						</div>
						
						<button class="btn btn-lg mt-3 mb-2 signButton" type="submit" data-toggle="modal" data-target="#dateModal">
							<i class="icon-login"></i> Sign in
						</button>
							
					</div>
						
				</div>
					
			</form>
				
		</section>
		
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
	
</body>

</html>
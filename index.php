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
			<a id="homeButton" href="index.php" role="button">Welcome to <span id="logo">myBudget</span>.com!</a>
		</h1>
		
		<p id="subtitle">Your Personal Finance Manager</p>
		
	</header>
	
	<main>
		
		<section class="container-fluid square my-4 py-3">
			
			<p>Feeling like you need reining in your spending and getting your finances under control?<br />
			You've come to the right place!</p>
				
			<div class="row row-eq-height justify-content-center mb-2">
				
				<div class="col-sm-6 col-md-3 col-lg-2 pt-2 mt-2">
					<div class="pt-2 tile">
						<figure>
							<i class="icon-basket bigIcon"></i>
							<figcaption>Different<br />Income And Expense<br />Categories</figcaption>
						</figure>
					</div>
				</div>
				
				<div class="col-sm-6 col-md-3 col-lg-2 pt-2 mt-2">
					<div class="pt-2 tile">
						<figure>
							<i class="icon-money-1 bigIcon"></i>
							<figcaption>Manage<br />Your<br />Budget</figcaption>
						</figure>
					</div>
				</div>
				
				<div class="col-sm-6 col-md-3 col-lg-2 pt-2 mt-2">
					<div class="pt-2 tile">
						<figure>
							<i class="icon-chart-pie bigIcon"></i>
							<figcaption>Analyze<br />Your Finances<br />With Charts</figcaption>
						</figure>
					</div>
				</div>
				
				<div class="col-sm-6 col-md-3 col-lg-2 pt-2 mt-2">
					<div class="col-12 pt-2 tile">
						<figure>
							<i class="icon-cog-alt bigIcon"></i>
							<figcaption>Add, Remove<br />And Costum<br />Categories</figcaption>
						</figure>
					</div>
				</div>
				
			</div>
			
		</section>
		
		<section class="container-fluid square my-4 py-3">
		
			<p>Track your income and expances - avoid overspending!</p>
			
			<nav>
			
				<div class="row justify-content-center mb-2">
					
					<div class="col-md-4 mt-2">
						<div class="py-2 option">
							Already have an account?<br />Sign in here:<br />
							<a class="btn btn-lg my-2 signButton" href="login.php">
							<i class="icon-login"></i> Sign in
						</a>
						</div>
					</div>
					
					<div class="col-md-4 mt-2">
						<div class="py-2 option">
							No account yet?<br />Create it here:<br />
							<a class="btn btn-lg my-2 signButton" href="register.php">
								<i class="icon-user-plus"></i> Register
							</a>
						</div>
					</div>
					
				</div>
				
			</nav>
			
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
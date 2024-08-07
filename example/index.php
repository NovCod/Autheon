<?php include "inc.php"; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>User Dashboard</title>
		<link rel="stylesheet" href="styles.css">
	</head>
	<body class="dashboard-page">
		<header class="header">
			<div class="header-container">
				<div class="logo">MyApp</div>
				<nav class="navbar">
					<a href="#">Home</a>
					<a href="#">Profile</a>
					<a href="#">Settings</a>
					<a href="rooter.php">Logout</a>
				</nav>
			</div>
		</header>
		
		<main class="main-content">
			<div class="container">
				<h1>Welcome, [User id : <?=$user_id?>]</h1>
				<p>Your user area is here to help you manage your activities and settings.</p>
				<div class="cards">
				<?php if (isset($activated)) { ?>
					<div class="card">
						<h2>Account Activation Required</h2>
						<p>Your account is not yet activated. Please check your email for the activation link to complete the registration process.</p>
    <p><a href="#">Resend Activation Link</a></p>
					</div>
				<?php } else { ?>
				
					<div class="card">
						<h2>USER TABLE :</h2>
						<?php prettyPrint($user); ?>
					</div>
					
					<div class="card">
						<h2>SESSION :</h2>
						<?php prettyPrint($_SESSION); ?>
					</div>
					<div class="card">
						<h2>COOKIE :</h2>
						<?php prettyPrint($_COOKIE); ?>
						
					</div>
				<?php } ?>
				</div>
			</div>
		</main>
	</body>
</html>

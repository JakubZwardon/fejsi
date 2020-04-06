<?php

require 'config/config.php';
require 'includes/form_handlers/register_handler.php';
require 'includes/form_handlers/login_handler.php';

?>

<html>
<head>
	<title>fejsi- Registration</title>
	<link rel="stylesheet" type="text/css" href="assets/css/register_style.css" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="assets/js/register.js"></script>
</head>
<body>

	<?php
		//Show register form if pressed register_button
		if(isset($_POST['register_button'])) {
			echo '
			<script>
			
			$(document).ready(function() {
				$("#first").hide();
				$("#second").show();
			});

			</script>						
			';
		}
	?>

	<div class="wrapper">

		<div class="login_box">

			<div class="login_header">
				<h1>Fejsi!</h1>
				Login or sign up below!
			</div>

			<div id="first">
				<!-- login form  -->
				<form action="register.php" method="post">
					<input type="email" name="log_email" placeholder="e-mail address" required value="<?php 
					if (isset($_SESSION['log_email'])) {
						echo $_SESSION['log_email'];
					}?>"/>
					<br />
					<input type="password" name="log_password" placeholder="password" required/>
					<br />
					<input type="submit" name="login_button" value="Login"/>
					<br />				
					
					<!-- 		displaying error to the user -->
					<?php if (in_array("e-mail or password was incorrect<br />", $error_arrar)) echo "e-mail or password was incorrect<br />";?>
					<a href="#" id="signup" class="signup">Need an account? Register here!</a>
				</form>
			</div>

			<div id="second">
				<!-- register form -->
				<form action="register.php" method="post">
					<!-- 	input for first name -->
					<input type="text" name="reg_fname" placeholder="First Name" required value="<?php 
					if (isset($_SESSION['reg_fname'])) {
						echo $_SESSION['reg_fname'];
					}?>"/>
					<br />
					<!-- 		diplaying error to the user -->
					<?php if (in_array("Your first name must be between 2 and 25 characters<br />", $error_arrar)) echo "Your first name must be between 2 and 25 characters<br />";?>
					

					<!-- input for the last name -->
					<input type="text" name="reg_lname" placeholder="Last Name" required value="<?php 
					if (isset($_SESSION['reg_lname'])) {
						echo $_SESSION['reg_lname'];
					}?>"/>
					<br />
					<!-- 		displaying error message to the user -->
					<?php if (in_array("Your last name must be between 2 and 25 characters<br />", $error_arrar)) echo "Your last name must be between 2 and 25 characters<br />";?>
					
					
					<!-- 		input for e-mail -->
					<input type="email" name="reg_email" placeholder="e-mail" required value="<?php 
					if (isset($_SESSION['reg_email'])) {
						echo $_SESSION['reg_email'];
					}?>"/>
					<br />

					
					<!-- 		input for e-mail confirm -->
					<input type="email" name="reg_email2" placeholder="Confirm e-mail" required value="<?php 
					if (isset($_SESSION['reg_email2'])) {
						echo $_SESSION['reg_email2'];
					}?>"/>
					<br />
					<!-- 		displaying error to teh user -->
					<?php if (in_array("Email already in use<br />", $error_arrar)) echo "Email already in use<br />";
					elseif (in_array("Invalid email format<br />", $error_arrar)) echo "Invalid email format<br />";
					elseif (in_array("Emails don't match<br />", $error_arrar)) echo "Emails don't match<br />";?>
					
					
					<!-- 		input for password -->
					<input type="password" name="reg_password" placeholder="password" required />
					<br />
					<input type="password" name="reg_password2" placeholder="Confirm password" required />
					<br />
					<!-- 		displaying error message -->
					<?php if (in_array("Your password must be between 5 and 30 characters<br />", $error_arrar)) echo "Your password must be between 5 and 30 characters<br />";
					elseif (in_array("Your password can only contain English characters or numbers<br />", $error_arrar)) echo "Your password can only contain English characters or numbers<br />";
					elseif (in_array("Your passwords do not match<br />", $error_arrar)) echo "Your passwords do not match<br />";?>
					
					
					<input type="submit" name="register_button" value="Register"/>
					<br />
					
					<!-- 		displaying success message to the user -->
					<?php if (in_array("<span style='color: #14C800;'>You're all set! Go ahead and login!</span><br />", $error_arrar)) echo "<span style='color: #14C800;'>You're all set! Go ahead and login!</span><br />";?>
					<a href="#" id="signin" class="signin">Already have on account? Sign in here!</a>

				</form>
			</div>
		</div>
	</div>
</body>
</html>
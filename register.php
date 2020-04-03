<?php
session_start();
$con = mysqli_connect("localhost", "root", "Karolinka2019", "social");

if (mysqli_connect_errno()) {
	echo "Failed to connect: " . mysqli_connect_errno();
}

//Declaring variables to prevent errors
$fname = ""; //First Name
$lname = ""; //Last Name
$em = ""; //email
$em2 = ""; //email 2
$password = ""; //password
$password2 = ""; //password 2
$date = ""; //Sign up date
$error_arrar = ""; //Holds error messages

//Registration process

if (isset($_POST['register_button'])) {
	//first name
	$fname = strip_tags($_POST['reg_fname']); //remove html tags
	$fname = str_replace(' ', '', $fname); //remove spaces
	$fname = ucfirst(strtolower($fname)); //uppercase first letter
	$_SESSION['reg_fname'] = $fname; //Stores first name into session variable
	
	//last name
	$lname = strip_tags($_POST['reg_lname']); //remove html tags
	$lname = str_replace(' ', '', $lname); //remove spaces
	$lname = ucfirst(strtolower($lname)); //uppercase first letter
	$_SESSION['reg_lname'] = $lname; //Stores last name into session variable

	//email
	$em = strip_tags($_POST['reg_email']); //remove html tags
	$em = str_replace(' ', '', $em); //remove spaces
	$em = ucfirst(strtolower($em)); //uppercase first letter
	$_SESSION['reg_email'] = $em; //Stores email into session variable
	
	//email2
	$em2 = strip_tags($_POST['reg_email2']); //remove html tags
	$em2 = str_replace(' ', '', $em2); //remove spaces
	$em2 = ucfirst(strtolower($em2)); //uppercase first letter
	$_SESSION['reg_email2'] = $em2; //Stores email2 into session variable
	
	//password
	$password = strip_tags($_POST['reg_password']); //remove html tags
	$password2 = strip_tags($_POST['reg_password2']); //remove html tags
	
	//date
	$date = date("Y-m-d"); //current date
	
	//check if emails match
	if ($em == $em2) {
		//check if emails is in valid format
		if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
			$em = filter_var($em, FILTER_VALIDATE_EMAIL);
			
			//check if email already exists
			$e_check = mysqli_query($con, "SELECT email FROM users WHERE email='$em'");
			//count the number of rows returned
			$num_rows = mysqli_num_rows($e_check);
			
			if ($num_rows > 0) {
				echo "Email is already in use";
			}
		} else {
			echo "Email invalid format";
		}
	} else {
		echo "Emails don't match";
	}
	
	//check length of the first name
	if ((strlen($fname) > 25) || (strlen($fname) < 2)) {
		echo "Your first name must be betwean 2 and 25 characters";
	}
	
	//check length of the last name
	if ((strlen($lname) > 25) || (strlen($lname) < 2)) {
		echo "Your last name must be betwean 2 and 25 characters";
	}
	
	//check if passwords match
	if ($password != $password2) {
		echo "Your passwords do not match";
	} else {
		//check is password is valid
		if (preg_match('/[^A-Za-z0-9]/', $password)) {
			echo "Your password can only contain English characters or numbers";
		};
		//check length of password(is it in range) 
		if ((strlen($password) > 30) || (strlen($password) < 5)) {
			echo "Your password must be betwen 5 and 30 characters";
		}
	}
}
?>

<html>
<head>
	<title>fejsi- Registration</title>
</head>
<body>
	<form action="register.php" method="post">
		<input type="text" name="reg_fname" placeholder="First Name" required value="<?php 
		if (isset($_SESSION['reg_fname'])) {
			echo $_SESSION['reg_fname'];
		}?>"/>
		<br />
		<input type="text" name="reg_lname" placeholder="Last Name" required value="<?php 
		if (isset($_SESSION['reg_lname'])) {
			echo $_SESSION['reg_lname'];
		}?>"/>
		<br />
		<input type="email" name="reg_email" placeholder="e-mail" required value="<?php 
		if (isset($_SESSION['reg_email'])) {
			echo $_SESSION['reg_email'];
		}?>"/>
		<br />
		<input type="email" name="reg_email2" placeholder="Confirm e-mail" required value="<?php 
		if (isset($_SESSION['reg_email2'])) {
			echo $_SESSION['reg_email2'];
		}?>"/>
		<br />
		<input type="password" name="reg_password" placeholder="password" required />
		<br />
		<input type="password" name="reg_password2" placeholder="Confirm password" required />
		<br />
		<input type="submit" name="register_button" value="Register"/>
	</form>
</body>
</html>
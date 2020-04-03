<?php
session_start();
$con = mysqli_connect("localhost", "root", "Karolinka2019", "social");

if (mysqli_connect_errno()) {
	echo "Failed to connect: " . mysqli_connect_errno();
}

//Declaring variables to prevent errors
$fname = ""; //First Name
$lname = ""; //Last Name
$username = ""; //Uniqate user name(concate fname+lname)
$em = ""; //email
$em2 = ""; //email 2
$password = ""; //password
$password2 = ""; //password 2
$date = ""; //Sign up date
$error_arrar = array(); //Holds error messages

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
				array_push($error_arrar, "Email already in use<br />");			
			}
		} else {
			array_push($error_arrar, "Invalid email format<br />");
		}
	} else {
		array_push($error_arrar, "Emails don't match<br />");
	}
	
	//check length of the first name
	if ((strlen($fname) > 25) || (strlen($fname) < 2)) {
		array_push($error_arrar, "Your first name must be between 2 and 25 characters<br />");
	}
	
	//check length of the last name
	if ((strlen($lname) > 25) || (strlen($lname) < 2)) {
		array_push($error_arrar, "Your last name must be between 2 and 25 characters<br />");
	}
	
	//check if passwords match
	if ($password != $password2) {
		array_push($error_arrar, "Your passwords do not match<br />");
	} else {
		//check is password is valid
		if (preg_match('/[^A-Za-z0-9]/', $password)) {
			array_push($error_arrar, "Your password can only contain English characters or numbers<br />");
		};
		//check length of password(is it in range) 
		if ((strlen($password) > 30) || (strlen($password) < 5)) {
			array_push($error_arrar, "Your password must be between 5 and 30 characters<br />");
		}
	}
	
	//only if there is no errors
	if (empty($error_arrar)) {
		$password = md5($password); //encrypt password
		
		//generating user name by concatenating first name and last name
		$username = strtolower($fname . "_" . $lname);
		//check if username arleady exist in the databse
		$check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
		//if username already exist add number to username
		$i = 0;
		while (mysqli_num_rows($check_username_query)) {
			$i++;
			$username = $username . "_" . $i;
			$check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
		}
		
		//Give to user random profile picture
		$rand = rand(1, 2);
		if($rand == 1)
			$profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
		else if($rand == 2)
			$profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";
		
		//Insert dates to database
		$query = mysqli_query($con, "INSERT INTO users VALUES (NULL, '$fname', '$lname', '$username', '$em', '$password', '$date', '$profile_pic', '0', '0', 'no', ',')");
		if (mysqli_error($con)) {
			echo mysqli_error($con); //print error when somethings go wrong
		}else {
			//add success message to error array
			array_push($error_arrar, "<span style='color: #14C800;'>You're all set! Go ahead and login!</span><br />");
			
			//clear session variables
			$_SESSION['reg_fname'] = "";
			$_SESSION['reg_lname'] = "";
			$_SESSION['reg_email'] = "";
			$_SESSION['reg_email2'] = "";
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

	</form>
</body>
</html>
<?php

//login process
if (isset($_POST['login_button'])) {
	
	$email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL);
	$_SESSION['log_email'] = $email;
	
	$password = md5($_POST['log_password']);
	
	//chack email and password in database
	$check_database_query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND password='$password'");
	if (mysqli_error($con)) {
		echo mysqli_error($con); //print error when somethings go wrong
	}else { //SELECT from database go fine then check dates get from database
		$checko_login_query = mysqli_num_rows($check_database_query); //0- no match, 1 login and password match- login - success
		
		//success login
		if ($checko_login_query == 1) {
			$row = mysqli_fetch_array($check_database_query);
			$username = $row['username'];
			
			//check if account is closed
			$user_closed_query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND user_closed='yes'");
			if (mysqli_num_rows($user_closed_query) == 1) {
				//reopen closed accoutn by simply login
				$reopen_account = mysqli_query($con, "UPDATE users SET user_closed='no' WHERE email='$email'");
			}
			
			//this session variable means that user is loged in
			$_SESSION['username'] = $username;
			header("Location: index.php");
			exit();
		} else { //failure login
			array_push($error_arrar, "e-mail or password was incorrect<br />");
		}
	}
}

?>
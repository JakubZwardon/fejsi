<?php
if(isset($_POST['updata_details'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];

    $emailCheck = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");
    $row = mysqli_fetch_array($emailCheck);

    if((mysqli_num_rows($emailCheck) == 0)) {
        $message = "Details Updated!<br><br>";

        $query = mysqli_query($con, "UPDATE users SET first_name='$firstName', last_name='$lastName', email='$email' WHERE username='$userLoggedIn'");

    } else if($row['username'] == $userLoggedIn){
        $message = "Details Updated!<br><br>";

        $query = mysqli_query($con, "UPDATE users SET first_name='$firstName', last_name='$lastName' WHERE username='$userLoggedIn'");
    } else {
        $message = "That email is already in use!<br><br>";
    }
} else {
    $message = "";
}

//**************************************************************************************

if(isset($_POST['updata_password'])) {
    $oldPassword = strip_tags($_POST['old_password']);
    $newPassword = strip_tags($_POST['new_password']);
    $newPasswordConfirm = strip_tags($_POST['new_password_confirm']);

    $password_query = mysqli_query($con, "SELECT password FROM users WHERE username='$userLoggedIn'");
    $row = mysqli_fetch_array($password_query);
    $dbPassword = $row['password'];

    if(md5($oldPassword) == $dbPassword) {

        if($newPassword == $newPasswordConfirm) {
            if(strlen($newPassword) <= 4) {
                $passwordMessage = "Sorry, your password must greater then 4 characters!<br><br>";
            } else {
                $newPasswordMd5 = md5($newPassword);
                $query = mysqli_query($con, "UPDATE users SET password='$newPasswordMd5' WHERE username='$userLoggedIn'");
                $passwordMessage = "Password has been changed!<br><br>";
            }
        } else {
            $passwordMessage = "You two passwords need to match!<br><br>";
        }
    } else {
        $passwordMessage = "The old password is incorrect!<br><br>";
    }
} else {
    $passwordMessage = "";
}

//*****************************************************

if(isset($_POST['close_account'])) {
    header("Location: close_account.php");
}




?>
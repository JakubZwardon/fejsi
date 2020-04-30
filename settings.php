<?php
include("includes/header.php");
include("includes/form_handlers/settings_handler.php");
?>

<div class="main_column column">
    <h4>Account Settings</h4>
    <?php
        echo "<img src='" . $user['profile_pic'] ."' id='small_profile_pic'>";
    ?>

    <br>
    <a href="upload.php">Upload new profile picture</a><br><br><br><br>

    Modify the values and click 'Update Details'

    <?php
        $user_data_query = mysqli_query($con, "SELECT first_name, last_name, email FROM users WHERE username='$userLoggedIn'");
        $row = mysqli_fetch_array($user_data_query);

        $first_name = $row['first_name'];
        $last_name = $row['last_name'];
        $email = $row['email'];
    
    ?>

    <form action="settings.php" method="POST">
        First name: <input type="text" name="first_name" id="settings_input" value="<?php echo $first_name; ?>"><br>
        Last name: <input type="text" name="last_name" id="settings_input" value="<?php echo $last_name; ?>"><br>
        E-mail: <input type="email" name="email" id="settings_input" value="<?php echo $email; ?>"><br>

        <?php echo $message; ?>

        <input type="submit" name="updata_details" id="save_details" value="Update Details" class="info settings_submit"><br>
    </form>

    <h4>Change password</h4>
    <form action="settings.php" method="POST">
        Old Password: <input type="password" name="old_password" id="settings_input"><br>
        New Password: <input type="password" name="new_password" id="settings_input"><br>
        New Password Again: <input type="password" name="new_password_confirm" id="settings_input"><br>

        <?php echo $passwordMessage; ?>

        <input type="submit" name="updata_password" id="save_password" value="Update Password" class="info settings_submit"><br>
    </form>

    <h4>Close Account</h4>
    <form action="settings.php" method="POST">
        <input type="submit" name="close_account" id="close_account" value="Close Account" class="danger settings_submit">
    </form>


</div>
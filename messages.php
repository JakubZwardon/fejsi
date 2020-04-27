<?php
include("includes/header.php");

$messageObj = new Message($con, $userLoggedIn);

if(isset($_GET['u'])) {
    $userTo = $_GET['u'];
} else {
    $userTo = $messageObj->getMostRecentUser();
    if($userTo == false) {
        $userTo = 'new';
    }
}

if($userTo != "new") {
    $userToObj = new User($con, $userTo);

    if(isset($_POST['post_message'])) {
        if(isset($_POST['message_body'])) {
            $messageBody = mysqli_real_escape_string($con, $_POST['message_body']);
            $messageSendDate = date("Y-m-d H:i:s");
            $messageObj->sendMessage($userTo, $messageBody, $messageSendDate);
        }
    }
}

?>


<div class="user_details column">
	<a href="<?php echo $userLoggedIn ?>"><img src="<?php echo $user['profile_pic']; ?>"></a>

	<div class="user_details_left_right">
		<a href="<?php echo $userLoggedIn ?>">
			<?php
			echo $user['first_name'] . " " . $user['last_name'];
			?>
		</a>
		<br />

		<?php
		echo "Posts: " . $user['num_posts'] . "<br />";
		echo "Likes: " . $user['num_likes'];
		?>
	</div>

</div>

<div class="main_column column" id="main_column">
    <?php
        if($userTo != "new") {
            echo "<h4>You and <a href='$userTo'>" . $userToObj->getFirstAndLastName() . "</a></h4><hr><br>";

            echo "<div class='loaded_messages' id='scroll_messages'>";
                echo $messageObj->getMessages($userTo);
            echo "</div>";
        } else {
            echo "<h4>New Message</h4>";
        }
    ?>

    <div class="message_post">
        <form action="" method="POST">
            <?php
                if($userTo == "new") {
                    echo "Select the friend you would like to message <br><br>";
                    ?>
                    To: <input type='text' onkeyup='getUsers(this.value, "<?php echo $userLoggedIn; ?>")' name='q' placeholder='Name' autocomplete='off' id='search_text_input'>
                    <?php
                    echo "<div class='results'></div>";
                } else {
                    echo "<textarea name='message_body' id='message_textarea' placeholder='Write youre message ...'></textarea>";
                    echo "<input type='submit' name='post_message' class='info' id='message_submit' value='Send'>";
                }
            ?>
        </form>
    </div>

    <script>
        let div = document.getElementById("scroll_messages");
        if(div != null)
            div.scrollTop = div.scrollHeight;
    </script>
</div>

<div class="user_details column" id="conversations">
        <h4>Conversations</h4>

        <div class="loaded_conversations">
            <?php echo $messageObj->getConversations(); ?>
        </div>
        <br>
        <a href="messages.php?u=new">New Message.</a>
    </div>
<?php
include("includes/header.php");
include("includes/classes/User.php");
include("includes/classes/Post.php");

if(isset($_GET['profile_username'])) {
	$username = $_GET['profile_username'];	//Username of profile owner
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
	$userArray = mysqli_fetch_array($user_details_query);

	//Get number of friends
	$numFriends = (substr_count($userArray['friend_array'], ',')) - 1;
}

//Remove frinend
if(isset($_POST['remove_friend'])) {
	//removed owner of profile from the logged in user and vice versa
	$loggedInUserObj = new User($con, $userLoggedIn);
	$loggedInUserObj->removeFriend($username);
}

//Add friend
if(isset($_POST['add_friend'])) {
	//send friend request to owner of profile from logged in user
	$loggedInUserObj = new User($con, $userLoggedIn);
	$loggedInUserObj->sendRequest($username);
}

if(isset($_POST['respond_request'])) {
	header('Location: requests.php');
}

?>

<style type="text/css">
	.wrapper {
		margin-left: 0;
		padding-left: 0;
	}
</style>

<div class="profile_left">
	<img src="<?php echo $userArray['profile_pic']; ?>">

	<div class="profile_info">
		<p><?php echo "Posts: " . $userArray['num_posts']; ?></p>
		<p><?php echo "Likes: " . $userArray['num_likes']; ?></p>
		<p><?php echo "Friends: " . $numFriends; ?></p>
	</div>

	<form action="<?php echo $username ?>" method="POST">
		<?php
		$profileUserObj = new User($con, $username);

		if($profileUserObj->isClosed()) {
			header('Location: user_closed.php');
		}

		$loggedInUserObj = new User($con, $userLoggedIn);

		//checking if it's the same person
		if($userLoggedIn != $username) {
			//checking if they are friends
			if($loggedInUserObj->isFriend($username)) {
				echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend" /><br />';
			} else if($loggedInUserObj->didReceiveRequest($username)) {	//is get friend request
				echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request" /><br />';
			} else if($loggedInUserObj->didSendRequest($username)) { //is already sent friend request
				echo '<input type="submit" name="" class="default" value="Request Sent" /><br />';
			} else { //show add friend button
				echo '<input type="submit" name="add_friend" class="success" value="Add Friend" /><br />';
			}
		}
		?>
	</form>
</div>

<div class="main_column column">
	<?php echo $username; ?>
</div>

<!-- closing div from header file -->
</div>
</body>

</html>
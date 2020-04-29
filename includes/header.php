<?php

require 'config/config.php';
include("classes/User.php");
include("classes/Post.php");
include("classes/Message.php");
include("classes/Notification.php");

//Redirect not logged user
if (isset($_SESSION['username'])) {
	$userLoggedIn = $_SESSION['username'];
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE  username='$userLoggedIn'");
	$user = mysqli_fetch_array($user_details_query);
} else {
	header('Location: register.php');
}

?>

<html>

<head>
	<title>Welcome to Fejsi</title>

	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<link rel="stylesheet" type="text/css" href="assets/css/jquery.Jcrop.css">

	<!-- Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="assets/js/bootstrap.js"></script>
	<script src="assets/js/bootbox.min.js"></script>
	<script src="assets/js/fejsi.js"></script>
	<script src="assets/js/jquery.Jcrop.js"></script>
	<script src="assets/js/jcrop_bits.js"></script>
</head>

<body>

	<div class="top_bar">

		<div class="logo">
			<a href="index.php">Fejsi!</a>
		</div>
		
		<div class="search">
			<form action="search.php" method="GET" name="search_form">
				<input type="text" name="q" placeholder="Search..." autocomplete="off" id="search_text_input" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')">

				<div class="button_holder">
					<img src="assets/images/icons/search.png">
				</div>
			</form>

				<div class="search_results">
				</div>

				<div class="search_results_footer_empty">
				</div>
			
		</div>

		<nav>
			<?php
				//Unread messages
				$message = new Message($con, $userLoggedIn);
				$numMessages = $message->getUnreadNumber();

				//Unread notifications
				$notification = new Notification($con, $userLoggedIn);
				$numNotifications = $notification->getUnreadNumber();

				//Friend requests
				$userObj = new User($con, $userLoggedIn);
				$numRequests = $userObj->getNumberOfFriendRequests();
			?>
			<a href="<?php echo $userLoggedIn ?>">
				<?php echo $user['first_name']; ?>
			</a>
			<a href="index.php">
				<i class="fa fa-home fa-lg"></i>
			</a>
			<a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')">
				<i class="fa fa-envelope fa-lg"></i>
				<?php
					if($numMessages > 0)
						echo '<span class="notification_badge" id="unread_messages">' . $numMessages . '</span>';
				?>
			</a>
			<a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')">
				<i class="fa fa-bell-o fa-lg"></i>
				<?php
					if($numNotifications > 0)
						echo '<span class="notification_badge" id="unread_notifications">' . $numNotifications . '</span>';
				?>
			</a>
			<a href="requests.php">
				<i class="fa fa-users fa-lg"></i>
				<?php
					if($numRequests > 0)
						echo '<span class="notification_badge" id="unread_requests">' . $numRequests . '</span>';
				?>
			</a>
			<a href="#">
				<i class="fa fa-cog fa-lg"></i>
			</a>
			<a href="includes/handlers/logout.php">
				<i class="fa fa-sign-out fa-lg"></i>
			</a>
		</nav>

		<div class="dropdown_data_window" style="height: 0px; border: none;"></div>
		<input type="hidden" id="dropdown_data_type" value="">

	</div>

	<script>

	$(document).ready(function() {

		let userLoggedIn = '<?php echo $userLoggedIn?>';

		$('.dropdown_data_window').scroll(function() {
			let innerHeight = $('.dropdown_data_window').innerHeight();
			let scrollTop = $('.dropdown_data_window').scrollTop();
			let page = $('.dropdown_data_window').find('.nextPageDropdownData').val();	//get the val og 'page' send from 'Post class- loadPostsFriends method'
			let noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();	//get the val og 'noMorePosts' send from 'Post class- loadPostsFriends method'

			if((scrollTop + innerHeight >= $('.dropdown_data_window')[0].scrollHeight) && (noMoreData == 'false')) {
				let pageName;
				let type = $('#dropdown_data_type').val();

				if(type == 'notification') {
					pageName = "ajax_load_notifications.php";
				} else if(type == 'message') {
					pageName = "ajax_load_messages.php";
				}

				let ajaxReq = $.ajax({
					url: "includes/handlers/" + pageName,
					type: "POST",
					data: "page=" + page + "&user=" + userLoggedIn,
					cache: false,

					success: function(response) {
						$('.dropdown_data_window').append(response);			
						
						$('.dropdown_data_window').find('.nextPageDropdownData').remove();	//removes current next page
						$('.dropdown_data_window').find('.noMoreDropdownData').remove();
					}
				});
			}	//end if

			return false;
		});		//end $(window).scroll(function()

	});

	</script>

	<!-- this div will be closed in index file -->
	<div class="wrapper">
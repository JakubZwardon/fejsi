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
	
	<input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_modal" value="Post Something">

</div>

<div class="profile_main_column column">
	<div class="posts_area"></div>
	<img id="loading" style="height: 40px" src="assets/images/icons/loading.gif" />
</div>

<!-- Modal -->
<div class="modal fade" id="post_modal" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Post something</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
	  </div>
	  
      <div class="modal-body">
		  <p>This will appear on the user's profile page and also their newsfeed for your 	friends to see!</p>

		  <form action="" class="profile_post" method="POST">
			  <div class="form-group">
				  <textarea class="form-control" name="post_body"></textarea>

				  <input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
				  <input type="hidden" name="user_to" value="<?php echo $username; ?>">
			  </div>
		  </form>
	  </div>
	  
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
      </div>
    </div>
  </div>
</div>

<script>
//infinite scrolling
let userLoggedIn = '<?php echo $userLoggedIn; ?>';
let profileUsername = '<?php echo$username; ?>';

$(document).ready(function() {
	
	$('#loading').show();

	//Original Ajax request for loading first posts
	$.ajax({
		url: "includes/handlers/ajax_load_profile_posts.php", 
		type: "POST",
		data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
		cache: false,

		success: function(data) {
			$('#loading').hide();
			$('.posts_area').html(data);			
		}
	});

	$(window).scroll(function() {
		let height = $('.posts_area').height();	//div contain posts
		let scrollTop = $(this).scrollTop();
		let page = $('.posts_area').find('.next_page').val();	//get the val og 'page' send from 'Post class- loadPostsFriends method'
		let noMorePosts = $('.posts_area').find('.no_more_posts').val();	//get the val og 'noMorePosts' send from 'Post class- loadPostsFriends method'

		if((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && (noMorePosts == 'false')) {
			$('#loading').show();

			//loading posts
			let ajaxReq = $.ajax({
				url: "includes/handlers/ajax_load_profile_posts.php",
				type: "POST",
				data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
				cache: false,

				success: function(response) {
					$('.posts_area').find('.next_page').remove();	//removes current next page
					$('.posts_area').find('.no_more_posts').remove();

					$('#loading').hide();
					$('.posts_area').append(response);					
				}
			});
		}	//end if

		return false;
	});		//end $(window).scroll(function()

});

</script>

<!-- closing div from header file -->
</div>
</body>

</html>
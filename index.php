<?php
include("includes/header.php");

if(isset($_POST['post'])) {

	$uploadOk = 1;
	$imageName = $_FILES['fileToUpload']['name'];
	$errorMessage = "";

	if($imageName != "") {
		$targetDir = "assets/images/posts/";
		$imageName = $targetDir . uniqid() . basename($imageName);
		$imageFileType =  pathinfo($imageName, PATHINFO_EXTENSION);

		if($_FILES['fileToUpload']['size'] > 10000000) {
			$errorMessage = "Sorry your file is too large!";
			$uploadOk = 0;
		}

		if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
			$errorMessage = "Sorry only jpeg, jpg, png files are allowed!";
			$uploadOk = 0;
		}

		if($uploadOk) {
			if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
				//image uploaded ok
			} else {
				//image did not upload
				$uploadOk = 0;
			}
		}
	}

	if($uploadOk) {
		$post = new Post($con, $userLoggedIn);
		$post->submitPost($_POST['post_text'], 'none', $imageName);
		header('Location: index.php');  //protect from rewrite dates to database when refresh page
	} else {
		echo "<div style='text-align:center;' class='alert alert-danger'>
				" . $errorMessage . " 
			</div>";
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

<div class="main_column column">
	<form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
		<input type="file" name="fileToUpload" id="fileToUpload">
		<textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
		<input type="submit" name="post" id="post_button" value="Post">
		<hr>		
	</form>

	<div class="posts_area"></div>
	<img id="loading" style="height: 40px" src="assets/images/icons/loading.gif" />

</div>

<div class="user_details column">
	<div class="trends">
		<?php
			$query = mysqli_query($con, "SELECT * FROM trends ORDER BY hits DESC LIMIT 10");

			echo "<h4>Popular</h4>";

			foreach($query as $row) {
				$word = $row['title'];
				$wordDot = strlen($word) >= 14 ? "..." : "";
				$trimmedWord = str_split($word, 14);
				$trimmedWord = $trimmedWord[0];

				echo "<div style='padding: 2px;'>";
				echo $trimmedWord . $wordDot;
				echo "<br></div>";

			}
		?>
	</div>
</div>

<script>

$(document).ready(function() {
	let userLoggedIn = '<?php echo $userLoggedIn?>';
	
	$('#loading').show();

	//Original Ajax request for loading first posts
	$.ajax({
		url: "includes/handlers/ajax_load_posts.php", 
		type: "POST",
		data: "page=1&userLoggedIn=" + userLoggedIn,
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

			let ajaxReq = $.ajax({
				url: "includes/handlers/ajax_load_posts.php",
				type: "POST",
				data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
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
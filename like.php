<!-- Support of 'like' mechanism for a given post -->

<html>
<head>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <style type="text/css">
        * {
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            background-color: #fff;
        }

        form {
            position: absolute;
            top: 0;
        }
        
    </style>
</head>
<body>


<?php

require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Notification.php");

//Redirect not logged user
if (isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE  username='$userLoggedIn'");
    $user = mysqli_fetch_array($user_details_query);
} else {
    header('Location: register.php');
}

//Get id of post
if (isset($_GET['post_id'])) {
    $postId = $_GET['post_id'];
}

$get_likes = mysqli_query($con, "SELECT * FROM posts WHERE id='$postId'");
$row = mysqli_fetch_array($get_likes);
$totalLikes = $row['likes'];    //get total likes of post

//user who get like, who wrote the post
$userLiked = $row['added_by'];

//get info about user who wrote the post
$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLiked'");
$row = mysqli_fetch_array($user_details_query);
//get number of all likes for the user who wrote the post
$totalUserLikes = $row['num_likes'];

//Like button operation
if(isset($_POST['like_button'])) {
    $totalLikes++;
    //updating the increased number of likes for a given post
    $query = mysqli_query($con, "UPDATE posts SET likes='$totalLikes' WHERE id='$postId'");

    $totalUserLikes++;
    //updating the increased number of likes for the user who wrote the post
    $user_likes = mysqli_query($con, "UPDATE users SET num_likes='$totalUserLikes' WHERE username='$userLiked'");

    //saving information that the logged in user likes the post
    $insert_user = mysqli_query($con, "INSERT INTO likes VALUES (NULL, '$userLoggedIn', '$postId')");

    //Insert notification
    if($userLiked != $userLoggedIn) {
        $notification = new Notification($con, $userLoggedIn);
        $notification->insertNotification($postId, $userLiked, 'like');
    }
}

//Unlike button operation
if(isset($_POST['unlike_button'])) {
    $totalLikes--;
    //updating the decreased number of likes for a given post
    $query = mysqli_query($con, "UPDATE posts SET likes='$totalLikes' WHERE id='$postId'");

    $totalUserLikes--;
    //updating the decreased number of likes for the user who wrote the post
    $user_likes = mysqli_query($con, "UPDATE users SET num_likes='$totalUserLikes' WHERE username='$userLiked'");

    //deleting information that the logged in user likes the post
    $insert_user = mysqli_query($con, "DELETE FROM likes WHERE username='$userLoggedIn' AND post_id='$postId'");
}

//check for previous likes for logged in user and a given post
$check_query = mysqli_query($con, "SELECT * FROM likes WHERE username='$userLoggedIn' AND post_id='$postId'");
$num_rows = mysqli_num_rows($check_query);

//creating the appropriate button and displaying the number of likes
if($num_rows > 0) {
    //create an unlike button
    echo '<form action="like.php?post_id=' . $postId . '" method="POST">
            <input type="submit" class="comment_like" name="unlike_button" value="Unlike">
            <div class="like_value">
                ' . $totalLikes . ' Likes
            </div>
        </form>';
} else {
    //create like button
    echo '<form action="like.php?post_id=' . $postId . '" method="POST">
            <input type="submit" class="comment_like" name="like_button" value="Like">
            <div class="like_value">
                ' . $totalLikes . ' Likes
            </div>
        </form>';
}
?>
</body>
</html>
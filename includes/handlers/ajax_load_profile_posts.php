<?php
//load profile posts

include("../../config/config.php");
include("../classes/User.php");
include("../classes/Post.php");

$limit = 10;    //num of posts to be loaded per call

$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadProfilePosts($_REQUEST, $limit);

?>
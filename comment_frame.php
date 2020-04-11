<?php

    require 'config/config.php';
    include("includes/classes/User.php");
    include("includes/classes/Post.php");

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
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <style type="text/css">
        * {
            font-size: small;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            background-color: #eff7f6;
        }
        
    </style>

    <script>
        //toggle comment section
        function toggle() {
            let element = document.getElementById("comment_section");
            debugger;

            if (element.style.display == "block")
                element.style.display = "none";
            else
                element.style.display = "block";

            debugger;
        }
    </script>
</head>

<body>
    <?php
    //Get id of post
    if (isset($_GET['post_id'])) {
        $postId = $_GET['post_id'];
    }

    $user_query = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id='$postId'");
    $row = mysqli_fetch_array($user_query);

    $postedTo = $row['added_by'];

    //prepare comment and put it to database
    if(isset($_POST['post_comment' . $postId])) {
        $postBody = $_POST['post_body'];
        $postBody = mysqli_escape_string($con, $postBody);
        $dateTimeNow = date("Y-m-d H:i:s");

        $insert_post = mysqli_query($con, "INSERT INTO comments VALUES(NULL, '$postBody', '$userLoggedIn', '$postedTo', '$dateTimeNow', 'no', '$postId')");
        echo "<p>Comment Posted! </p>";
    }

    ?>

    <form action="comment_frame.php?post_id=<?php echo $postId ?>" id="comment_form" name="post_comment<?php echo $postId; ?>" method="POST">
        <textarea name="post_body"></textarea>
        <input type="submit" name="post_comment<?php echo $postId; ?>" value="Post">
    </form>

    <?php 

    //load comments
    $getComments = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$postId' ORDER BY id ASC");
    $count = mysqli_num_rows($getComments);

    if($count != 0) {
        while($comment = mysqli_fetch_array($getComments)) {
            $commentBody = $comment['post_body'];
            $postedTo = $comment['posted_to'];
            $postedBy = $comment['posted_by'];
            $dateAdded = $comment['date_added'];
            $removed = $comment['removed'];

            //timeframe
            $dateTimeNow = date("Y-m-d H:i:s");
            $startDate = new DateTime($dateAdded);  //Time of post
            $endDate = new DateTime($dateTimeNow); //Current time
            $interval = $startDate->diff($endDate);

            if($interval->y >= 1) {
                if($interval->y == 1) {
                    $timeMessage = $interval->y . " year ago";  //1 year ago
                } else {
                    $timeMessage = $interval->y . " years ago";  //more then 1 year so wrote years
                }

            } elseif($interval->m >= 1) {
                if($interval->d == 0) {
                    $days = " ago";
                } elseif($interval->d == 1) {
                    $days = $interval->d . " day ago";
                } else {
                    $days = $interval->d . " days ago";
                }

                if($interval->m == 1) {
                    $timeMessage = $interval->m . " month" . $days;
                } else {
                    $timeMessage = $interval->m . " months" . $days;
                }

            } elseif($interval->d >= 1) {
                if($interval->d == 1) {
                    $timeMessage = "Yesterday";
                } else {
                    $timeMessage = $interval->d . " days ago";
                }

            } elseif ($interval->h >= 1) {
                if($interval->h == 1) {
                    $timeMessage = $interval->h . " hour ago";
                } else {
                    $timeMessage = $interval->h . " hours ago";
                }

            } elseif ($interval->i >= 1) {
                if($interval->i == 1) {
                    $timeMessage = $interval->i . " minute ago";
                } else {
                    $timeMessage = $interval->i . " minutes ago";
                }

            } else {
                if($interval->s < 30) {
                    $timeMessage = "Just now";
                } else {
                    $timeMessage = $interval->s . " seconds ago";
                }
            }

            $userObj = new User($con, $postedBy);

            ?>

            <div class="comment_section">
                <a href="<?php echo $postedBy ?>" target="_parent"><img src="<?php echo $userObj->getProfilePic(); ?>" title="<?php echo $postedBy ?>"></a>
                <a href="<?php echo $postedBy ?>" target="_parent"><b><?php echo$userObj->getFirstAndLastName(); ?></b></a>
                &nbsp;&nbsp;&nbsp;&nbsp; <?php echo $timeMessage . "<br />" . $commentBody ?>
                <hr>
            </div>

            <?php

        }
    } else {
        echo "<center><br /><br />No Comments to show!</center>";
    }

    ?>

    
    

</body>

</html>
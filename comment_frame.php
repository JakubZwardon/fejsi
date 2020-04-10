<html>

<head>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body>

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

    <script>
        //toggle comment section
        function toggle() {
            let element = document.getElementById("comment_section");

            if (element.style.display == "block")
                element.style.display = "none";
            else
                element.style.display = "block";
        }
    </script>

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

    <!-- Load comments -->

</body>

</html>
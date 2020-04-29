<?php
include("includes/header.php");

?>

<div class="main_column column" id="main_column">
    <h4>Friend Requests</h4>

    <?php
    
    //get friend requests to logged in user
    $query = mysqli_query($con, "SELECT * FROM friend_requests WHERE user_to='$userLoggedIn'");
    if(mysqli_num_rows($query) == 0) {
        echo "You have no friend requests at this time!";
    } else {
        //for all users who send friend request
        while($row = mysqli_fetch_array($query)) {
            //user who send friend request
            $userFrom = $row['user_from'];
            $userFromObj = new User($con, $userFrom);

            echo $userFromObj->getFirstAndLastName() . " sent you a friend request!";

            //friends array of user who send request
            $UserFromFriendArray = $userFromObj->getFriendArray();

            //Accept friend request
            if(isset($_POST['accept_request' . $userFrom])) {
                //Add profile owner user to logged in user friend array
                $addFriendQuery = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userFrom,') WHERE username='$userLoggedIn'");
                //Add logged in user to profile owner user friend array
                $addFriendQuery = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$userFrom'");

                //Delete friend request from friend_requests table
                $delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$userFrom'");

                echo "You are now friends with $userFromObj->getFirstAndLastName() !";
                header('Location: requests.php');
            }

            //Ignore friend request
            if(isset($_POST['ignore_request' . $userFrom])) {
                //Delete friend request from friend_requests table
                $delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$userFrom'");

                echo "Request from $userFromObj->getFirstAndLastName() ignored!";
                header('Location: requests.php');
            }

            ?>

            <form action="requests.php" method="POST">
                <input type="submit" name="accept_request<?php echo $userFrom ;?>" id="accept_button" value="Accept">
                <input type="submit" name="ignore_request<?php echo $userFrom ;?>" id="ignore_button" value="Ignore">
            </form>

            <?php
        }
    }

    ?>

</div>
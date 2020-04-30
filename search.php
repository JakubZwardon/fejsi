<?php
include("includes/header.php");

if(isset($_GET['q'])) {
    $query = $_GET['q'];
} else {
    $query = "";
}

if(isset($_GET['type'])) {
    $type = $_GET['type'];
} else {
    $type = "name";
}
?>

<div class="main_column column" id="main_column">
    <?php
        if($query == "") {
            echo "<p>You must enter something in the search box.</p>";
        } else {
            if($type == "username") {
                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
            } else {
                $names = explode(" ", $query);
                
                if(count($names) == 3) {
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[2]%') AND user_closed='no'");
                }
                else if(count($names) == 2) {
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed='no'");
                } else {
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed='no'");
                }
            }

            //Check if results were found
            if(mysqli_num_rows($usersReturnedQuery) == 0) {
                echo "<p>We can't find anyone with a " . $type . " like " . $query . ".</p>";
            } else {
                echo "<p>";
                echo mysqli_num_rows($usersReturnedQuery) . " results found: <br> <br>";
                echo "</p>";
            }

            echo "<p id='grey'>Try searching for:</p>";
            echo "<a href='search.php?q=" . $query . "&type=name'>Names</a>, <a href='search.php?q=" . $query . "&type=username'>Usernames</a><br><br><hr id='search_hr'>";

            while($row = mysqli_fetch_array($usersReturnedQuery)) {
                $userObj = new User($con, $user['username']);

                $button = "";
                $mutualFriends = "";

                if($user['username'] != $row['username']) {
                    //Generate button depending on friendship status
                    if($userObj->isFriend($row['username'])) {
                        $button = "<input type='submit' name='" . $row['username'] . "'class='danger' value='Remove Friend'>";
                    } else if($userObj->didReceiveRequest($row['username'])) {
                        $button = "<input type='submit' name='" . $row['username'] . "'class='warning' value='Respond to request'>";
                    } else if($userObj->didSendRequest($row['username'])) {
                        $button = "<input type='submit' name='" . $row['username'] . "'class='default' value='Request sent'>";
                    } else {
                        $button = "<input type='submit' name='" . $row['username'] . "'class='success' value='Add friend'>";
                    }

                    $mutualFriends = $userObj->getMutualFriends($row['username']) . " friends in common.";

                    //Button forms
                    if(isset($_POST[$row['username']])) {
                        if($userObj->isFriend($row['username'])) {
                            $userObj->removeFriend($row['username']);
                            header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                        } else if($userObj->didReceiveRequest($row['username'])) {
                            header('Location: requests.php');
                        } else if($userObj->didSendRequest($row['username'])) {

                        } else {
                            $userObj->sendRequest($row['username']);
                            header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                        }
                    }

                }

                echo "<div class='search_result'>
                        <div class='searchPageFriendButtons'>
                            <form action='' method='POST'>
                                " . $button . "
                                <br>
                            </form>
                        </div>

                        <div class='result_profile_pic'>
                            <a href='" . $row['username'] . "'><img src='" . $row['profile_pic'] . "' style='height: 100px;'></a>
                        </div>
                        
                        <a href='" . $row['username'] . "'>" . $row['first_name'] . " " . $row['last_name'] . "
                            <p id='grey'>" . $row['username'] . "</p>
                        </a>
                        <br>
                        " . $mutualFriends . " <br>
                    </div><hr id='search_hr'>";
            }
        }
    ?>
</div>
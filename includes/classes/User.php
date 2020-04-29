<?php

class User {
    private $user;
    private $con;

    public function __construct($con, $username) {
        $this->con = $con;
        $userDetailsQuery = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
        $this->user = mysqli_fetch_array($userDetailsQuery);
    }

    public function getUserName() {
        return $this->user['username'];
    }

    public function getNumPosts() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT num_posts FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['num_posts'];
    }

    public function getFirstAndLastName() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['first_name'] . " " . $row['last_name'];

        //return $this->user['first_name'] . " " . $this->user['last_name'];
    }

    public function getProfilePic() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT profile_pic FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['profile_pic'];
    }

    public function getFriendArray() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['friend_array'];
    }

    public function isClosed() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT user_closed FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);

        if($row['user_closed'] == "yes") {
            return true;
        } else {
            return false;
        }
    }

    public function isFriend($usernameToChceck) {
        $usernameComma = "," . $usernameToChceck . ",";
        //check if $usernameToChceck is in friend array or is the same as logged in user
        if((strstr($this->user['friend_array'], $usernameComma)) || ($usernameToChceck == $this->user['username'])) {
            return true;
        } else {
            return false;
        }
    }

    //is there friend requests?
    public function didReceiveRequest($userFrom) {
        $userTo = $this->user['username'];
        $check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$userTo' AND user_from='$userFrom'");

        if(mysqli_num_rows($check_request_query) > 0) {
            return true;
        } else {
            return false;
        }
    }

    //is there friend requests?
    public function didSendRequest($userTo) {
        $userFrom = $this->user['username'];
        $check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$userTo' AND user_from='$userFrom'");

        if(mysqli_num_rows($check_request_query) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function removeFriend($userToRemove) {
        $loggedInUser = $this->user['username'];

        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$userToRemove'");
        $row = mysqli_fetch_array($query);
        $friendArrayUsername = $row['friend_array'];    //friend array of user to remove from friends

        //delete user from logged in user friend array
        $newFriendArray = str_replace($userToRemove . ",", "", $this->user['friend_array']);
        $removeFriend = mysqli_query($this->con, "UPDATE users SET friend_array='$newFriendArray' WHERE username='$loggedInUser'");

        //delete logged in user from userToRemove friend array
        $newFriendArray = str_replace($this->user['username'] . ",", "", $friendArrayUsername);
        $removeFriend = mysqli_query($this->con, "UPDATE users SET friend_array='$newFriendArray' WHERE username='$userToRemove'");

    }

    public function sendRequest($userTo) {
        $loggedInUser = $this->user['username'];

        $query = mysqli_query($this->con, "INSERT INTO friend_requests VALUES(NULL, '$userTo', '$loggedInUser')");
    }

    public function getMutualFriends($userToCheck) {
        $mutualFriends = 0;
        $userArray = $this->user['friend_array'];

        $userArrayExplode = explode(",", $userArray);

        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$userToCheck'");
        $row = mysqli_fetch_array($query);
        $userToCheckArray = $row['friend_array'];
        $userToCheckArrayExplode = explode(",", $userToCheckArray);

        foreach($userArrayExplode as $i) {
            foreach($userToCheckArrayExplode as $j) {
                if($i == $j && $i != "") {
                    $mutualFriends++;
                }
            }
        }
        return $mutualFriends;
    }

    public function getNumberOfFriendRequests() {
        $userLoggedIn = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$userLoggedIn'");
        return mysqli_num_rows($query);
    }
}

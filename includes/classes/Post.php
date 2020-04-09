<?php

class Post {
    private $userObj;
    private $con;

    public function __construct($con, $username) {
        $this->con = $con;        
        $this->userObj = new User($con, $username);
    }

    public function submitPost($body, $userTo) {
        $body = strip_tags($body); //remove html tags
        $body = mysqli_real_escape_string($this->con, $body);

        $body = str_replace('\r\n', '<br />', $body);
        
        $checkEmpty = preg_replace('/\s+/', '', $body); //Deletes all spaces

        //check is empty post
        if($checkEmpty != '') {
            //Current date and time
            $dateAdded = date("Y-m-d H:i:s");
            $addedBy = $this->userObj->getUserName();

            //If user is on own profile, user_to is 'none'
            if($userTo == $addedBy) {
                $userTo = "none";
            }

            //Insert post
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES(NULL, '$body', '$addedBy', '$userTo', '$dateAdded', 'no', 'no', 0)");
            $returnedId = mysqli_insert_id($this->con);
            if (mysqli_error($this->con)) {
                echo mysqli_error($this->con); //print error when somethings go wrong
            }

            //insert notification

            //update post count for user
            $numPosts = $this->userObj->getNumPosts();
            $numPosts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$numPosts' WHERE username='$addedBy'");
            if (mysqli_error($this->con)) {
                echo mysqli_error($this->con); //print error when somethings go wrong
            }




        }
    }

    public function loadPostsFriends() {
        $str = "";
        $data = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

        while($row = mysqli_fetch_array($data)) {
            $id = $row['id'];
            $body = $row['body'];
            $addedBy = $row['added_by'];
            $dateTime = $row['date_added'];

            //prepare 'userTo' string so it can be included even if not posted to a user
            if($row['user_to'] == "none") {
                $userTo = "";
            } else {
                $userToObj = new User($con, $row['user_to']);
                $userToName = $userToObj->getFirstAndLastName();
                $userTo = "to <a href='" . $row['user_to'] . "'>" . $userToName . "</a>";
            }

            //check if user who posted, has their account closed
            $addedByObj = new User($this->con, $addedBy);
            if($addedByObj->isClosed()) {
                continue;
            }
            $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$addedBy'");
            $user_row = mysqli_fetch_array($user_details_query);
            $firstName = $user_row['first_name'];
            $lastName = $user_row['last_name'];
            $profilePic = $user_row['profile_pic'];

            //timeframe
            $dateTimeNow = date("Y-m-d H:i:s");
            $startDate = new DateTime($dateTime);  //Time of post
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

            $str .= "<div class='ststus_post'>
                        <div class='post_profile_pic'>
                            <img src='$profilePic' width='50px' />
                        </div>

                        <div class='posted_by' style='color:#acacac;'>
                            <a href='$addedBy'> $firstName $lastName </a> $userTo &nbsp;&nbsp;&nbsp;&nbsp;$timeMessage
                        </div>

                        <div id='post_body'>
                            $body
                            <br />
                        </div>

                    </div>";
        }

        echo $str;
    }
}

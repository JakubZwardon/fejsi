<?php
class Notification {
    private $userObj;
    private $con;

    public function __construct($con, $username)
    {
        $this->con = $con;
        $this->userObj = new User($con, $username);
    }

    public function getUnreadNumber() {
        $userLoggedIn = $this->userObj->getUserName();
        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE viewed='no' AND user_to ='$userLoggedIn'");
        return mysqli_num_rows($query);
    }

    public function insertNotification($postId, $userTo, $type) {
        $userLoggedIn = $this->userObj->getUserName();
        $userLoggedInName = $this->userObj->getFirstAndLastName();

        $dateTime = date("Y-m-d H:i:s");

        switch($type) {
            case 'comment':
                $message = $userLoggedInName . " commented on your post" ;
                break;
            case 'like':
                $message = $userLoggedInName . " liked your post" ;
                break;
            case 'profile_post':
                $message = $userLoggedInName . " posted on your profile" ;
                break;
            case 'comment_non_owner':
                $message = $userLoggedInName . " commented on a post you commented on" ;
                break;
            case 'profile_comment':
                $message = $userLoggedInName . " commented on your profile post" ;
                break;

        }

        $link = "post.php?id=" . $postId;

        $insertQuery = mysqli_query($this->con, "INSERT INTO notifications VALUES(NULL, '$userTo', '$userLoggedIn', '$message', '$link', '$dateTime', 'no', 'no')");

    }

    public function getLatestMessage($userLoggedIn, $otherUser) {
        $detailsArray = array();

        $query = mysqli_query($this->con, "SELECT body, user_to, date FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$otherUser') OR (user_to='$otherUser' AND user_from='$userLoggedIn') ORDER BY id DESC LIMIT 1");
        if (mysqli_error($this->con)) {
            echo "Error reading from database in getLatestMessage function! ";
            echo mysqli_error($this->con);
        }
        $row = mysqli_fetch_array($query);
        $sentBy = ($row['user_to'] == $userLoggedIn) ? "They said: " : "You said: ";

        //timeframe
        $dateTimeNow = date("Y-m-d H:i:s");
        $startDate = new DateTime($row['date']);  //Time of post
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

        array_push($detailsArray, $sentBy);
        array_push($detailsArray, $row['body']);
        array_push($detailsArray, $timeMessage);

        return $detailsArray;
    }

    public function getNotifications($request, $limit) {
        $page = $request['page'];
        $username = $this->userObj->getUserName();
        $returnString = "";

        if($page == 1)
            $start = 0;
        else
            $start = ($page - 1) * $limit;

        $setViewedQuery = mysqli_query($this->con, "UPDATE notifications SET viewed='yes' WHERE user_to='$username'");

        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to='$username' ORDER BY id DESC");
        if (mysqli_error($this->con)) {
            echo "Error reading from database in getNotifications function! ";
            echo mysqli_error($this->con);
        }

        if(mysqli_num_rows($query) == 0) {
            echo "You have no nitifications.";
            return;
        }

        $numIterations = 0; //Number of messages checked
        $count = 1; //Number of messages posted

        while($row = mysqli_fetch_array($query)) {
            if($numIterations++ < $start)
                continue;

            if($count > $limit)
                break;
            else
                $count++;

            $userFrom = $row['user_from'];

            $userDataQueru = mysqli_query($this->con, "SELECT * FROM users WHERE username='$userFrom'");
            $userData = mysqli_fetch_array($userDataQueru);



            
            //timeframe
            $dateTimeNow = date("Y-m-d H:i:s");
            $startDate = new DateTime($row['datetime']);  //Time of post
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




            // $opened = $row['opened'];
            // if($row = mysqli_fetch_array($isUnreadQuery))
                $style = ($row['opened'] == 'no') ? "background-color: #ddedff;" : "";
            // else
            //     $style = "";

            $returnString .= "<a href='" . $row['link'] . "'>
                                    <div class='resultDisplay resultDisplayNotifications' style='".$style."'>
                                        <div class='notificationsProfilePic'>
                                            <img src='" . $userData['profile_pic'] . "'>
                                        </div>
                                        <p class='timestamp_smaller' id='grey'>" . $timeMessage . "</p>" . $row['message'] . "
                                    </div>
                                </a>";
        }

        if($count > $limit)
            $returnString .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'>
                                <input type='hidden' class='noMoreDropdownData' value='false'>";
        else
            $returnString .= "<input type='hidden' class='noMoreDropdownData' value='true'>
                                <p style='text-align: center;'>No more notifications to load!</p>";
        
        return $returnString;
    }

}

?>
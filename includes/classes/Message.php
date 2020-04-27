<?php
class Message {
    private $userObj;
    private $con;

    public function __construct($con, $username)
    {
        $this->con = $con;
        $this->userObj = new User($con, $username);
    }

    public function getMostRecentUser() {
        $username = $this->userObj->getUserName();

        $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$username' OR user_from='$username' ORDER BY id DESC LIMIT 1");

        if(mysqli_num_rows($query) == 0) {
            return false;
        }

        $row = mysqli_fetch_array($query);
        $userTo = $row['user_to'];
        $userFrom = $row['user_from'];

        if($username != $userFrom) {
            return $userFrom;
        } else {
            return $userTo;
        }
    }

    public function sendMessage($userTo, $body, $date) {
        if($body != "") {
            $username = $this->userObj->getUserName();

            $query = mysqli_query($this->con, "INSERT INTO messages VALUES(NULL, '$userTo', '$username', '$body', '$date', 'no', 'no', 'no')");

            if (mysqli_error($this->con)) {
                echo "Error writing to database in sendMessage function! ";
                echo mysqli_error($this->con);
            }
        }
    }

    public function getMessages($otherUser) {
        $username = $this->userObj->getUserName();
        $data = "";     //final string which stores messages

        $markAsOpenedQuery = mysqli_query($this->con, "UPDATE messages SET opened='yes' WHERE user_to='$username' AND user_from='$otherUser'");
        if (mysqli_error($this->con)) {
            echo "Error writing to database in getMessages function! ";
            echo mysqli_error($this->con);
        }

        $getMessagesQuery = mysqli_query($this->con, "SELECT * FROM messages WHERE (user_to='$username' AND user_from='$otherUser') OR (user_to='$otherUser' AND user_from='$username')");
        if (mysqli_error($this->con)) {
            echo "Error reading from database in getMessages function! ";
            echo mysqli_error($this->con);
        }

        while($messagesRow = mysqli_fetch_array($getMessagesQuery)) {
            $userTo = $messagesRow['user_to'];
            $userFrom = $messagesRow['user_from'];
            $body = $messagesRow['body'];

            $div_top = ($userTo == $username) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";

            $data .= $div_top . $body . "</div><br><br>";
        }

        return $data;
    }

    public function getConversations() {
        $username = $this->userObj->getUserName();
        $returnString = "";
        $convos = array();

        $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$username' OR user_from='$username' ORDER BY id DESC");
        if (mysqli_error($this->con)) {
            echo "Error reading from database in getConversations function! ";
            echo mysqli_error($this->con);
        }

        while($row = mysqli_fetch_array($query)) {
            $userToPush = ($row['user_to'] != $username) ? $row['user_to'] : $row['user_from'];

            if(!in_array($userToPush, $convos)) {
                array_push($convos, $userToPush);
            }
        }

        foreach($convos as $otherUser) {
            $userFoundObj = new User($this->con, $otherUser);
            $latestMessageDetails = $this->getLatestMessage($username, $otherUser);

            $dots = (strlen($latestMessageDetails[1]) >= 12) ? "..." : "";
            $split = str_split($latestMessageDetails[1], 12);
            $split = $split[0] . $dots;

            $returnString .= "<a href='messages.php?u=$otherUser'><div class='user_found_messages'>
                                <img src='" . $userFoundObj->getProfilePic() . "' style='border-radius: 5px;'>
                                " . $userFoundObj->getFirstAndLastName() . "
                                <span class='timestamp_smaller' id='grey'>" . $latestMessageDetails[2] . "</span>
                                <p id='grey' style='margin: 0;'>" . $latestMessageDetails[0] . $split . " </p>
                                </div>
                                </a>";
        }
        
        return $returnString;
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

    public function getConvosDropdown($request, $limit) {
        $page = $request['page'];
        $username = $this->userObj->getUserName();
        $returnString = "";
        $convos = array();

        if($page == 1)
            $start = 0;
        else
            $start = ($page - 1) * $limit;

        $setViewedQuery = mysqli_query($this->con, "UPDATE messages SET viewed='yes' WHERE user_to='$username'");

        $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$username' OR user_from='$username' ORDER BY id DESC");
        if (mysqli_error($this->con)) {
            echo "Error reading from database in getConvosDropdown function! ";
            echo mysqli_error($this->con);
        }

        while($row = mysqli_fetch_array($query)) {
            $userToPush = ($row['user_to'] != $username) ? $row['user_to'] : $row['user_from'];

            if(!in_array($userToPush, $convos)) {
                array_push($convos, $userToPush);
            }
        }

        $numIterations = 0; //Number of messages checked
        $count = 1; //Number of messages posted

        foreach($convos as $otherUser) {
            if($numIterations++ < $start)
                continue;

            if($count > $limit)
                break;
            else
                $count++;

            $isUnreadQuery = mysqli_query($this->con, "SELECT opened FROM messages WHERE user_to='$username' AND user_from='$otherUser' ORDER BY id DESC");
            if (mysqli_error($this->con)) {
                echo "Error reading from database in getConvosDropdown function! ";
                echo mysqli_error($this->con);
            }
            if($row = mysqli_fetch_array($isUnreadQuery))
                $style = ($row['opened'] == 'no') ? "background-color: #ddedff;" : "";
            else
                $style = "";

            $userFoundObj = new User($this->con, $otherUser);
            $latestMessageDetails = $this->getLatestMessage($username, $otherUser);

            $dots = (strlen($latestMessageDetails[1]) >= 12) ? "..." : "";
            $split = str_split($latestMessageDetails[1], 12);
            $split = $split[0] . $dots;

            $returnString .= "<a href='messages.php?u=$otherUser'>
                                <div class='user_found_messages' style='" . $style . "'>
                                <img src='" . $userFoundObj->getProfilePic() . "' style='border-radius: 5px;'>
                                " . $userFoundObj->getFirstAndLastName() . "
                                <span class='timestamp_smaller' id='grey'>" . $latestMessageDetails[2] . "</span>
                                <p id='grey' style='margin: 0;'>" . $latestMessageDetails[0] . $split . " </p>
                                </div>
                                </a>";
        }

        if($count > $limit)
            $returnString .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'>
                                <input type='hidden' class='noMoreDropdownData' value='false'>";
        else
            $returnString .= "<input type='hidden' class='noMoreDropdownData' value='true'>
                                <p style='text-align: center;'>No more messages to load!</p>";
        
        return $returnString;
    }

    public function getUnreadNumber() {
        $userLoggedIn = $this->userObj->getUserName();
        $query = mysqli_query($this->con, "SELECT * FROM messages WHERE viewed='no' AND user_to ='$userLoggedIn'");
        return mysqli_num_rows($query);
    }
}



?>
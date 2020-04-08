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
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES(NULL, '$body', '$addedBy', '$userTo', '$dateAdded', 'non', 'non', 0)");
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
}

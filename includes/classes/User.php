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
}

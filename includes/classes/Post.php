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

            $bodyArray = preg_split("/\s+/", $body);
            foreach($bodyArray as $key => $value) {
                if(strpos($value, "www.youtube.com/watch?v=") !== false) {

                    $link = preg_split("!&!", $value);
                    $value = preg_replace("!watch\?v=!", "embed/", $link[0]);
                    $value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value . "\'></iframe><br>";
                    $bodyArray[$key] = $value;
                }
            }
            $body = implode(" ", $bodyArray);

            //Current date and time
            $dateAdded = date("Y-m-d H:i:s");
            $addedBy = $this->userObj->getUserName();

            //If user is on own profile, user_to is 'none'
            if($userTo == $addedBy) {
                $userTo = "none";
            }

            //Insert post
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES(NULL, '$body', '$addedBy', '$userTo', '$dateAdded', 'no', 'no', 0, 'no')");
            $returnedId = mysqli_insert_id($this->con);
            if (mysqli_error($this->con)) {
                echo mysqli_error($this->con); //print error when somethings go wrong
            }

            //insert notification
            if($userTo != 'none') {
                $notification = new Notification($this->con, $addedBy);
                $notification->insertNotification($returnedId, $userTo, 'profile_post');
            }

            //update post count for user
            $numPosts = $this->userObj->getNumPosts();
            $numPosts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$numPosts' WHERE username='$addedBy'");
            if (mysqli_error($this->con)) {
                echo mysqli_error($this->con); //print error when somethings go wrong
            }
            
            //list of words for which we do not count trends
            $stopWords = "a co coś czymś i jakoś kimś o po pod przed w za";

            $stopWords = preg_split("/[\s,]+/", $stopWords);

            $noPunctuation = str_replace('<br />', ' ', $body);
            $noPunctuation = preg_replace("/[^ąĄćĆęĘłŁóÓśŚżŻźŹa-zA-Z 0-9]+/", "", $noPunctuation);

            if((strpos($noPunctuation, "height") === false) && (strpos($noPunctuation, "width") === false) && (strpos($noPunctuation, "http") === false)) {
                $noPunctuation = preg_split("/[\s,]+/", $noPunctuation);

                foreach($stopWords as $value) {
                    foreach($noPunctuation as $key => $value2) {
                        if(strtolower($value) == strtolower($value2)) {
                            $noPunctuation[$key] = "";
                        }
                    }
                }

                foreach($noPunctuation as $value) {
                    $this->calculateTrend(ucfirst($value));
                }
            }
        }
    }

    public function calculateTrend($term) {
        if($term != "") {
            $query = mysqli_query($this->con, "SELECT * FROM trends WHERE title='$term'");

            if(mysqli_num_rows($query) == 0) {
                $insert_query = mysqli_query($this->con, "INSERT INTO trends(title, hits) VALUES('$term', '1')");
            } else {
                $insert_query = mysqli_query($this->con, "UPDATE trends SET hits=hits+1 WHERE title='$term'");
            }
        }

        
    }

    public function loadPostsFriends($data, $limit) {
        
        $page = $data['page'];
        $userLoggedIn = $this->userObj->getUserName();

        if($page == 1) {
            $start = 0;
        } else {
            $start = ($page - 1) * $limit;
        }

        $str = "";
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

        if(mysqli_num_rows($data_query) > 0) {

            $numIterations = 0;
            $count = 1;

            while($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $addedBy = $row['added_by'];
                $dateTime = $row['date_added'];

                //prepare 'userTo' string so it can be included even if not posted to a user
                if($row['user_to'] == "none") {
                    $userTo = "";
                } else {
                    $userToObj = new User($this->con, $row['user_to']);
                    $userToName = $userToObj->getFirstAndLastName();
                    $userTo = "to <a href='" . $row['user_to'] . "'>" . $userToName . "</a>";
                }

                //check if user who posted, has their account closed
                $addedByObj = new User($this->con, $addedBy);
                if($addedByObj->isClosed()) {
                    continue;
                }

                //Create new object for logged in user
                $userLoggedInObj = new User($this->con, $userLoggedIn);
                //check if post is from the friend or owner
                //true proces, false skip this post and go to another without showing it
                if(!$userLoggedInObj->isFriend($addedBy)) {
                    continue;
                }

                //Search place to start load posts
                if($numIterations++ < $start) {
                    continue;
                }

                //once 10 posts have been loaded, break
                if($count > $limit) {
                    break;
                } else {
                    $count++;
                }

                //Only owner of post may delete it
                if($userLoggedIn == $addedBy) {
                    $deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                } else {
                    $deleteButton = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$addedBy'");
                $user_row = mysqli_fetch_array($user_details_query);
                $firstName = $user_row['first_name'];
                $lastName = $user_row['last_name'];
                $profilePic = $user_row['profile_pic'];

                ?>

                <script>
                    function toggle<?php echo $id; ?>() {
                        let target = $(event.target);

                        if(!target.is("a")) {
                            let element = document.getElementById("toggle_comment<?php echo$id; ?>");

                            if (element.style.display == "block")
                                element.style.display = "none";
                            else
                                element.style.display = "block";
                        }
                    }
                </script>

                <?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

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

                $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                            <div class='post_profile_pic'>
                                <img src='$profilePic' width='50px' />
                            </div>

                            <div class='posted_by' style='color:#acacac;'>
                                <a href='$addedBy'>$firstName $lastName </a> $userTo &nbsp;&nbsp;&nbsp;&nbsp;$timeMessage
                                $deleteButton
                            </div>

                            <div id='post_body'>
                                $body
                                <br />
                                <br />
                                <br />
                            </div>

                            <div class='newsfeedPostOptions'>
                                Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                            </div>

                        </div>
                        <div class='post_comment' id='toggle_comment$id' style='display: none;'>
                            <iframe src='comment_frame.php?post_id=$id' id='comment_iframe'></iframe>
                        </div>
                        <hr />";                              
                ?>

                <script>
                    //When click on post delete_button
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').click(function() {                            
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {                                                            
                                // $.post("includes/form_handlers/delete_post.php", {result:result, post_id: <?php echo $id; ?>}).done(function() {if(result) location.reload();});
                                // $.post("includes/form_handlers/delete_post.php", {result:result, post_id: <?php echo $id; ?>}, function() {if(result) location.reload();});
                                $.post("includes/form_handlers/delete_post.php?post_id= <?php echo $id; ?>", {result:result}, function() {if(result) location.reload();});                                
                            });
                        });
                    });                    
                </script>

                <?php                        
            }

            if($count > $limit) {
                $str .= "<input type='hidden' class='next_page' value='" . ($page + 1) . "'>
                            <input type='hidden' class='no_more_posts' value='false'>";
            } else {
                $str .= "<input type='hidden' class='no_more_posts' value='true'>
                            <p style='text-align: center;'> No more posts to show!</p>";
            }

        }

        echo $str;
    }

    public function loadProfilePosts($data, $limit) {
        
        $page = $data['page'];
        $profileUsername = $data['profileUsername'];
        $userLoggedIn = $this->userObj->getUserName();

        if($page == 1) {
            $start = 0;
        } else {
            $start = ($page - 1) * $limit;
        }

        $str = "";  //String to return
        //only the profile owner's posts and not those written to someone and normally not marked as removed
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUsername' AND user_to='none') OR user_to='$profileUsername') ORDER BY id DESC");

        if(mysqli_num_rows($data_query) > 0) {

            $numIterations = 0;
            $count = 1;

            while($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $addedBy = $row['added_by'];
                $dateTime = $row['date_added'];                

                //Search place to start load posts
                if($numIterations++ < $start) {
                    continue;
                }

                //once 10 posts have been loaded, break
                if($count > $limit) {
                    break;
                } else {
                    $count++;
                }

                //Only owner of post may delete it
                if($userLoggedIn == $addedBy) {
                    $deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                } else {
                    $deleteButton = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$addedBy'");
                $user_row = mysqli_fetch_array($user_details_query);
                $firstName = $user_row['first_name'];
                $lastName = $user_row['last_name'];
                $profilePic = $user_row['profile_pic'];

                ?>

                <script>
                    function toggle<?php echo $id; ?>() {
                        let target = $(event.target);

                        if(!target.is("a")) {
                            let element = document.getElementById("toggle_comment<?php echo$id; ?>");

                            if (element.style.display == "block")
                                element.style.display = "none";
                            else
                                element.style.display = "block";
                        }
                    }
                </script>

                <?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

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

                $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                            <div class='post_profile_pic'>
                                <img src='$profilePic' width='50px' />
                            </div>

                            <div class='posted_by' style='color:#acacac;'>
                                <a href='$addedBy'>$firstName $lastName </a> &nbsp;&nbsp;&nbsp;&nbsp;$timeMessage
                                $deleteButton
                            </div>

                            <div id='post_body'>
                                $body
                                <br />
                                <br />
                                <br />
                            </div>

                            <div class='newsfeedPostOptions'>
                                Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                            </div>

                        </div>
                        <div class='post_comment' id='toggle_comment$id' style='display: none;'>
                            <iframe src='comment_frame.php?post_id=$id' id='comment_iframe'></iframe>
                        </div>
                        <hr />";                              
                ?>

                <script>
                    //When click on post delete_button
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').click(function() {                            
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {                                                            
                                // $.post("includes/form_handlers/delete_post.php", {result:result, post_id: <?php echo $id; ?>}).done(function() {if(result) location.reload();});
                                // $.post("includes/form_handlers/delete_post.php", {result:result, post_id: <?php echo $id; ?>}, function() {if(result) location.reload();});
                                $.post("includes/form_handlers/delete_post.php?post_id= <?php echo $id; ?>", {result:result}, function() {if(result) location.reload();});                                
                            });
                        });
                    });                    
                </script>

                <?php                        
            }

            if($count > $limit) {
                $str .= "<input type='hidden' class='next_page' value='" . ($page + 1) . "'>
                            <input type='hidden' class='no_more_posts' value='false'>";
            } else {
                $str .= "<input type='hidden' class='no_more_posts' value='true'>
                            <p style='text-align: center;'> No more posts to show!</p>";
            }

        }

        echo $str;
    }

    public function getSinglePost($postId) {
        $userLoggedIn = $this->userObj->getUserName();
        $str = "";

        $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$postId'");

        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$postId'");

        if(mysqli_num_rows($data_query) > 0) {
            $row = mysqli_fetch_array($data_query);
            $id = $row['id'];
            $body = $row['body'];
            $addedBy = $row['added_by'];
            $dateTime = $row['date_added'];

            //prepare 'userTo' string so it can be included even if not posted to a user
            if($row['user_to'] == "none") {
                $userTo = "";
            } else {
                $userToObj = new User($this->con, $row['user_to']);
                $userToName = $userToObj->getFirstAndLastName();
                $userTo = "to <a href='" . $row['user_to'] . "'>" . $userToName . "</a>";
            }

            //check if user who posted, has their account closed
            $addedByObj = new User($this->con, $addedBy);
            if($addedByObj->isClosed()) {
                return;
            }

            //Create new object for logged in user
            $userLoggedInObj = new User($this->con, $userLoggedIn);
            //check if post is from the friend or owner
            //true proces, false skip this post and go to another without showing it
            if(!$userLoggedInObj->isFriend($addedBy)) {
                echo "<p>You cannot see this post because you are not friends with this user.</p>";
                return;
            }

            //Only owner of post may delete it
            if($userLoggedIn == $addedBy) {
                $deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
            } else {
                $deleteButton = "";
            }

            $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$addedBy'");
            $user_row = mysqli_fetch_array($user_details_query);
            $firstName = $user_row['first_name'];
            $lastName = $user_row['last_name'];
            $profilePic = $user_row['profile_pic'];

            ?>

            <script>
                function toggle<?php echo $id; ?>() {
                    let target = $(event.target);

                    if(!target.is("a")) {
                        let element = document.getElementById("toggle_comment<?php echo$id; ?>");

                        if (element.style.display == "block")
                            element.style.display = "none";
                        else
                            element.style.display = "block";
                    }
                }
            </script>

            <?php

            $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
            $comments_check_num = mysqli_num_rows($comments_check);

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

            $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                        <div class='post_profile_pic'>
                            <img src='$profilePic' width='50px' />
                        </div>

                        <div class='posted_by' style='color:#acacac;'>
                            <a href='$addedBy'>$firstName $lastName </a> $userTo &nbsp;&nbsp;&nbsp;&nbsp;$timeMessage
                            $deleteButton
                        </div>

                        <div id='post_body'>
                            $body
                            <br />
                            <br />
                            <br />
                        </div>

                        <div class='newsfeedPostOptions'>
                            Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                            <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                        </div>

                    </div>
                    <div class='post_comment' id='toggle_comment$id' style='display: none;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe'></iframe>
                    </div>
                    <hr />";                              
            ?>

            <script>
                //When click on post delete_button
                $(document).ready(function() {
                    $('#post<?php echo $id; ?>').click(function() {                            
                        bootbox.confirm("Are you sure you want to delete this post?", function(result) {                                                            
                            // $.post("includes/form_handlers/delete_post.php", {result:result, post_id: <?php echo $id; ?>}).done(function() {if(result) location.reload();});
                            // $.post("includes/form_handlers/delete_post.php", {result:result, post_id: <?php echo $id; ?>}, function() {if(result) location.reload();});
                            $.post("includes/form_handlers/delete_post.php?post_id= <?php echo $id; ?>", {result:result}, function() {if(result) location.reload();});                                
                        });
                    });
                });                    
            </script>

            <?php                        
        
        } else {
            echo "<p>No post found. If you clicked link, it may be broken.</p>";
            return;
        }

        echo $str;
    }
}

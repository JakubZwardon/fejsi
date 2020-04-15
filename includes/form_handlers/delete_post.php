<?php
require '../../config/config.php';

//get id of post
if(isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
}

//if result is set on true mark post as 'deleted'
if(isset($_POST['result'])) {
    if($_POST['result'] == 'true') {
        $query = mysqli_query($con, "UPDATE posts SET deleted='yes' WHERE id='$post_id'");
    }
}

?>
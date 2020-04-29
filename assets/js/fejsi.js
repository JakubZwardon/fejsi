$(document).ready(function() {
    let searchTextInputFoldWidth;
    let searchTextInputExpandWidth = '250px';

    //expand search field when clicked
    $('#search_text_input').focus(function() {
        if(window.matchMedia("(min-width: 800px)").matches) {
            searchTextInputFoldWidth = $(this).width();
            $(this).animate({width: searchTextInputExpandWidth}, 500);
        }
    });

    //fold search field when clicked
    $('#search_text_input').focusout(function() {
        if(window.matchMedia("(min-width: 800px)").matches) {
            $(this).animate({width: searchTextInputFoldWidth}, 500);
            $('#search_text_input').val("");
        }
    });

    //submit the form when click on img
    $('.button_holder').on('click', function() {
        document.search_form.submit();
    });

    //Button for profile post
    $('#submit_profile_post').click(function() {
        $.ajax({
            type: "POST",
            url: "includes/handlers/ajax_submit_profile_post.php",
            // dates from form class='profile_post'
            data: $('form.profile_post').serialize(),
            success: function(msg) {
                $("#post_modal").modal('hide');
                location.reload();
            },
            error: function() {
                alert('Failure');
            }
        });
        return false;
    });
});

$(document).click(function(e) {
    if(e.target.class != "search_results" && e.target.id != "search_text_input") {
        $(".search_results").html("");
        $('.search_results_footer').html("");
        $('.search_results_footer').toggleClass("search_results_footer_empty");
        $('.search_results_footer').toggleClass("search_results_footer");
    }

    if(e.target.class != "dropdown_data_window") {
        $(".dropdown_data_window").html("");
        $(".dropdown_data_window").css({"padding":"0px", "height":"0px", "border":"none"});
    }
});


function getUsers(value, user) {
    $.post("includes/handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data) {
        
        $(".results").html(data);
    });
}

function getDropdownData(user, type) {
    if($(".dropdown_data_window").css("height") == "0px") {
        let pageName;

        if(type == 'notification') {
            pageName = "ajax_load_notifications.php";
            $("span").remove("#unread_notifications");
        } else if(type == 'message') {
            pageName = "ajax_load_messages.php";
            $("span").remove("#unread_messages");
        }

        let ajaxreq = $.ajax({
            url: "includes/handlers/" + pageName,
            type: "POST",
            data: "page=1&user=" + user,
            cache: false,
            success: function(response) {
                $(".dropdown_data_window").html(response);
                $(".dropdown_data_window").css({"padding" : "0px", "height" : "280px", "border" : "1px solid #dadada"});
                $("#dropdown_data_type").val(type);
            }
        });
    } else {
        $(".dropdown_data_window").html("");
        $(".dropdown_data_window").css({"padding" : "0px", "height" : "0px", "border" : "none"});
    }
}

function getLiveSearchUsers(value, user) {
    $.post("includes/handlers/ajax_search.php", {query:value, userLoggedIn:user}, function(data) {
        if($(".search_results_footer_empty")) {
            $(".search_results_footer_empty").toggleClass("search_results_footer");
            $(".search_results_footer_empty").toggleClass("search_results_footer_empty");
        }
        $('.search_results').html(data);
        $('.search_results_footer').html("<a href='search.php?q=" + value + "'>See All Results</a>");

        if(data == "") {
            $('.search_results_footer').html("");
            $('.search_results_footer').toggleClass("search_results_footer_empty");
            $('.search_results_footer').toggleClass("search_results_footer");
        }
    });
}
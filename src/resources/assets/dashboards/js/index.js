let join_randomGame = $("#join_randomGame");
let join_gameWithId = $("#join_gameWithId");
let join_createGame = $("#join_createGame");
let join_submitWord = $("#join_submitWord");
let profile_login = $(".loginForm");
let profile_update = $(".updateProfile");
let profile_logOut = $("#profile_logOut_button");
let profile_signup = $(".signupForm");
let rules_content = $("#rules_content");
let rules_previous = $("#rules_previous");
let rules_next = $("#rules_next");
let rules_currentPage = $("#rules_currentPage");
let join_gameWithId_button = join_gameWithId.find("button");
let join_gameWithId_input = join_gameWithId.find("input");

$(".loginForm input").val("");
$(".signupForm input").val("");

profile_login.find("button").click(function () {
    profile_login.find("form").submit();
});

profile_signup.find("button").click(function () {
    profile_signup.find("form").submit();
});

profile_update.find("button").click(function () {
    profile_update.find("form").submit();
});

profile_logOut.click(function () {
    window.location.href = "/logout";
});

join_randomGame.find("button").click(function () {
    window.location.href = "/join";
});

join_gameWithId_button.click(function () {
    let val = join_gameWithId_input.val();
    if(val != ""){
        window.location.href = "/play/"+join_gameWithId.find("input").val();
    }else{
        alert("TODO : error message for empty value");
    }
});

let currentPage = 1;

let display_page = function(){
    rules_content.find("div").hide();
    rules_content.find('div[page="'+currentPage+'"]').show();
    rules_currentPage.text("Page "+currentPage+" / "+rules_content.attr('max-page'));
    if(currentPage == 1) {
        rules_previous.attr("disabled", true).addClass("is-disabled");
    }else{
        rules_previous.attr("disabled", false).removeClass("is-disabled");
    }
    if(currentPage == rules_content.attr('max-page')){
        rules_next.attr("disabled", true).addClass("is-disabled");
    }else{
        rules_next.attr("disabled", false).removeClass("is-disabled");
    }
}


let displayJoinButton = function(){
    if(join_gameWithId_input.val().length == 6){
        join_gameWithId_button.addClass("is-primary").removeClass("is-disabled").attr("disabled",false);
    }else{
        join_gameWithId_button.removeClass("is-primary").addClass("is-disabled").attr("disabled",true);
    }
}
join_gameWithId_input.keyup(function (e) {
    displayJoinButton();
});


let maxHeight = 0;
rules_content.find("div").each(function (index, element) {
    maxHeight = $(element).innerHeight() > maxHeight ? $(element).innerHeight() : maxHeight;
});
rules_content.find("div").each(function (index, element) {
    $(element).innerHeight(maxHeight);
});

displayJoinButton();
display_page();

join_createGame.find("button").click(function () {
    join_createGame.find("form").submit();
});

join_submitWord.find("button").click(function () {
    console.log("TODO : submit word");
});

rules_previous.click(function () {
    currentPage--;
    display_page();
});

rules_next.click(function () {
    currentPage++;
    display_page();
});
let join_randomGame;
let join_gameWithId;
let join_createGame;
let join_submitWord;
let profile_login = $(".loginForm");
let profile_logOut = $("#profile_logOut_button");
let profile_signup = $(".signupForm");
let profile_logout;
let profile_update;

$(".loginForm input").val("");
$(".signupForm input").val("");

profile_login.find("button").click(function () {
    profile_login.find("form").submit();
});

profile_signup.find("button").click(function () {
    profile_signup.find("form").submit();
});

profile_logOut.click(function () {
    window.location.href = "/logout";
});

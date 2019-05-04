
let profile_logOut = $("#profile_logOut_button");
let profile_index = $("#profile_index_button");

profile_logOut.click(function () {
    window.location.href = "/logout";
});

profile_index.click(function () {
    window.location.href = "/";
});
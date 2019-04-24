let e = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':6001'
});

e.channel('game-'+gameId)
    .listen('PostCreatedEvent', function (e) {
        console.log('PostCreatedEvent', e);
    })
    .listen('NewChatEvent', function (e) {
        let isCurrentUser = e.userId == currentUserId;


        let lastMessage = $("#chat-messages").find("section:last");
        let insertedInLast = false;
        if(lastMessage.length){
            if($($(lastMessage).get(0)).attr("msg-userId") == e.userId){
                $($(lastMessage).get(0)).find("p").append("<br/>"+e.message);
                insertedInLast = true;
            }
        }

        if(!insertedInLast) {
            let i = $("<i>")
                .addClass("smallMargin")
                .append(
                    $("<img>")
                        .addClass("nes-avatar is-rounded pixelated")
                        .attr("src", "https://www.gravatar.com/avatar?s=15")
                )
                .append("<br/>" + e.userName);
            let div = $("<div>")
                .addClass("nes-balloon smallPadding from-" + (isCurrentUser ? "right" : "left"))
                .append(
                    $("<p>").text(e.message)
                );
            $("#chat-messages").append(
                $("<section>")
                    .addClass("message smallMargin -" + (isCurrentUser ? "right" : "left"))
                    .attr("msg-userId", e.userId)
                    .append(isCurrentUser ? div : i)
                    .append(isCurrentUser ? i : div)
            );
        }

        if($("#chat-messages").outerHeight() + $("#chat-input-section").outerHeight() + 20 > $(".chat").innerHeight()){
            $("#chat-messages").outerHeight($(".chat").innerHeight() - $("#chat-input-section").outerHeight() - 30);
        }
        $("#chat-messages").scrollTop($("#chat-messages")[0].scrollHeight);
    });

let sendChat = function(){
    $("#chat-input").attr("disabled", true);
    $.ajax({
        type: 'POST',
        url: '/chat/'+gameId,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            message: $("#chat-input").val()
        },
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            $("#chat-input").attr("disabled", false);
            $("#chat-input").val("");
            $("#chat-input").focus();
        },
        error: function(html, status) {
            console.log(html);
            $("#chat-input").attr("disabled", false);
            $("#chat-input").focus();
        }
    });
};

$("#chat-btn").click(sendChat);

$('#chat-input').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        sendChat();
    }
});

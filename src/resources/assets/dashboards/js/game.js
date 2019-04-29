let e = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':6001',
    authEndpoint: "/broadcasting/auth"
});


let chat_input = $('#chat-input');
let gameSection = $("#game");
let gameBegin = $("#game-begin");
let playersHost = $("#players-host");
let playersChooser = $("#players-chooser");
let gameWord_display = $("#game-word_display");
let gameWord_current = $("#game-word_current");
let gameWord_currentWords = $("#game-word_current_words");
let gameWord_helperInput = $("#game-word_helperInput");
let gameWord_chooserInput = $("#game-word_chooserInput");
let gameWord_timer = $("#game-timer");
let gameWord_helperInput_button = gameWord_helperInput.find("button");
let gameWord_helperInput_input = gameWord_helperInput.find("input");
let gameWord_chooserInput_button = gameWord_chooserInput.find("button:not(.is-error)");
let gameWord_chooserInput_buttonPass = gameWord_chooserInput.find("button.is-error");
let gameWord_chooserInput_input = gameWord_chooserInput.find("input");
let gameBegin_dots = gameBegin.find("span.dot");
let gameBegin_progress = gameBegin.find("progress");
let gameBegin_interval;
let gameWord_timer_interval;
let gameBegin_dotCount;
let gameBegin_button = gameBegin.find("button");

let isHost = function(){
    return playersHost.attr("host-userId") == currentUserId;
};

let isChooser = function(){
    return playersChooser.attr("chooser-userId") == currentUserId;
};

let addUser = function(section, user){
    $("#"+section+"-list").append(
        $("<section>")
            .attr("profile-userId", user.id)
            .addClass("nes-container is-dark member-card")
            .append(
                $("<div>")
                    .addClass("avatar")
                    .append(
                        $("<img>")
                            .attr("src","/uploads/users/"+user.image)
                    )
            )
            .append(
                $("<div>")
                    .addClass("profile")
                    .append(
                        $("<h4>")
                            .addClass("name")
                            .text(user.name)
                    )
                    .append(
                        $("<p>")
                            .text(user.desc)
                    )
            )
    );
    update_nbPlayers();
};

let sendChat = function(){
    chat_input.attr("disabled", true);
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
            chat_input.attr("disabled", false);
            chat_input.val("");
            chat_input.focus();
        },
        error: function(html, status) {
            console.log(html);
            chat_input.attr("disabled", false);
            chat_input.focus();
        }
    });
};

$("#chat-btn").click(sendChat);

$("#btn-exitGame").click(function () {
    window.location.href = "/";
});

chat_input.keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        sendChat();
    }
});

let update_nbPlayers = function(){
    let nbPlayers = $("#players-list").find("section").length;
    gameBegin_progress.attr("value", nbPlayers);
    gameBegin_button.text(nbPlayers+"/7 players");
    if(nbPlayers < 2 || !isHost()){ // TODO -> 3 players min
        gameBegin_button.addClass("is-disabled").removeClass("is-primary");
    }else{
        gameBegin_button.removeClass("is-disabled").addClass("is-primary");
    }
};

gameBegin_button.click(function () {
    if(isHost() && !gameBegin_button.hasClass('is-disabled')) {
        gameBegin_button.attr("disabled", true);
        gameBegin_button.addClass("is-disabled");
        gameBegin_button.text("Game will start soon !");
        $.ajax({
            type: 'POST',
            url: window.location.pathname + '/start',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function (data) {
                //console.log(data);
                gameBegin_button.text("Game is starting !");
                gameBegin_button.attr("disabled", true);
            },
            error: function (html, status) {
                console.log(html);
                gameBegin_button.attr("disabled", false);
                update_nbPlayers();
            }
        });
    }
});

gameWord_helperInput_button.click(function () {
    gameWord_helperInput_button.attr("disabled", true);
    gameWord_helperInput_input.attr("disabled", true);
    gameWord_helperInput_button.addClass("is-disabled");
    gameWord_helperInput_input.addClass("is-disabled");
    gameWord_helperInput_button.text("Sending...");
    $.ajax({
        type: 'POST',
        url: window.location.pathname + '/wordHelper',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            word: gameWord_helperInput_input.val()
        },
        dataType: 'json',
        success: function (data) {
            gameWord_helperInput.hide();
        },
        error: function (html, status) {
            console.log("error",html);
            gameWord_helperInput_button.attr("disabled", false);
            gameWord_helperInput_input.attr("disabled", false);
            gameWord_helperInput_button.removeClass("is-disabled");
            gameWord_helperInput_input.removeClass("is-disabled");
            gameWord_helperInput_button.text("Send the word");
        }
    });
});

gameWord_chooserInput_buttonPass.click(function () {
    gameWord_chooserInput_button.attr("disabled", true);
    gameWord_chooserInput_buttonPass.attr("disabled", true);
    gameWord_chooserInput_input.attr("disabled", true);
    gameWord_chooserInput_button.addClass("is-disabled");
    gameWord_chooserInput_buttonPass.addClass("is-disabled").removeClass("is-error");
    gameWord_chooserInput_input.addClass("is-disabled");
    gameWord_chooserInput_buttonPass.text("Passing...");
    $.ajax({
        type: 'POST',
        url: window.location.pathname + '/passChooser',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            word: gameWord_chooserInput_input.val()
        },
        dataType: 'json',
        success: function (data) {
            gameWord_chooserInput_input.hide();
        },
        error: function (html, status) {
            console.log("error",html);
            gameWord_chooserInput_button.attr("disabled", false);
            gameWord_chooserInput_buttonPass.attr("disabled", false);
            gameWord_chooserInput_input.attr("disabled", false);
            gameWord_chooserInput_button.removeClass("is-disabled");
            gameWord_chooserInput_buttonPass.removeClass("is-disabled").addClass("is-error");
            gameWord_chooserInput_input.removeClass("is-disabled");
            gameWord_chooserInput_buttonPass.text("Don't guess the word");
        }
    });
});

gameWord_chooserInput_button.click(function () {
    gameWord_chooserInput_button.attr("disabled", true);
    gameWord_chooserInput_buttonPass.attr("disabled", true);
    gameWord_chooserInput_input.attr("disabled", true);
    gameWord_chooserInput_button.addClass("is-disabled");
    gameWord_chooserInput_buttonPass.addClass("is-disabled").removeClass("is-error");
    gameWord_chooserInput_input.addClass("is-disabled");
    gameWord_chooserInput_button.text("Sending...");
    $.ajax({
        type: 'POST',
        url: window.location.pathname + '/wordChooser',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            word: gameWord_chooserInput_input.val()
        },
        dataType: 'json',
        success: function (data) {
            gameWord_chooserInput_input.hide();
        },
        error: function (html, status) {
            console.log("error",html);
            gameWord_chooserInput_button.attr("disabled", false);
            gameWord_chooserInput_buttonPass.attr("disabled", false);
            gameWord_chooserInput_input.attr("disabled", false);
            gameWord_chooserInput_button.removeClass("is-disabled");
            gameWord_chooserInput_buttonPass.removeClass("is-disabled").addClass("is-error");
            gameWord_chooserInput_input.removeClass("is-disabled");
            gameWord_chooserInput_button.text("Guess the word");
        }
    });
});

// Only use for displaying 3 dots (loading)
let indicatorIsRunning = false;
function startIndicator(){
    if(!indicatorIsRunning) {
        indicatorIsRunning = true;
        gameBegin_dotCount = 1;

        gameBegin_interval = setInterval(function () {

            gameBegin_dots.text('.'.repeat(gameBegin_dotCount));

            gameBegin_dotCount++;
            if (gameBegin_dotCount > 3) {
                gameBegin_dotCount = 0;
            }

        }, 1000);
    }
}
function stopIndicator(){
    indicatorIsRunning = false;
    clearInterval(gameBegin_interval);
    gameBegin_dotCount = 0;
    gameBegin_dots.text('');
}

let timerIsRunning = false;
function startTimer(){
    if(!timerIsRunning) {
        timerIsRunning = true;
        gameWord_timer_interval = setInterval(function () {
            gameWord_timer.find("progress").attr("value", parseInt(gameWord_timer.find("progress").attr("value")-1));
        }, 1000);
    }
}
function stopTimer(){
    timerIsRunning = false;
    clearInterval(gameWord_timer_interval);
}

let updateCurrentWords = function(round, wordsPlayers){
    if(wordsPlayers != null || round.step == 1 || round.step == 3) {
        gameWord_currentWords.children().remove();
        let words = [];
        for (var k in round.words) {
            if (round.words.hasOwnProperty(k)) {
                let textClass;
                let textValue;
                if(round.step == 3){
                    textValue = round.words[k].word != null ? round.words[k].word : 'Hidden';
                    textClass = round.words[k].isSelected ? '' : 'is-disabled';
                }else{
                    let wordToDisplay = null;
                    if(wordsPlayers != null) {
                        wordToDisplay = round.words[k].word != null ? round.words[k].word : (wordsPlayers[round.words[k].id] == null ? null : wordsPlayers[round.words[k].id]);
                    }
                    textClass = wordToDisplay == null ? 'is-disabled' : '';
                    textValue = wordToDisplay == null ? (round.words[k].done ? 'Word sent' : 'Thinking') : wordToDisplay;
                }
                words.push(
                    $("<div>").addClass("col-sm-6")
                        .append(
                            $("<section>").addClass("nes-container with-title")
                                .attr("userId", round.words[k].id)
                                .append($("<h3>").addClass("title").text(round.words[k].name))
                                .append($("<p>").append($("<span>").addClass("nes-text " + textClass).text(textValue)))
                                .append($("<div>").addClass("row text-center choiceButton")
                                    .append($("<div>").addClass("col-xs-4").append($("<button>").addClass("nes-btn is-success").html("&#x2714;").click(sendSelectWord)))
                                    .append($("<div>").addClass("col-xs-4").append($("<button>").addClass("nes-btn is-warning").html("?").click(sendSelectWord)))
                                    .append($("<div>").addClass("col-xs-4").append($("<button>").addClass("nes-btn is-error").html("X").click(sendSelectWord)))
                                )
                        ));
            }
        }
        for (let i = 0; i < words.length; i += 2) {
            gameWord_currentWords.append(
                $("<div>").addClass("row")
                    .append(words[i])
                    .append(i + 1 < words.length ? words[i + 1] : null)
            );
        }
    }
    if(round.step == 2 && !isChooser()){
        for (var k in round.words) {
            if (round.words.hasOwnProperty(k)) {
                if(round.words[k].select[currentUserId+""] !== undefined){
                    console.log(JSON.stringify(round.words[k].select[currentUserId+""]));
                    let spanClass;
                    switch (round.words[k].select[currentUserId+""]) {
                        case null:
                            spanClass = 'is-warning';
                            break;
                        case true:
                            spanClass = 'is-success';
                            break;
                        case false:
                            spanClass = 'is-error';
                            break;
                    }
                    gameWord_currentWords.find('section[userId="'+k+'"] span').addClass(spanClass);
                    gameWord_currentWords.find('section[userId="'+k+'"] .choiceButton').hide();
                }else{
                    gameWord_currentWords.find('section[userId="'+k+'"] .choiceButton').show();
                }
            }
        }
    }else{
        $(".choiceButton").hide();
    }
};

let sendSelectWord = function (){
    let userId = $(this).parent().parent().parent().attr("userId");
    gameWord_currentWords.find('section[userId="'+userId+'"] button').attr('disabled', true).addClass("is-disabled");
    let choice;
    let spanClass;
    switch ($(this).html()) {
        case "?":
            choice = null;
            spanClass = 'is-warning';
            break;
        case "X":
            choice = false;
            spanClass = 'is-error';
            break;
        default: // check
            choice = true;
            spanClass = 'is-success';
    }

    $.ajax({
        type: 'POST',
        url: window.location.pathname+'/selectWord',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {userId : userId, choice: choice},
        dataType: 'json',
        success: function(data) {
            gameWord_currentWords.find('section[userId="'+userId+'"] span').addClass(spanClass);
            gameWord_currentWords.find('section[userId="'+userId+'"] .choiceButton').hide();
        },
        error: function(html, status) {
            gameWord_currentWords.find('section[userId="'+userId+'"] button').attr('disabled', false).removeClass("is-disabled");
            console.log(html);
            // TODO error !
        }
    });
};

let printGame = function(game, words){
    // {"currentRound":0,"nbRounds":5,"gameStatus":"begin","rounds":[]}
    gameSection.children().hide();
    $("#players-game").text(game.currentRound+" / "+game.nbRounds);
    playersHost.attr("host-userId", game.hostId).text(game.hostName);
    playersChooser.attr("host-userId", null).text(">_");
    $("#players-list").children().remove();
    for (var k in game.players){
        if (game.players.hasOwnProperty(k)) {
            addUser("players", game.players[k]);
        }
    }

    switch(game.gameStatus){
        case "begin":
            update_nbPlayers();
            startIndicator();
            gameBegin.show();
            break;
        case "running":
            stopIndicator();
            let round = game.rounds[game.currentRound-1];
            playersChooser.attr("chooser-userId", round.chooserId).text(round.chooserName);
            updateCurrentWords(round, words);
            gameWord_current.show();
            gameWord_timer.find("progress").attr("value",Math.floor(round.nextStepTimer - Date.now()/1000)).attr("max",round.timer);
            gameWord_timer.show();
            startTimer();

            switch (round.step) {
                case 1:
                    if(isChooser()){

                    }else{
                        gameWord_display.show();
                        if(!round.words[currentUserId].done) {
                            gameWord_helperInput.show();
                        }
                    }
                    break;
                case 2:
                    if(isChooser()){

                    }else{
                        gameWord_display.show();
                    }
                    break;
                case 3:
                    if(isChooser()){
                        gameWord_chooserInput.show();
                    }else{
                        gameWord_display.show();
                    }
                    break;

            }
            break;
        default:
            alert("TODO : game status '"+game.gameStatus+"' !");
    }
};

if(currentWord) {
    gameWord_display.find("span").text(currentWord);
}
printGame(JSON.parse(game), (words == null ? [] : JSON.parse(words)));

e.private('player-'+currentUserId)
    .listen('GameEvent', function (game) {
        console.log("game",JSON.parse(game.content));
        let word = JSON.parse(game.word);
        let words = null;
        if(word){
            if(word.word){
                gameWord_display.find("span").text(word.word);
            }else if(word.words){
                words = JSON.parse(word.words);
            }
        }
        printGame(JSON.parse(game.content), words);
    });

e.join('game-'+gameId)
    .here((users) => {
        users.forEach(function (element) {
            //addUser('players', element);
        });
    })
    .joining((user) => {
        //addUser('players', user);
        if(isHost()){
            $.ajax({
                type: 'POST',
                url: window.location.pathname+'/player/add',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {userId : user.userId},
                dataType: 'json',
                success: function(data) {
                    //console.log('add', data);
                },
                error: function(html, status) {
                    console.log(html);
                    // TODO error !
                }
            });
        }
    })
    .leaving((user) => {
        //$(".onlineUsers").find("section.member-card[profile-userId='"+user.userId+"']").remove();
        let updateHost = false
        if(playersHost.attr("host-userId") == user.userId){
            //console.log("Host leaved");
            updateHost = true;
            $("#players-list").find("section").each(function (index, element) {
                if(element != user.userId && element < currentUserId){
                    updateHost = false;
                }
            });
        }
        if(isHost() || updateHost){
            $.ajax({
                type: 'POST',
                url: window.location.pathname+'/player/remove',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {userId : user.userId},
                dataType: 'json',
                success: function(data) {
                    //console.log(data);
                },
                error: function(html, status) {
                    console.log(html);
                    // TODO error !
                }
            });
        }
    })
    .listen('GameEvent', function (game) {
        console.log("game",JSON.parse(game.content));
        printGame(JSON.parse(game.content), null);
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
                        .attr("src", "/uploads/users/"+e.userImage)
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


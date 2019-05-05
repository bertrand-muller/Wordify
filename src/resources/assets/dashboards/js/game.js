let e = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':6001',
    authEndpoint: "/broadcasting/auth"
});


let chat_input = $('#chat-input');
let game_allRounds = $("#game-allRounds");
let gameSection = $("#game");
let gameBegin = $("#game-begin");
let playersGame = $("#players-gameInfo");
let playersRounds = $("#players-game");
let playersChooser = $("#players-chooser");
let gameWord_wordGuest_display = $("#game-wordGuesser_display");
let gameWord_display = $("#game-word_display");
let gameWord_current = $("#game-word_current");
let gameWord_currentWords = $("#game-word_current_words");
let gameWord_helperInput = $("#game-word_helperInput");
let gameWord_chooserInput = $("#game-word_chooserInput");
let gameWord_timer = $("#game-timer");
let gameWord_status = $("#game-word_status");
let popup_profile = $("#profilModal");
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
let usersOnline = [];

let isHost = function(){
    return playersGame.attr("host-userId") == currentUserId;
};

let isChooser = function(){
    return playersChooser.attr("chooser-userId") == currentUserId;
};

let addUser = function(section, user){
    let sectionToAdd = $("<a>")
        .click(function () {
            $.ajax({
                type: 'GET',
                url: '/user/' + user.id,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (data) {
                    popup_profile.find("div.title div.name").text(user.id == currentUserId ? 'Your profile' : 'Profile of '+data.name);
                    popup_profile.find("div.title div.avatar img").attr("src","/uploads/users/"+data.image);
                    popup_profile.find("p.content").html(data.badges);
                    popup_profile.modal();
                },
                error: function (html, status) {
                    console.log(html);
                }
            });
        })
        .attr("profile-userId", user.id)
        .addClass("nes-container is-dark member-card"+(user.id == currentUserId ? ' isCurrentUser':''))
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
                .addClass("users")
                .append(
                    $("<h4>")
                        .addClass("name")
                        .text(user.name)
                )
                .append(
                    $("<span>").text(playersGame.attr("host-userId") == user.id ? 'host' : isUserOnline(user.id) || playersGame.attr("game-status") == "begin" ? '' : 'watch')
                )
    );
    if(user.id == currentUserId){
        $("#" + section + "-list").prepend(sectionToAdd);
    }else {
        $("#" + section + "-list").append(sectionToAdd);
    }
    update_nbPlayers();
};

let sendChat = function(){
    if(chat_input.val() != "") {
        chat_input.attr("disabled", true);
        $.ajax({
            type: 'POST',
            url: '/chat/' + gameId,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                message: $("#chat-input").val()
            },
            dataType: 'json',
            success: function (data) {
                //console.log(data);
                chat_input.attr("disabled", false);
                chat_input.val("");
                chat_input.focus();
            },
            error: function (html, status) {
                console.log(html);
                chat_input.attr("disabled", false);
                chat_input.focus();
            }
        });
    }
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
    let nbPlayers = $("#players-list").find("a").length;
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
        gameBegin_button.addClass("is-disabled").removeClass("is-primary");
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
                gameBegin_button.attr("disabled", true).addClass("is-disabled").addClass("is-primary");
            },
            error: function (html, status) {
                console.log(html);
                gameBegin_button.attr("disabled", false).removeClass("is-disabled").addClass("is-primary");
                update_nbPlayers();
            }
        });
    }
});

gameWord_helperInput_button.click(function () {
    if(gameWord_helperInput_input.val() != "") {
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
                gameWord_helperInput_input.val("");
                gameWord_helperInput_button.attr("disabled", false);
                gameWord_helperInput_input.attr("disabled", false);
                gameWord_helperInput_button.removeClass("is-disabled");
                gameWord_helperInput_input.removeClass("is-disabled");
                gameWord_helperInput_button.text("Send the word");
            },
            error: function (html, status) {
                console.log("error", html);
                gameWord_helperInput_button.attr("disabled", false);
                gameWord_helperInput_input.attr("disabled", false);
                gameWord_helperInput_button.removeClass("is-disabled");
                gameWord_helperInput_input.removeClass("is-disabled");
                gameWord_helperInput_button.text("Send the word");
            }
        });
    }
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
        dataType: 'json',
        success: function (data) {
            gameWord_chooserInput_input.hide();
            gameWord_chooserInput_input.val("");
            gameWord_chooserInput_button.attr("disabled", false);
            gameWord_chooserInput_buttonPass.attr("disabled", false);
            gameWord_chooserInput_input.attr("disabled", false);
            gameWord_chooserInput_button.removeClass("is-disabled");
            gameWord_chooserInput_buttonPass.removeClass("is-disabled").addClass("is-error");
            gameWord_chooserInput_input.removeClass("is-disabled");
            gameWord_chooserInput_buttonPass.text("Don't guess the word");
        },
        error: function (html, status) {
            console.log("error", html);
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
    if(gameWord_chooserInput_input.val() != "") {
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
                gameWord_chooserInput.hide();
                gameWord_chooserInput_input.val("");
                gameWord_chooserInput_button.attr("disabled", false);
                gameWord_chooserInput_buttonPass.attr("disabled", false);
                gameWord_chooserInput_input.attr("disabled", false);
                gameWord_chooserInput_button.removeClass("is-disabled");
                gameWord_chooserInput_buttonPass.removeClass("is-disabled").addClass("is-error");
                gameWord_chooserInput_input.removeClass("is-disabled");
                gameWord_chooserInput_button.text("Guess the word");
            },
            error: function (html, status) {
                console.log("error", html);
                gameWord_chooserInput_button.attr("disabled", false);
                gameWord_chooserInput_buttonPass.attr("disabled", false);
                gameWord_chooserInput_input.attr("disabled", false);
                gameWord_chooserInput_button.removeClass("is-disabled");
                gameWord_chooserInput_buttonPass.removeClass("is-disabled").addClass("is-error");
                gameWord_chooserInput_input.removeClass("is-disabled");
                gameWord_chooserInput_button.text("Guess the word");
            }
        });
    }
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

let getDefinition = function(){
    console.log($(this).parent().find("span.wordValue").text());
    let btn = $(this).attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: '/definition/'+btn.parent().find("span.wordValue").text(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (data) {
            let modal = $("#definitionModal");
            //console.log(data);
            modal.find("p.title span").text(data.word);
            let content = modal.find("p.content");
            content.text("").children().remove();
            if(data.definitions.length == 0){
                content.html("<span class='nes-text is-disabled'>Definition can't be found</span>");
            }else {
                let ul = $("<ul>").addClass("nes-list is-disc");
                data.definitions.forEach(function (definition) {
                    ul.append($("<li>").text(definition.definition.substring(0,1).toUpperCase()+definition.definition.substring(1)));
                });
                content.append(ul);
            }
            modal.modal();
            btn.attr("disabled", false);
        },
        error: function (html, status) {
            btn.attr("disabled", false);
        }
    });
};

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
                                .append($("<p>")
                                    .append($("<span>").addClass("wordValue nes-text " + textClass).text(textValue))
                                    .append(textClass == 'is-disabled' ? null : $("<button>").attr("data-toggle","modal").attr("data-target","#definitionModal").addClass("nes-btn btn-getDefinition").click(getDefinition).append($("<span>").text("?"))))
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
                if (round.words[k].select[currentUserId + ""] !== undefined) {
                    let spanClass;
                    switch (round.words[k].select[currentUserId + ""]) {
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
                    gameWord_currentWords.find('section[userId="' + k + '"] span').addClass(spanClass);
                    gameWord_currentWords.find('section[userId="' + k + '"] .choiceButton').hide();
                } else {
                    gameWord_currentWords.find('section[userId="' + k + '"] .choiceButton').show();
                }
            }
        }
    }else{
        $(".choiceButton").hide();
    }
    if(round.step == 2 && isChooser()) {
        for (var k in round.words) {
            if (round.words.hasOwnProperty(k)) {
                gameWord_currentWords.find('section[userId="' + k + '"] span').text("Selecting");
            }
        }
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
            if(playersGame.attr("round-step") == 2) {
                gameWord_currentWords.find('section[userId="' + userId + '"] span').addClass(spanClass);
            }
            gameWord_currentWords.find('section[userId="'+userId+'"] .choiceButton').hide();
        },
        error: function(html, status) {
            gameWord_currentWords.find('section[userId="'+userId+'"] button').attr('disabled', false).removeClass("is-disabled");
            console.log(html);
            // TODO error !
        }
    });
};

gameSection.children().hide();

let isUserOnline = function(userId){
    let response = false;
    usersOnline.forEach(function (user) {
        if(user.id+"" == userId){
            response = true;
        }
    });
    return response;
};

let printRounds = function(game){
    let score = 0;
    game.rounds.forEach(function (gameRound) {
        let scoreToAdd = 0;
        let roundText = 'current';
        switch (gameRound.win) {
            case -1:
                scoreToAdd = -1;
                roundText = 'failed';
                break;
            case 0:
                scoreToAdd = 0;
                roundText = 'passed';
                break;
            case 1:
                scoreToAdd = 1;
                roundText = 'win';
                break;
        }
        playersRounds.find('div.round[roundId="'+gameRound.id+'"] span').text(roundText);
        score += scoreToAdd;
    });
    playersRounds.find('div.score span').removeClass("is-error is-success").addClass(score == 0 ? '' : (score > 0 ? 'is-success' : 'is-error')).text(score);
};

let printGame = function(game, words){
    console.log("game",game);

    // {"currentRound":0,"nbRounds":5,"gameStatus":"begin","rounds":[]}
    gameSection.children().hide();
    $(".onlineUsers").find("a.member-card .users span").text("");
    $(".onlineUsers").find("a.member-card").each(function (index, element) {
        let userId = $(element).attr('profile-userId');
        let text = '';
        if(!game.players[userId] && game.gameStatus != 'begin'){
            text = 'watch';
        }
        if(!isUserOnline(userId)){
            text = "away";
        }
        if(userId == game.hostId){
            text = 'host';
        }
        $(element).find('.users span').text(text);
    });
    playersGame.attr("host-userId", game.hostId).attr("game-status", game.gameStatus).attr("round-step", 0);
    playersChooser.attr("host-userId", null).text(">_");

    switch(game.gameStatus){
        case "begin":
            update_nbPlayers();
            startIndicator();
            gameBegin.show();
            break;
        case "running":
            stopIndicator();
            let round = game.rounds[game.currentRound-1];
            playersGame.attr("round-step", round.step);
            playersChooser.attr("chooser-userId", round.chooserId).text(round.chooserName);
            updateCurrentWords(round, words);
            gameWord_current.show();
            gameWord_timer.find("progress").attr("value",Math.trunc(round.nextStepTimer - Date.now()/1000)).attr("max",round.timer);
            gameWord_timer.show();
            startTimer();
            printRounds(game);
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
                case 4:
                    gameWord_wordGuest_display.find("span").remove();
                    gameWord_status.find("span").remove();
                    gameWord_display.find("span").remove();
                    let spanClass;
                    let spanText;
                    let statusText;
                    let statusClass;
                    switch (round.win) {
                        case -1:
                            spanText = round.guessWord;
                            statusText = 'Failed';
                            statusClass = 'is-error';
                            break;
                        case 0:
                            spanText = 'Passed';
                            statusText = 'Not guessed';
                            statusClass = '';
                            break;
                        case 1:
                            spanText = round.guessWord;
                            statusText = '<i class="nes-icon trophy"></i><span class="win">Win</span>';
                            statusClass = 'is-warning';
                            break;
                    }
                    gameWord_status.show().append($("<span>").addClass("nes-text "+statusClass).html(statusText));
                    gameWord_wordGuest_display.show().append($("<span>").text(spanText));
                    gameWord_display.show().append($("<span>").text(round.word));
                    for (var k in round.words) {
                        if (round.words.hasOwnProperty(k)) {
                            gameWord_currentWords.find('section.nes-container[userid="'+round.words[k].id+'"] span').text(round.words[k].word);
                        }
                    }
                    break;
            }
            break;
        case "finished":
            if(game.nextGame){
                window.location.href = '/play/'+game.nextGame;
            }
            printRounds(game);
            let score = 0;
            game.rounds.forEach(function (gameRound) {
                let scoreToAdd = 0;
                let roundText = '';
                let roundColor = '';
                let guessWord = null;
                switch (gameRound.win) {
                    case -1:
                        roundText = 'failed';
                        roundColor = 'is-error';
                        guessWord = $("<div>").text("Word guessed : "+gameRound.guessWord);
                        scoreToAdd = -1;
                        break;
                    case 0:
                        roundText = 'passed';
                        roundColor = 'is-disabled';
                        scoreToAdd = 0;
                        break;
                    case 1:
                        roundText = 'win';
                        roundColor = 'is-success';
                        guessWord = $("<div>").text("Word guessed : "+gameRound.guessWord);
                        scoreToAdd = 1;
                        break;
                }
                score += scoreToAdd;
                let parentSpan = game_allRounds.find('span[round="'+gameRound.id+'"]');
                parentSpan.children().remove();
                let ul = $("<ul>").addClass("nes-list is-disc");
                for(var k in gameRound.words){
                    if(gameRound.words.hasOwnProperty(k)){
                        ul.append(
                            $("<li>").html(gameRound.words[k].name+" : <span class='nes-text "+(gameRound.words[k].isSelected ? '' : 'is-disabled')+"'>"+gameRound.words[k].word+"</span>")
                        );

                    }
                }
                parentSpan
                    .append($("<div>").html("Status : <span class='nes-text "+roundColor+"'>"+roundText+"</span>"))
                    .append($("<div>").text("Guesser : "+gameRound.chooserName))
                    .append($("<div>").text("Word to guess : "+gameRound.word))
                    .append(guessWord)
                    .append($("<div>").text("Words of helpers : "))
                    .append($("<div>").addClass("lists").append(ul));
            });
            let statusText = '';
            let statusClass = '';
            if(score > 0){ // win
                statusText = '<i class="nes-icon trophy"></i><span class="win">Victory !</span>';
                statusClass = "is-success";
            }else{ // loose
                statusText = '<span class="defeat">Defeat</span>';
                statusClass = "is-error";
            }
            gameWord_status.find("span").remove();
            gameWord_status.show().append($("<span>").addClass("nes-text "+statusClass).html(statusText));
            game_allRounds.show();

            let lastRound = game.rounds[game.currentRound-1];
            gameWord_timer.find("progress").attr("value",Math.trunc(lastRound.nextStepTimer - Date.now()/1000)).attr("max",lastRound.timer);
            if(Math.trunc(lastRound.nextStepTimer - Date.now()/1000) > 0) {
                gameWord_timer.show();
            }
            break;
        default:
            alert("TODO : game status '"+game.gameStatus+"' !");
    }
};

if(currentWord) {
    gameWord_display.find("span").text(currentWord);
}

e.private('player-'+currentUserId)
    .listen('GameEvent', function (game) {
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
        if(users.length == 1 && !isHost()){
            $.ajax({
                type: 'GET',
                url: window.location.pathname+'/host',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
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
        let jsonGame = JSON.parse(game);
        usersOnline = users;

        for (var k in jsonGame.players) {
            if (jsonGame.players.hasOwnProperty(k)) {
                addUser('players', jsonGame.players[k]);
            }
        }

        users.forEach(function (element) {
            if(!jsonGame.players.hasOwnProperty(element.id)) {
                addUser('players', element);
            }
        });
        printGame(jsonGame, (words == null ? [] : JSON.parse(words)));
    })
    .joining((user) => {
        if($(".onlineUsers").find("a.member-card[profile-userId='"+user.id+"']").length == 0) {
            addUser('players', user);
        }else{
            $(".onlineUsers").find("a.member-card[profile-userId='"+user.id+"'] .users span").text('');
        }
        usersOnline[user.id] = user;
    })
    .leaving((user) => {
        delete usersOnline[user.id];
        if(playersGame.attr("game-status") == 'begin'){
            $(".onlineUsers").find("a.member-card[profile-userId='"+user.id+"']").remove();
        }else{
            $(".onlineUsers").find("a.member-card[profile-userId='"+user.id+"'] .users span").text("away");
        }
        if(playersGame.attr("host-userId") == user.id){
            $.ajax({
                type: 'GET',
                url: window.location.pathname+'/host',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
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


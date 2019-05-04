
let profile_logOut = $("#profile_logOut_button");
let profile_index = $("#profile_index_button");
let addWord = $("#addWord");
let findWord = $("#findWord");
let wordList = $("#word-list");
let wordValidate_profile = $(".btn-profile");
let wordValidate_question = $(".btn-question");
let wordValidate_validate = $(".btn-check");
let wordValidate_delete = $(".btn-cross");

let addWordInList = function(word){
    let added = false;
    let previousElement = null;
    let div = $("<div>").addClass("row").attr("wordId", word.id)
        .append(
            $("<div>").addClass("col-xs-2")
                .append($("<button>")
                    .addClass("nes-btn smallPadding smallMargin btn-question")
                    .attr("type","button")
                    .text("?")
                    .click(clickQuestion)))
        .append(
            $("<div>").addClass("wordHeight col-xs-8")
                .append($("<span>")
                    .text(word.word)))
        .append(
            $("<div>").addClass("col-xs-2 text-right")
                .append($("<button>")
                    .addClass("nes-btn smallPadding smallMargin is-error btn-cross")
                    .attr("type","button")
                    .text("X")
                    .click(clickDelete)));
    let splitLabel = $("<label>").addClass("split");
    wordList.find("div.row").each(function (index, element) {
        if($(element).find("span").text() > word.word){
            added = true;
        }
        if(!added){
            previousElement = element;
        }
    });
    if(previousElement == null){
        previousElement = wordList.find("h3");
    }else{
        previousElement = $(previousElement).next();
    }
    splitLabel.insertAfter(previousElement);
    div.insertAfter(previousElement);
    wordList.find("p").hide();
    findWord.find("button").trigger("click");
};

findWord.find("button.is-primary").click(function () {
    wordList.find("div.row").show();
    wordList.find("label").show();
    wordList.find("div.row").each(function (index, element) {
        if(!$(element).find("span").text().toLowerCase().includes(findWord.find("input").val())){
            $(element).hide();
            $(element).next().hide();
        }
    });
    wordList.find("label:visible").last().hide();
});

findWord.find("button.is-warning").click(function () {
    findWord.find("input").val("");
    findWord.find("button.is-primary").trigger("click");
});

profile_logOut.click(function () {
    window.location.href = "/logout";
});

profile_index.click(function () {
    window.location.href = "/";
});

addWord.find("button").click(function (){
    if(addWord.find("input").val() != "") {
        addWord.find("button").attr("disabled", true);
        addWord.find("input").attr("disabled", true);
        $.ajax({
            type: 'POST',
            url: '/word/submit',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                word: addWord.find("input").val()
            },
            dataType: 'json',
            success: function (data) {
                addWordInList(data);
                addWord.find("span.nes-text").removeClass("is-error").addClass("is-success").text('Word "' + data.word + '" has been submited.');
                addWord.find("input").val("");
                addWord.find("button").attr("disabled", false);
                addWord.find("input").attr("disabled", false);
            },
            error: function (jqrXhr) {
                switch (jqrXhr.status) {
                    case 400:
                        addWord.find("span.nes-text").removeClass("is-success").addClass("is-error").text('Word "' + jqrXhr.responseJSON + '" already exists.');
                        addWord.find("input").val("");
                        break;
                    default:
                        addWord.find("span.nes-text").removeClass("is-success").addClass("is-error").text('An error occured, please try again.');
                        console.log(jqrXhr);
                }
                addWord.find("button").attr("disabled", false);
                addWord.find("input").attr("disabled", false);
            }
        });
    }
});

let updateButtons = function(button, disabled){
    if(disabled) {
        button.parent().parent().find("button").attr("disabled", true).removeClass("is-primary is-success is-error").addClass('is-disabled');
    }else{
        button.parent().parent().find("button").attr("disabled", false).removeClass("is-disabled");
        button.parent().parent().find("button").each(function (index, element) {
           if ($(element).hasClass('btn-profile')){
               $(element).addClass("is-primary");
           } else if ($(element).hasClass('btn-check')){
               $(element).addClass("is-success");
           } else if ($(element).hasClass('btn-cross')){
               $(element).addClass("is-error");
           }
        });
    }
};

wordValidate_validate.click(function () {
    let btn = $(this);
    updateButtons(btn, true);
    $.ajax({
        type: 'POST',
        url: '/word/validate',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            wordId: btn.parent().parent().attr("wordId")
        },
        dataType: 'json',
        success: function (data) {
            btn.parent().parent().next().remove();
            btn.parent().parent().remove();
            addWordInList(data);
            console.log("TODO remove");
        },
        error: function (jqrXhr) {
            console.log(jqrXhr);
            updateButtons(btn, false);
        }
    });
});

let questionWord = function(btn){
    updateButtons(btn, true);
    $.ajax({
        type: 'GET',
        url: '/word/definition/'+btn.parent().parent().find("span"),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (data) {
            // TODO profile
            updateButtons(btn, false);
            console.log("TODO question ",data);
        },
        error: function (jqrXhr) {
            console.log(jqrXhr);
            updateButtons(btn, false);
        }
    });
};

let clickQuestion = function(){
    questionWord($(this));
};

wordValidate_question.click(function () {
    questionWord($(this));
});

let deleteWord = function(btn){
    updateButtons(btn, true);
    $.ajax({
        type: 'POST',
        url: '/word/delete',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            wordId: btn.parent().parent().attr("wordId")
        },
        dataType: 'json',
        success: function (data) {
            let parent = btn.parent().parent().parent();
            btn.parent().parent().next().remove();
            btn.parent().parent().remove();
            if(parent.find("div").length == 0){
                parent.find("p").show();
            }
        },
        error: function (jqrXhr) {
            console.log(jqrXhr);
            updateButtons(btn, false);
        }
    });
};

let clickDelete = function(){
    deleteWord($(this));
};

wordValidate_delete.click(function () {
    deleteWord($(this));
});

wordValidate_profile.click(function () {
    let btn = $(this);
    updateButtons(btn, true);
    $.ajax({
        type: 'GET',
        url: '/users/'+btn.attr("userId"),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (data) {
            // TODO profile
            updateButtons(btn, false);
            console.log(data, "TODO profile");
        },
        error: function (jqrXhr) {
            console.log(jqrXhr);
            updateButtons(btn, false);
        }
    });
});


if($("#wordValidated").find("div").length != 0){
    $("#wordValidated").find("p").hide();
}
if(wordList.find("div").length != 0){
    wordList.find("p").hide();
}
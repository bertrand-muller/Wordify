$(function(ready) {

    // Remove default welcome notification.
    $('.ui-pnotify').remove();


    // Function used to display a notification.
    let displayNotification = function(message, type) {
        let mapping = [];
        mapping['success'] = {title: 'Succès !', icon: 'fa fa-check'};
        mapping['error'] = {title: 'Erreur !', icon: 'fa fa-warning'};
        mapping['info'] = {title: 'Notice', icon: 'fa fa-info'};
        new PNotify({
            delay: 2000,
            icon: mapping[type].icon,
            icons: 'bootstrap3',
            modules: {
                Mobile: {
                    swipeDismiss: true,
                    styling: true
                }
            },
            styling: 'bootstrap3',
            text: message,
            title: mapping[type].title,
            type: type
        });
    };


    // Global variables.
    let newFrenchWord = $('#newFrenchWord');
    let newEnglishWord = $('#newEnglishWord');
    let newFrenchDefinition = $('#newFrenchDefinition');
    let newEnglishDefinition = $('#newEnglishDefinition');
    let newPicture = $('#newPicture');
    let newWordAddButton = $('#newWordButton');

    let updateChooseWord = $('#updateChooseWord');
    let updateFrenchWord = $('#updateFrenchWord');
    let updateEnglishWord = $('#updateEnglishWord');
    let updateFrenchDefinition = $('#updateFrenchDefinition');
    let updateEnglishDefinition = $('#updateEnglishDefinition');
    let updatePicture = $('#updatePicture');
    let updateWordButton = $('#updateWordButton');


    // Function used to add a new word
    let addWord = function(french, english, frenchDefinition, englishDefinition, picture) {

        // Disable corresponding button
        newWordAddButton.attr('disabled', true);

        // Create form data
        let formData = new FormData();
        formData.append('french', french);
        formData.append('english', english);
        formData.append('frenchDefinition', frenchDefinition);
        formData.append('englishDefinition', englishDefinition);
        formData.append('picture', picture);

        $.ajax({
            type: 'POST',
            url: '/dashboards/words/management/add/word',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: false,
            processData: false,
            cache: false,
            data: formData,
            dataType: 'json',
            success: function(data) {

                // Add word in dropdown for 'update' form
                updateChooseWord.append(new Option(data.english + '(' + data.french + ')', data.id));

                // Display success notification.
                displayNotification('The new word has been successfully added:<br> "' + data.english + '" (E) - "' + data.french + '" (F)', 'success');

                // Enable the button.
                newWordAddButton.attr('disabled', false);

            },
            error: function(html, status) {

                // Display error notification.
                displayNotification($.parseJSON(html.responseText).error, 'error');

                // Enable the button.
                newWordAddButton.attr('disabled', false);
            }
        });
    };


    // Function used to update a word.
    let updateWord = function(idWord, french, english, frenchDefinition, englishDefinition, picture) {

        // Disable corresponding button.
        updateWordButton.attr('disabled', true);

        // Create form data
        let formData = new FormData();
        formData.append('idWord', idWord);
        formData.append('french', french);
        formData.append('english', english);
        formData.append('frenchDefinition', frenchDefinition);
        formData.append('englishDefinition', englishDefinition);
        formData.append('picture', picture);

        $.ajax({
            type: 'POST',
            url: '/dashboards/words/management/update/word',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: false,
            processData: false,
            cache: false,
            data: formData,
            dataType: 'json',
            success: function(data) {

                // Display default values in inputs.
                updateEnglishWord.val(data.english);
                updateFrenchWord.val(data.french);
                updateFrenchDefinition.val(data.frenchDefinition);
                updateEnglishDefinition.val(data.englishDefinition);

                // Display success notification.
                displayNotification('The word "' + data.english + '" ("' + data.french + ') has been successfully updated', 'success');

                // Enable the button.
                updateWordButton.attr('disabled', false);

            },
            error: function(html, status) {

                // Display error notification.
                displayNotification($.parseJSON(html.responseText).error, 'error');

                // Enable the button.
                updateWordButton.attr('disabled', false);
            }
        });
    };


    // Function used to populate 'update' form according to the word chosen.
    let populateUpdateForm = function(idWord) {

        // Force id to be integer
        let id = parseInt(idWord);

        $.ajax({
            type: 'POST',
            url: '/dashboards/words/management/get/word',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                idWord: id
            },
            dataType: 'json',
            success: function(data) {

                // Display default values in inputs.
                updateEnglishWord.val(data.english);
                updateFrenchWord.val(data.french);
                updateFrenchDefinition.val(data.frenchDefinition);
                updateEnglishDefinition.val(data.englishDefinition);

            },
            error: function(html, status) {
                // Display error notification.
                displayNotification($.parseJSON(html.responseText).error, 'error');
            }
        });
    };


    // Detect when user wants to add a new word.
    newWordAddButton.on('click', function() {
        let french = newFrenchWord.val();
        let english = newEnglishWord.val();
        let frenchDefinition = newFrenchDefinition.val();
        let englishDefinition = newEnglishDefinition.val();
        let newPictureFile = newPicture[0].files[0];
        addWord(french, english, frenchDefinition, englishDefinition, newPictureFile);
    });


    // Detect when user chooses a word to update.
    updateChooseWord.on('change', function() {
        let idWord = updateChooseWord.val();
        populateUpdateForm(idWord);
    });


    // Detect when user wants to update a word.
    updateWordButton.on('click', function() {
        let idWord = updateChooseWord.val();
        let french = updateFrenchWord.val();
        let english = updateEnglishWord.val();
        let frenchDefinition = updateFrenchDefinition.val();
        let englishDefinition = updateEnglishDefinition.val();
        let updatePictureFile = updatePicture[0].files[0];
        updateWord(idWord, french, english, frenchDefinition, englishDefinition, updatePictureFile);
    });


    // Populate update form.
    populateUpdateForm(updateChooseWord.val());

});
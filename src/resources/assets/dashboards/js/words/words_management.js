$(function(ready) {

    // Remove default welcome notification
    $('.ui-pnotify').remove();


    // Function used to display a notification
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


    // Global variables
    let newFrenchWord = $('#newFrenchWord');
    let newEnglishWord = $('#newEnglishWord');
    let newFrenchDefinition = $('#newFrenchDefinition');
    let newEnglishDefinition = $('#newEnglishDefinition');
    let newPicture = $('#newPicture');
    let newWordAddButton = $('#newWordAdd');


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

                // Display success notification
                displayNotification('The new word has been successfully added:<br> "' + data.english + '" (E) - "' + data.french + '" (F)', 'success');

                // Enable the button
                newWordAddButton.attr('disabled', false);

            },
            error: function(html, status) {

                // Display error notification
                displayNotification($.parseJSON(html.responseText).error, 'error');

                // Enable the button
                newWordAddButton.attr('disabled', false);
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

});
var importAllSurveys = {
    init: function () {
        $('.import_button').click(function (event) {
            event.preventDefault();
            var resultsContainer = $(this).parent('td').find('div.results');
            if (! resultsContainer.length) {
                $(this).parent('td').append('<div class="results"></div>');
                resultsContainer = $(this).parent('td').find('div.results');
            }
            importResponses($(this), resultsContainer);
        });
        $('.clear_button').click(function (event) {
            event.preventDefault();
            var confirmMsg = 'Are you sure you want to delete all responses to this questionnaire?';
            if (! confirm(confirmMsg)) {
                return;
            }
            var resultsContainer = $(this).parent('td').find('div.results');
            if (! resultsContainer.length) {
                $(this).parent('td').append('<div class="results"></div>');
                resultsContainer = $(this).parent('td').find('div.results');
            }
            clearResponses($(this), resultsContainer);
        });
    }
};

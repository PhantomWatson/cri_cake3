var importAllSurveys = {
    init: function () {
        $('.import_button').click(function (event) {
            event.preventDefault();
            var resultsContainer = importAllSurveys.getResultsContainer(this);
            importResponses($(this), resultsContainer);
        });
        $('.clear_button').click(function (event) {
            event.preventDefault();
            var confirmMsg = 'Are you sure you want to delete all responses to this questionnaire?';
            if (! confirm(confirmMsg)) {
                return;
            }
            var resultsContainer = importAllSurveys.getResultsContainer(this);
            importAllSurveys.clearResponses($(this), resultsContainer);
        });
    },

    clearResponses: function (button, resultsContainer) {
        var surveyId = button.data('survey-id');
        $.ajax({
            url: '/admin/surveys/clear-responses/' + surveyId + '.json',
            beforeSend: function () {
                button.addClass('disabled');
                var loading_indicator = $('<img src="/data_center/img/loading_small.gif" class="loading" />');
                button.append(loading_indicator);
                if (resultsContainer.is(':visible')) {
                    resultsContainer.slideUp(200);
                }
            },
            success: function (data) {
                resultsContainer.addClass('alert');
                if (data.success) {
                    resultsContainer
                        .addClass('alert-success')
                        .removeClass('alert-danger');
                    resultsContainer.html('Responses cleared');
                } else {
                    resultsContainer
                        .addClass('alert-danger')
                        .removeClass('alert-success');
                    resultsContainer.html('Error clearing responses');
                }
                resultsContainer.slideDown();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                resultsContainer
                    .addClass('alert')
                    .addClass('alert-danger')
                    .removeClass('alert-success');
                resultsContainer.html('Error clearing responses');
                resultsContainer.slideDown();
            },
            complete: function () {
                button.removeClass('disabled');
                button.find('.loading').remove();
            }
        });
    },

    getResultsContainer: function (button) {
        button = $(button);
        var resultsContainer = button.parent('td').find('div.results');
        if (! resultsContainer.length) {
            button.parent('td').append('<div class="results"></div>');
            resultsContainer = button.parent('td').find('div.results');
        }
        return resultsContainer;
    }
};

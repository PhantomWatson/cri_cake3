var clearResponses = function (button, resultsContainer) {
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
};

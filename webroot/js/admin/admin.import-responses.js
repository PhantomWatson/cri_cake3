var importResponses = function (button, resultsContainer) {
    var surveyId = button.data('survey-id');
    $.ajax({
        url: '/surveys/import/' + surveyId,
        beforeSend: function () {
            button.addClass('disabled');
            var loading_indicator = $('<img src="/data_center/img/loading_small.gif" class="loading" />');
            button.append(loading_indicator);
            if (resultsContainer.is(':visible')) {
                resultsContainer.slideUp(200);
            }
        },
        success: function (data) {
            resultsContainer
                .addClass('alert')
                .addClass('alert-success')
                .removeClass('alert-danger');
            resultsContainer.html(data);
            resultsContainer.slideDown();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            resultsContainer
                .addClass('alert')
                .addClass('alert-danger')
                .removeClass('alert-success');
            resultsContainer.html(jqXHR.responseText);
            resultsContainer.slideDown();
        },
        complete: function () {
            button.removeClass('disabled');
            button.find('.loading').remove();
            button.parent().children('.last_import_time').html('Responses were last imported a moment ago');
        }
    });
};

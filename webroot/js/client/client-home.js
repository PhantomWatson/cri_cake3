var clientHome = {
    init: function () {
        this.setupImport();
        this.setupToggledContainers();
        this.setupConfirmationButtons();

        $('.importing_note_toggler').click(function (event) {
            event.preventDefault();
            var note = $(this).closest('td').find('.importing_note');
            note.slideToggle();
        });
    },
    setupImport: function () {
        $('.import-results').each(function () {
            var resultsContainer = $(this);
            if (resultsContainer.is(':empty')) {
                resultsContainer.hide();
                return;
            }

            var errorList = resultsContainer.find('ul');
            var errorToggler = $('<button class="btn btn-default btn-sm">Show</button>');
            errorToggler.click(function (event) {
                event.preventDefault();
                errorList.slideToggle();
            });
            errorList.before(errorToggler);
            errorList.hide();
        });

        $('.import_button').click(function (event) {
            event.preventDefault();
            var link = $(this);

            if (link.hasClass('disabled')) {
                return;
            }

            var survey_id = link.data('survey-id');
            var row = link.closest('tr');
            var resultsContainer = row.find('.import-results');

            $.ajax({
                url: '/surveys/import/'+survey_id,
                beforeSend: function () {
                    link.addClass('disabled');
                    var loading_indicator = $('<img src="/data_center/img/loading_small.gif" class="loading" />');
                    link.append(loading_indicator);
                    if (resultsContainer.is(':visible')) {
                        resultsContainer.slideUp(200);
                    }
                },
                success: function (data) {
                    resultsContainer.attr('class', 'import-results alert alert-success');
                    resultsContainer.html(data);
                    resultsContainer.slideDown();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    resultsContainer.attr('class', 'import-results alert alert-danger');
                    resultsContainer.html(jqXHR.responseText);
                    resultsContainer.slideDown();
                },
                complete: function () {
                    link.removeClass('disabled');
                    link.find('.loading').remove();
                }
            });
        });
    },
    setupToggledContainers: function () {
        var steps = $('#client_home').find('> table > tbody');
        steps.each(function () {
            var step = $(this);
            var button = step.find('button.step-header');
            var details = step.find('tr').not(':first-child');
            if (details.length > 0) {
                button.attr('title', 'Click for details');
                if (! step.hasClass('current')) {
                    details.hide();
                    button.addClass('closed');
                }
            }
            button.click(function (event) {
                event.preventDefault();

                if (details.length === 0) {
                    return;
                }

                details.toggle();
                button.toggleClass('closed');
            });
        });
    },
    setupConfirmationButtons: function () {
        $('.opt-out').click(function (event) {
            var msg = 'Are you sure you want to permanently opt out of this part ' +
                'of the Community Readiness Initiative?';
            if (! confirm(msg)) {
                event.preventDefault();
            }
        });
    }
};

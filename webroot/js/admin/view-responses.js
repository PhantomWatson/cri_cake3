var adminViewResponses = {
    init: function () {
        this.setupAlignmentTable();
        this.setupUpdateAlignment();
    },
    setupAlignmentTable: function () {
        $('.custom_alignment_calc').change(function () {
            var container = $(this).closest('.responses');
            adminViewResponses.updateAlignment(container);
            adminViewResponses.updateRespondentCount(container);
        });
        $('.calc-mode').change(function (event) {
            event.preventDefault();
            var container = $(this).closest('.responses');
            var mode = $(this).val();
            container.find('td.selected, th.selected').toggle(mode === 'selected');
            adminViewResponses.updateRespondentCount(container);
            adminViewResponses.updateAlignment(container);
        });
        var userIcon = '<span class="glyphicon glyphicon-user"></span>';
        var showRespondentsLabel = {
            show: userIcon + ' Show respondent info',
            hide: userIcon + ' Hide respondent info'
        };
        $('#show-respondents')
            .html(showRespondentsLabel.show)
            .click(function (event) {
                event.preventDefault();
                var button = $(this);
                if (button.data('label') === 'show') {
                    $('tr.respondent').css('display', 'table-row');
                    button.data('label', 'hide');
                    button.html(showRespondentsLabel.hide);
                } else {
                    $('tr.respondent').hide();
                    button.data('label', 'show');
                    button.html(showRespondentsLabel.show);
                }
            });
        $('tr.respondent').hide();
        $('ul.nav-tabs li[role=presentation]').first().find('a').tab('show');
        var fullscreenIcon = '<span class="glyphicon glyphicon-fullscreen"></span>';
        var windowIcon = '<span class="glyphicon glyphicon-list-alt"></span>';
        var toggleFullscreenLabel = {
            fullscreen: fullscreenIcon + ' <span class="text">Show table full size</span>',
            window: windowIcon + ' <span class="text">Show table in window</span>'
        };
        $('#toggle-table-scroll')
            .html(toggleFullscreenLabel.fullscreen)
            .data('mode', 'scrolling')
            .click(function (event) {
                event.preventDefault();
                var link = $(this);
                var containers = $('#admin-responses-view .tab-pane > .responses > div');
                if (link.data('mode') === 'scrolling') {
                    containers.removeClass('scrollable_table');
                    link.html(toggleFullscreenLabel.window);
                    link.data('mode', 'fullscreen');
                } else {
                    containers.addClass('scrollable_table');
                    link.html(toggleFullscreenLabel.fullscreen);
                    link.data('mode', 'scrolling');
                }
            });
        $('.full-response-button').click(function (event) {
            var button = $(this);
            var respondentId = button.data('respondent-id');
            adminViewResponses.showFullResponse(respondentId);
        });
    },
    showFullResponse: function (respondentId) {
        $.ajax({
            url: '/admin/responses/get-full-response/' + respondentId,
            dataType: 'json',
            beforeSend: function (xhr) {
                var modal = $('#full-response-modal');
                var loadingIcon = '<img src="/data_center/img/loading_small.gif" />';
                modal.find('.modal-body').html('Loading... ' + loadingIcon);
                modal.modal();
            },
            success: function (data, textStatus, jqXHR) {
                var modal = $('#full-response-modal');
                var modalBody = modal.find('.modal-body');

                // Error message
                if (data.hasOwnProperty('message')) {
                    modalBody.html('<span class="text-danger">' + data.message + '</span>');
                    return;
                }

                // Success
                modalBody.html('');
                var response = data.response;
                for (var heading in response) {
                    modalBody.append('<h3>' + heading + '</h3>');
                    var answerList = $('<ul></ul>');
                    var answers = response[heading];
                    for (var i = 0; i < answers.length; i++) {
                        answerList.append('<li>' + answers[i] + '</li>');
                    }
                    modalBody.append(answerList);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var modal = $('#full-response-modal');
                var modalBody = modal.find('.modal-body');
                var response = $.parseJSON(jqXHR.responseText);
                var msg = '';
                if (response.hasOwnProperty('message')) {
                    msg = response.message;
                } else {
                    msg = 'There was an error loading that response';
                }
                modalBody.html('<span class="text-danger">' + msg + '</span>');
            }
        });
    },
    updateAlignment: function (container) {
        var respondents = [];
        if (this.getCalcMode(container) == 'selected') {
            respondents = container.find('.custom_alignment_calc:checked');
        } else {
            respondents = container.find('td.approved .glyphicon-ok');
        }

        var sum = 0;
        respondents.each(function () {
            var value = $(this).closest('tr').data('alignment');
            sum = value + sum;
        });

        var count = respondents.length;
        var average = count ? Math.round(sum / count) : 0;
        var resultContainer = container.find('span.total_alignment');
        resultContainer.html(average + '%');
    },
    getRespondentCount: function (container) {
        if (this.getCalcMode(container) == 'selected') {
            return container.find('input.custom_alignment_calc:checked').length;
        }
        return container.find('td.approved .glyphicon-ok').length;
    },
    updateRespondentCount: function (container) {
        var respondentCount = adminViewResponses.getRespondentCount(container);
        container.find('.respondent_count').html(respondentCount);
        var respondentPlurality = container.find('.respondent_plurality');
        respondentPlurality.html('respondent');
        if (respondentCount != 1) {
            respondentPlurality.append('s');
        }
    },
    getCalcMode: function (container) {
        return container.find('.calc-mode').val();
    },
    setupUpdateAlignment: function () {
        $('#update-alignment').click(function (event) {
            event.preventDefault();
            var button = $(this);
            $.ajax({
                url: button.data('update-url'),
                beforeSend: function () {
                    button.addClass('disabled');
                    var loading_indicator = $('<img src="/data_center/img/loading_small.gif" alt="Loading..." />');
                    button.append(loading_indicator);
                },
                success: function (data) {
                    var alert = button.closest('.alert');
                    alert.fadeOut(300, function () {
                        alert.removeClass('alert-danger');
                        alert.addClass('alert-success');
                        alert.html('Alignment data saved. ');
                        var refreshButton = $('<button class="btn btn-default btn-sm">Refresh this page</button>');
                        refreshButton.click(function () {
                            location.reload();
                        });
                        alert.append(refreshButton);
                        alert.fadeIn();
                    });
                },
                error: function () {
                    var msg = 'There was an error updating this community\'s alignment score';
                    $(msg).insertAfter(button);
                },
                complete: function () {
                    button.removeClass('disabled');
                    button.children('img').remove();
                }
            });
        });
    }
};

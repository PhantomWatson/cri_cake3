var unapprovedRespondents = {
    currentBulkAction: null,

    init: function () {
        $('#toggle_dismissed').click(function (event) {
            event.preventDefault();
            $('#dismissed_respondents').find('> div').slideToggle();
        });
        $('a.approve, a.dismiss').click(function (event) {
            event.preventDefault();
            var link = $(this);
            unapprovedRespondents.updateRespondent(link);
        });
        this.setupBulkAction();
    },
    setupBulkAction: function () {
        $('#bulk-actions').find('button').click(function (event) {
            event.preventDefault();
            var button = $(this);
            var loadingIndicator = $('<img src="/data_center/img/loading_small.gif" class="loading" />');
            button.append(loadingIndicator);
            unapprovedRespondents.currentBulkAction = button.data('action');
            button.addClass('disabled');
            button.siblings('button').addClass('disabled');
            $('a.' + button.data('action')).each(function () {
                var link = $(this);

                // Skip over action links that are hidden because they were recently clicked on
                if (link.is(':visible')) {
                    unapprovedRespondents.updateRespondent(link);
                }
            });
        });
    },

    /**
     * If the current bulk action appears to be complete, removes bulk action buttons
     */
    checkBulkActionsComplete: function () {
        if (! this.currentBulkAction) {
            return;
        }

        var isComplete = $('a.' + this.currentBulkAction + '.disabled').length === 0;
        if (! isComplete) {
            return;
        }

        $('#bulk-actions').slideUp(1000, function () {
            $('#bulk-actions').remove();
        });
        this.currentBulkAction = null;
    },

    updateRespondent: function (link) {
        $.ajax({
            url: link.attr('href'),
            beforeSend: function () {
                link.addClass('disabled');
                var loading_indicator = $('<img src="/data_center/img/loading_small.gif" class="loading" />');
                link.append(loading_indicator);
                link.closest('td').find('p.text-danger, p.text-success, p.text-warning').slideUp();
            },
            success: function (data) {
                var result = null;
                if (data === 'success') {
                    if (link.hasClass('dismiss')) {
                        result = $('<p class="text-warning">Dismissed</p>');
                        link.closest('tr').addClass('bg-warning');
                    } else {
                        result = $('<p class="text-success">Approved</p>');
                        link.closest('tr').addClass('bg-success');
                    }
                    result.hide();
                    link.closest('td').append(result);
                    link.closest('span.actions').slideUp(300, function () {
                        result.slideDown();
                    });
                } else {
                    result = $('<p class="text-danger">Error</p>');
                    link.closest('td').append(result);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
                var msg = 'There was an error updating this respondent\'s status. ';
                msg += 'Please try again or contact us for assistance.';
                alert(msg);
            },
            complete: function () {
                link.find('.loading').remove();
                link.parent().children('a').removeClass('disabled');
                unapprovedRespondents.checkBulkActionsComplete();
            }
        });
    }
};

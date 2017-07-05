var surveyInvitationForm = {
    counter: 1,
    already_invited: [],
    uninvited_respondents: [],
    cookieKey: 'invitationFormData',
    cookieExpiration: 365, // in days
    rowLimit: 20,
    surveyId: null,

    init: function (params) {
        this.counter = params.counter;
        this.already_invited = params.already_invited;
        this.uninvited_respondents = params.uninvited_respondents;
        this.surveyId = params.surveyId;

        // Show first row and all rows with values, hide others
        $('#UserClientInviteForm tbody tr').each(function () {
            var row = $(this);

            if (row.is(':first-child')) {
                surveyInvitationForm.showRow(row);
                return;
            }

            var inputs = row.find('input');
            for (var n = 0; n < inputs.length; n++) {
                if ($(inputs[n]).val()) {
                    surveyInvitationForm.showRow(row);
                    return;
                }
            }

            surveyInvitationForm.removeRow(row);
        });
        this.toggleRemoveButtons();

        // Set up buttons
        $('#add_another').click(function (event) {
            event.preventDefault();
            surveyInvitationForm.showRow();
        });
        $('#sent_invitations_toggler').click(function (event) {
            event.preventDefault();
            $('#sent_invitations').slideToggle();
        });
        $('#suggestions_toggler').click(function (event) {
            event.preventDefault();
            $('#invitation_suggestions').slideToggle();
        });
        $('#UserClientInviteForm').submit(function (event) {
            var form = $(this);

            // Note redundant emails
            if (form.find('.already_invited').length > 0) {
                alert('Please remove any email addresses that have already been recorded before continuing.');
                event.preventDefault();
                return false;
            }

            // Note any blank fields
            var inputs = form.find('input:visible');
            for (var i = 0; i < inputs.length; i++) {
                if ($(inputs[i]).val() === '') {
                    alert('All fields (name, email, and professional title) must be filled out before continuing.');
                    event.preventDefault();
                    return false;
                }
            }

            return true;
        });
        $('#UserClientInviteForm button.remove').click(function () {
            surveyInvitationForm.removeRow($(this).parents('tr'));
        });
        $('#clear-data').click(function (event) {
            event.preventDefault();
            var link = $(this);
            var resultsContainer = $('#clear-data-results');
            $.ajax({
                url: '/surveys/clear-saved-invitation-data/'+surveyInvitationForm.surveyId,
                dataType: 'json',
                beforeSend: function () {
                    link.addClass('disabled');
                    var loading_indicator = $('<img src="/data_center/img/loading_small.gif" class="loading" />');
                    link.append(loading_indicator);
                    if (resultsContainer.is(':visible')) {
                        resultsContainer.hide();
                    }
                },
                success: function (data) {
                    resultsContainer.attr('class', 'text-success');
                    resultsContainer.html('<span class="glyphicon glyphicon-ok"></span> Saved data cleared');
                    resultsContainer.fadeIn(200);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    resultsContainer.attr('class', 'text-danger');
                    resultsContainer.html('<span class="glyphicon glyphicon-warning-sign"></span> Error clearing data');
                    resultsContainer.fadeIn(200);
                },
                complete: function () {
                    link.removeClass('disabled');
                    link.find('.loading').remove();
                }
            });
        });

        // Set up form protection
        formProtector.protect('UserClientInviteForm', {});

        // Set up email trimming / checking
        $('#UserClientInviteForm input[type=email]').change(function () {
            var field = $(this);

            // Trim whitespace
            var trimmedInput = field.val().trim();
            field.val(trimmedInput);

            // Check validity of email
            surveyInvitationForm.checkEmail(field);
        });
    },

    uploadDone: function () {
        $('#upload-progress').slideUp(200);
        var uploadToggle = $('#toggle-upload');
        uploadToggle.removeClass('disabled');
        uploadToggle.find('img').remove();
    },

    displayUploadResult: function (msg, className) {
        var container = $('#upload-result');
        var showMsg = function () {
            container.attr('class', className);
            container.html(msg);
            container.slideDown(300);
        };
        if (container.is(':visible')) {
            container.slideUp(300, showMsg);
        } else {
            showMsg();
        }
    },

    /**
     * If a row is provided, shows that row. Otherwise, shows next hidden row
     *
     * @param row
     */
    showRow: function (row) {
        if (row) {
            row = $(row);
        } else {
            row = $('#UserClientInviteForm tbody tr').not(':visible').first();
        }

        row.css('display', 'table-row');
        row.find('input').prop('required', true);

        var visibleCount = $('#UserClientInviteForm tbody tr:visible').length;

        if (visibleCount >= this.rowLimit) {
            if ($('#limit-warning').length === 0) {
                var warning = $('<p id="limit-warning" class="alert alert-info"></p>');
                warning.html("Sorry, at the moment only "+this.rowLimit+" invitations can be sent out at a time.");
                warning.hide();
                $('#UserClientInviteForm table').after(warning);
                warning.slideDown(500);
            }
            $('#add_another').hide();
        }

        this.toggleRemoveButtons();
    },

    /**
     * Hides a row, clears its input, and places it at the bottom of the table
     *
     * @param row
     */
    removeRow: function (row) {
        row = $(row);
        row.hide();
        row.find('input').val('');
        row.find('input').prop('required', false);
        $('#UserClientInviteForm tbody').append(row.detach());

        if (! $('#add_another').is(':visible')) {
            $('#add_another').show();
        }

        var visibleRowCount = $('#UserClientInviteForm tbody tr:visible').length;
        if (visibleRowCount < this.rowLimit) {
            $('#limit-warning').slideUp(function () {
                $(this).remove();
            });
        }

        this.toggleRemoveButtons();
    },

    /**
     * Hides all 'remove' buttons if only one row is visible
     */
    toggleRemoveButtons: function () {
        if ($('#UserClientInviteForm tbody tr:visible').length == 1) {
            $('button.remove').hide();
        } else {
            $('button.remove').show();
        }
    },

    checkEmail: function (field) {
        var email = field.val();
        if (email === '') {
            return;
        }
        var container = field.closest('td');
        container.children('.error-message').remove();
        var error_msg = null;
        if (this.isInvitedRespondent(email)) {
            error_msg = $('<div class="error-message already_invited">An invitation has already been sent to '+email+'</div>');
            container.append(error_msg);
        } else {
            error_msg = field.parent('td').children('.error-message');
            error_msg.removeClass('already_invited');
            error_msg.slideUp(function () {
                $(this).remove();
            });
        }
    },

    isInvitedRespondent: function (email) {
        return this.already_invited.indexOf(email) != -1;
    },

    isUninvitedRespondent: function (email) {
        return this.uninvited_respondents.indexOf(email) != -1;
    },

    lastRowIsBlank: function () {
        var row = $('#UserClientInviteForm tbody tr:last-child');
        if (row.find('input[name*="[name]"]').val() !== '') {
            return false;
        }
        if (row.find('input[name*="[email]"]').val() !== '') {
            return false;
        }
        if (row.find('input[name*="[title]"]').val() !== '') {
            return false;
        }
        return true;
    }
};

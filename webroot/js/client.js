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
        
        this.addRow();
        
        $('#add_another').click(function (event) {
            event.preventDefault();
            surveyInvitationForm.addRow();
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
            if (form.find('.already_invited').length > 0) {
                alert('Please remove any email addresses that have already been recorded before continuing.');
                event.preventDefault();
                return false;
            }
            return true;
        });
        
        // Set up form protection
        formProtector.protect('UserClientInviteForm', {
            ignore: ['spreadsheet-upload-input']
        });
        
        // Set up spreadsheet uploading
        $('#toggle-upload').click(function (event) {
            event.preventDefault();
            $('#upload-container').slideToggle(300);
        });
        $('#spreadsheet-upload').fileupload({
            dataType: 'json',
            url: '/client/surveys/upload-invitation-spreadsheet/'+this.surveyId,
            add: function (e, data) {
                $('#upload-progress').slideDown(200);
                var loadingIndicator = $('<img src="/data_center/img/loading_small.gif" alt="(loading...)" />');
                $('#toggle-upload').prepend(loadingIndicator).addClass('disabled');
                var resultContainer = $('#upload-result');
                if (resultContainer.is(':visible')) {
                    resultContainer.slideUp(300);
                }
                data.submit();
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#upload-progress .progress-bar')
                    .css('width', progress + '%')
                    .html(progress + '%');
            },
            done: function (e, data) {
                surveyInvitationForm.uploadDone();
                var invitees = data.result.data;
                if (invitees.length === 0) {
                    surveyInvitationForm.displayUploadResult('No invitation information found in spreadsheet', 'alert alert-danger');
                } else {
                    surveyInvitationForm.insertSpreadsheetData(data.result.data);
                    var message = data.result.message;
                    surveyInvitationForm.displayUploadResult(message, 'alert alert-success');
                }
                console.log(data.result);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                surveyInvitationForm.uploadDone();
                var message = '';
                try {
                    var response = JSON.parse(jqXHR.responseText);
                    message = response.message;
                } catch(error) {
                    message = 'There was an error reading that spreadsheet.';
                }
                surveyInvitationForm.displayUploadResult(message, 'alert alert-danger');
            }
        });

        // Set up alternate submit modes
        $('#UserClientInviteForm button[type=submit]').click(function (event) {
            event.preventDefault();
            var buttonId = $(this).attr('id');
            var mode = buttonId.replace('invitations-', '');
            $('#UserClientInviteForm input[name=submit_mode]').attr('value', mode);
            $('#UserClientInviteForm').submit();
        });
        $('#UserClientInviteForm').submit(function (event) {
            var mode = $('#UserClientInviteForm input[name=submit_mode]').attr('value');
            if (mode != 'save' && mode == 'send') {
                alert('Error submitting form. Please try again or email cri@bsu.edu for assistance.');
                event.preventDefault();
            }
        });

    },
    
    insertSpreadsheetData: function (data) {
        for (var i = 0; i < data.length; i++) {
            var invitee = data[i];
            if (! invitee.hasOwnProperty('name') || ! invitee.hasOwnProperty('email') || ! invitee.hasOwnProperty('title')) {
                continue;
            }
            if (! this.lastRowIsBlank()) {
                this.addRow();
            }
            var row = $('#UserClientInviteForm tbody.input tr:last-child');
            row.find('input[name*="[name]"]').val(invitee.name);
            row.find('input[name*="[email]"]').val(invitee.email);
            row.find('input[name*="[title]"]').val(invitee.title);
        }
        
        // Make sure formProtector knows about these new fields
        formProtector.protect('UserClientInviteForm', {
            ignore: ['spreadsheet-upload-input']
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
    
    addRow: function () {
        var template_container = $('#invitation_fields_template');
        var new_container = template_container.clone();
        new_container.attr('id', '');
        new_container.find('input[type=email], input[type=text]').each(function () {
            var field = $(this);
            field.prop('disabled', false);
            field.attr('id', '');
            field.change(function () {
                var field = $(this);
                var trimmed_input = field.val().trim();
                field.val(trimmed_input);
                if (field.attr('type') == 'email') {
                    surveyInvitationForm.checkEmail(field);
                }
            });
            var fieldname = field.attr('name').replace('0', surveyInvitationForm.counter);
            field.attr('name', fieldname);
        });
        new_container.find('button.remove').click(function () {
            $(this).parents('tr').remove();
            if (! $('#add_another').is(':visible')) {
                $('#add_another').show();
            }
        });
        this.counter++;
        $('#UserClientInviteForm tbody.input').append(new_container);
        
        var rowCount = $('#UserClientInviteForm tbody.input tr').length;
        if (rowCount >= this.rowLimit) {
            if ($('#limit-warning').length === 0) {
                var warning = $('<p id="limit-warning" class="alert alert-info"></p>');
                warning.html("Sorry, at the moment only "+this.rowLimit+" invitations can be sent out at a time.");
                warning.hide();
                $('#UserClientInviteForm table').after(warning);
                warning.slideDown(500);
            }
            $('#add_another').hide();
        }
        
        // Make sure formProtector knows about these new fields
        formProtector.protect('UserClientInviteForm', {
            ignore: ['spreadsheet-upload-input']
        });
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
        var row = $('#UserClientInviteForm tbody.input tr:last-child');
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

var clientHome = {
    init: function () {
        this.setupImport();
        this.setupToggledContainers();
        
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
            } else {
                var errorList = resultsContainer.find('ul');
                var errorToggler = $('<button class="btn btn-default btn-sm">Show</button>');
                errorToggler.click(function (event) {
                    event.preventDefault();
                    errorList.slideToggle();
                });
                errorList.before(errorToggler);
                errorList.hide();
            }
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
        var steps = $('#client_home > table > tbody');
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
    }
};

var unapprovedRespondents = {
    init: function () {
        $('#toggle_dismissed').click(function (event) {
            event.preventDefault();
            $('#dismissed_respondents > div').slideToggle();
        });
        $('a.approve, a.dismiss').click(function (event) {
            event.preventDefault();
            var link = $(this);
            unapprovedRespondents.updateRespondent(link);
        });
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
                if (data == 'success') {
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
                alert('There was an error updating this respondent\'s status. Please try again or contact us for assistance.');
            },
            complete: function () {
                link.find('.loading').remove();
                link.parent().children('a').removeClass('disabled');
            }
        });
    }
};

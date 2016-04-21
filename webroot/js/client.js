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
        
        this.addRow(false);
        
        $('#add_another').click(function (event) {
            event.preventDefault();
            surveyInvitationForm.addRow(true);
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
        
        // Set up form saving
        Cookies.json = true;
        $('#save').click(function (event) {
            event.preventDefault();
            surveyInvitationForm.save();
        });
        $('#load').click(function (event) {
            event.preventDefault();
            var isBlank = surveyInvitationForm.allRowsAreBlank();
            var msg = 'Replace entered information with saved information?';
            if (isBlank || confirm(msg)) {
                surveyInvitationForm.load();
            }
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
    
    save: function () {
        var rows = $('#UserClientInviteForm tr');
        var cookieData = [];
        for (var i = 0; i < rows.length; i++) {
            var name = $(rows[i]).find('input[name*="[name]"]').val();
            var email = $(rows[i]).find('input[name*="[email]"]').val();
            var title = $(rows[i]).find('input[name*="[title]"]').val();
            if (name === '' && email === '' && title === '') {
                continue;
            }
            var row = {
                name: name,
                email: email,
                title: title
            };
            cookieData.push(row);
        }
        Cookies.set(this.cookieKey, cookieData, {expires: this.cookieExpiration});
        $('#survey-invitation-save-status').html('<span id="invitation-form-loading" class="text-muted"><img src="/data_center/img/loading_small.gif" /> Saving...</span>');
        setTimeout(function () {
            $('#survey-invitation-save-status').html('<span class="text-success">Saved</span>');
        }, 1000);
        formProtector.setSaved('UserClientInviteForm');
    },
    
    load: function () {
        var cookieData = Cookies.get(this.cookieKey);
        if (typeof cookieData == 'undefined' || cookieData.length === 0) {
            alert('No saved data was found');
            return;
        }
        $('#UserClientInviteForm tbody.input tr').remove();
        for (var i = 0; i < cookieData.length; i++) {
            var data = cookieData[i];
            if (! data.hasOwnProperty('name') || ! data.hasOwnProperty('email') || ! data.hasOwnProperty('title')) {
                continue;
            }
            if (! this.lastRowIsBlank()) {
                this.addRow();
            }
            var row = $('#UserClientInviteForm tbody.input tr:last-child');
            row.find('input[name*="[name]"]').val(data.name);
            row.find('input[name*="[email]"]').val(data.email);
            row.find('input[name*="[title]"]').val(data.title);
        }
        $('#survey-invitation-save-status').html('<span class="text-success">Loaded</span>');
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
    },
    
    allRowsAreBlank: function () {
        var rows = $('#UserClientInviteForm tbody.input tr');
        for (var i = 0; i < rows.length; i++) {
            var row = $(rows[i]);
            if (row.find('input[name*="[name]"]').val() !== '') {
                return false;
            }
            if (row.find('input[name*="[email]"]').val() !== '') {
                return false;
            }
            if (row.find('input[name*="[title]"]').val() !== '') {
                return false;
            }
        }
        return true;
    },
    
    clearSavedData: function () {
        Cookies.remove(this.cookieKey);
    }
};

var clientHome = {
    init: function () {
        this.setupImport();
        
        $('.importing_note_toggler').click(function (event) {
            event.preventDefault();
            var note = $(this).closest('td').find('.importing_note');
            note.slideToggle();
        });
    },
    setupImport: function () {
        $('.import_button').click(function (event) {
            event.preventDefault();
            var link = $(this);
            
            if (link.hasClass('disabled')) {
                return;
            }
            
            var survey_id = link.data('survey-id');
            var row = link.closest('tr');
            $.ajax({
                url: '/surveys/import/'+survey_id,
                beforeSend: function () {
                    link.addClass('disabled');
                    var loading_indicator = $('<img src="/data_center/img/loading_small.gif" class="loading" />');
                    link.append(loading_indicator);
                },
                success: function (data) {
                    var alert = $('<div class="last_import alert alert-success" role="alert">'+data+'</div>');
                    alert.hide();
                    row.find('.last_import').slideUp(function () {
                        $(this).remove();
                    });
                    var info_container = row.children('td:nth-child(2)');
                    info_container.append(alert);
                    alert.slideDown();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    var alert = $('<div class="last_import alert alert-danger" role="alert">'+jqXHR.responseText+'</div>');
                    alert.hide();
                    row.find('.last_import').slideUp(function () {
                        $(this).remove();
                    });
                    var info_container = row.children('td:nth-child(2)');
                    info_container.append(alert);
                    alert.slideDown();
                },
                complete: function () {
                    link.removeClass('disabled');
                    link.find('.loading').remove();
                }
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

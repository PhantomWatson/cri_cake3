var adminUserEdit = {
    community_counter: 0,
    
    init: function (params) {
        var community_container = $('<ul id="community_container"></ul>');
        var community_select = $('#community');
        community_select.after(community_container);
        community_select.prop('selectedIndex', 0);
        
        if (params.selected_communities.length > 0) {
            for (var i = 0; i < params.selected_communities.length; i++) {
                var community = params.selected_communities[i];
                this.addCommunity(community.id, community.name, false);
            }
        }
        
        community_select.change(function () {
            var select = $(this);
            var c_id = select.val();
            var preselected = $('li[data-community-id="'+c_id+'"]');
            if (preselected.length === 0) {
                var c_name = select.find('option:selected').text();
                adminUserEdit.addCommunity(c_id, c_name, true);
            }
            select.prop('selectedIndex', 0);
        });
        
        $('#all-communities-0, #all-communities-1').change(function () {
            adminUserEdit.toggleAllCommunities(true);
        });
        this.toggleAllCommunities(false);
        
        $('#role').change(function () {
            adminUserEdit.onRoleChange(true);
        });
        this.onRoleChange(false);
        
        $('#password-fields-button button').click(function (event) {
            event.preventDefault();
            $('#password-fields-button').slideUp(300);
            $('#password-fields').slideDown(300);
        });
    },
    
    addCommunity: function (id, name, animate) {
        var li = $('<li data-community-id="'+id+'"></li>');
        var link = $('<a href="#"><span class="glyphicon glyphicon-remove"></span> <span class="link_label">'+name+'</span></a>');
        link.click(function (event) {
            event.preventDefault();
            li.slideUp(300, function () {
                li.remove();
            });
        });
        li.append(link);
        li.append('<input type="hidden" name="consultant_communities['+this.community_counter+'][id]" value="'+id+'" />');
        this.community_counter++;
        if (animate) {
            li.hide();
        }
        $('#community_container').prepend(li);
        if (animate) {
            li.slideDown();
        }
    },
    
    toggleAllCommunities: function (animate) {
        if ($('#all-communities-0').is(':checked')) {
            if (animate) {
                $('#community').slideDown();
                $('#community_container').slideDown();
            } else {
                $('#community').show();
                $('#community_container').show();
            }
        } else {
            if (animate) {
                $('#community').slideUp();
                $('#community_container').slideUp();
            } else {
                $('#community').hide();
                $('#community_container').hide();
            }
        }
    },
    
    onRoleChange: function (animate) {
        var role = $('#role').val();
        var duration = animate ? 300 : 0;
        if (role == 'consultant') {
            $('#consultant_communities').slideDown(duration);
            $('#client_communities').slideUp(duration);
        } else if (role == 'client') {
            $('#client_communities').slideDown(duration);
            $('#consultant_communities').slideUp(duration);
        } else {
            $('#consultant_communities').slideUp(duration);
            $('#client_communities').slideUp(duration);
        }
    }
};

function getRandomPassword() {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < 5; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

var communityForm = {
    community_id: null,
    areaTypes: null,
    
    init: function (params) {
        this.community_id = params.community_id;
        this.areaTypes = params.areaTypes;
        
        $('#meeting-date-set-0, #meeting-date-set-1').change(function () {
            communityForm.toggleDateFields(true);
        });
        this.toggleDateFields(false);
        this.setupAreaSelection();
    },
    
    toggleDateFields: function (animate) {
        if ($('#meeting-date-set-0').is(':checked')) {
            if (animate) {
                $('#meeting_date_fields').slideUp();
            } else {
                $('#meeting_date_fields').hide();
            }
        }
        if ($('#meeting-date-set-1').is(':checked')) {
            if (animate) {
                $('#meeting_date_fields').slideDown();
            } else {
                $('#meeting_date_fields').show();
            }
        }
    },
    
    setupAreaSelection: function () {
        $('#local-area-id, #parent-area-id').each(function () {
            var areaSelector = $(this);
            
            // Insert type selector
            var typeSelector = $('<select class="form-control"></select>');
            for (var i = 0; i < communityForm.areaTypes.length; i++) {
                var type = communityForm.areaTypes[i];
                typeSelector.append('<option value="'+type+'">'+type+'</option>');
            }
            areaSelector.before(typeSelector);
            typeSelector.change(function (event) {
                var type = $(this).val();
                communityForm.changeAreaType(areaSelector, type);
            });
            
            // Set type selector to correct value (or a default value)
            var selected = areaSelector.find('option:selected');
            var selectedType = '';
            if (selected.length === 0 || selected.val() === '') {
                if (areaSelector.attr('id') == 'parent-area-id') {
                    selectedType = 'County';
                } else {
                    selectedType = 'City';
                }
            } else {
                selectedType = selected.parent('optgroup').attr('label');
            }
            typeSelector.find('option[value="'+selectedType+'"]').prop('selected', true);
            communityForm.changeAreaType(areaSelector, selectedType);
        });
    },
    
    changeAreaType: function (areaSelector, type) {
        areaSelector.find('optgroup[label="'+type+'"]').show();
        areaSelector.find('optgroup').not('[label="'+type+'"]').hide();
    }
};

var adminSurveysIndex = {
    init: function () {
        $('#surveys_admin_index .help_toggler').click(function (event) {
            event.preventDefault();
            $('#surveys_admin_index .help_message').slideToggle();
        });
    }
};

var adminViewResponses = {
    init: function () {
        $('.custom_alignment_calc').change(function () {
            var container = $(this).closest('.responses');
            adminViewResponses.updateAlignment(container);
            adminViewResponses.updateRespondentCount(container);
        });
        $('.calc-mode').change(function (event) {
            event.preventDefault();
            var container = $(this).closest('.responses');
            var mode = $(this).val();
            container.find('td.selected, th.selected').toggle(mode == 'selected');
            adminViewResponses.updateRespondentCount(container);
            adminViewResponses.updateAlignment(container);
        });
        var showRespondentsLabel = {
            show: '<span class="glyphicon glyphicon-user"></span> Show respondent info',
            hide: '<span class="glyphicon glyphicon-user"></span> Hide respondent info'
        };
        $('#show-respondents')
            .html(showRespondentsLabel.show)
            .click(function (event) {
                event.preventDefault();
                var button = $(this);
                if (button.data('label') == 'show') {
                    $('tr.respondent').show();
                    button.data('label', 'hide');
                    button.html(showRespondentsLabel.hide);
                } else {
                    $('tr.respondent').hide();
                    button.data('label', 'show');
                    button.html(showRespondentsLabel.show);
                }
            });
        $('ul.nav-tabs li[role=presentation]').first().find('a').tab('show');
        var toggleFullscreenLabel = {
            fullscreen: '<span class="glyphicon glyphicon-fullscreen"></span> <span class="text">Show table full size</span>',
            window: '<span class="glyphicon glyphicon-list-alt"></span> <span class="text">Show table in window</span>'
        };
        $('#toggle-table-scroll')
            .html(toggleFullscreenLabel.fullscreen)
            .data('mode', 'scrolling')
            .click(function (event) {
                event.preventDefault();
                var link = $(this);
                var containers = $('#admin-responses-view .tab-pane > .responses > div');
                if (link.data('mode') == 'scrolling') {
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
            url: '/admin/responses/get-full-response/'+respondentId,
            dataType: 'json',
            beforeSend: function (xhr) {
                var modal = $('#full-response-modal');
                modal.find('.modal-body').html('Loading... <img src="/data_center/img/loading_small.gif" />');
                modal.modal();
            },
            success: function (data, textStatus, jqXHR) {
                var modal = $('#full-response-modal');
                var modalBody = modal.find('.modal-body');
                modalBody.html('');
                var response = data.response;
                for (var heading in response) {
                    modalBody.append('<h3>'+heading+'</h3>');
                    var answerList = $('<ul></ul>');
                    var answers = response[heading];
                    for (var i = 0; i < answers.length; i++) {
                        answerList.append('<li>'+answers[i]+'</li>');
                    }
                    modalBody.append(answerList);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var response = $.parseJSON(jqXHR.responseText);
                var msg = '';
                if (response.hasOwnProperty('message')) {
                    msg = response.message;
                } else {
                    msg = 'There was an error loading that response';
                }
                $('#full-response-modal .modal-body').html('<span class="text-danger">'+msg+'</span>');
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
        resultContainer.html(average+'%');
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
    }
};

var adminCommunitiesIndex = {
    init: function () {
        $('a.survey_link_toggler').click(function (event) {
            event.preventDefault();
            $(this).siblings('.survey_links').slideToggle(200);
        });
        $('#search_toggler').click(function (event) {
            event.preventDefault();
            var form = $('#admin_community_search_form');
            if (form.is(':visible')) {
                form.slideUp(200);
            } else {
                form.slideDown(200);
                form.children('input').focus();
            }
        });
        $('#admin_community_search_form input[type="text"]').bind("change paste keyup", function() {
            var matching = $(this).val();
            adminCommunitiesIndex.filter(matching);
        });
        $('#glossary_toggler').click(function (event) {
            event.preventDefault();
            $('#glossary').slideToggle();
        });
    },
    
    filter: function (matching) {
        if (matching === '') {
            $('table.communities tbody tr').show();
            return;
        }
        $('table.communities tbody tr').each(function () {
            var row = $(this);
            var communityName = row.data('community-name').toLowerCase();
            matching = matching.toLowerCase();
            if (communityName.search(matching) == -1) {
                row.hide();
            } else {
                row.show();
            }
        });
    }
};

var adminPurchasesIndex = {
    init: function () {
        $('button.refunded, button.details').click(function (event) {
            event.preventDefault();
            $(this).closest('tr').next('tr.details').find('ul').slideToggle();
        });
    }
};

var surveyLink = {
    community_id: null,
    survey_type: null,
    
    init: function (params) {
        this.community_id = params.community_id;
        this.survey_type = params.type;
        this.setupSurveyLinking();

        if ($('#survey-link-buttons').data('is-new') === 1) {
            $('#survey-link-submit').prop('disabled', true);
        }
    },
    
    setupSurveyLinking: function () {
        $('.link_survey').each(function () {
            var container = $(this);
            
            container.find('button.lookup').click(function (event) {
                event.preventDefault();
                var results_container = container.find('.lookup_results');
                if (results_container.is(':visible')) {
                    results_container.slideUp();
                } else {
                    surveyLink.lookupUrl(container);
                }
            });
            
            container.find('button.show_details').click(function (event) {
                event.preventDefault();
                container.find('.details').slideToggle();
            });
        });
    },
    
    lookupUrl: function (container) {
        var lookup_link = container.find('button.lookup');
        var lookup_url = '/surveys/get_survey_list';
        var results_container = container.find('.lookup_results');
        var loadingMessages = $('.loading_messages');
        
        $.ajax({
            url: lookup_url,
            beforeSend: function () {
                lookup_link.addClass('disabled');
                var loading_indicator = $('<img src="/data_center/img/loading_small.gif" alt="Loading..." />');
                lookup_link.append(loading_indicator);
            },
            success: function (data) {
                data = jQuery.parseJSON(data);
                results_container.empty();
                if (data.length === 0) {
                    results_container.append('<p class="alert alert-danger">Error: No questionnaires found</p>');
                    return;
                }
                results_container.append('<p>Please select the correct SurveyMonkey questionnaire:</p>');
                var list = $('<ul></ul>');
                function clickCallback(event) {
                    return function (event) {
                        event.preventDefault();
                        var sm_id = $(this).data('survey-id');
                        var url = $(this).data('survey-url');
                        surveyLink.checkSurveyAssignment(container, sm_id, function () {
                            surveyLink.setQnaIds(container, sm_id, function () {
                                surveyLink.selectSurvey(container, sm_id, url);
                            });
                        });
                    };
                }
                for (var i = 0; i < data.length; i++) {
                    var sm_id = data[i].sm_id;
                    var url = data[i].url;
                    var title = data[i].title;
                    var link = $('<a href="#" data-survey-id="'+sm_id+'" data-survey-url="'+url+'">'+title+'</a>');
                    link.click(clickCallback());
                    var li = $('<li></li>').append(link);
                    list.append(li);
                }
                results_container.append(list);
                results_container.slideDown();
            },
            error: function () {
                var msg = '<p class="alert alert-danger">Error: No questionnaires found</p>';
                if (results_container.is(':visible')) {
                    results_container.slideUp(300, function () {
                        results_container.html(msg);
                        results_container.slideDown(300);
                    });
                } else {
                    results_container.html(msg);
                    results_container.slideDown(300);
                }
            },
            complete: function () {
                lookup_link.removeClass('disabled');
                lookup_link.children('img').remove();
            }
        });
    },
    
    checkSurveyAssignment: function (container, sm_id, success_callback) {
        var url_field = $('#sm-url');
        var loadingMessages = $('.loading_messages');
        
        $.ajax({
            url: '/surveys/check_survey_assignment/'+sm_id,
            dataType: 'json',
            beforeSend: function () {
                loadingMessages.html('<span class="loading"><img src="/data_center/img/loading_small.gif" /> Checking questionnaire uniqueness...</span>');
            },
            success: function (data) {
                var displayError = function (msg) {
                    $('.loading_messages').html('<span class="label label-danger">Error</span><p class="url_error">'+msg+'</p>');
                };
                if (data === null) {
                    loadingMessages.html(' ');
                    success_callback();
                } else if (data.id != surveyLink.community_id) {
                    displayError('That questionnaire is already assigned to another community: <a href="/admin/communities/edit/'+data.id+'">'+data.name+'</a>');
                } else if (data.type != surveyLink.survey_type) {
                    displayError('That questionnaire is already linked as this community\'s community '+data.type+'s questionnaire.');
                } else {
                    loadingMessages.html(' ');
                    success_callback();
                }
            },
            error: function (jqXHR, errorType, exception) {
                loadingMessages.html('<p class="url_error"><span class="label label-danger">Error</span> Error checking questionnaire uniqueness. </p>');
                var retry_link = $('<a href="#" class="retry">Retry</a>');
                retry_link.click(function (event) {
                    event.preventDefault();
                    surveyLink.checkSurveyAssignment(container, sm_id, success_callback);
                });
                loadingMessages.find('p').append(retry_link);
            }
        });
    },
    
    setQnaIds: function (container, sm_id, success_callback) {
        var loadingMessages = container.find('.loading_messages');
        var displayError = function (message) {
            var retry_link = $('<a href="#" class="retry">Retry</a>');
            retry_link.click(function (event) {
                event.preventDefault();
                surveyLink.setQnaIds(container, sm_id, success_callback);
            });
            
            loadingMessages.html('<p class="url_error"><span class="label label-danger">Error</span> '+message+' </p>');
            loadingMessages.find('p').append(retry_link);
        };
        
        $.ajax({
            url: '/surveys/get_qna_ids/'+sm_id,
            beforeSend: function () {
                loadingMessages.html('<span class="loading"><img src="/data_center/img/loading_small.gif" /> Extracting PWR<sup>3</sup> question info...</span>');
            },
            success: function (data) {
                data = jQuery.parseJSON(data);
                var success = data[0];
                if (success) {
                    var fields = data[2];
                    for (var fieldname in fields) {
                        var hidden_field = container.find("input[data-fieldname='"+fieldname+"']");
                        var id = fields[fieldname];
                        hidden_field.val(id);
                    }
                    success_callback();
                } else {
                    var error_msg = data[1];
                    displayError(error_msg);
                }
            },
            error: function (jqXHR, errorType, exception) {
                displayError('Error extracting PWR<sup>3</sup> question info');
            },
            complete: function () {
                container.find('.loading').remove();
            }
        });
    },
    
    selectSurvey: function (container, sm_id, url) { console.log('sm_id is '+sm_id); console.log('url is '+url);
        var results_container = container.find('.lookup_results');
        
        // Clean up appearance
        if (results_container.is(':visible')) {
            results_container.slideUp();
        }
        container.find('.url_error, .retry').remove();

        // Enable submit button
        $('#survey-link-submit').prop('disabled', false);

        // Assign ID
        var id_field = $('#sm-id');
        id_field.val(sm_id);
        
        // Assign URL if available
        var url_field = $('#sm-url');
        var linkStatus = container.find('.link_status');
        var surveyUrl = container.find('span.survey_url');
        var readyStatusMsg = '<span class="text-warning"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Ready to be linked</span>';
        if (url) {
            url_field.val(url);
            linkStatus.html(readyStatusMsg);            
            surveyUrl.html('<a href="'+url+'">'+url+'</a>');
            return;
        }
        
        // Begin lookup of URL if not
        url_field.val('');
        var loadingMessages = $('.loading_messages');
        $.ajax({
            url: '/surveys/get_survey_url/'+sm_id,
            beforeSend: function () {
                url_field.prop('disabled', true);
                var loading_indicator = '<span class="loading"><img src="/data_center/img/loading_small.gif" /> Retrieving URL...</span>';
                loadingMessages.html(loading_indicator);
            },
            success: function (data) {
                url_field.val(data);
                linkStatus.html(readyStatusMsg);
                surveyUrl.html('<a href="'+data+'">'+data+'</a>');
            },
            error: function (jqXHR, errorType, exception) {
                var error_msg = 'No URL found for this questionnaire. Web link collector may not be configured yet.';
                loadingMessages.html('<p class="url_error"><span class="label label-danger">Error</span> '+error_msg+' </p>');
                var retry_link = $('<a href="#" class="retry">Retry</a>');
                retry_link.click(function (event) {
                    event.preventDefault();
                    surveyLink.checkSurveyAssignment(container, sm_id, function () {
                        surveyLink.setQnaIds(container, sm_id, function () {
                            surveyLink.selectSurvey(container, sm_id, url);
                        });
                    });
                });
                loadingMessages.find('p').append(retry_link);
            },
            complete: function () {
                url_field.prop('disabled', false);
                container.find('.loading').remove();
            }
        });
    }
};

var surveyOverview = {
    community_id: null,

    init: function (params) {
        this.community_id = params.community_id;
        this.setupImport();
        
        $('.invitations_toggler').click(function (event) {
            event.preventDefault();
            $(this).closest('div.panel-body').find('.invitations_list').slideToggle();
        });
    },
    setupImport: function () {
        var resultsContainer = $('#import-results');
        if (resultsContainer.is(':empty')) {
            resultsContainer.hide();
        } else {
            var errorList = resultsContainer.find('ul');
            var errorToggler = $('<button class="btn btn-default btn-sm">Show errors</button>');
            errorToggler.click(function (event) {
                event.preventDefault();
                errorList.slideToggle();
            });
            errorList.before(errorToggler);
            errorList.hide();
        }
        
        $('.import_button').click(function (event) {
            event.preventDefault();
            var link = $(this);
            
            if (link.hasClass('disabled')) {
                return;
            }
            
            var survey_id = link.data('survey-id');
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
                    resultsContainer.attr('class', 'alert alert-success');
                    resultsContainer.html(data);
                    resultsContainer.slideDown();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    resultsContainer.attr('class', 'alert alert-danger');
                    resultsContainer.html(jqXHR.responseText);
                    resultsContainer.slideDown();
                },
                complete: function () {
                    link.removeClass('disabled');
                    link.find('.loading').remove();
                    link.parent().children('.last_import_time').html('Responses were last imported a moment ago');
                }
            });
        });
    }
};

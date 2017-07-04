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
                    results_container.append('<p class="alert alert-info">No questionnaires found</p>');
                    results_container.slideDown();
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
                    var link = $('<a href="#" data-survey-id="' + sm_id + '" data-survey-url="' + url + '"></a>');
                    link.html(title);
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
        var loadingMessages = $('.loading_messages');

        $.ajax({
            url: '/surveys/check_survey_assignment/' + sm_id,
            dataType: 'json',
            beforeSend: function () {
                var msg = '<img src="/data_center/img/loading_small.gif" /> Checking questionnaire uniqueness...';
                msg = '<span class="loading">' + msg + '</span>';
                loadingMessages.html(msg);
            },
            success: function (data) {
                var displayError = function (msg) {
                    msg = '<span class="label label-danger">Error</span><p class="url_error">' + msg + '</p>';
                    $('.loading_messages').html(msg);
                };
                var msg;
                if (data.hasOwnProperty('community')) {
                    data = data.community;
                }
                if (data === null) {
                    loadingMessages.html(' ');
                    success_callback();
                } else if (data.id !== surveyLink.community_id) {
                    msg = 'That questionnaire is already assigned to another community: ';
                    msg += '<a href="/admin/communities/edit/' + data.id + '">' + data.name + '</a>';
                    displayError(msg);
                } else if (data.type !== surveyLink.survey_type) {
                    msg = 'That questionnaire is already linked as this community\'s community ';
                    msg += data.type + 's questionnaire.';
                    displayError(msg);
                } else {
                    loadingMessages.html(' ');
                    success_callback();
                }
            },
            error: function (jqXHR, errorType, exception) {
                var msg = '<span class="label label-danger">Error</span> Error checking questionnaire uniqueness. ';
                msg = '<p class="url_error">' + msg + '</p>';
                loadingMessages.html(msg);
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

            var msg = '<p class="url_error"><span class="label label-danger">Error</span> ' + message + ' </p>';
            loadingMessages.html(msg);
            loadingMessages.find('p').append(retry_link);
        };

        $.ajax({
            url: '/surveys/get_qna_ids/' + sm_id,
            beforeSend: function () {
                var msg = 'Extracting PWR<sup>3</sup> question info...';
                msg = '<span class="loading"><img src="/data_center/img/loading_small.gif" /> ' + msg + '</span>';
                loadingMessages.html(msg);
            },
            success: function (data) {
                data = jQuery.parseJSON(data);
                var success = data[0];
                if (success) {

                    // Make sure all required fields have values
                    var fields = data[2];
                    var officialsExclusiveFields = [
                        'aware_of_plan_qid',
                        'aware_of_city_plan_aid',
                        'aware_of_county_plan_aid',
                        'aware_of_regional_plan_aid',
                        'unaware_of_plan_aid'
                    ];
                    var surveyType = surveyLink.getSurveyType();
                    for (var fieldname in fields) {
                        if (fieldname.search('_aid') === -1 && fieldname.search('_qid') === -1) {
                            continue;
                        }
                        var hidden_field = container.find("input[data-fieldname='" + fieldname + "']");
                        var id = fields[fieldname];

                        // Skip over inapplicable fields
                        if (surveyType !== 'official' && officialsExclusiveFields.indexOf(fieldname) !== -1) {
                            continue;
                        }

                        if (! id) {
                            displayError('This questionnaire is missing a question or answer ID (' + fieldname + ')');
                            return;
                        }
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

    getSurveyType: function () {
        return $('#surveyType').val();
    },

    selectSurvey: function (container, sm_id, url) {
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
        var readyStatusMsg = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
        readyStatusMsg += 'Ready to be linked';
        readyStatusMsg = '<span class="text-warning">' + readyStatusMsg + '</span>';
        if (url) {
            url_field.val(url);
            linkStatus.html(readyStatusMsg);
            surveyUrl.html('<a href="' + url + '">' + url + '</a>');
            return;
        }

        // Begin lookup of URL if not
        url_field.val('');
        var loadingMessages = $('.loading_messages');
        $.ajax({
            url: '/surveys/get_survey_url/' + sm_id,
            beforeSend: function () {
                url_field.prop('disabled', true);
                var loadingIndicator = '<img src="/data_center/img/loading_small.gif" /> Retrieving URL...';
                loadingIndicator = '<span class="loading">' + loadingIndicator + '</span>';
                loadingMessages.html(loadingIndicator);
            },
            success: function (data) {
                url_field.val(data);
                linkStatus.html(readyStatusMsg);
                surveyUrl.html('<a href="' + data + '">' + data + '</a>');
            },
            error: function (jqXHR, errorType, exception) {
                var msg = 'No URL found for this questionnaire. Web link collector may not be configured yet.';
                msg = '<p class="url_error"><span class="label label-danger">Error</span> ' + msg + ' </p>';
                loadingMessages.html(msg);
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

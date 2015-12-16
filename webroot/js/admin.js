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
		
		$('#password-fields-button a').click(function (event) {
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
	
	init: function (params) {
		this.community_id = params.community_id;
		
		$('#meeting-date-set-0, #meeting-date-set-1').change(function () {
			communityForm.toggleDateFields(true);
		});
		this.toggleDateFields(false);
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
		$('#admin_responses_view .respondent_popup_handle').click(function (event) {
			event.preventDefault();
			$(this).siblings('.respondent_popup').toggle();
		});
		$('.custom_alignment_calc').change(function () {
			var result_container = $('tfoot td.selected');
			var sum = 0;
			var selected = $('.custom_alignment_calc:checked');
			selected.each(function () {
				var value = $(this).data('alignment');
				sum = value + sum;
			});
			var count = selected.length;
			var average = count ? Math.round(sum / count) : 0;
			result_container.html(average+'%');
		});
		$('#toggle_custom_calc').click(function (event) {
			event.preventDefault();
			$('td.selected, th.selected').toggle();
		});
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
				form.slideUp();
			} else {
				form.slideDown();
				form.children('input').focus();
			}
		});
		$('#admin_community_search_form input[type="text"]').autocomplete({
			source: '/communities/autocomplete',
			minLength: 2,
			select: function (event, ui) {
				$('#admin_community_search_form input[type="text"]').val(ui.item.label);
				var loading_indicator = ' <img src="/data_center/img/loading_small.gif" class="loading" />';
				$('#admin_community_search_form button').append(loading_indicator).addClass('disabled');
				$('#admin_community_search_form').submit();
			},
			search: function (event, ui) {
			    if ($('#admin_community_search_form .loading').length === 0) {
			        var loading_indicator = '<img src="/data_center/img/loading_small.gif" class="loading" alt="loading" />';
	                $('#admin_community_search_form button').after(loading_indicator);
			    }
			},
			response: function (event, ui) {
				$('#admin_community_search_form .loading').remove();
			}
		});
		$('#glossary_toggler').click(function (event) {
			event.preventDefault();
			$('#glossary').slideToggle();
		});
	}
};

var adminPurchasesIndex = {
    init: function () {
        $('a.refunded, a.details').click(function (event) {
            event.preventDefault();
            $(this).closest('tr').next('tr.details').find('ul').slideToggle();
        });
    }
};

var surveyOverview = {
    community_id: null,

    init: function (params) {
        this.community_id = params.community_id;
        this.setupImport();
        this.setupSurveyLinking();
        
        $('.invitations_toggler').click(function (event) {
            event.preventDefault();
            $(this).closest('div.panel-body').find('.invitations_list').slideToggle();
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
            $.ajax({
                url: '/surveys/import/'+survey_id,
                beforeSend: function () {
                    link.addClass('disabled');
                    var loading_indicator = $('<img src="/data_center/img/loading_small.gif" class="loading" />');
                    link.append(loading_indicator);
                },
                success: function (data) {
                    var alert = $('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>'+data+'</div>');
                    alert.hide();
                    link.before(alert);
                    alert.slideDown();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    console.log(textStatus);
                    console.log(errorThrown);
                },
                complete: function () {
                    link.removeClass('disabled');
                    link.find('.loading').remove();
                    link.parent().children('.last_import_time').html('Responses were last imported a moment ago');
                }
            });
        });
    },
    
    setupSurveyLinking: function () {
        $('.link_survey').each(function () {
            var container = $(this);
            
            container.find('a.lookup').click(function (event) {
                event.preventDefault();
                var results_container = container.find('.lookup_results');
                if (results_container.is(':visible')) {
                    results_container.slideUp();
                } else {
                    surveyOverview.lookupUrl(container);
                }
            });
            
            container.find('a.show_details').click(function (event) {
                event.preventDefault();
                container.find('.details').slideToggle();
            });
        });
    },
    
    lookupUrl: function (container) {
        var lookup_link = container.find('a.lookup');
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
                    results_container.append('<p class="alert alert-danger">Error: No surveys found</p>');
                    return;
                }
                results_container.append('<p>Please select the correct SurveyMonkey survey:</p>');
                var list = $('<ul></ul>');
                function clickCallback(event) {
                    return function (event) {
                        event.preventDefault();
                        var sm_id = $(this).data('survey-id');
                        var url = $(this).data('survey-url');
                        surveyOverview.checkSurveyAssignment(container, sm_id, function () {
                            surveyOverview.setQnaIds(container, sm_id, function () {
                                surveyOverview.selectSurvey(container, sm_id, url);
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
                var msg = '<p class="alert alert-danger">Error: No surveys found</p>';
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
        var url_field = container.find('input.survey_url');
        var link_label = container.find('.link_label');
        var link_status = container.find('.link_status');
        
        $.ajax({
            url: '/surveys/check_survey_assignment/'+sm_id,
            dataType: 'json',
            beforeSend: function () {
                var loading_indicator = '<span class="loading"><img src="/data_center/img/loading_small.gif" /> Checking survey uniqueness...</span>';
                link_label.html(loading_indicator);
            },
            success: function (data) {
                if (data === null || data.id == surveyOverview.community_id) {
                    link_label.html(' ');
                    success_callback();
                } else {
                    link_label.html('<span class="label label-danger">Error</span>');
                    link_status.html('<span class="url_error">That survey is already assigned to another community: <a href="/admin/communities/edit/'+data.id+'">'+data.name+'</a></span>');
                }
            },
            error: function (jqXHR, errorType, exception) {
                link_label.html('<span class="label label-danger">Error</span>');
                link_status.html('<span class="url_error">Error checking survey uniqueness</span>');
                var retry_link = $('<a href="#" class="retry">Retry</a>');
                retry_link.click(function (event) {
                    event.preventDefault();
                    surveyOverview.checkSurveyAssignment(container, sm_id, success_callback);
                });
                link_status.append(retry_link);
            }
        });
    },
    
    setQnaIds: function (container, sm_id, success_callback) {
        var link_label = container.find('.link_label');
        var displayError = function (message) {
            var link_status = container.find('.link_status');
            
            var retry_link = $('<a href="#" class="retry">Retry</a>');
            retry_link.click(function (event) {
                event.preventDefault();
                surveyOverview.setQnaIds(container, sm_id, success_callback);
            });
            
            link_label.html('<span class="label label-danger">Error</span>');
            link_status.html('<span class="url_error">'+message+'</span>');
            link_status.append(retry_link);
        };
        
        $.ajax({
            url: '/surveys/get_qna_ids/'+sm_id,
            beforeSend: function () {
                var loading_indicator = '<span class="loading"><img src="/data_center/img/loading_small.gif" /> Extracting PWR<sup>3</sup> question info...</span>';
                link_label.html(loading_indicator);
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
    
    selectSurvey: function (container, sm_id, url) {
        var results_container = container.find('.lookup_results');
        
        // Clean up appearance
        if (results_container.is(':visible')) {
            results_container.slideUp();
        }
        container.find('.url_error, .retry').remove();
        
        // Assign ID
        var id_field = container.find('input.survey_sm_id');
        id_field.val(sm_id);
        
        // Assign URL if available
        var url_field = container.find('input.survey_url');
        var link_label = container.find('.link_label');
        var link_status = container.find('.link_status');
        if (url) {
            url_field.val(url);
            link_label.html('<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>');            
            link_status.html('<p>Survey URL:<a href="'+url+'">'+url+'</a></p>');
            return;
        }
        
        // Begin lookup of URL if not
        url_field.val('');
        $.ajax({
            url: '/surveys/get_survey_url/'+sm_id,
            beforeSend: function () {
                url_field.prop('disabled', true);
                var loading_indicator = '<span class="loading"><img src="/data_center/img/loading_small.gif" /> Retrieving URL...</span>';
                link_label.html(loading_indicator);
            },
            success: function (data) {
                url_field.val(data);
                link_label.html('<span class="label label-success">Linked</span>');
                link_status.html('<a href="'+data+'">'+data+'</a>');
            },
            error: function (jqXHR, errorType, exception) {
                link_label.html('<span class="label label-danger">Error</span>');
                var error_msg = (jqXHR.responseText.indexOf(error_msg) != -1) ? 
                    '<span class="url_error">No URL found for this survey. Web link collector may not be configured yet.</span>' : 
                    '<span class="url_error">Error looking up URL</span>';
                link_status.html(error_msg);
                var retry_link = $('<a href="#" class="retry">Retry</a>');
                retry_link.click(function (event) {
                    event.preventDefault();
                    surveyOverview.checkSurveyAssignment(container, sm_id, function () {
                        surveyOverview.setQnaIds(container, sm_id, function () {
                            surveyOverview.selectSurvey(container, sm_id, url);
                        });
                    });
                });
                link_status.append(retry_link);
            },
            complete: function () {
                url_field.prop('disabled', false);
                container.find('.loading').remove();
            }
        });
    }
};

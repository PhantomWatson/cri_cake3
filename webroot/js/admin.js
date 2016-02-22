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
			var result_container = $(this).closest('.responses').find('span.total_alignment');
			var sum = 0;
			var selected = $(this).closest('table').find('.custom_alignment_calc:checked');
			selected.each(function () {
				var value = $(this).data('alignment');
				sum = value + sum;
			});
			var count = selected.length;
			var average = count ? Math.round(sum / count) : 0;
			result_container.html(average+'%');
		});
		$('.toggle_custom_calc').click(function (event) {
			event.preventDefault();
			$(this).closest('.responses').find('td.selected, th.selected').toggle();
		});
		$('#show_respondents').click(function (event) {
		   event.preventDefault();
		   $('tr.respondent').toggle();
		});
		$('ul.nav-tabs li[role=presentation]').first().addClass('active');
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

var surveyLink = {
    community_id: null,
    survey_type: null,
    
    init: function (params) {
        this.community_id = params.community_id;
        this.survey_type = params.type;
        this.setupSurveyLinking();
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
                    surveyLink.lookupUrl(container);
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
        var loadingMessages = $('.loading_messages');
        
        $.ajax({
            url: '/surveys/check_survey_assignment/'+sm_id,
            dataType: 'json',
            beforeSend: function () {
                loadingMessages.html('<span class="loading"><img src="/data_center/img/loading_small.gif" /> Checking survey uniqueness...</span>');
            },
            success: function (data) {
                var displayError = function (msg) {
                    $('.loading_messages').html('<span class="label label-danger">Error</span><p class="url_error">'+msg+'</p>');
                };
                if (data === null) {
                    loadingMessages.html(' ');
                    success_callback();
                } else if (data.id != surveyLink.community_id) {
                    displayError('That survey is already assigned to another community: <a href="/admin/communities/edit/'+data.id+'">'+data.name+'</a>');
                } else if (data.type != surveyLink.survey_type) {
                    displayError('That survey is already linked as this community\'s community '+data.type+'s survey.');
                } else {
                    loadingMessages.html(' ');
                    success_callback();
                }
            },
            error: function (jqXHR, errorType, exception) {
                loadingMessages.html('<p class="url_error"><span class="label label-danger">Error</span> Error checking survey uniqueness. </p>');
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
                var error_msg = 'No URL found for this survey. Web link collector may not be configured yet.';
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
    }
};

var surveyInvitationForm = {
	counter: 1,
	already_invited: [],
	uninvited_respondents: [],
	
	init: function (params) {
		this.counter = params.counter;
		this.already_invited = params.already_invited;
		this.uninvited_respondents = params.uninvited_respondents;
		this.addField(false);
		$('#add_another').click(function (event) {
			event.preventDefault();
			surveyInvitationForm.addField(true);
		});
		$('#sent_invitations_toggler').click(function (event) {
			event.preventDefault();
			$('#sent_invitations').slideToggle();
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
	},
	
	addField: function (animate) {
		var template_container = $('#invitation_fields_template');
		var new_container = template_container.clone();
		new_container.attr('id', '');
		
		var new_email = new_container.find('input[type=email]');
		new_email.prop('disabled', false);
		new_email.attr('id', '');
		new_email.change(function () {
			var field = $(this);
			var trimmed_input = field.val().trim();
			field.val(trimmed_input);
			surveyInvitationForm.checkEmail(field);
		});
		var fieldname = new_email.attr('name').replace('0', this.counter);
		new_email.attr('name', fieldname);
		
		var new_name = new_container.find('input[type=text]');
		new_name.prop('disabled', false);
		new_name.attr('id', '');
		var fieldname = new_name.attr('name').replace('0', this.counter);
		new_name.attr('name', fieldname);
		
		new_container.find('button.remove').click(function () {
			$(this).parent('div').slideUp(function () {
				$(this).remove();
			});
		});
		this.counter++;
		$('#UserClientInviteForm fieldset').append(new_container);
		if (animate) {
			new_container.slideDown();
		} else {
			new_container.show();
		}
	},
	
	checkEmail: function (field) {
		var email = field.val();
		if (email == '') {
			return;
		}
		var container = field.closest('.form-inline');
		container.children('.error-message').remove();
		if (this.isInvitedRespondent(email)) {
			var error_msg = $('<div class="error-message already_invited">An invitation has already been sent to '+email+'</div>');
			container.append(error_msg);
		} else {
			var error_msg = field.parent('div').children('.error-message');
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
	}
};

var surveyOverview = {
	init: function () {
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
					var alert = $('<p class="last_import alert alert-success" role="alert">'+data+'</p>');
					alert.hide();
					row.find('.last_import').slideUp(function () {
						$(this).remove();
					});
					var info_container = row.children('td:nth-child(2)');
					info_container.append(alert);
					alert.slideDown();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.log(jqXHR);
					console.log(textStatus);
					console.log(errorThrown);
					var alert = $('<p class="last_import alert alert-danger" role="alert">There was an error checking for new responses.<br />Please try again or contact us for assistance.</p>');
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
				if (data == 'success') {
					if (link.hasClass('dismiss')) {
						var result = $('<p class="text-warning">Dismissed</p>');
						link.closest('tr').addClass('bg-warning');
					} else {
						var result = $('<p class="text-success">Approved</p>');
						link.closest('tr').addClass('bg-success');
					}
					result.hide();
					link.closest('td').append(result);
					link.closest('span.actions').slideUp(300, function () {
						result.slideDown();
					});
				} else {
					var result = $('<p class="text-danger">Error</p>');
					link.closest('td').append(result);
				}				
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log(jqXHR);
				console.log(textStatus);
				console.log(errorThrown);
				alert('There was an error checking for new responses. Please try again or contact us for assistance.');
			},
			complete: function () {
				link.find('.loading').remove();
				link.parent().children('a').removeClass('disabled');
			}
		});
	}
};
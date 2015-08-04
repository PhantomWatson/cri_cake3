function setupSidebar() {
	var form = $('#community_select');
	var selector = form.find('select').first();
	
	// Reset the selector to its default state
	selector.val('');
	
	// Auto-submit
	selector.change(function () {
		form.submit();
	});
	
	// Hide submit button
	form.find('input[type="submit"]').hide();
	
	form.submit(function (event) {
		event.preventDefault();
		var community_id = selector.val();
		if (community_id) {
			window.location.href = '/community/'+community_id;
		}
	});
}

function showFlashMessages() {
	var messages = $('#flash_messages_bootstrap');
	if (! messages.is(':visible')) {
		messages.slideDown(500);
	}
}

function insertFlashMessage(message, classname) {
    var bootstrap_class = 'alert-info';
    if (classname == 'error') {
		bootstrap_class = 'alert-danger';
    } else if (classname == 'success') {
		bootstrap_class = 'alert-success';
	}
	
	var alert = $('<div class="alert alert-dismissible '+bootstrap_class+'" role="alert"></div>');
	alert.append('<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>');
	alert.append(message);
	
	var container = $('#flash_messages_bootstrap');
	if (container.is(':visible')) {
		alert.hide();
		container.append(alert);
		alert.slideDown();
	} else {
		container.append(alert);
		showBootstrapFlashMessages();
	}
}
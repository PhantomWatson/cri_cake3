var sidebar = {
    init: function () {
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
};

var flashMessage = {
    init: function () {
    	var messages = $('#flash_messages_bootstrap');
    	if (! messages.is(':visible')) {
    		messages.slideDown(500);
    	}
    },
    insert: function (message, classname) {
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
};

/**
 * Protects forms from being navigated away from before data is submitted.
 */ 
var formProtector = {
    protect: function (formId) {
        // Set up noting changes to form fields
        var form = $('#'+formId);
        form.find('select, input, textarea').change(function (event) {
            formProtector.setChanged(formId);
        });
        form.submit(function (event) {
            formProtector.setSubmitting(formId);
            return true; 
        });
        
        // Set up warning (with old Internet Explorer compatibility)
        var createEvent = window.attachEvent || window.addEventListener;
        var trigger = window.attachEvent ? 'onbeforeunload' : 'beforeunload';
        createEvent(trigger, function(event) {
            var form = $('#'+formId);
            if (form.data('changed') === 1 && form.data('submitting') !== 1) {
                formProtector.warn(event);
            }
        });
    },
    warn: function (event) {
        var msg = 'Are you sure you want to leave this page? The information that you have entered will be lost.';
        (event || window.event).returnValue = msg;
        return msg;
    },
    setChanged: function (formId) {
        $('#'+formId).data('changed', 1);
    },
    setSaved: function (formId) {
        $('#'+formId).data('changed', 0);
    },
    setSubmitting: function (formId) {
        $('#'+formId).data('submitting', 1);
    }
};
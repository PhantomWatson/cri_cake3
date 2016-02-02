/**
 * Protects forms from being navigated away from before data is submitted.
 * 
 * @author Graham Watson [gtwatson@bsu.edu]
 * @requires jQuery
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
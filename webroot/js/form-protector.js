/**
 * Protects forms from being navigated away from before data is submitted.
 * 
 * @author Graham Watson [gtwatson@bsu.edu]
 * @requires jQuery
 */ 
var formProtector = {
    ignoredInputs: [],
    warningMessage: 'Are you sure you want to leave this page? The information that you have entered will be lost.',
    
    protect: function (formId, params) {
        if (params.hasOwnProperty('ignore')) {
            this.ignoredInputs = params.ignore;
        }
        if (params.hasOwnProperty('warning')) {
            this.warningMessage = params.warning;
        }
        
        // Set up noting changes to form fields
        var form = $('#'+formId);
        var ignoredInputs = this.ignoredInputs;
        form.find('select, input, textarea').change(function (event) {
            var inputId = $(this).prop('id');
            if (ignoredInputs.indexOf(inputId) === -1) {
                formProtector.setChanged(formId);
            }
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
        (event || window.event).returnValue = this.warningMessage;
        return this.warningMessage;
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
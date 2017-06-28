var presentationsForm = {
    init: function () {
        $('#presentations-form input[type=radio]').change(function () {
            presentationsForm.toggleDate($(this).closest('section'));
        });
    },

    toggleDate: function (container) {
        var date = container.find('div.date');
        var presentationScheduled = container.find('input[value=1]').is(':checked');
        if (presentationScheduled) {
            if (! date.is(':visible')) {
                date.slideDown();
            }
        } else if (date.is(':visible')) {
            date.slideUp();
        }
    }
};

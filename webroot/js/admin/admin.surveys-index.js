var adminSurveysIndex = {
    init: function () {
        $('#surveys_admin_index .help_toggler').click(function (event) {
            event.preventDefault();
            $('#surveys_admin_index .help_message').slideToggle();
        });
    }
};

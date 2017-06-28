var activityRecords = {
    init: function () {

        // For any nonempty "details" row, wrap "event" cell with button to toggle viewing those details
        $('#activity-records tr.details > td > div').each(function () {
            var details = $(this);
            var button = $('<button class="btn btn-link"></button>');
            button.click(function (event) {
                event.preventDefault();
                details.slideToggle();
            });
            details
                .parents('tr')
                .prev()
                .find('td:first-child')
                .wrapInner(button);
        });

        $('#activity-records-intro button').click(function (event) {
            event.preventDefault();
            $('#activities-tracked').slideToggle();
        });
    }
};

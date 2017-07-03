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
        var resultsContainer = $('#import-results');
        if (resultsContainer.is(':empty')) {
            resultsContainer.hide();
        } else {
            var errorList = resultsContainer.find('ul');
            var errorToggler = $('<button class="btn btn-default btn-sm">Show errors</button>');
            errorToggler.click(function (event) {
                event.preventDefault();
                errorList.slideToggle();
            });
            errorList.before(errorToggler);
            errorList.hide();
        }

        $('.import_button').click(function (event) {
            event.preventDefault();
            var link = $(this);

            if (link.hasClass('disabled')) {
                return;
            }

            importResponses(link, resultsContainer);
        });
    }
};

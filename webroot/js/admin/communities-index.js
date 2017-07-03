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
                form.slideUp(200);
                adminCommunitiesIndex.filter('');
            } else {
                form.slideDown(200);
                form.children('input').focus();
                var existingValue = $('#admin_community_search_form input[type="text"]').val();
                adminCommunitiesIndex.filter(existingValue);
            }
        });
        $('#admin_community_search_form input[type="text"]').bind("change paste keyup", function() {
            var matching = $(this).val();
            adminCommunitiesIndex.filter(matching);
        });
    },

    filter: function (matching) {
        if (matching === '') {
            $('table.communities tbody tr').show();
            return;
        }
        $('table.communities tbody tr').each(function () {
            var row = $(this);
            var communityName = row.data('community-name').toLowerCase();
            matching = matching.toLowerCase();
            if (communityName.search(matching) == -1) {
                row.hide();
            } else {
                row.show();
            }
        });
    }
};

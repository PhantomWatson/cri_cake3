var adminCommunitiesIndex = {
    currentPage: 1,
    lastPage: null,
    paginationButtons: null,
    perPage: null,
    rows: null,

    init: function (options) {
        this.perPage = options.perPage;

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

        this.setupPagination();
    },

    setupPagination: function () {
        this.rows = $('#communities_admin_index').find('table.communities tbody tr');
        this.paginationButtons = $('.communities-index-pagination button');
        this.paginationButtons.each(function () {
            var button = $(this);

            // Previous
            if (button.is(':first-child')) {
                button.click(function (event) {
                    event.preventDefault();
                    adminCommunitiesIndex.showPage(adminCommunitiesIndex.currentPage - 1);
                });
                return;
            }

            // Next
            if (button.is(':last-child')) {
                button.click(function (event) {
                    event.preventDefault();
                    adminCommunitiesIndex.showPage(adminCommunitiesIndex.currentPage + 1);
                });
                return;
            }

            // Page number
            button.click(function (event) {
                event.preventDefault();
                var page = button.data('page-num');
                adminCommunitiesIndex.showPage(page);
            });
        });
        this.lastPage = $('.communities-index-pagination').first().find('button').length - 2;

        this.showPage(1);
    },

    showPage: function (pageNum) {
        this.currentPage = pageNum;

        // Show only appropriate rows
        var visibleFirst = (pageNum - 1) * this.perPage + 1;
        var visibleLast = pageNum * this.perPage;
        var visibleSelector = ':nth-child(n+' + visibleFirst + '):nth-child(-n+' + visibleLast + ')';
        this.rows.hide();
        this.rows.filter(visibleSelector).show();

        // Add 'active' class to appropriate button
        this.paginationButtons.removeClass('active');
        this.paginationButtons.filter('[data-page-num=' + pageNum + ']').addClass('active');

        // Enable/disable previous and next buttons
        this.paginationButtons.filter(':first-child').prop('disabled', this.currentPage === 1);
        this.paginationButtons.filter(':last-child').prop('disabled', this.currentPage === this.lastPage);
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

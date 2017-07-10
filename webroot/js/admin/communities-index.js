var adminCommunitiesIndex = {
    currentPage: 1,
    lastPage: null,
    paginationButtons: null,
    perPage: null,
    rows: null,

    init: function (options) {
        this.perPage = options.perPage;
        this.rows = $('#communities_admin_index').find('table.communities tbody tr');

        $('#search_toggler').click(function (event) {
            event.preventDefault();
            var form = $('#admin_community_search_form');
            if (form.is(':visible')) {
                form.slideUp(200);
                adminCommunitiesIndex.filter('');
            } else {
                form.slideDown(200);
                form.children('input').focus();
                var existingValue = form.find('input[type="text"]').val();
                adminCommunitiesIndex.filter(existingValue);
            }
        });
        $('#admin_community_search_form').find('input[type="text"]').bind("change paste keyup", function() {
            var matching = $(this).val();
            adminCommunitiesIndex.filter(matching);
        });

        var firstCategory = $('#community-index-categories').find('ul button').first().data('category');
        this.selectCategory(firstCategory);
        this.setupCategories();
        this.setupPagination();
    },

    setupPagination: function () {
        var rowsNotFiltered = this.rows.not('.filtered-out');
        this.lastPage = Math.ceil(rowsNotFiltered.length / this.perPage);

        // Generate pagination buttons
        var hasButton, newButton;
        var prevButton = $('.communities-index-pagination button:last-child');
        var onClick = function (event) {
            event.preventDefault();
            var page = $(this).data('page-num');
            adminCommunitiesIndex.showPage(page);
        };
        for (var page = 1; page <= this.lastPage; page++) {
            // Take no action if button already exists
            hasButton = $('.communities-index-pagination button[data-page-num=' + page +']').length > 0;
            if (hasButton) {
                continue;
            }

            // Generate new button
            newButton = $('<button class="btn btn-default" data-page-num="' + page + '">' + page + '</button>');
            newButton.click(onClick);
            newButton.insertBefore(prevButton);
        }

        // Remove extraneous pagination buttons
        var outOfBoundsPage = this.lastPage + 1;
        var outOfBoundsButton;
        while (true) {
            var selector = '.communities-index-pagination button[data-page-num=' + outOfBoundsPage + ']';
            outOfBoundsButton = $(selector);
            if (outOfBoundsButton.length > 0) {
                outOfBoundsButton.remove();
                outOfBoundsPage++;
                continue;
            }

            break;
        }

        this.paginationButtons = $('.communities-index-pagination button');

        // Set up previous and next buttons
        this.paginationButtons.filter(':first-child').click(function (event) {
            event.preventDefault();
            adminCommunitiesIndex.showPage(adminCommunitiesIndex.currentPage - 1);
        });
        this.paginationButtons.filter(':last-child').click(function (event) {
            event.preventDefault();
            adminCommunitiesIndex.showPage(adminCommunitiesIndex.currentPage + 1);
        });

        this.showPage(1);
    },

    setupCategories: function () {
        $('#community-index-categories').find('ul button').click(function (event) {
            event.preventDefault();
            var category = $(this).data('category');
            adminCommunitiesIndex.selectCategory(category);
        });
    },

    selectCategory: function (category) {
        // Display current category
        var categoryCapitalized = category.charAt(0).toUpperCase() + category.slice(1);
        $('#community-index-categories').find('button.dropdown-toggle strong').html(categoryCapitalized);

        if (category === 'all') {
            this.rows.filter('.filtered-out')
                .css('display', '')
                .removeClass('filtered-out');
        } else {
            this.rows.each(function () {
                var row = $(this);
                var isActive = row.data('active') === 1;
                var isFilteredOut = (category === 'active' && ! isActive) || (category === 'inactive' && isActive);
                row.toggleClass('filtered-out', isFilteredOut);
            });
        }

        // Re-generate pagination links in case the number of pages has just changed
        this.setupPagination();

        // Re-assemble current page
        this.showPage(this.currentPage);
    },

    showPage: function (pageNum) {
        if (pageNum > this.lastPage) {
            pageNum = this.lastPage;
        }
        if (pageNum < 1) {
            pageNum = 1;
        }
        this.currentPage = pageNum;

        // Show only appropriate rows
        var rowsToSkip = (pageNum - 1) * this.perPage;
        var rowsToShow = this.perPage;
        this.rows.each(function () {
            var row = $(this);
            if (row.hasClass('filtered-out')) {
                row.hide();
                return;
            }

            if (rowsToSkip > 0) {
                rowsToSkip--;
                row.hide();
                return;
            }

            if (rowsToShow > 0) {
                rowsToShow--;
                row.show();
                return;
            }

            row.hide();
        });

        // Add 'active' class to appropriate button
        this.paginationButtons.removeClass('active');
        this.paginationButtons.filter('[data-page-num=' + pageNum + ']').addClass('active');

        // Enable/disable previous and next buttons
        var onFirstPage = parseInt(this.currentPage) === 1;
        var onLastPage = parseInt(this.currentPage) === parseInt(this.lastPage);
        this.paginationButtons.filter(':first-child').prop('disabled', onFirstPage);
        this.paginationButtons.filter(':last-child').prop('disabled', onLastPage);
    },

    filter: function (matching) {
        var rows = $('table.communities tbody tr');
        if (matching === '') {
            rows.show();
            return;
        }
        rows.each(function () {
            var row = $(this);
            var communityName = row.data('community-name').toLowerCase();
            matching = matching.toLowerCase();
            row.toggle(communityName.search(matching) === -1);
        });
    }
};

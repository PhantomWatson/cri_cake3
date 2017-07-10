var adminCommunitiesIndex = {
    currentPage: 1,
    lastPage: null,
    paginationButtons: null,
    perPage: null,
    rows: null,

    init: function (options) {
        this.perPage = options.perPage;
        this.rows = $('#communities_admin_index').find('table.communities tbody tr');

        this.setupCategories();
        this.setupSearchForm();
        var firstCategory = $('#community-index-categories').find('ul button').first().data('category');
        this.selectCategory(firstCategory);
    },

    setupSearchForm: function () {
        var form = $('#admin_community_search_form');

        this.rows.attr('data-matches-search', 1);

        $('#search_toggler').click(function (event) {
            event.preventDefault();

            // Disable filter if toggling the input off
            if (form.is(':visible')) {
                form.slideUp(200);
                adminCommunitiesIndex.filter('');
                return;
            }

            form.slideDown(200);
            form.children('input').focus();

            // Re-apply filter when re-displaying input field
            var existingValue = form.find('input[type="text"]').val();
            adminCommunitiesIndex.filter(existingValue);
        });

        // Apply filter upon any character being entered
        var searchInput = form.find('input[type="text"]');
        searchInput.bind('change paste keyup', function () {
            var matching = $(this).val();
            adminCommunitiesIndex.filter(matching);
        });
    },

    /**
     * Returns the number of rows that can be viewed
     * (not filtered out by category or search term)
     */
    countViewableRows: function () {
        return this.rows
            .filter('[data-matches-category=1]')
            .filter('[data-matches-search=1]')
            .length;
    },

    setupPagination: function () {
        this.lastPage = Math.ceil(this.countViewableRows() / this.perPage);

        // Generate pagination buttons
        var hasButton;
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
            $('<button></button>')
                .html(page)
                .addClass('btn btn-default')
                .attr('data-page-num', page)
                .click(onClick)
                .insertBefore(prevButton);
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
    },

    setupCategories: function () {
        $('#community-index-categories').find('ul button').click(function (event) {
            event.preventDefault();
            var category = $(this).data('category');
            adminCommunitiesIndex.selectCategory(category);
        });
    },

    selectCategory: function (category) {
        // Display label for current category
        var categoryCapitalized = category.charAt(0).toUpperCase() + category.slice(1);
        var categoryLabel = $('#community-index-categories').find('button.dropdown-toggle strong');
        categoryLabel.html(categoryCapitalized);

        this.rows.each(function () {
            var row = $(this);
            var isActive = row.data('active') === 1;
            var matchesCategory = (category === 'all') ||
                (category === 'active' && isActive) ||
                (category === 'inactive' && ! isActive);
            row.attr('data-matches-category', matchesCategory ? '1' : '0');
        });

        // Re-generate pagination links in case the number of pages has just changed
        this.setupPagination();

        // Re-assemble current page
        this.showPage(this.currentPage);
    },

    showPage: function (pageNum) {
        pageNum = Math.min(pageNum, this.lastPage);
        pageNum = Math.max(pageNum, 1);
        this.currentPage = pageNum;

        // Show only appropriate rows
        var rowsToSkip = (pageNum - 1) * this.perPage;
        var rowsToShow = this.perPage;
        this.rows.each(function () {
            var row = $(this);
            if (row.attr('data-matches-category') === '0' || row.attr('data-matches-search') === '0') {
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

    filter: function (searchTerm) {
        if (searchTerm === '') {
            this.rows.attr('data-matches-search', 1);
        } else {
            this.rows.each(function () {
                var row = $(this);
                var communityName = row.data('community-name').toLowerCase();
                searchTerm = searchTerm.toLowerCase();
                var matches = communityName.search(searchTerm) === -1 ? 0 : 1;
                row.attr('data-matches-search', matches);
            });
        }
        this.setupPagination();
        this.showPage(1);
    }
};

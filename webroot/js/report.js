var adminReport = {
    notes: [],

    /**
     * Track whether or not int alignment columns have ever been minimized
     * (these columns should start minimized, but we have to wait until the
     * wider group of columns that they're part of become visible to do this)
     */
    minimizedIntAlignment: {
        officials: false,
        organizations: false
    },

    init: function () {
        this.setSurveyTypes();
        this.setupSorting();
        var tables = $('table.report');

        // Set up expanding/collapsing survey groups
        tables.find('button.survey-toggler').click(function (event) {
            event.preventDefault();
            var table = $(this).closest('table');
            table.toggleClass('expanded');

            /* Minimize int alignment columns if this is the first time
             * they would be visible */
            var surveyType = table.data('survey-type');
            if (! adminReport.minimizedIntAlignment[surveyType]) {
                adminReport.toggleIntAlignment(table);
            }

            adminReport.updateColspans(table);
        });

        // Set up internal alignment expanding/collapsing
        tables.addClass('int-alignment-expanded');
        tables
            .find('.col-group-headers th')
            .filter(':nth-child(5)')
            .attr('data-col-group', 'int-alignment');

        var buttons = tables.find('.col-group-headers th[data-col-group=int-alignment] button');
        buttons.click(function (event) {
            event.preventDefault();
            var table = $(this).closest('table');
            adminReport.toggleIntAlignment(table);
            adminReport.updateColspans(table);
        });

        // Set up showing notes for communities
        $('#notes-modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var communityId = button.data('community-id');
            var communityName = button.data('community-name');
            var title = null;
            var body = null;
            if (button.hasClass('notes')) {
                title = communityName + ' - Notes';
                body = adminReport.notes[communityId];
            } else if (button.hasClass('recent-activity')) {
                title = communityName + ' - Recent Activity';
                body = button.siblings('div.recent-activity').html();
            }
            $(this).find('.modal-title').html(title);
            $(this).find('.modal-body').html(body);
        });
    },

    toggleIntAlignment: function (table) {
        var currentlyExpanded = table.hasClass('int-alignment-expanded');
        if (currentlyExpanded) {
            var surveyType = table.data('survey-type');
            this.minimizedIntAlignment[surveyType] = true;
        }

        // Expand/collapse columns
        table.toggleClass('int-alignment-expanded');
        var header = table.find('.col-group-headers th[data-col-group=int-alignment]');
        var newColspan = currentlyExpanded ? 1 : 6;
        header.prop('colspan', newColspan);

        // Update button labels
        var button = header.find('button');
        var newLabel = currentlyExpanded ? 'Details' : 'Internal Alignment';
        button.html(newLabel);

        // Update 'overall internal alignment' column header
        var overallColHeader = table.find('.general-header .int-overall-alignment');
        newLabel = currentlyExpanded ? 'Internal Alignment' : 'Overall';
        overallColHeader.html(newLabel);
    },

    updateColspans: function (table) {
        var count = table.find('tbody tr:first-child td:visible').length;
        var header = table.find('.survey-group-header th');
        header.prop('colspan', count);
    },

    setSurveyTypes: function () {
        var tables = $('table.report');

        this.markOfficialsSurvey($(tables[0]));
        this.markOrgsSurvey($(tables[1]));
    },

    markOfficialsSurvey: function (element) {
        element.attr('data-survey-type', 'officials');
    },

    markOrgsSurvey: function (element) {
        element.attr('data-survey-type', 'organizations');
    },

    setupSorting: function () {
        var cells;
        var officialsTable = $('table.report[data-survey-type=officials]');
        cells = officialsTable.find('.general-header th');
        cells.filter(':nth-child(1)').attr('data-sort', 'string');
        cells.filter(':nth-child(n+2):nth-child(-n+14)').attr('data-sort', 'float');
        cells.filter(':nth-child(n+15):nth-child(-n+17)').attr('data-sort', 'string');
        officialsTable.stupidtable();

        var orgsTable = $('table.report[data-survey-type=organizations]');
        cells = orgsTable.find('.general-header th');
        cells.filter(':nth-child(1)').attr('data-sort', 'string');
        cells.filter(':nth-child(n+2):nth-child(-n+12)').attr('data-sort', 'float');
        cells.filter(':nth-child(n+13):nth-child(-n+15)').attr('data-sort', 'string');
        orgsTable.stupidtable();
    }
};

var adminReport = {
    notes: [],

    minimizedIntAlignment: {
        officials: false,
        organizations: false
    },

    init: function () {
        this.setSurveyTypes();
        this.setupSorting();
        var table = $('#report');

        // Set up expanding/collapsing survey groups
        table.find('button.survey-toggler').click(function (event) {
            event.preventDefault();
            var surveyType = $(this).parent().data('survey-type');
            $('#report').toggleClass(surveyType + '-expanded');
            if (! adminReport.minimizedIntAlignment[surveyType]) {
                adminReport.toggleIntAlignment(surveyType);
            }
            adminReport.updateColspans();
        });

        // Set up internal alignment expanding/collapsing
        table.addClass('officials-int-alignment-expanded organizations-int-alignment-expanded');
        var headerCells = table.find('.col-group-headers th');
        headerCells.filter(':nth-child(5)').attr('data-col-group', 'int-alignment');
        headerCells.filter(':nth-child(9)').attr('data-col-group', 'int-alignment');
        var buttons = table.find('.col-group-headers th[data-col-group=int-alignment] button');
        buttons.click(function (event) {
            event.preventDefault();
            adminReport.toggleIntAlignment($(this).parent().data('survey-type'));
            adminReport.updateColspans();
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

    toggleIntAlignment: function (surveyType) {
        var table = $('#report');
        var currentlyExpanded = table.hasClass(surveyType + '-int-alignment-expanded');
        if (currentlyExpanded) {
            this.minimizedIntAlignment[surveyType] = true;
        }

        // Expand/collapse columns
        table.toggleClass(surveyType + '-int-alignment-expanded');
        var header = table.find('.col-group-headers th[data-survey-type=' + surveyType + '][data-col-group=int-alignment]');
        var newColspan = currentlyExpanded ? 1 : 6;
        header.prop('colspan', newColspan);

        // Update button labels
        var button = header.find('button');
        var newLabel = currentlyExpanded ? 'Details' : 'Internal Alignment';
        button.html(newLabel);

        // Update 'overall internal alignment' column header
        var overallColHeader = table.find('.general-header .int-overall-alignment[data-survey-type=' + surveyType + ']');
        newLabel = currentlyExpanded ? 'Internal Alignment' : 'Overall';
        overallColHeader.html(newLabel);
    },

    updateColspans: function () {
        var table = $('#report');

        // Make leading blank cell stretch over officials survey col if it's minimized
        var colspan = table.hasClass('officials-expanded') ? 1 : 2;
        var surveyGroupHeader = table.find('.survey-group-header');
        surveyGroupHeader.find('td').prop('colspan', colspan);

        this.updateSurveyHeaderColspan('officials');
        this.updateSurveyHeaderColspan('organizations');
    },

    updateSurveyHeaderColspan: function (surveyType) {
        var count = $('#report tbody tr:first-child td[data-survey-type="' + surveyType + '"]:visible').length;

        // Add one for the 'status' column, which isn't covered by the above selector
        count++;

        var header = $('#report .survey-group-header th[data-survey-type="' + surveyType + '"]');
        header.prop('colspan', count);
    },

    setSurveyTypes: function () {
        var table = $('#report');

        var cells = table.find('.survey-group-header th');
        this.markOfficialsSurvey(cells.filter(':nth-child(2)'));
        this.markOrgsSurvey(cells.filter(':nth-child(3)'));

        cells = table.find('.col-group-headers').find('td, th');
        this.markOfficialsSurvey(cells.filter(':nth-child(n+3):nth-child(-n+6)'));
        this.markOrgsSurvey(cells.filter(':nth-child(n+7):nth-child(-n+10)'));

        cells = table.find('.general-header th');
        this.markOfficialsSurvey(cells.filter(':nth-child(n+2):nth-child(-n+18)'));
        this.markOrgsSurvey(cells.filter(':nth-child(n+19):nth-child(-n+32)'));

        cells = table.find('tbody td');
        this.markOfficialsSurvey(cells.filter(':nth-child(n+2):nth-child(-n+16)'));
        this.markOrgsSurvey(cells.filter(':nth-child(n+18):nth-child(-n+29)'));
    },

    markOfficialsSurvey: function (element) {
        element.attr('data-survey-type', 'officials');
    },

    markOrgsSurvey: function (element) {
        element.attr('data-survey-type', 'organizations');
    },

    setupSorting: function () {
        var table = $('#report');
        var cells = table.find('.general-header th');
        cells.filter(':nth-child(1)').attr('data-sort', 'string');
        cells.filter(':nth-child(n+2):nth-child(-n+12)').attr('data-sort', 'float');
        cells.filter(':nth-child(n+13):nth-child(-n+17)').attr('data-sort', 'string');
        cells.filter(':nth-child(n+19):nth-child(-n+29)').attr('data-sort', 'float');
        cells.filter(':nth-child(n+30):nth-child(-n+31)').attr('data-sort', 'string');
        table.stupidtable();
    }
};
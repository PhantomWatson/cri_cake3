var adminReport = {
    notes: [],

    minimizedIntAlignment: {
        officials: false,
        organizations: false
    },

    init: function () {
        var table = $('#report');

        // Set up expanding/collapsing survey groups
        table.find('button.survey-toggler').click(function (event) {
            event.preventDefault();
            var surveyType = $(this).data('survey-type');
            $('#report').toggleClass(surveyType + '-expanded');
            if (! adminReport.minimizedIntAlignment[surveyType]) {
                adminReport.toggleIntAlignment(surveyType);
            }
            adminReport.updateColspans();
        });

        // Set up internal alignment expanding/collapsing
        table.addClass('officials-int-alignment-expanded organizations-int-alignment-expanded');
        table.find('.internal-alignment-headers th button').click(function (event) {
            event.preventDefault();
            adminReport.toggleIntAlignment($(this).data('survey-type'));
            adminReport.updateColspans();
        });

        // Set up sorting
        table.stupidtable();

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
        var header = table.find('.internal-alignment-headers th[data-survey-type=' + surveyType + ']');
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

        // Leading blank cell, top row
        var colspan = table.hasClass('officials-expanded') ? 1 : 2;
        var surveyGroupHeader = table.find('.survey-group-header');
        surveyGroupHeader.find('td').attr('colspan', colspan);

        this.updateSurveyHeaderColspan('officials');
        this.updateSurveyHeaderColspan('organizations');
    },

    updateSurveyHeaderColspan: function (surveyType) {
        var count = $('#report tbody tr:first-child td[data-survey-type="' + surveyType + '"]:visible').length;

        // Add one for the 'status' column, which isn't covered by the above selector
        count++;

        var header = $('#report .survey-group-header th[data-survey-type="' + surveyType + '"]');
        header.attr('colspan', count);
    }
};
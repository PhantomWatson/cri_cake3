var alignmentCalculationSettings = {
    init: function () {
        $('#filter-by-community input[type="text"]').bind("change paste keyup", function() {
            var matching = $(this).val();
            alignmentCalculationSettings.filter(matching);
        });
    },
    filter: function (matching) {
        matching = matching.toLowerCase();
        var rows = $('#alignmentCalcSettings tbody tr').not('.default');
        if (matching === '') {
            rows.show();
            return;
        }
        rows.each(function () {
            var row = $(this);
            var communityName = row.find('td:first-child').text().trim().toLowerCase();
            if (communityName.search(matching) == -1) {
                row.hide();
            } else {
                row.show();
            }
        });
    }
};

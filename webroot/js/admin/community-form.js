var communityForm = {
    community_id: null,
    areaTypes: null,

    init: function (params) {
        this.community_id = params.community_id;
        this.areaTypes = params.areaTypes;

        $('#meeting-date-set-0, #meeting-date-set-1').change(function () {
            communityForm.toggleDateFields(true);
        });
        this.toggleDateFields(false);
        this.setupAreaSelection();
    },

    toggleDateFields: function (animate) {
        if ($('#meeting-date-set-0').is(':checked')) {
            if (animate) {
                $('#meeting_date_fields').slideUp();
            } else {
                $('#meeting_date_fields').hide();
            }
        }
        if ($('#meeting-date-set-1').is(':checked')) {
            if (animate) {
                $('#meeting_date_fields').slideDown();
            } else {
                $('#meeting_date_fields').show();
            }
        }
    },

    setupAreaSelection: function () {
        $('#local-area-id, #parent-area-id').each(function () {
            var areaSelector = $(this);

            // Insert type selector
            var typeSelector = $('<select class="form-control"></select>');
            for (var i = 0; i < communityForm.areaTypes.length; i++) {
                var type = communityForm.areaTypes[i];
                typeSelector.append('<option value="'+type+'">'+type+'</option>');
            }
            areaSelector.before(typeSelector);
            typeSelector.change(function (event) {
                var type = $(this).val();
                communityForm.changeAreaType(areaSelector, type);
            });

            // Set type selector to correct value (or a default value)
            var selected = areaSelector.find('option:selected');
            var selectedType = '';
            if (selected.length === 0 || selected.val() === '') {
                if (areaSelector.attr('id') == 'parent-area-id') {
                    selectedType = 'County';
                } else {
                    selectedType = 'City';
                }
            } else {
                selectedType = selected.parent('optgroup').attr('label');
            }
            typeSelector.find('option[value="'+selectedType+'"]').prop('selected', true);
            communityForm.changeAreaType(areaSelector, selectedType);
        });
    },

    changeAreaType: function (areaSelector, type) {
        areaSelector.find('optgroup[label="'+type+'"]').show();
        areaSelector.find('optgroup').not('[label="'+type+'"]').hide();
    }
};

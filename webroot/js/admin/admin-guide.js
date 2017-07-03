var adminGuide = {
    init: function () {
        var sections = $('section.admin-guide');
        var clickFunction = function () {
            var section = $(this).parents('section');
            adminGuide.toggleSection(section);
        };
        for (var i = 0; i < sections.length; i++) {
            var section = $(sections[i]);
            var header = section.find('h2');
            var button = $('<button class="btn btn-default btn-block"></button>');
            button.click(clickFunction);
            header.wrapInner(button);
        }
    },

    toggleSection: function (section) {
        section.find('h2').next().slideToggle();
    }
};

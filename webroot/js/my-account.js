var myAccount = {
    init: function () {
        var form = $('#my-account');
        var passSection = form.find('section.password');
        passSection.find('> h2 > button').click(function (event) {
            event.preventDefault();
            passSection.find('> div').toggleClass('active');
        });
    }
};
var adminPurchasesIndex = {
    init: function () {
        $('button.refunded, button.details').click(function (event) {
            event.preventDefault();
            $(this).closest('tr').next('tr.details').find('ul').slideToggle();
        });
    }
};

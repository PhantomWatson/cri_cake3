var ocraPayments = {
    init: function () {
        $('.select-all button').click(function (event) {
            event.preventDefault();
            var check = $(this).data('mode') ? true : false;
            $(this).closest('table').find('input[type=checkbox]').prop('checked', check);
            $(this).data('mode', !check);
        });
    }
};

var sidebar = {
    init: function () {
        var form = $('#community-select');
        var selector = form.find('select').first();
        
        // Reset the selector to its default state
        selector.val('');
        
        // Auto-submit
        selector.change(function () {
            form.submit();
        });
        
        // Hide submit button
        form.find('input[type="submit"]').hide();
        
        form.submit(function (event) {
            event.preventDefault();
            var community_slug = selector.val();
            if (community_slug) {
                window.location.href = '/community/'+community_slug;
            }
        });
    }
};

var flashMessage = {
    init: function () {
        var messages = $('#flash_messages_bootstrap');
        if (! messages.is(':visible')) {
            messages.slideDown(500);
        }
    },
    insert: function (message, className) {
        var alert = $('<div class="alert alert-dismissible" role="alert"></div>');
        switch (className) {
            case 'error':
                alert.addClass('alert-danger');
                break;
            case 'success':
                alert.addClass('alert-success');
                break;
            default:
                alert.addClass('alert-info');
        }
        var button = $('<button type="button" class="close" data-dismiss="alert"></button>')
            .append('<span aria-hidden="true">&times;</span>');
        alert.append(button).append(message);

        var container = $('#flash_messages_bootstrap');
        if (container.is(':visible')) {
            alert.hide();
            container.append(alert);
            alert.slideDown();
            return;
        }

        container.append(alert);
        container.slideDown();
    } 
};

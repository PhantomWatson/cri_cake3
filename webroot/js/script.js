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

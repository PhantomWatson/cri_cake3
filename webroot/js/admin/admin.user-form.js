var adminUserEdit = {
    community_counter: 0,

    init: function (params) {
        var community_container = $('<ul id="community_container"></ul>');
        var community_select = $('#community');
        community_select.after(community_container);
        community_select.prop('selectedIndex', 0);

        if (params.selected_communities.length > 0) {
            for (var i = 0; i < params.selected_communities.length; i++) {
                var community = params.selected_communities[i];
                this.addCommunity(community.id, community.name, false);
            }
        }

        community_select.change(function () {
            var select = $(this);
            var c_id = select.val();
            var preselected = $('li[data-community-id="'+c_id+'"]');
            if (preselected.length === 0) {
                var c_name = select.find('option:selected').text();
                adminUserEdit.addCommunity(c_id, c_name, true);
            }
            select.prop('selectedIndex', 0);
        });

        $('#all-communities-0, #all-communities-1').change(function () {
            adminUserEdit.toggleAllCommunities(true);
        });
        this.toggleAllCommunities(false);

        $('#role').change(function () {
            adminUserEdit.onRoleChange(true);
        });
        this.onRoleChange(false);

        $('#password-fields-button button').click(function (event) {
            event.preventDefault();
            $('#password-fields-button').slideUp(300);
            $('#password-fields').slideDown(300);
        });
    },

    addCommunity: function (id, name, animate) {
        var li = $('<li data-community-id="' + id + '"></li>');
        var label = '<span class="glyphicon glyphicon-remove"></span> <span class="link_label">' + name + '</span>';
        var link = $('<a href="#">' + label + '</a>');
        link.click(function (event) {
            event.preventDefault();
            li.slideUp(300, function () {
                li.remove();
            });
        });
        li.append(link);
        var input = '<input type="hidden" name="consultant_communities[' + this.community_counter + '][id]" ' +
            'value="' + id + '" />';
        li.append(input);
        this.community_counter++;
        if (animate) {
            li.hide();
        }
        $('#community_container').prepend(li);
        if (animate) {
            li.slideDown();
        }
    },

    toggleAllCommunities: function (animate) {
        if ($('#all-communities-0').is(':checked')) {
            if (animate) {
                $('#community').slideDown();
                $('#community_container').slideDown();
            } else {
                $('#community').show();
                $('#community_container').show();
            }
        } else {
            if (animate) {
                $('#community').slideUp();
                $('#community_container').slideUp();
            } else {
                $('#community').hide();
                $('#community_container').hide();
            }
        }
    },

    onRoleChange: function (animate) {
        var role = $('#role').val();
        var duration = animate ? 300 : 0;
        if (role == 'consultant') {
            $('#consultant_communities').slideDown(duration);
            $('#client_communities').slideUp(duration);
        } else if (role == 'client') {
            $('#client_communities').slideDown(duration);
            $('#consultant_communities').slideUp(duration);
        } else {
            $('#consultant_communities').slideUp(duration);
            $('#client_communities').slideUp(duration);
        }
    }
};

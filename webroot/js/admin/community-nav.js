var adminHeader = {
    communityId: null,
    surveyId: null,
    surveyIds: [],
    slugs: {},

    init: function (params) {
        this.communityId = params.communityId;
        this.slugs = params.slugs;
        this.surveyId = params.surveyId;
        this.surveyIds = params.surveyIds;
        this.surveyType = params.surveyType;

        this.selectCommunity(this.communityId);
        this.selectPage(params.currentUrl);

        $('#admin-sidebar-community').submit(function (event) {
            event.preventDefault();
            var url = adminHeader.getUrl();
            if (url) {
                adminHeader.removeError();
                window.location.href = url;
            }
        });
    },

    getUrl: function () {
        var communityId =  $('#admin-sidebar-community select[name=community]').val();
        if (! communityId) {
            this.displayError('Please select a community');
            return false;
        }

        var selectedPageOpt = $('#admin-sidebar-community select[name=page] option:selected');
        var selectedPage = selectedPageOpt.val();
        if (! selectedPage) {
            this.displayError('Please select a page');
            return false;
        }

        var surveyType = selectedPageOpt.closest('optgroup').data('survey-type');
        var surveyId = this.getSurveyId(communityId, surveyType);
        var url = this.getParsedUrl(selectedPage, {
            communityId: communityId,
            surveyType: surveyType,
            surveyId: surveyId
        });

        if (! surveyId && url.search('{survey-id}') !== -1) {
            var communityName = $('#admin-sidebar-community select[name=community] option:selected').text().trim();
            var msg = 'The ' + surveyType  + ' questionnaire has not yet been set up for ' + communityName + '.';
            this.displayError(msg);
            return false;
        }

        return url;
    },

    getSurveyId: function (communityId, surveyType) {
        if (! communityId || ! surveyType) {
            return false;
        }

        if (this.surveyIds.hasOwnProperty(communityId)) {
            var community = adminHeader.surveyIds[communityId];
            if (community.hasOwnProperty(surveyType)) {
                return this.surveyIds[communityId][surveyType];
            }
        }

        return false;
    },

    displayError: function (msg) {
        var alert = $('<p class="admin-header-error alert alert-info">' + msg + '</p>');
        alert.hide();
        var header = $('#admin-sidebar-community');
        var existingAlert = header.find('.admin-header-error');
        if (existingAlert.length > 0) {
            existingAlert.fadeOut(300, function () {
                existingAlert.remove();
                header.append(alert);
                alert.fadeIn(300);
            });
        } else {
            header.append(alert);
            alert.fadeIn(300);
        }
        setTimeout(function () {
            adminHeader.removeError();
        }, 5000);
    },

    removeError: function () {
        var alert = $('#admin-sidebar-community .admin-header-error');
        if (! alert.length) {
            return;
        }
        alert.slideUp(300, function () {
            alert.remove();
        });
    },

    selectCommunity: function (communityId) {
        $('#admin-sidebar-community select[name=community]').val(communityId);
    },

    selectPage: function (currentUrl) {
        $('#admin-sidebar-community select[name=page] optgroup').each(function () {
            var optgroup = $(this);
            var surveyType = optgroup.data('survey-type');
            optgroup.find('option').each(function () {
                var option = $(this);
                var urlTemplate = option.val();
                if (! urlTemplate) {
                    return;
                }

                // Special case for admins viewing client home page
                if (urlTemplate === '/admin/communities/clienthome/{community-id}') {
                    if (currentUrl === '/client/home') {
                        option.prop('selected', true);
                        return;
                    }
                }

                var optionUrl = adminHeader.getParsedUrl(urlTemplate, {
                    communityId: adminHeader.communityId,
                    surveyType: surveyType,
                    surveyId: adminHeader.surveyId
                });
                if (currentUrl === optionUrl) {
                    option.prop('selected', true);
                }
            });
        });
    },

    getParsedUrl: function (urlTemplate, params) {
        var url = urlTemplate;
        var communitySlug = this.slugs[params.communityId];

        url = url.replace('{survey-type}', params.surveyType);
        url = url.replace('{community-id}', params.communityId);
        url = url.replace('{survey-id}', params.surveyId);
        url = url.replace('{community-slug}', communitySlug);

        if (urlTemplate.search('{survey-id}') !== -1) {
            var surveyId;
            if (params.surveyId) {
                surveyId = params.surveyId;
            } else {
                surveyId = adminHeader.getSurveyId(params.communityId, params.surveyType);
            }
            url = url.replace('{survey-id}', surveyId);
        }

        return url;
    }
};

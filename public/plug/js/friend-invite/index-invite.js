(function () {
    "use strict";

    var inviteMessages = {};
    var inviteMessagesKey = '';

    $('.js-radio-blue').on('change', function (event) {
        inviteMessagesKey = $(this).val();
        setInviteMessage();
    });

    function setInviteMessage() {
        var inviteBtns = $('.js-social-invite-button');
        var inviteEmailBtn = $('.js-email-invite-button');
        var emailTemplate = inviteEmailBtn.data('social-template');
        var socialTemplate = '';

        inviteBtns.each(function (index) {
            socialTemplate = $(this).data('social-template');
            $(this).data('text', inviteMessages[socialTemplate][inviteMessagesKey]);
        });

        if (inviteEmailBtn.length) {
            inviteEmailBtn.attr('href', __site_url + 'invite/popup_forms/invite_by_email?&template=' + emailTemplate + '&message_key=' + inviteMessagesKey);
        }
    };

    function FriendInvite(messages) {
        inviteMessages = JSON.parse(messages);
        inviteMessagesKey = Object.keys(Object.values(inviteMessages)[0])[0];

        setInviteMessage();
    }

    mix(globalThis, { FriendInvite: FriendInvite }, false);
})();


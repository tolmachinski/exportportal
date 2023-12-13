module.exports = [
    {
        label: "Seller picture detail page",
        url: "/backstop_test/picture/lorem-ipsum-620",
        jsFileName: "sellerPictureDetail",
    },
    {
        label: "Seller picture detail page leave comment dropdown",
        url: "/backstop_test/picture/lorem-ipsum-620",
        jsFileName: "sellerPictureDetail",
        authentication: "certified manufacturer",
        showLeaveCommentDropdown: true,
    },
    {
        label: "Seller picture detail page add comment popup",
        url: "/backstop_test/picture/lorem-ipsum-620",
        jsFileName: "sellerPictureDetail",
        authentication: "certified manufacturer",
        showLeaveCommentDropdown: true,
        showAddCommentPopup: true,
    },
    {
        label: "Seller picture detail page add comment popup error validation",
        url: "/backstop_test/picture/lorem-ipsum-620",
        jsFileName: "sellerPictureDetail",
        authentication: "certified manufacturer",
        showLeaveCommentDropdown: true,
        showAddCommentPopup: true,
        submitEmptyForm: true,
    },
];

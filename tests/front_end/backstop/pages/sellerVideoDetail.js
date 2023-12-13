module.exports = [
    {
        label: "Seller video detail page",
        url: "/backstop_test/video/backstopvideo1649682232134-470",
        jsFileName: "sellerVideoDetail",
    },
    {
        label: "Seller video detail page leave comment dropdown",
        url: "/backstop_test/video/backstopvideo1649682232134-470",
        jsFileName: "sellerVideoDetail",
        authentication: "certified manufacturer",
        showLeaveCommentDropdown: true,
    },
    {
        label: "Seller video detail page add comment popup",
        url: "/backstop_test/video/backstopvideo1649682232134-470",
        jsFileName: "sellerVideoDetail",
        authentication: "certified manufacturer",
        showLeaveCommentDropdown: true,
        showAddCommentPopup: true,
    },
    {
        label: "Seller video detail page add comment popup error validation",
        url: "/backstop_test/video/backstopvideo1649682232134-470",
        jsFileName: "sellerVideoDetail",
        authentication: "certified manufacturer",
        showLeaveCommentDropdown: true,
        showAddCommentPopup: true,
        submitEmptyForm: true,
    },
];

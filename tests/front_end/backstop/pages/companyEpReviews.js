//
module.exports = [
    {
        label: "Company ep reviews",
        url: "/backstop-best-test/reviews_ep",
        jsFileName: "companyEpReviews",
    },
    {
        label: "Company ep reviews reason popup",
        url: "/backstop-best-test/reviews_ep",
        jsFileName: "companyEpReviews",
        openReviewDropdown: true,
        openReasonPopup: true,
        authentication: "certified manufacturer",
    },
    {
        label: "Company ep reviews send reason success",
        url: "/backstop-best-test/reviews_ep",
        jsFileName: "companyEpReviews",
        openReviewDropdown: true,
        openReasonPopup: true,
        sendReport: true,
        authentication: "certified manufacturer",
    },
    {
        label: "Company ep reviews edit review popup",
        url: "/backstop-best-test/reviews_ep",
        jsFileName: "companyEpReviews",
        openReviewDropdown: true,
        openEditPopup: true,
        authentication: "buyer",
    },
    {
        label: "Company ep reviews edit review popup submit success",
        url: "/backstop-best-test/reviews_ep",
        jsFileName: "companyEpReviews",
        openReviewDropdown: true,
        openEditPopup: true,
        submitEditPopup: true,
        authentication: "buyer",
    },
    {
        label: "Company ep reviews add reply popup",
        url: "/backstop-best-test/reviews_ep",
        jsFileName: "companyEpReviews",
        openReplyDropdown: true,
        openAddReplyPopup: true,
        authentication: "certified manufacturer",
    },
    {
        label: "Company ep reviews edit reply popup",
        url: "/backstop-best-test/reviews_ep",
        jsFileName: "companyEpReviews",
        openReplyDropdown: true,
        openEditReplyPopup: true,
        authentication: "certified manufacturer",
    },
    {
        label: "Company ep reviews edit reply popup submit success",
        url: "/backstop-best-test/reviews_ep",
        jsFileName: "companyEpReviews",
        openReplyDropdown: true,
        openEditReplyPopup: true,
        editReply: true,
        authentication: "certified manufacturer",
    },
];
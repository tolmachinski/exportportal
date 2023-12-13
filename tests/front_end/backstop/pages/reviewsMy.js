module.exports = [
    {
        label: "My Reviews",
        url: "/reviews/my",
        jsFileName: "reviewsMy",
        authentication: "buyer",
    },
    {
        label: "My Reviews Add Review",
        url: "/reviews/my?backsop=true",
        jsFileName: "reviewsMy",
        authentication: "buyer",
        addReview: true,
    },
    {
        label: "My Reviews Edit Review",
        url: "/reviews/my",
        jsFileName: "reviewsMy",
        authentication: "buyer",
        editReview: true,
    },
    {
        label: "My Reviews Delete Review",
        url: "/reviews/my",
        jsFileName: "reviewsMy",
        authentication: "buyer",
        deleteReview: true,
    },
    {
        label: "My Reviews Details Review",
        url: "/reviews/my",
        jsFileName: "reviewsMy",
        authentication: "buyer",
        detailsReview: true,
    },
]

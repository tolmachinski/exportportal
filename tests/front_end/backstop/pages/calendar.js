module.exports = [
    {
        label: "calendar",
        url: "/calendar/my",
        jsFileName: "calendar",
        authentication: "certified seller"
    },
    {
        label: "calendar - event detail",
        url: "/calendar/my",
        jsFileName: "calendar",
        authentication: "certified seller",
        eventDetail: true
    },
    {
        label: "calendar - event delete",
        url: "/calendar/my",
        jsFileName: "calendar",
        authentication: "certified seller",
        eventDetail: true,
        eventDelete: true
    },
    {
        label: "calendar - show more",
        url: "/calendar/my",
        jsFileName: "calendar",
        authentication: "certified seller",
        showMore: true
    },
]

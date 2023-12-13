module.exports = [
    {
        label: "events",
        url: "/ep_events",
        jsFileName: "events"
    },
    {
        label: "events - open mobile filter",
        url: "/ep_events",
        jsFileName: "events",
        openMenu: true
    },
    {
        label: "events - add to calendar",
        url: "/ep_events",
        jsFileName: "events",
        authentication: "certified seller",
        eventAdd: true
    },
    {
        label: "events - remove from calendar",
        url: "/ep_events",
        jsFileName: "events",
        authentication: "certified seller",
        eventRemove: true
    },
    {
        label: "events - share",
        url: "/ep_events",
        jsFileName: "events",
        authentication: "certified seller",
        eventShare: true
    },
    {
        label: "events - detail",
        url: "/ep_events/detail/48/backstopbusiness-molodosti",
        jsFileName: "events",
        detailPage: true
    },
    {
        label: "events - past",
        url: "/ep_events/past",
        jsFileName: "events"
    },
]

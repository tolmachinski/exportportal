module.exports = [
    {
        label: "EPL resources",
        subdomain: "epl",
        url: "/resources",
        jsFileName: "eplResources",
    },
    {
        label: "EPL resources for logged",
        subdomain: "epl",
        url: "/resources",
        authentication: "freight forwarder",
        jsFileName: "eplResources",
    },
    {
        label: "EPL resources with opened faq",
        subdomain: "epl",
        url: "/resources",
        jsFileName: "eplResources",
        openFaq: true,
    },
    {
        label: "EPL resources with opened popup registration guide video",
        subdomain: "epl",
        url: "/resources",
        jsFileName: "eplResources",
        openRegistrationGuideVideo: true,
    },
    {
        label: "EPL resources with opened popup profile completion video",
        subdomain: "epl",
        url: "/resources",
        jsFileName: "eplResources",
        openProfileCompletionGuideVideo: true,
    },
]

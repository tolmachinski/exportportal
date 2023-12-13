module.exports = [
    {
        label: "Help",
        url: "/help",
        jsFileName: "help",
    },
    {
        label: "Help with hover",
        url: "/help",
        jsFileName: "help",
        hoverCard: true
    },
    {
        label: "Help with opened Schedule a demo popup",
        url: "/help",
        jsFileName: "help",
        type: "help",
        openScheduleDemoPopup: true,
    },
    {
        label: "Help Schedule a demo popup validation error",
        url: "/help",
        jsFileName: "help",
        type: "help",
        openScheduleDemoPopup: true,
        validate: true,
    },
    {
        label: "Help Schedule a demo popup submited",
        url: "/help",
        jsFileName: "help",
        type: "help",
        openScheduleDemoPopup: true,
        isSubmited: true,
    },
    {
        label: "Help search with results FAQ tab",
        url: "/help/search/?keywords=help",
        jsFileName: "help",
        faqTab: true,
    },
    {
        label: "Help search with results Topics tab",
        url: "/help/search/?keywords=help",
        jsFileName: "help",
        topicsTab: true,
    },
    {
        label: "Help search with results Questions tab",
        url: "/help/search/?keywords=help",
        jsFileName: "help",
        questionsTab: true,
    },
]

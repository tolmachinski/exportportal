module.exports = [
    {
        label: "EP updates",
        url: "/ep_updates",
        jsFileName: "epUpdates",
    },
    {
        label: "EP updates with search",
        url: "/ep_updates",
        keywordExist: true,
        keyword: "Lorem",
        jsFileName: "epUpdates",
    },
    {
        label: "EP updates with search- did not match any words",
        url: "/ep_updates",
        keywordExist: true,
        keyword: "###",
        jsFileName: "epUpdates",
    },
]

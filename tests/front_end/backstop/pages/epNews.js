module.exports = [
    {
        label: "EP news",
        url: "/ep_news",
        jsFileName: "epNews",
    },
    {
        label: "EP news with all images",
        url: "/ep_news",
        jsFileName: "epNews",
        withImages: true,
    },
    {
        label: "EP news without images",
        url: "/ep_news",
        jsFileName: "epNews",
        withImages: false,
    },
    {
        label: "EP news with search",
        url: "/ep_news?keywords=b",
        jsFileName: "epNews",
    },
    {
        label: "EP news with search error",
        url: "/ep_news?keywords=basdasdqw13qwsdf3t43f",
    },
]

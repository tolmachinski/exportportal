module.exports = [
    {
        label: "Topics Help",
        url: "/topics/help",
        jsFileName: "topics",
    },
    {
        label: "Topics Help Found",
        url: "/topics/help?keywords=import",
        jsFileName: "topics",
    },
    {
        label: "Topics Help Not Found",
        url: "/topics/help?keywords=aaa",
        jsFileName: "topics",
    },
    {
        label: "Topics Help Subscribe",
        url: "/topics/help",
        jsFileName: "topics",
        subscribe: true,
    },
    {
        label: "Topics Help Send Subscribe",
        url: "/topics/help",
        jsFileName: "topics",
        sendSubscribe: true,
    },
]

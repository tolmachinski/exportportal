module.exports = [
    {
        label: "Mass media",
        url: "/mass_media",
        jsFileName: "massMedia",
    },
    {
        label: "Mass media  with all images",
        url: "/mass_media",
        jsFileName: "massMedia",
        withImages: true,
    },
    {
        label: "Mass media without all images",
        url: "/mass_media",
        jsFileName: "massMedia",
        withImages: false,
    },
    {
        label: "Mass media with search",
        url: "/mass_media?keywords=the",
        jsFileName: "massMedia",
    },
    {
        label: "Mass media with error search",
        url: "/mass_media?keywords=basdasdqw13qwsdf3t43f",
        jsFileName: "massMedia",
    },
]

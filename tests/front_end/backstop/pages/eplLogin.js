module.exports = [
    {
        label: "EPL login popup",
        subdomain: "epl",
        url: "/login",
        jsFileName: "eplLogin",
        openLoginPopup: true,
    },
    {
        label: "EPL login popup validation error",
        subdomain: "epl",
        url: "/login",
        jsFileName: "eplLogin",
        openLoginPopup: true,
        validationError: true,
    },
    {
        label: "EPL Login page",
        subdomain: "epl",
        url: "/login",
    },
    {
        label: "EPL Login page validation error",
        subdomain: "epl",
        url: "/login",
        jsFileName: "eplLogin",
        validationError: true,
    },
    {
        label: "EPL Login with info popup go to ep",
        subdomain: "epl",
        url: "/login",
        jsFileName: "eplLogin",
        authorize: true,
        notShipper: true,
        login: "backstop-1611836072357@backstop.test",
        password: "f3C7n4T2h9b0R3c1"
    },
]

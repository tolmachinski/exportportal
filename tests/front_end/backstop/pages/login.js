module.exports = [
    {
        label: "Login",
        url: "/login"
    },
    {
        label: "Login validation error",
        url: "/login",
        jsFileName: "login",
        isLogin: false
    },
    {
        label: "Login with success login with select account",
        url: "/login",
        jsFileName: "login",
        isLogin: true,
        login: "felics96@gmail.com",
        password: "Hero123123"
    },
    {
        label: "Login with redirect to EPL",
        url: "/login",
        jsFileName: "login",
        isLogin: true,
        login: "backstop-1603808871833@backstop.test",
        password: "f3C7n4T2h9b0R3c1"
    },
]

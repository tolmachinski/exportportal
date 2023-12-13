module.exports = [
    {
        label: "Forgot password",
        url: "/authenticate/forgot",
    },
    {
        label: "Forgot password error validation",
        url: "/authenticate/forgot",
        jsFileName: "forgotPassword",
        submitEmptyForm: true,
    },
    {
        label: "Forgot password not found email error",
        url: "/authenticate/forgot",
        jsFileName: "forgotPassword",
        notFoundEmail: true,
    },
]

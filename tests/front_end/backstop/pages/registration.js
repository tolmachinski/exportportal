module.exports = [
    // Main registration page
    {
        label: "Registration",
        url: "/register",
    },
    // Buyer registration
    {
        label: "Registration as buyer 1/3",
        url: "/register/buyer",
        jsFileName: "registration",
    },
    {
        label: "Registration as buyer 2/3",
        url: "/register/buyer",
        jsFileName: "registration",
        accountClass: "buyer",
        step: 2
    },
    {
        label: "Registration as buyer 2.5/3",
        url: "/register/buyer",
        jsFileName: "registration",
        accountClass: "buyer",
        step: 2.5
    },
    {
        label: "Registration as buyer 3/3",
        url: "/register/buyer",
        jsFileName: "registration",
        accountClass: "buyer",
        step: 3
    },
    {
        label: "Registration as buyer success",
        url: "/register/buyer",
        jsFileName: "registration",
        accountClass: "buyer",
        step: 4
    },
    // Seller registration
    {
        label: "Registration as seller 1/3",
        url: "/register/seller",
        jsFileName: "registration",
    },
    {
        label: "Registration as seller 2/3",
        url: "/register/seller",
        jsFileName: "registration",
        accountClass: "seller",
        step: 2
    },
    {
        label: "Registration as seller 2.5/3",
        url: "/register/seller",
        jsFileName: "registration",
        accountClass: "seller",
        step: 2.5
    },
    {
        label: "Registration as seller 3/3",
        url: "/register/seller",
        jsFileName: "registration",
        accountClass: "seller",
        step: 3
    },
    {
        label: "Registration as seller success",
        url: "/register/seller",
        jsFileName: "registration",
        accountClass: "seller",
        step: 4
    },
    // Manufacturer registration
    {
        label: "Registration as manufacturer 1/3",
        url: "/register/manufacturer",
        jsFileName: "registration",
    },
    {
        label: "Registration as manufacturer 2/3",
        url: "/register/manufacturer",
        jsFileName: "registration",
        accountClass: "manufacturer",
        step: 2
    },
    {
        label: "Registration as manufacturer 2.5/3",
        url: "/register/manufacturer",
        jsFileName: "registration",
        accountClass: "manufacturer",
        step: 2.5
    },
    {
        label: "Registration as manufacturer 3/3",
        url: "/register/manufacturer",
        jsFileName: "registration",
        accountClass: "manufacturer",
        step: 3
    },
    {
        label: "Registration as manufacturer success",
        url: "/register/manufacturer",
        jsFileName: "registration",
        accountClass: "manufacturer",
        step: 4
    },
]

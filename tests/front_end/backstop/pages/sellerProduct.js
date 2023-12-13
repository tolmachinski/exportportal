module.exports = [
    {
        label: "Seller Product",
        url: "/backstop_test/products",
        jsFileName: "sellerProduct",
    },
    {
        label: "Seller Product with filter and search",
        url: "/backstop_test/products/category/atv-four-wheelers-1168?keywords=Lorem",
        jsFileName: "sellerProduct",
    },
    {
        label: "Seller Product with filter and bad search",
        url: "/backstop_test/products/category/atv-four-wheelers-1168?keywords=badSearchBackstop",
        jsFileName: "sellerProduct",
    },
    {
        label: "Seller Product Hover",
        url: "/backstop_test/products",
        jsFileName: "sellerProduct",
        hoverItem: true,
    },

]

module.exports = [
    {
        label: "Droplist",
        url: "/items/droplist",
        jsFileName: "droplist",
        authentication: "buyer",
    },
    {
        label: "Droplist open filter",
        url: "/items/droplist",
        jsFileName: "droplist",
        authentication: "buyer",
        openFilter: true
    },
    {
        label: "Item add to droplist",
        url: "/items/droplist",
        jsFileName: "droplist",
        authentication: "buyer",
        editDroplist: true,
        openActions: true
    },
    {
        label: "Item remove from droplist",
        url: "/items/droplist",
        jsFileName: "droplist",
        authentication: "buyer",
        removeFromDroplist: true,
        openActions: true
    }
]

module.exports = [
    {
        label: "Search",
        url: "/search?keywords=test",
        jsFileName: "search",
    },
    {
        label: "Search nothing found",
        url: "/search?keywords=-=/*\-",
        jsFileName: "search",
        nothingFound: true,
    },
    {
        label: "Search with sorting",
        url: "/search?keywords=test&sort_by=create_date-desc",
        jsFileName: "search",
    },
    {
        label: "Search with modal",
        url: "/search?keywords=food",
        jsFileName: "search",
        openRequestProducts: true
    },
    {
        label: "Search with opened dropdown in header search form",
        url: "/search",
        jsFileName: "search",
        openDropdown: true,
        nothingFound: true,
    },
    {
        label: "Search with filled header search form",
        url: "/search",
        jsFileName: "search",
        fillSearchForm: true,
        searchType: "items",
        nothingFound: true,
        searchType: "items",
    },
    {
        label: "Search with filled categories in header search form",
        url: "/search",
        jsFileName: "search",
        openDropdown: true,
        clickCategoryItem: true,
        fillSearchForm: true,
        nothingFound: true,
    },
    {
        label: "Search with filled b2b in header search form",
        url: "/search",
        jsFileName: "search",
        openDropdown: true,
        clickB2bItem: true,
        fillSearchForm: true,
        nothingFound: true,
        itemBackstop: true,
        authentication: "certified seller",
    },
    {
        label: "Search with filled events in header search form",
        url: "/search",
        jsFileName: "search",
        openDropdown: true,
        clickEventsItem: true,
        fillSearchForm: true,
        nothingFound: true,
        itemBackstop: true
    },
    {
        label: "Search with filled help in header search form",
        url: "/help",
        jsFileName: "search",
        openDropdown: true,
        clickHeplItem: true,
        fillSearchForm: true,
        nothingFound: true,
    },
    {
        label: "Search with filled blogs in header search form",
        url: "/search",
        jsFileName: "search",
        openDropdown: true,
        clickBlogslItem: true,
        fillSearchForm: true,
        nothingFound: true,
    },
]
module.exports = [
    {
        label: "Community Help all questions",
        subdomain: "community",
        url: "/questions",
        jsFileName: "communityQuestionsList",
    },
    {
        label: "Community Help all questions Sidebar View More",
        subdomain: "community",
        url: "/questions",
        jsFileName: "communityQuestionsList",
        viewMore: true,
    },
    {
        label: "Community Help show search form",
        subdomain: "community",
        url: "/questions",
        jsFileName: "communityQuestionsList",
        showSearchForm: true,
    },
    {
        label: "Community Help found questions",
        subdomain: "community",
        url: "/questions/category/buying-and-selling-67/country/moldova-139?keywords=aaa",
        jsFileName: "communityQuestionsList",
        search: true,
    },
    {
        label: "Community Help not found questions",
        subdomain: "community",
        url: "/questions/category/buying-and-selling-67/country/moldova-139?keywords=BackstopTestNothingFound",
        jsFileName: "communityQuestionsList",
        search: true,
    },
]

import $ from "jquery";

const wrapBlogTables = () => {
    const tables = $(".mblog-detail__content table");

    if (!tables.length) {
        return true;
    }

    tables.wrap("<div class='mblog-detail__table'><div class='mblog-detail__table-inner'></div></div>");

    return true;
};

export default wrapBlogTables;

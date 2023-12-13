const getNavbarRange = (selector, tzStart, tzEnd, viewType) => {
    const start = tzStart.toDate();
    let middle;
    if (viewType === "month") {
        middle = new Date(start.getTime() + (tzEnd.toDate().getTime() - start.getTime()) / 2);
    }
    document.querySelector(selector).textContent = `${middle.toLocaleString("en-us", { month: "long" })} ${middle.getFullYear()}`;
};

export default getNavbarRange;

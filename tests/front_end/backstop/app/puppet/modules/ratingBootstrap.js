module.exports = async (page, scenario) => {
    await page.waitForFunction(fillRatingStars);
    await page.waitForTimeout(500);
};

function fillRatingStars() {
    return (async () => {
        const ratingCenter = document.querySelectorAll(`[atas="global__rating-circle"]`);
        const ratingCenterLine = document.querySelectorAll(`[atas="global__rating-circle__static-line-bg"]`);

        if (ratingCenter.length) {
            ratingCenter.forEach(item => {
                item.textContent = 5;
            });
        }

        for (let star of document.querySelectorAll(".rating-symbol-background")) {
            star.classList.remove("txt-gray-light");

            if (star.classList.contains("ep-icon_diamond")) {
                star.classList.add("txt-green");
            } else {
                star.classList.add("txt-orange");
            }
        }

        if (ratingCenterLine.length) {
            ratingCenterLine.forEach(item => {
                item.style.width = "100%";
            });
        }

        return true;
    })();
}

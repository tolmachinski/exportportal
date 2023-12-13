module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const pictureFn = require("../functions/picture");
    const functions = {
        recursiveCallOnCatch: require("../functions/recursive-try-catcher"),
        click: require("../functions/click"),
    };
    const clientWidth = await page.evaluate(() => {
        return document.documentElement.clientWidth;
    });
    const data = {
        fcpBanner: {
            desktop: variables.img(1420, 400),
            tablet: variables.img(991, 774),
            mobile: variables.img(425, 550),
        },
        updatesBanner: {
            desktop: variables.img(1420, 200),
            tablet: variables.img(991, 255),
            mobile: variables.img(425, 806),
        },
        picksOfMonth: {
            title: {
                date: variables.dateFormat.month,
            },
            product: {
                image: variables.img(200, 200),
                title: variables.lorem(100),
                price: "$99.999.00",
                oldPrice: "$99.999.00",
                discount: "- 99%",
                countryFlag: variables.img(24, 24),
                countryName: "United States of America",
            },
            seller: {
                image: variables.img(200, 200),
                title: variables.lorem(11),
                type: "Verified Manufacturer",
                members: "Member from Nov 2020",
                countryFlag: variables.img(24, 24),
                countryName: "United States of America",
            },
        },
        exclusiveDeals: {
            title: variables.lorem(50),
            background: variables.img(700, 299),
            img: variables.img(200, 200),
        },
        blogs: {
            src: variables.img(776, 337),
            title: variables.lorem(149),
            by_who: "Export Portal",
            date: variables.dateFormat.withoutTime,
        },
        ffMagazine: {
            image: variables.img(232, 329),
        },
        dashboardBanner: {
            image: variables.img(140, 65),
            title: variables.lorem(150),
            btn: variables.lorem(50),
        },
    };

    // FCP banner
    await page.waitForFunction(
        pictureFn,
        {},
        {
            selectorAll: `[atas="global__header-slider-widget-banner"] picture`,
            src: data.fcpBanner.desktop,
            media: {
                mobile: {
                    src: data.fcpBanner.mobile,
                    attr: "(max-width:425px)",
                },
                tablet: {
                    src: data.fcpBanner.tablet,
                    attr: "(max-width:991px)",
                },
            },
        }
    );

    if (scenario.isFreightForwarder) {
        await page.waitForFunction(freightForwardersSlider, {}, data.ffMagazine);
    }
    // Banner
    await page.waitForFunction(
        pictureFn,
        {},
        {
            selectorAll: `[atas="global__updates-from-ep-widget-banner"] picture`,
            src: data.updatesBanner.desktop,
            media: {
                mobile: {
                    src: data.updatesBanner.mobile,
                    attr: "(max-width:425px)",
                },
                tablet: {
                    src: data.updatesBanner.tablet,
                    attr: "(max-width:991px)",
                },
            },
        }
    );
    // Picks of month
    if (scenario.isGuest || scenario.isSeller || scenario.isBuyer) {
        await page.waitForFunction(picksOfMonth, {}, data.picksOfMonth);
    }
    // Exclusive deals
    await page.waitForFunction(exclusiveDeals, {}, data.exclusiveDeals);
    // Await ajax loaded Latest Items Slider
    await functions.recursiveCallOnCatch(
        async () => {
            await page.waitForSelector(`[atas="home__latest-items-slider"].slick-initialized`, { visible: true });
        },
        async () => {
            await page.waitForSelector(`[atas="home__our-blog-slider"]`);
        },
        120000
    );
    // Await ajax loaded Blog Slider
    await functions.recursiveCallOnCatch(
        async () => {
            await page.waitForSelector(`[atas="home__our-blog-slider"].slick-initialized`, { visible: true });
        },
        async () => {
            await page.waitForSelector(`[atas="home__our-blog-slider"]`);
        },
        120000
    );

    if (!scenario.isGuest) {
        // Await ajax loaded Reviews Slider
        await page.waitForNetworkIdle();
        await functions.recursiveCallOnCatch(
            async () => {
                await page.waitForSelector(`[atas="global__reviews-slider"].slick-initialized`, { visible: true });
            },
            async () => {},
            150000
        );

        // Replace reviews
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await require("../modules/reviewsSlider")(page, scenario);
    }

    // Blogs
    await page.waitForFunction(blogSlider, {}, data.blogs);
    // Products
    await require("../modules/productCard")(page);

    // Open menu modal share
    if (scenario.openShare) {
        await page.waitForFunction(openShare);
    }

    if (scenario.showQuickMenu || scenario.showFullMenu || scenario.showSwitchAccount) {
        // Open dashboard menu
        await page.waitForFunction(openDashboardMenu);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="global__dashboard-menu__dashboard-banner"]`);
        await page.waitForFunction(dashboardBanner, {}, data.dashboardBanner);
    }

    if (scenario.showFullMenu) {
        if (clientWidth > 991) {
            await functions.click(page, `[atas="global__dashboard-menu__full-menu-btn"]`);
        } else {
            await functions.click(page, `[atas="global__mep-dashboard-menu__full-menu-btn"]`);
        }
        await page.waitForTimeout(500);
    }

    if (scenario.showSwitchAccount) {
        // Show switch account
        await page.waitForFunction(openSwitchAccount);
        await page.waitForTimeout(500);
    }

    if (scenario.showPopupUserPreferences) {
        if (clientWidth > 991) {
            // Open preferences popup
            await functions.click(page, `[atas="global__header__user-preferences-btn"]`);
            await page.waitForTimeout(500);
        } else {
            // Open dashboard menu
            await page.waitForFunction(openDashboardMenu);
            await page.waitForTimeout(500);
            // Open preferences popup
            await functions.click(page, `[atas="global__mep-dashboard__user-preferences-btn"]`);
            await page.waitForTimeout(500);
        }
    }

    if (scenario.showPopupCategories) {
        // Open popup categories
        if (clientWidth > 991) {
            await functions.click(page, `[atas="global__header__categories-btn"]`);
            await page.waitForTimeout(500);
        } else {
            await functions.click(page, `[atas="global__mep-header__categories-btn"]`);
            await page.waitForTimeout(500);
        }

        // This code needs to be refactored when side categories will be refactored because now it's all in js
        await page.waitForNetworkIdle();
        await functions.click(page, `[atas="global__side-categories__first-step-list"] li[data-category='1']`);
        await page.waitForNetworkIdle();
        await functions.click(page, `#js-first-side-category-list a[data-category='383']`);
        await page.waitForNetworkIdle();
        await functions.click(page, `#js-center-side-category-list a[data-category='398']`);
        await page.waitForNetworkIdle();
    }

    if (scenario.showCompleteProfileVideo) {
        await page.waitForSelector(`[atas="home__steps-to-start_video-1"]`);
        await functions.click(page, `[atas="home__steps-to-start_video-1"]`);
        await require("../modules/onLoadIframe")(page, `.bootstrap-dialog .js-popup-video-iframe`);
    }

    if (scenario.showAddItemVideo) {
        await page.waitForSelector(`[atas="home__steps-to-start_video-2"]`);
        await functions.click(page, `[atas="home__steps-to-start_video-2"]`);
        await require("../modules/onLoadIframe")(page, `.bootstrap-dialog .js-popup-video-iframe`);
    }

    if (scenario.showStartSellingVideo) {
        await page.waitForSelector(`[atas="home__steps-to-start_video-3"]`);
        await functions.click(page, `[atas="home__steps-to-start_video-3"]`);
        await require("../modules/onLoadIframe")(page, `.bootstrap-dialog .js-popup-video-iframe`);
    }

    // Replace user info
    await require("../modules/userInfo")(page, scenario);

    // Open click to call
    if (scenario.openClickToCall) {
        await page.waitForFunction(openClickToCallPopup);
        await page.waitForNetworkIdle();
    }
    if (scenario.openClickToCall && scenario.validate) {
        await page.waitForFunction(openClickToCallPopup);
        await page.waitForNetworkIdle();
        await functions.click(page, `[atas="global__click-to-call-popup_form_submit-btn_popup"]`);
    }
};

function openShare() {
    return (async function () {
        document.querySelector(`[atas="global__item-btn-share"]`).click();
        return true;
    })();
}

function openClickToCallPopup() {
    return (async function () {
        document.querySelector(`[atas="right-sidebar__click-to-call-btn"]`).click();
        return true;
    })();
}

function picksOfMonth(picksOfMonthData) {
    return (async function () {
        // ----- PRODUCT ----- //
        const product = document.querySelector('[atas="home__picks-of-month-product"]');
        if (!product) {
            console.error("Ошибка в секции Best picks of the month, не найден блок с топ продуктом");
        }
        const discountBadge = product.querySelector(`[atas="home__picks-of-month-item-discount"]`);
        // Main image
        product.querySelector(`[atas="home__picks-of-month-item-image"]`).src = picksOfMonthData.product.image;
        // Discount
        if (discountBadge) {
            discountBadge.textContent = picksOfMonthData.product.discount;
        }
        // Title
        product.querySelector(`[atas="home__picks-of-month-item-title"]`).textContent = picksOfMonthData.product.title;
        // Price
        product.querySelector(`[atas="home__picks-of-month-item-new-price"]`).textContent = picksOfMonthData.product.price;
        product.querySelector(`[atas="home__picks-of-month-item-old-price"]`).textContent = picksOfMonthData.product.oldPrice;
        // Country
        product.querySelector(`[atas="home__picks-of-month-item-country"] img`).src = picksOfMonthData.product.countryFlag;
        product.querySelector(`[atas="home__picks-of-month-item-country"] span`).textContent = picksOfMonthData.product.countryName;
        // ----- SELLER ----- //
        const bestSeller = document.querySelector(`[atas="home__picks-of-month-seller"]`);
        if (!bestSeller) {
            console.error("Ошибка в секции Best picks of the month, не найден блок с топ компанией");
        }
        const bestSellerType = bestSeller.querySelector(`[atas="home__picks-of-month-seller-company-type"]`);
        // Main image
        bestSeller.querySelector(`[atas="home__picks-of-month-seller-img"]`).src = picksOfMonthData.seller.image;
        // Title
        bestSeller.querySelector(`[atas="home__picks-of-month-seller-title"]`).textContent = picksOfMonthData.seller.title;
        // Country name
        bestSellerType.textContent = picksOfMonthData.seller.countryName;
        bestSellerType.classList.remove("txt-orange", "txt-green");
        bestSellerType.classList.add("txt-green");
        // Date
        bestSeller.querySelector(`[atas="home__picks-of-month-seller-date"]`).textContent = picksOfMonthData.seller.members;
        // Country
        bestSeller.querySelector(`[atas="home__picks-of-month-seller-country"] img`).src = picksOfMonthData.seller.countryFlag;
        bestSeller.querySelector(`[atas="home__picks-of-month-seller-country"] span`).textContent = picksOfMonthData.seller.countryName;

        const titleDate = document.querySelector(`[atas="home__picks-of-month_title_date"]`);
        if (discountBadge) {
            titleDate.textContent = picksOfMonthData.title.date;
        }

        return true;
    })();
}

function exclusiveDeals(exclusiveDealsData) {
    return (async function () {
        // Title
        document.querySelectorAll('[atas="home__exclusive-deals-title"]').forEach(title => {
            title.textContent = exclusiveDealsData.title;
        });
        // Background
        document.querySelectorAll('[atas="home__exclusive-deals-background"]').forEach(background => {
            background.querySelectorAll("source, img").forEach(img => {
                img.srcset = exclusiveDealsData.background;
                img.src = exclusiveDealsData.background;
            });
        });
        // Images
        document.querySelectorAll('[atas="home__exclusive-deals-imgs"]').forEach(imgs => {
            const imgsNode = imgs.querySelectorAll("img");
            if (imgsNode.length < 4) {
                for (let i = 0; i < 4 - imgsNode.length; i += 1) {
                    const clone = imgs.children[0].cloneNode();
                    clone.src = exclusiveDealsData.img;
                    imgs.appendChild(clone);
                }
            }
            imgsNode.forEach(img => {
                img.src = exclusiveDealsData.img;
            });
        });

        return true;
    })();
}

function blogSlider(blog) {
    return (async function () {
        document.querySelectorAll('[atas="home__blog-item"]').forEach(e => {
            // Image
            let img = e.querySelector('[atas="home__blog-img"]');
            img.dataset.src = blog.src;
            img.src = blog.src;
            // Title
            e.querySelector('[atas="home__blog-link"]').textContent = blog.title;
            // Who write
            e.querySelector('[atas="home__blog-author"]').textContent = blog.by_who;
            // Date
            e.querySelector('[atas="home__blog-date"]').textContent = blog.date;
        });

        return true;
    })();
}

function openDashboardMenu() {
    return (async function () {
        if (document.body.clientWidth > 991) {
            window.scrollTo({
                top: 0,
            });
            document.querySelector(`[atas="global__header__navbar-toggler"]`).click();
        } else {
            document.querySelector(`[atas="global__mep-header__navbar-toggler"]`).click();
        }

        return true;
    })();
}

function openSwitchAccount() {
    return (async function () {
        if (document.body.clientWidth < 992) {
            document.querySelector(`[atas="global__mep-dashboard__switch-account-btn"]`).click();
        }

        return true;
    })();
}

function freightForwardersSlider(data) {
    return (async function () {
        document.querySelectorAll(`[atas="page__home__freight-forwarders-magazine-slider_item-image"]`).forEach(image => {
            image.src = data.image;
        });
        return true;
    })();
}

function dashboardBanner(banner) {
    return (async function () {
        document.querySelectorAll(`[atas="global__dashboard-menu__dashboard-banner"]`).forEach(item => {
            item.querySelector(`[atas="global__dashboard-menu__dashboard-banner_image"]`).src = banner.image;
            item.querySelector(`[atas="global__dashboard-menu__dashboard-banner_suptitle"]`).textContent = banner.title;
            item.querySelector(`[atas="global__dashboard-menu__dashboard-banner_title"]`).textContent = banner.title;
            item.querySelector(`[atas="global__dashboard-menu__dashboard-banner_btn"]`).textContent = banner.btn;
        });

        return true;
    })();
}

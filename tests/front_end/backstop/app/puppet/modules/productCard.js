/*
    Должна быть скидка хоть на 1 продукте из всего списка,
    для того чтобы на странице был блок с % скидкой и блок с ценой без скидки,
    чтобы можно было клонировать их и добавлять на все элементы
*/
module.exports = async (page, hoverItem = false) => {
    const variables = require('../variables/variables');
    const itemData = {
        img: variables.img(375, 281),
        userGroups: variables.userGroups,
        items:[
            {
                discount: variables.discount.max,
                text: variables.lorem(75),
                price: variables.price.max,
                lowPrice: variables.price.max,
                country: variables.country.name,
                countrySrc: variables.country.flag,
                groupTitle: variables.lorem(45),
                group: variables.userGroups.certified.text.seller,
                groupClass: variables.userGroups.certified.class
            },
            {
                discount: variables.discount.high,
                text: variables.lorem(11),
                price: variables.price.high,
                lowPrice: variables.price.medium,
                country: variables.country.name,
                countrySrc: variables.country.flag,
                groupTitle: variables.lorem(11),
                group: variables.userGroups.verified.text.seller,
                groupClass: variables.userGroups.verified.class,
            },
            {
                discount: variables.discount.outOfStock,
                text: variables.lorem(75),
                price: variables.price.medium,
                lowPrice: variables.price.low,
                country: variables.country.name,
                countrySrc: variables.country.flag,
                groupTitle: variables.lorem(45),
                group: variables.userGroups.certified.text.manufacturer,
                groupClass: variables.userGroups.certified.class
            },
            {
                discount: variables.discount.samples,
                text: variables.lorem(11),
                price: variables.price.low,
                lowPrice: variables.price.min,
                country: variables.country.name,
                countrySrc: variables.country.flag,
                groupTitle: variables.lorem(11),
                group: variables.userGroups.verified.text.manufacturer,
                groupClass: variables.userGroups.verified.class,
            },
        ],
    };
    await page.waitForFunction(productCard, {}, itemData);

    const miniItemData = { ...itemData };
    const miniDiscounts = [ variables.discount.max, variables.discount.high, variables.discount.min, variables.discount.low ];
    miniItemData.items.forEach((key, i) => {
        miniItemData.items[i].discount = miniDiscounts[i];
    });
    await page.waitForFunction(miniProductCard, {}, miniItemData);

    if (hoverItem) {
        await page.waitForNetworkIdle();
        await page.waitForSelector('[atas="global__item"]');
        await page.waitForFunction(() => {
            return (() => {
                if (window.matchMedia("(min-width: 991px)").matches) {
                    document.querySelector('[atas="global__item"]').classList.add("hover");
                }
                return true;
            })();
        });
    }
}

function productCard(data){
    return (async function(){
        document.querySelectorAll('[atas="global__item"]').forEach((el, i) => {
            if(window.getComputedStyle(el)['display'] === 'none'){
                return true;
            }
            let info = data.items[i % data.items.length],
                img = el.querySelector(`[atas="global__item-image"]`),
                flag = el.querySelector(`[atas="global__item-country"] img`),
                countryName = el.querySelector(`[atas="global__item-country-name"]`),
                accountGroup = el.querySelector(`[atas="global__item-account-group"]`),
                badge = el.querySelector(`[atas="global__item-badge"]`),
                discount = el.querySelector(`[atas="global__item-discount"]`),
                oldPrice = el.querySelector(`[atas="global__item-old-price"]`),
                seller = el.querySelector(`[atas="global__item-seller-name"]`),
                badgeElem = discount ? discount : badge.querySelector(`[atas="global__item-badge_samples"], [atas="global__item-badge_stock"]`),
                originCountry = el.querySelector(`[atas="global__item-country-origin"]`);
            // Discount
            if (badgeElem) {
                badgeElem.textContent = info.discount;
                if (info.discount === "OUT OF STOCK" || info.discount === "SAMPLES ONLY") {
                    badgeElem.classList.add("products__status-item--stock-out");
                    badgeElem.classList.remove("bg-blue2");
                    el.classList.add("products__item--stock-out");
                } else {
                    badgeElem.classList.remove("products__status-item--stock-out");
                    badgeElem.classList.add("bg-blue2");
                    el.classList.remove("products__item--stock-out");
                }
            } else {
                let discountNode = `[atas="global__item-discount"]`;
                if(document.querySelector(discountNode)){
                    let cBadge = document.querySelector(discountNode).cloneNode();
                    cBadge.textContent = info.discount;
                    if (info.discount === "OUT OF STOCK" || info.discount === "SAMPLES ONLY") {
                        cBadge.classList.add("products__status-item--stock-out");
                        cBadge.classList.remove("bg-blue2");
                        cBadge.style.width = "auto";
                        cBadge.style.backgroundColor = "#9e9e9e";
                        el.classList.add("products__item--stock-out");
                    }

                    badge.append(cBadge);
                }
            }
            // Image
            img.src = data.img;
            img.dataset.src = data.img;
            // Title
            el.querySelector(`[atas="global__item-title"]`).textContent = info.text;
            // Price
            el.querySelector(`[atas="global__item-new-price"]`).textContent = info.price;
            // Old price
            if(oldPrice){
                oldPrice.textContent = info.lowPrice;
            } else {
                let otherOldPrice = `[atas="global__item-old-price"]`
                if(document.querySelector(otherOldPrice)){
                    let cOldPrice = document.querySelector(otherOldPrice).cloneNode();
                    cOldPrice.textContent = info.lowPrice;
                    el.querySelector(`[atas="global__item-price"]`).append(cOldPrice);
                }
            }
            // Country
            if(flag){
                flag.src = info.countrySrc;
                countryName.textContent = info.country;
                el.querySelector(`[atas="global__item-country"]`).innerHTML = `${flag.outerHTML} ${countryName.outerHTML}`;
            }
            // Country of Origin
            if(originCountry){
                originCountry.textContent = info.country;
            }
            // === Hidden block info for mobile
            // Group title
            if(seller){
                seller.textContent = info.groupTitle;
            }
            // Group type
            if(accountGroup){
                for(let key in data.userGroups){
                    accountGroup.classList.remove(data.userGroups[key].class);
                }
                accountGroup.classList.add(info.groupClass);
                accountGroup.textContent = info.group;
            }
            // Border add/remove for all
            if(i%2 == 0){
                el.classList.remove('products__item--highlight');
            } else {
                el.classList.add('products__item--highlight');
            }
        });

        return true
    })()
}

function miniProductCard(data) {
    return (async () => {
        document.querySelectorAll('[atas="global__mini-item"]').forEach((el, i) => {
            if (window.getComputedStyle(el)['display'] === 'none') {
                return true;
            }

            let info = data.items[i % data.items.length],
                img = el.querySelector(`[atas="global__mini-item_image"]`),
                flag = el.querySelector(`[atas="global__mini-item_country"] img`),
                accountGroup = el.querySelector(`[atas="global__mini-item_account-group"]`),
                badge = el.querySelector(`[atas="global__mini-item_badge"]`),
                discount = el.querySelector(`[atas="global__mini-item_discount"]`),
                oldPrice = el.querySelector(`[atas="global__mini-item_old-price"]`),
                seller = el.querySelector(`[atas="global__mini-item_seller-name"]`);

            if(discount) {
                discount.textContent = info.discount;
                discount.classList.remove("products__status-item--stock-out");
                discount.classList.add("bg-blue2");
                el.classList.remove("products__item--stock-out");
            } else {
                let discountNode = `[atas="global__mini-item_discount"]`;
                if(document.querySelector(discountNode)){
                    let cBadge = document.querySelector(discountNode).cloneNode();
                    cBadge.textContent = info.discount;
                    if(info.discount === "OUT OF STOCK" || info.discount === "SAMPLES ONLY") {
                        cBadge.classList.add("products__status-item--stock-out");
                        cBadge.classList.remove("bg-blue2");
                        cBadge.style.width = "auto";
                        cBadge.style.backgroundColor = "#9e9e9e";
                        el.classList.add("products__item--stock-out");
                    }

                    badge.append(cBadge);
                }
            }
            // Image
            img.src = data.img;
            img.dataset.src = data.img;
            // Title
            el.querySelector(`[atas="global__mini-item_title"]`).textContent = info.text;
            // Price
            el.querySelector(`[atas="global__mini-item_new-price"]`).textContent = info.price;
            // Old price
            if(oldPrice){
                oldPrice.textContent = info.lowPrice;
            } else {
                let otherOldPrice = `[atas="global__mini-item_old-price"]`
                if(document.querySelector(otherOldPrice)){
                    let cOldPrice = document.querySelector(otherOldPrice).cloneNode();
                    cOldPrice.textContent = info.lowPrice;
                    el.querySelector(`[atas="global__mini-item_price"]`).append(cOldPrice);
                }
            }
            // Country
            if(flag){
                flag.src = info.countrySrc;
                el.querySelector(`[atas="global__mini-item_country"]`).innerHTML = `${flag.outerHTML} ${info.country}`;
            }
            // === Hidden block info for mobile
            // Group title
            if(seller){
                seller.textContent = info.groupTitle;
            }
            // Group type
            if(accountGroup){
                for(let key in data.userGroups){
                    accountGroup.classList.remove(data.userGroups[key].class);
                }
                accountGroup.classList.add(info.groupClass);
                accountGroup.textContent = info.group;
            }
            // Border add/remove for all
            if(i%2 == 0){
                el.classList.remove('products__item--highlight');
            } else {
                el.classList.add('products__item--highlight');
            }
        });

        return true;
    })()
}

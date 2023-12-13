module.exports = async page => {
    const variables = require("../variables/variables");
    const data = {
        img: variables.img(140, 140),
        userGroups: variables.userGroups,
    };
    data.items = [
        {
            title: variables.lorem(75),
            group: variables.userGroups.certified.text.seller,
            groupClass: variables.userGroups.certified.class,
            member: "Member from Sep 2019",
            country: variables.country.name,
            countrySrc: variables.country.flag,
        },
        {
            title: variables.lorem(30),
            group: variables.userGroups.verified.text.seller,
            groupClass: variables.userGroups.verified.class,
            member: "Member from Aug 2020",
            country: variables.country.name,
            countrySrc: variables.country.flag,
        },
        {
            title: variables.lorem(20),
            group: variables.userGroups.certified.text.manufacturer,
            groupClass: variables.userGroups.certified.class,
            member: "Member from Nov 2021",
            country: variables.country.name,
            countrySrc: variables.country.flag,
        },
        {
            title: variables.lorem(11),
            group: variables.userGroups.verified.text.manufacturer,
            groupClass: variables.userGroups.verified.class,
            member: "Member from Feb 2015",
            country: variables.country.name,
            countrySrc: variables.country.flag,
        },
    ];

    await page.waitForFunction(companyCard, {}, data);
};

function companyCard(data) {
    return (async function () {
        document.querySelectorAll('[atas="global__item__company-card"]').forEach((el, i) => {
            const info = data.items[i % data.items.length];
            const img = el.querySelector('[atas="global__item__company-card_image"]');
            const flag = el.querySelector('[atas="global__item__company-card_country-flag"]');
            const accountGroup = el.querySelector('[atas="global__item__company-card_user-group"]');
            const member = el.querySelector('[atas="global__item__company-card_member-from"]');
            const branch = el.querySelector('[atas="global__item__company-card_branch"]');

            // Image
            img.src = data.img;
            // Title
            el.querySelector('[atas="global__item__company-card_title"]').textContent = info.title;
            // Account group
            if (accountGroup) {
                for (let key in data.userGroups) {
                    accountGroup.classList.remove(data.userGroups[key].class);
                }
                accountGroup.classList.add(info.groupClass);
                accountGroup.textContent = info.group;
            }
            // Member
            if(member) {
                member.textContent = info.member;
            }
            // Branch
            if(branch) {
                branch.textContent = info.name;
            }
            // Country
            flag.src = info.countrySrc;
            el.querySelector('[atas="global__item__company-card_country-name"]').textContent = info.country;
        });
        return true;
    })();
}

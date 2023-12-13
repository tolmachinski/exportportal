module.exports = async (page, scenario = {}) => {
    const variables = require("../variables/variables");

    const data = {
        name: variables.name.xLong,
        img: variables.img(76, 76),
        date: `follower ${variables.dateFormat.withoutTime}`,
        userGroups: variables.userGroups,
        followers: [
            {
                group: variables.userGroups.certified.text.seller,
                groupClass: variables.userGroups.certified.class,
            },
            {
                group: variables.userGroups.verified.text.seller,
                groupClass: variables.userGroups.verified.class,
            },
            {
                group: variables.userGroups.certified.text.manufacturer,
                groupClass: variables.userGroups.certified.class,
            },
            {
                group: variables.userGroups.verified.text.manufacturer,
                groupClass: variables.userGroups.verified.class,
            },
        ],
    };

    await page.waitForFunction(replaceFollowers, {}, { data });
};

function replaceFollowers({ data }) {
    return (async () => {
        document.querySelectorAll(`[atas="global__item__followers-block"]`).forEach((follower, index) => {
            follower.querySelector(`[atas="global__item__followers-block_name"]`).textContent = data.name;
            follower.querySelector(`[atas="global__item__followers-block_date"]`).textContent = data.date;
            follower.querySelector(`[atas="global__item__followers-block_image"]`).src = data.img;

            const followersGroup = follower.querySelector(`[atas="global__item__followers-block_group"]`);
            const info = data.followers[index % data.followers.length];

            for (let key in data.userGroups) {
                followersGroup.classList.remove(data.userGroups[key].class);
            }
            followersGroup.classList.add(info.groupClass);
            followersGroup.textContent = info.group;
        });

        return true;
    })();
}

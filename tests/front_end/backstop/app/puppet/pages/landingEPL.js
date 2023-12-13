module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const data = {
        blogs: {
            title: variables.lorem(100),
            text: variables.lorem(150),
            img: variables.img(300, 200),
        },
        eduMaterials: {
            title: variables.lorem(100),
            text: variables.lorem(150),
            img: variables.img(300, 420),
        },
    };

    await page.waitForFunction(replaceBlogs, {}, data.blogs);
    await page.waitForFunction(replaceEduMaterials, {}, data.eduMaterials);
};

function replaceBlogs(data) {
    return (async () => {
        document.querySelectorAll(`[atas="epl-landing__blog-item"]`).forEach(blog => {
            const title = blog.querySelector(`[atas="epl-landing__blog-title"]`);
            const text = blog.querySelector(`[atas="epl-landing__blog-description"]`);
            const img = blog.querySelector(`[atas="epl-landing__blog-img"]`);

            title.textContent = data.title;
            text.textContent = data.text;
            img.dataset.src = data.img;
            img.src = data.img;
        });

        return true;
    })();
}

function replaceEduMaterials(data) {
    return (async () => {
        document.querySelectorAll(`[atas="epl-landing__edu-item"]`).forEach(eduMaterial => {
            const title = eduMaterial.querySelector(`[atas="epl-landing__edu-title"]`);
            const text = eduMaterial.querySelector(`[atas="epl-landing__edu-description"]`);
            const img = eduMaterial.querySelector(`[atas="epl-landing__edu-img"]`);

            title.textContent = data.title;
            text.textContent = data.text;
            img.dataset.src = data.img;
            img.src = data.img;
        });

        return true;
    })();
}

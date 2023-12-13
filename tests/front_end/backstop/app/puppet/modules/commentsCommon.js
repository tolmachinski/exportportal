module.exports = async (page, commentLength) => {
    const variables = require('../variables/variables');
    const data = {
        img: variables.img(80, 80),
        name: variables.name.medium,
        date: variables.dateFormat.withTime,
        comment: variables.lorem(commentLength)
    }

    await page.waitForFunction(comment, {}, data);
}

function comment(data){
    return (() => {
        document.querySelector(`[atas="global__common-comments_title_counter"]`).textContent = 99999;
        for(let comment of document.querySelectorAll(`[atas="global__common-comments_item"]`)){
            comment.querySelector(`[atas="global__common-comments_item_image"]`).dataset.src = data.img;
            comment.querySelector(`[atas="global__common-comments_item_image"]`).src = data.img;
            comment.querySelector(`[atas="global__common-comments_item_user-name"]`).textContent = data.name;
            comment.querySelector(`[atas="global__common-comments_item_date"]`).textContent = data.date;
            comment.querySelector(`[atas="global__common-comments_item_text"]`).textContent = data.comment;
        }

        return true
    })();
}

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
    return (async function(){
        const commentCounter = document.querySelector(`[atas="global__comment-counter"]`);
        if (commentCounter) {
            commentCounter.textContent = 99999;
        }

        for(let comment of document.querySelectorAll(`[atas="global__comment"]`)){
            // Image
            comment.querySelector(`[atas="global__comment-image"]`).dataset.src = data.img;
            comment.querySelector(`[atas="global__comment-image"]`).src = data.img;
            // User name
            comment.querySelector(`[atas="global__comment-name"]`).textContent = data.name;
            // Comment date
            comment.querySelector(`[atas="global__comment-date"]`).textContent = data.date;
            // Comment text
            comment.querySelector(`[atas="global__comment-text"]`).textContent = data.comment;
        }
        return true
    })()
}

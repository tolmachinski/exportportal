module.exports = function(data){
    return (async function(){
        let pictures = document.querySelectorAll(data.selectorAll);
        for (let picture of pictures) {
            let img = picture.querySelector('img'),
                sources = picture.querySelectorAll('source');
            for (let source of sources) {
                if (data.media) {
                    // Mobile
                    if (data.media.mobile && data.media.mobile.attr == source.media) {
                        source.dataset.srcset = data.media.mobile.src;
                        source.srcset = data.media.mobile.src;
                        continue;
                    }
                    // Tablet
                    if (data.media.tablet && data.media.tablet.attr == source.media) {
                        source.dataset.srcset = data.media.tablet.src;
                        source.srcset = data.media.tablet.src;
                        continue;
                    }
                }

                // Default
                source.dataset.srcset = data.src;
                source.srcset = data.src;
            }
            img.dataset.src = data.src;
            img.src = data.src;
        }
        return true;
    })()
};

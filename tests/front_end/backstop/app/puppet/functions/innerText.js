module.exports = function(data){
    return (async function(){
        for(const selector of document.querySelectorAll(data.selectorAll)){
            selector.innerHTML = data.inner;
        }
        return true
    })()
};

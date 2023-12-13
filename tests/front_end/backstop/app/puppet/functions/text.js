module.exports = function(data){
    return (async function(){
        for(const selector of document.querySelectorAll(data.selectorAll)){
            selector.textContent = data.text;
        }
        return true
    })()
};

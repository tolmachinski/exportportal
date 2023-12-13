module.exports = function(data){
    return (async function(){
        for(let selector of document.querySelectorAll(data.selectorAll)){
            selector.innerHTML  = selector.innerHTML.replaceAll(/\d[0-9]*/gm, data.value);
        }
        return true
    })()
};

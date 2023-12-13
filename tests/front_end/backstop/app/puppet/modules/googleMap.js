module.exports = function(){
    return (async function(){
        let b = document.createElement('div'),
            map = document.querySelector(`[atas="global__google-map"]`);
        b.setAttribute('style', 'background-color: #000; width: 100%; height: 100%; position: absolute; left: 0; top: 0; color: #fff; display:flex; justify-content: center; align-items:center; font-size: 30px;');
        b.textContent = "Google map here";
        map.append(b);
        map.style.position = 'relative';
        return true
    })()
}

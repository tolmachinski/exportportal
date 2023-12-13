module.exports = async data => {
    return (() => {
        const iframe = document.querySelectorAll(data.selector);

        if (iframe.length) {
            iframe.forEach(el => {
                const w = document.createElement('div');
                const b = document.createElement('div');
                b.setAttribute('style', 'background-color: #000; width: 100%; height: 100%; position: absolute; left: 0; top: 0; color: #fff; display:flex; justify-content: center; align-items:center; font-size: 30px;');
                w.setAttribute('style', 'width: 100%; height: 100%; position: relative;');
                b.textContent = "Video here";
                const iframeWrapper = el.parentElement;
                w.appendChild(el);
                w.appendChild(b);
                w.style.position = 'relative';
                iframeWrapper.append(w);
            });

        }

        return true;
    })();
};

const delay = async function (timeout = 1000) {
    return new Promise(resolve => {
        setTimeout(() => {
            resolve();
        }, timeout);
    });
};

export default delay;

const recursiveCallOnCatch = async function(callback = () => {}, onCatch = () => {}, timeout = 120000){
    let timeoutPassed = false;
    setTimeout(() => {
        timeoutPassed = true;
    }, timeout);

    return async () => {
        try {
            await callback();
        } catch(e) {
            if (!timeoutPassed) {
                await onCatch();
                await recursiveCallOnCatch(callback, onCatch);
            }
        }

        return true;
    }
}

module.exports = recursiveCallOnCatch;

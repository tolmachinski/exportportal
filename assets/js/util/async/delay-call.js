import delay from "@src/util/async/delay";

const delayCall = async function (fn, timeout = 1000) {
    await delay(timeout);

    return fn.call(this);
};

export default delayCall;

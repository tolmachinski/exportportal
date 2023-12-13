const getSwipeCount = breakpoints => {
    const keys = Object.keys(breakpoints).sort((a, b) => a - b);
    let swipeCount = Object.values(breakpoints)[keys.length - 1];
    keys.every(key => {
        if (window.matchMedia(`(max-width:${key}px)`).matches) {
            swipeCount = breakpoints[key];
        }

        return !window.matchMedia(`(max-width:${key}px)`).matches;
    });

    return swipeCount;
};

export default getSwipeCount;

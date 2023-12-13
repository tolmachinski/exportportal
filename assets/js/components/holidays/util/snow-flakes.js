// @ts-nocheck

export default () => {
    const img = document.createElement("img");
    const snowflakes = document.querySelector("#js-holiday-snowflakes");
    const snowflake = snowflakes.children[0];
    let maxFlakes = 10;
    if (window.matchMedia("(max-width: 575px)").matches) {
        maxFlakes = 3;
    } else if (window.matchMedia("(max-width:991px)").matches) {
        maxFlakes = 5;
    }
    maxFlakes += Math.round(Math.random() * 4);
    img.src =
        "data:image/png;base64,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";
    img.width = 22;
    snowflake.style.left = "1%";
    snowflake.style.animationDelay = `${Math.random() * 10}s, ${Math.random() * 3}s`;
    snowflake.appendChild(img);

    for (let i = 0; i < maxFlakes - 1; i += 1) {
        const clone = snowflake.cloneNode(true);

        clone.style.left = `${((i + 1) * 100) / maxFlakes}%`;
        clone.style.animationDelay = `${Math.random() * 10}s, ${Math.random() * 3}s`;
        clone.children[0].width = 14 + Math.round(Math.random() * 10);
        snowflakes.appendChild(clone);
    }
};

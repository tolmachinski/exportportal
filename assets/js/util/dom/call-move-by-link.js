const callMoveByLink = btn => {
    const link = btn.data("link");
    const target = btn.data("target");

    if (link !== undefined) {
        if (target !== undefined) {
            window.open(link, target);
        } else {
            window.location.href = link;
        }
    }
};

export default callMoveByLink;

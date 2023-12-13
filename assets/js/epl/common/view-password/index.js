const viewPassword = btn => {
    const input = btn.siblings("input");

    if (input.prop("type") === "text") {
        input.prop("type", "password");
    } else {
        input.prop("type", "text");
    }

    btn.find(".ep-icon").toggleClass("ep-icon_visible ep-icon_invisible");
};

export default viewPassword;

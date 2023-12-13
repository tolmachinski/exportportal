import $ from "jquery";

const toggleMinMax = button => {
    const target = $(`#${button.data("target")} [data-minMax]`);
    target.toggleClass("display-n_i");
    button.text(
        button.text() === button.data("text") ? button.data("textToggled") : button.data("text")
    );
}

export { toggleMinMax }

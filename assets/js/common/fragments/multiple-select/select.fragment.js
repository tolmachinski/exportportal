import $ from "jquery";

import { addMultipleSelect } from "@src/plugins/multiple-select/index";

export default (selector, options = {}) => {
    addMultipleSelect($(selector), options);
};

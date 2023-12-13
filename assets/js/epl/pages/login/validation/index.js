import initJqueryValidation from "@src/plugins/jquery-validation/lazy";
import { login } from "@src/epl/pages/login/callbacks/index";
import { EMAIL, PASSWORD } from "@src/plugins/jquery-validation/rules";

const validateForm = formSelector => {
    const validationOptions = {
        rules: {
            email: EMAIL,
            password: PASSWORD,
        },
    };

    initJqueryValidation(formSelector, login.bind(null, formSelector), validationOptions);
};

export default formSelector => {
    validateForm(formSelector);
};

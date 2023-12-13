import { registerFragment } from "@src/fragments";

const fragments = [
    ["epl-authorization:resend-confirmation-email", () => import("@src/epl/pages/register/fragments/resend-email/index")],
    ["epl-contact-us:form-fragment", () => import("@src/epl/common/contact-us/index")],
    ["epl-footer:scroll-up", () => import("@src/epl/common/scroll-up/index")],
    ["epl-dashboard:menu", () => import("@src/epl/components/dashboard/fragments/nav-header-menu")],
    ["epl-forgot:init-validation", () => import("@src/epl/pages/forgot_password/validation/index")],
    ["epl-login:init-validation", () => import("@src/epl/pages/login/validation/index")],
    ["epl-register:register_steps", () => import("@src/epl/pages/register/fragments/register-steps/index")],
    ["epl-reset-pswd:init-validation", () => import("@src/epl/pages/reset_password/validation/index")],
];

fragments.forEach(([name, importCallback]) => registerFragment(name, importCallback));

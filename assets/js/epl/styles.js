export default () => {
    // @ts-ignore
    import(/* webpackChunkName: "epl_styles_old_dashboard" */ "@scss/epl/old-dashboard-styles.scss");
    // @ts-ignore
    import(/* webpackChunkName: "epl_styles_general_critical" */ "@scss/epl/general-critical.scss");
    // @ts-ignore
    import(/* webpackChunkName: "epl_styles_general_logged_user_critical" */ "@scss/epl/general-critical-logged.scss");
    // @ts-ignore
    import(/* webpackChunkName: "epl_critical_styles_home" */ "@scss/epl/pages/home/speed.scss");
    // @ts-ignore
    import(/* webpackChunkName: "epl_critical_styles_forgot_password" */ "@scss/epl/pages/forgot_password/speed.scss");
    // @ts-ignore
    import(/* webpackChunkName: "epl_critical_styles_register" */ "@scss/epl/pages/register/speed.scss");
    // @ts-ignore
    import(/* webpackChunkName: "epl_critical_styles_resources" */ "@scss/epl/pages/resources/speed.scss");
};

/* eslint-disable no-underscore-dangle */
// @ts-nocheck
/* eslint-disable no-undef */
// ENV
export const IS_RECAPTCHA_ENABLE = ENV_RECAPTCHA_ENABLED;
export const RECAPTCHA_TOKEN = ENV_RECAPTCHA_TOKEN;
export const MATRIX_WEB_CLIENT_HOST = ENV_MATRIX_WEB_CLIENT_HOST;
export const BACKSTOP_TEST_MODE = ENV_BACKSTOP_TEST_MODE && new URL(globalThis.location.href).searchParams.has("backstop");
export const DISABLE_POPUP_SYSTEM = ENV_DISABLE_POPUP_SYSTEM;

// GLOBAL
export const DEBUG = globalThis.__debug_mode;
export const LANG = globalThis.__site_lang;
export const SITE_URL = globalThis.__site_url;
export const GROUP_SITE_URL = globalThis.__group_site_url;
export const SUBDOMAIN_URL = globalThis.__current_sub_domain_url;
export const LOGGED_IN = globalThis.__logged_in;
export const SHIPPER_URL = globalThis.__shipper_url;
export const SHIPPER_PAGE = globalThis.__shipper_page;
export const BLOG_URL = globalThis.__blog_url;
export const COOKIE_DOMAIN = globalThis.__js_cookie_domain;

// AUTOCOMPLETE
export const AUTOCOMPLETE_LOCAL_STORAGE_KEY = "EP_sch.ms";
export const AUTOCOMPLETE_ENTRIES_SHOWN_AMOUNT = 5;
export const AUTOCOMPLETE_ENTRIES_MAX_AMOUNT = 5;

// events
export const NUMBER_OF_NOTIFICATIONS = 6;

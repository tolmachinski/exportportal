export const USER_NAME = {
    required: true,
    minSize: 2,
    maxSize: 50,
    validUserName: true,
};

export const COUNTRY_CODE = {
    required: true,
};

export const PHONE = {
    required: true,
    selectPhoneMask: true,
    completePhoneMask: true,
};

export const PASSWORD = {
    required: true,
};

export const PASSWORD_CONFIRM = {
    required: true,
    equalTo: "#js-password",
};

export const EMAIL = {
    required: true,
    emailWithWhitespaces: true,
    noWhitespaces: true,
    maxSize: 100,
};

export const EMAIL_SUBJECT = {
    required: true,
    maxSize: 100,
};

export const EMAIL_CONTENT = {
    required: true,
    maxSize: 500,
};

export const COMPANY_NAME = {
    required: true,
    minSize: 3,
    maxSize: 50,
    companyTitle: true,
};

export const COMPANY_OFFICES_NUMBER = {
    required: true,
    naturalNumber: true,
    max: 999999,
};

export const COMPANY_TEU = {
    required: true,
    naturalNumber: true,
    max: 9999999999,
};

export const COUNTRY = {
    required: true,
};

export const STATE = {
    required: true,
};

export const CITY = {
    required: true,
};

export const LOCATION = {
    required: true,
    minSize: 2,
    maxSize: 300,
};

export const ADDRESS = {
    required: true,
    minSize: 3,
    maxSize: 255,
};

export const ZIP_CODE = {
    required: true,
    zipCode: true,
    maxSize: 20,
};

export const CHECKBOX = {
    required: true,
};

export const KEYWORDS = {
    required: true,
    minSize: 3,
    maxSize: 50,
};

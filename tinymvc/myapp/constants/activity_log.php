<?php

namespace App\Logger\Activity\ResourceTypes;

const COMPANY = 1;
const ITEM = 2;
const USER = 3;
const PERSONAL_DOCUMENT = 4;
const UPGRADE_REQUEST = 5;
const BILLING = 6;
const SHIPPER_COMPANY = 7;
const BUYER_COMPANY = 8;

namespace App\Logger\Activity\OperationTypes;

const ADD = 4;
const ADD_IMAGE = 6;
const ADD_LOGO = 8;
const DELETE = 9;
const DELETE_IMAGE = 7;
const DELETE_LOGO = 3;
const EDIT = 1;
const REGISTRATION = 11;
const REGISTRATION_NO_PROFILE = 12;
const SET_MAIN_IMAGE = 10;
const UPLOAD_DOCUMENT = 13;
const DECLINE_DOCUMENT = 14;
const ADMIN_UPLOAD_DOCUMENT = 15;
const START_UPGRADE_REQUEST = 16;
const CANCEL_UPGRADE_REQUEST = 17;
const CONFIRM_UPGRADE_REQUEST = 18;
const CONFIRM_BILLING = 19;
const ADMIN_CONFIRM_DOCUMENT = 20;
const ADMIN_VERIFICATION_USER = 21;
const BILLING_PAYMENT = 22;
const ADMIN_EDIT = 23;

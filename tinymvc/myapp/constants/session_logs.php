<?php

namespace App\Session_logs\Types;

const LOGGED_IN = 'logged_in';
const LOGGED_IN_COOKIE = 'logged_in_cookie';
const LOGGED_OUT = 'logged_out';
const LOGGED_OUT_CRON = 'logged_out_cron';
const START_EXPLORE_BY_ADMIN = 'start_explore_by_admin';
const END_EXPLORE_BY_ADMIN = 'end_explore_by_admin';

namespace App\Session_logs\Messages;

const LOGGED_IN = 'The user logged in.';
const LOGGED_IN_COOKIE = 'The user logged in from cookies information.';
const LOGGED_OUT = 'The user logged out.';
const LOGGED_OUT_CRON = 'The user has been logged out by system because of inactivity more than 15 min.';
const START_EXPLORE_BY_ADMIN = 'The admin [ADMIN_DATA] logged in as user';
const END_EXPLORE_BY_ADMIN = 'The admin [ADMIN_DATA] logout of user';

<?php

namespace App\Moderation\Types;

const TYPE_B2B = 'b2b';
const TYPE_ITEM = 'items';
const TYPE_COMPANY = 'companies';
const TYPE_B2B_NAME = 'B2B request';
const TYPE_ITEM_NAME = 'item';
const TYPE_COMPANY_NAME = 'company';
const TYPE_B2B_NAME_CAPITALIZED = 'B2B Request';
const TYPE_ITEM_NAME_CAPITALIZED = 'Item';
const TYPE_COMPANY_NAME_CAPITALIZED = 'Company';

namespace App\Moderation\Messages;

const MESSAGE_IMMODERATE = 'The resource was modified by {editor.fullname}';
const MESSAGE_MODERATE = 'The resource was moderated by {moderator.fullname}';
const MESSAGE_UNBLOCK = 'The resource was unblocked by {moderator.fullname}';
const MESSAGE_NOTICE = 'The notice about abuse ("{reason}") was sent by {moderator.fullname}';
const MESSAGE_BLOCK = 'The resource was blocked by {moderator.fullname} due to reason: "{reason}"';

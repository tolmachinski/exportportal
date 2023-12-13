# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).
Each version should:
- List its release date in the above format.
- Group changes to describe their impact on the project, as follows:
    - Added for new features.
    - Changed for changes in existing functionality.
    - Deprecated for once-stable features removed in upcoming releases.
    - Removed for deprecated features removed in this release.
    - Fixed for any bug fixes.
    - Security to invite users to upgrade in case of vulnerabilities.

## [v2.12.0]
## 2020-07-29
**Tasks**
1. [Maintenance refactoring;](https://trello.com/c/dvec6tly/303-maintenance-refactoring)
2. [Do not hide public pages for users with uncompleted profile](https://trello.com/c/8spIOtm0/286-do-not-hide-public-pages-for-users-with-uncompleted-profile)
3. [Text change on Seller fees page;](https://trello.com/c/rKoeiYRB/306-text-change-on-seller-fees-page)
4. [Replace upload library: Part 2 - Payment & Order's dispute](https://trello.com/c/1p6fOhni/272-replace-upload-library-part-2-create-new-upload-lib)
5. [Add item. Description section improvement;](https://trello.com/c/Um4rrlbt/261-add-item-description-section-improvement)
6. [Reusing uploaded document for other linked accounts;](https://trello.com/c/Y0SoRlWW/287-reusing-uploaded-document-for-other-linked-accounts)
7. [Bugs fixing [Redmine]](https://trello.com/c/KC6kHmnA/307-bugs-fixing-redmine)
8. [Zoho CRM - sync Contacts and create Custom views;](https://trello.com/c/rCni0voj/314-zoho-crm-sync-contacts-and-create-custom-views)

**Merge Request `!314`**

**index.php**
`On DEV`

```
if(
    $_ENV['MAINTENANCE_MODE'] === 'on' &&
    new DateTime("now", new DateTimeZone('UTC')) < new DateTime($_ENV['MAINTENANCE_END'], new DateTimeZone('UTC')) &&
    new DateTime("now", new DateTimeZone('UTC')) > new DateTime($_ENV['MAINTENANCE_START'], new DateTimeZone('UTC'))
){
    if(
        isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '89.28.49.94'
        || !isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['REMOTE_ADDR'] != '89.28.49.94'
    ){
        require __DIR__ . '/maintenance/index.php';
        exit();
    }
}
```

`On PROD`

```
if(
    $_ENV['MAINTENANCE_MODE'] === 'on' &&
    new DateTime("now", new DateTimeZone('UTC')) < new DateTime($_ENV['MAINTENANCE_END'], new DateTimeZone('UTC')) &&
    new DateTime("now", new DateTimeZone('UTC')) > new DateTime($_ENV['MAINTENANCE_START'], new DateTimeZone('UTC'))
){
    if(
        isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !preg_match('/^192\.168\.1\.[\d]{1,3}/', $_SERVER['HTTP_X_FORWARDED_FOR'])
        || !isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    ){
        require __DIR__ . '/maintenance/index.php';
        exit();
    }
}
```

**ENV**

```
###> Maintenance ###
MAINTENANCE_MODE=off ### can be on or off
MAINTENANCE_START='2020-07-14T11:26:00+00:00'
MAINTENANCE_END='2020-07-14T11:27:00+00:00'
###< Maintenance  ###
```

**Database**

`Update after PROD UPDATE`

- users_personal_documents:
    - add column `id_principal` (INT,  UNSIGNED)
    - date_latest_version_expires EXPRESSION:
        - if((json_unquote(json_extract(\`latest_version\`,'$.expirationDate')) <> 'null'),cast(json_unquote(json_extract(\`latest_version\`,'$.expirationDate')) as datetime),NULL)
    - date_latest_version_created EXPRESSION:
        - if((json_unquote(json_extract(\`latest_version\`,'$.creationDate')) <> 'null'),cast(json_unquote(json_extract(\`latest_version\`,'$.creationDate')) as datetime),NULL)
    - date_original_version_created EXPRESSION:
        - if((json_unquote(json_extract(\`latest_version\`,'$.originalCreationDate')) <> 'null'),cast(json_unquote(json_extract(\`latest_version\`,'$.originalCreationDate')) as datetime),NULL)

`Run SQL queries`

```
UPDATE users_personal_documents
INNER JOIN users
    ON users_personal_documents.id_user = users.idu
SET users_personal_documents.id_principal = IF(users.id_principal, users.id_principal, 0)
```

**Sitemap**
Must be updated: `YES`

**Composer**
Must be updated: `YES`

**Elasticsearch**
Must be updated: `YES`

**Configs**
Must be updated: `YES`

**JS**
Must be updated: `YES`

**i18n**
Must be updated: `YES`

## [v2.9.0]
## 2020-04-07
### Added
- `NEW` new records in **.env**
    ```
    ###> images optimization ###
    LIMIT_IMAGES_PER_OPERATION=10
    ###< images optimization ###
    ```
    ```
    ###> recaptcha  ###
    RECAPTCHA_PUBLIC_TOKEN_REGISTER="6LcMh-UUAAAAAI6Rhfur3KqIwP8p7RUFmmiKTTK7"
    RECAPTCHA_PRIVATE_TOKEN_REGISTER="6LcMh-UUAAAAAGDxEnW8yJqxnOrAO6cMkxYXTgVG"
    ###< recaptcha  ###

    APP_ENCRYPTION_AUTH_HASH_KEY="secret_login_auth_key"
    ```
    ```
    ###> SMTP Gmail account ###
    SMTP_GMAIL_ACCOUNT_EMAIL="dev.ep.noreply@gmail.com"
    SMTP_GMAIL_ACCOUNT_PASSWORD="\56v\%hmGy]!SkXM"
    SMTP_GMAIL_HOST="smtp.gmail.com"
    SMTP_GMAIL_PORT=465 ### use 587 for TLS or 465 for SSL
    SMTP_GMAIL_LIMIT_MESSAGES_PER_DAY=99
    ###< SMTP Gmail account ###
    ```

## [v2.8.0]
## 2020-04-07
### Added
- `NEW` new records in **.env**
    ```
    ###> elasticsearch ###
    ELASTIC_SEARCH_INDEX="ep"
    ###< elasticsearch ###
    ```

## [v2.7.0]
## 2020-03-26
### Added
- `NEW` new records in **.gitignore**

    ```
    /public/trade_news/*
    !/public/trade_news/.clean
    !/public/trade_news/.gitkeep
    ```

## [v2.5.5]
## 2020-03-13
### Added
- `NEW` new records in **.gitignore**

    ```
    /docs/api/*
    !/docs/api/resource/
    ```

- `UPDATE` **.gitignore** file

    ```
    /public/newsletter_archive/*
    !/public/newsletter_archive/.clean
    !/public/newsletter_archive/.gitkeep
    ```

- `NEW` record in **.env**

    ```
    ###> emailchecker api ###
    EMAILCHECKER_API_KEY="5F3A39C00B4FE1A5"
    EMAILCHECKER_API_MAX_TRIES=3
    EMAILCHECKER_API_TIMEOUT_SEC=10
    EMAILCHECKER_FAILURE_LOG_EMAIL=xxx
    ###< emailchecker api ###
    ```

- `UPDATE` record in **.env**

    ```
    EP_DOCS_INTEGRATION_DATE="2020-03-11"
    ```

- `UPDATE` **.htaccess** record

    - Add "(.+\\.md)" after "package.\*" on line 640

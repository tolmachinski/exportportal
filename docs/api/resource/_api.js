/**
 * @apiDefine InvalidAuth - Invalid Auth
 * @apiVersion 1.0.0
 *
 * @apiError InvalidAuth The request requires an user authentication
 *
 * @apiErrorExample InvalidAuth:
 *      {
 *          "error": "The request requires an user authentication",
 *          "status": 401
 *      }
 *
 */

/**
 * @apiDefine ServerError - Cannot return data
 * @apiVersion 1.0.0
 *
 * @apiError ServerError Cannot return data
 *
 * @apiErrorExample ServerError:
 *      {
 *          "error": "Server error, cannot get the data",
 *          "status": 404
 *      }
 *
 */

/**
 * @apiDefine OutputNoResults - No data to return
 * @apiVersion 1.0.0
 *
 * @apiError (Error 200) OutputNoResults No data on the specified parameters.
 *
 * @apiErrorExample OutputNoResults:
 *      {
 *          "error": "No data on the specified parameters.",
 *          "status": 200
 *      }
 *
 */




/**
 * @api {get} /api_partners/get_categories Items Categories
 * @apiName GetCategories
 * @apiVersion 1.0.0
 * @apiGroup EP_API
 *
 * @apiHeader (Header) {String} token Authorization value.
 * @apiParam {Number} [start] Offset  - which row to start from.
 * @apiParam {Number} [limit] Limit  - the number of rows (default = 10).
 *
 * @apiSuccess {Number} total Total number of categories.
 * @apiSuccess {Number} category_id Id of the category.
 * @apiSuccess {String} name Title of the category.
 * @apiSuccess {String} title Title of the category on our website.
 * @apiSuccess {String} link Link of the category.
 * @apiSuccess {String} translations_data The languages it is translated i.
 *
 * @apiExample Example CURL:
 *   curl --location --request POST '{{api_host_url}}/api_partners/get_categories?limit=2' --header 'token: Your_TOKEN_Here'
 *
 * @apiSuccessExample Success-Response for /api_partners/get_categories?limit=2:
 *     {
 *      "total": 65,
 *      "data": [
 *          {
 *               "category_id": "153",
 *               "name": "Apparel Articles and Accessories",
 *               "link": "apparel-articles-and-accessories",
 *               "title": "Apparel Articles and Accessories [LOCATION] | Export Portal",
 *               "translations_data": "{\"en\": {\"abbr_iso2\": \"en\", \"lang_name\": \"English\"}}"
 *           },
 *           {
 *               "category_id": "202",
 *               "name": "Shoes and Accessories",
 *               "link": "shoes-and-accessories",
 *               "title": "Shoes and Accessories [LOCATION] - Footwear on Export Portal",
 *               "translations_data": "{\"en\": {\"abbr_iso2\": \"en\", \"lang_name\": \"English\"}}"
 *           }
 *       ]
 *   }
 *
 *  @apiUse OutputNoResults
 *  @apiUse ServerError
 *  @apiUse InvalidAuth
 */




/**
 * @api {get} /api_partners/get_sellers Sellers
 * @apiName GetSellers
 * @apiVersion 1.0.0
 * @apiGroup EP_API
 *
 * @apiHeader (Header) {String} token Authorization value.
 * @apiParam {Number} [start] Offset  - which row to start from.
 * @apiParam {Number} [limit] Limit  - the number of rows (default = 10).
 * @apiParam {Number} [id_country] Country id  - the id of the country.
 *
 * @apiSuccess {Number} total Total number of sellers.
 * @apiSuccess {Number} id_company Id of the company
 * @apiSuccess {Number} id_user Id of the user
 * @apiSuccess {String} email_company Email of the company
 * @apiSuccess {String} name_company Name of the company
 * @apiSuccess {String} user_name Full name of the user
 * @apiSuccess {Number} phone_company Phone number of the company
 * @apiSuccess {Number} phone_code_company Phone prefix - code of the number above
 * @apiSuccess {Number} rating_count_company Rating count of the company
 * @apiSuccess {Number} rating_company Rating of the company
 * @apiSuccess {Number} id_country Country id of the company
 * @apiSuccess {String} country Name of the country
 * @apiSuccess {Number} id_city City id of the company
 * @apiSuccess {String} address_company Address of the company
 * @apiSuccess {String} company_industries Ids of the industries of the company
 * @apiSuccess {String} user_group_name Group of the company
 * @apiSuccess {String} user_link Link of the user on EP
 * @apiSuccess {String} company_link Link of the company on EP
 * @apiSuccess {String} photo_company_full_path URL of the logo
 *
 * @apiExample Example CURL:
 *    curl --location --request POST '{{api_host_url}}/api_partners/get_sellers?limit=2&id_country=139' --header 'token: Your_TOKEN_Here'
 *
 * @apiSuccessExample Success-Response for /api_partners/get_sellers?limit=2&id_country=139:
 *     {
 *      "total": 31,
 *      "data": [
 *      {
 *          "phone_company": "68922345",
 *          "country": "Moldova",
 *          "rating_count_company": "0",
 *          "phone_code_company": "+373",
 *          "logo_company": "5e426479d56ce.jpg",
 *          "user_name": "Alexandr Usinevici",
 *          "id_company": "405",
 *          "id_user": "1875",
 *          "email_company": "ua_seller@exportportal.com",
 *          "address_company": "str. Burebista 3",
 *          "name_company": "Tru-lea-lea &amp; Tro-lo-lo",
 *          "id_country": "139",
 *          "id_city": "1889396",
 *          "rating_company": "0",
 *          "company_industries": "389,701,1160",
 *          "user_group_name": "Certified Seller",
 *          "user_link": "{{api_host_url}}/usr/alexandr-usinevici-1875",
 *          "photo_company_full_path": "{{api_host_url}}/public/img/no_image/group/noimage-other.svg",
 *          "company_link": "{{api_host_url}}/trulealea123"
 *      },
 *      {
 *          "phone_company": "123123",
 *          "country": "Moldova",
 *          "rating_count_company": "0",
 *          "phone_code_company": "+373",
 *          "logo_company": "5e0f58b5e1777.jpg",
 *          "user_name": "Tanya Seller",
 *          "id_company": "365",
 *          "id_user": "1797",
 *          "email_company": "guriev.tatiana@mail.ru",
 *          "address_company": "bulgara 11111",
 *          "name_company": "EcoTanyaShop",
 *          "id_country": "139",
 *          "id_city": "1889396",
 *          "rating_company": "0",
 *          "company_industries": "112,203",
 *          "user_group_name": "Verified Seller",
 *          "user_link": "{{api_host_url}}/usr/tanya-seller-1797",
 *          "image": "{{api_host_url}}/public/img/company/365/logo/5e0f58b5e1777.jpg",
 *          "photo_company_full_path": "{{api_host_url}}/public/img/no_image/group/noimage-other.svg",
 *          "company_link": "{{api_host_url}}/seller/ecotanyashop-365"
 *      }
 *    ]
 *  }
 *
 * @apiUse OutputNoResults
 * @apiUse ServerError
 * @apiUse InvalidAuth
 */





 /**
 * @api {get} /api_partners/get_buyers Buyers
 * @apiName GetBuyers
 * @apiVersion 1.0.0
 * @apiGroup EP_API
 *
 * @apiHeader (Header) {String} token Authorization value.
 * @apiParam {Number} [start] Offset  - which row to start from.
 * @apiParam {Number} [limit] Limit  - the number of rows (default = 10).
 * @apiParam {Number} [id_countries] Country ids  - the ids of the countries you need separated by comma.
 *
 * @apiSuccess {Number} total Total number of buyers.
 * @apiSuccess {Number} idu  Id of the user
 * @apiSuccess {String} fname User first name
 * @apiSuccess {String} lname User last name
 * @apiSuccess {String} user_name User full name
 * @apiSuccess {String} email Email of the user
 * @apiSuccess {Number} phone Phone number of the company
 * @apiSuccess {Number} phone_code Phone prefix - code of the number above
 * @apiSuccess {Number} country Country id of the user
 * @apiSuccess {Number} city City id of the user
 * @apiSuccess {String} address Address of the user
 * @apiSuccess {String} user_country Name of the country
 * @apiSuccess {String} user_city Name of the user city
 * @apiSuccess {String} user_link Link of the user on EP
 * @apiSuccess {String} photo_full_path URL of the logo
 * @apiSuccess {String} user_photo Name of the photo
 *
 * @apiExample Example CURL:
 *   curl --location --request POST '{{api_host_url}}/api_partners/get_buyers?id_countries=20,136' --header 'token: Your_TOKEN_Here'
 *
 * @apiSuccessExample Success-Response for /api_partners/get_buyers?limit=1:
 *     {
 *      "total": 3,
 *      "data": [
 *      {
 *          "idu": "1794",
 *          "fname": "Tatiana",
 *          "lname": "Buyer",
 *          "email": "guriev.tatiana@gmail.com",
 *          "phone_code": "+33",
 *          "phone": "12123123",
 *          "country": "20",
 *          "city": "143748",
 *          "address": "123 Strett Burs",
 *          "user_name": "Tatiana Buyer",
 *          "user_country": "Belgium",
 *          "user_city": "Brussels, Brussels-Capital Region",
 *          "photo_full_path": "{{api_host_url}}/public/img/users/1794/5e469662833c4.jpg",
 *          "user_photo": "5e469662833c4.jpg",
 *          "user_link": "{{api_host_url}}/usr/tatiana-buyer-1794"
 *      }
 *    ]
 *  }
 *
 * @apiUse OutputNoResults
 * @apiUse InvalidAuth
 * @apiUse ServerError
 */


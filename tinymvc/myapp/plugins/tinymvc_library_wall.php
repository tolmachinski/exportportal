<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\DataProvider\B2bRequestProvider;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring: code style, optimize code
 */
class TinyMVC_Library_Wall
{
    /**
     * Holds controller's instance
     *
     * @var \TinyMVC_Controller
     */
    private $tmvc;

    /**
     * Data layer handler
     *
     * @var \Seller_Wall_Model
     */
    private $provider;

    /**
     * Data layer handler
     *
     * @var \App\DataProvider\B2bRequestProvider
     */
    private $b2bProvider;

    private $types = array(
        'certification' => 'certification_view',
        'b2b_request'   => 'b2b_view',
        'document'      => 'library_view',
        'banner'        => 'banner',
        'update'        => 'update_view',
        'video'         => 'video_view',
        'photo'         => 'photos_view',
        'news'          => 'news_view',
        'item'          => 'product_view',
    );

    /**
     * @param ContainerInterface $container The container instance
     */
	public function __construct(ContainerInterface $container)
    {
        $this->tmvc = $container->get(TinyMVC_Controller::class);
        $this->provider = $this->makeDataProvider();
        $this->b2bProvider = $container->get(B2bRequestProvider::class);
    }

    /**
     * Add new wall item
     * Example of calling:
     *
     * $this->load->library('Wall', 'wall');
     * $this->wall->add(array(
     *      'type' => 'item',
     *      'operation' => 'add|edit',
     *      'id_item' => 1
     * ));
     *
     *
     *
     * @param $params
     */
	public function add($params) {
        try {
            $this->addProcess($params);
        } catch (Exception $e) {
            // Or write error messages to a log file when implementing this as a background process

            return false;
        }
    }

    /**
     * Get the speciied amount of the items views
     *
     * @param int $user
     * @param array $context
     * @param int $take
     * @param int $skip
     * @param boolean $isMultiple
     *
     * @return string[]
     */
    public function getItemsViews($user, $context, $take, $skip, $isMultiple = false)
    {
        $items = $this->provider->get_items(array(
            'id_sellers' => (int) $user,
            'offset'     => (int) $skip,
            'limit'      => (int) $take,
        ));

        $output = array();
        $itemContext = $context;

        foreach ($items as $item) {
            if(!$this->isValidType($item['type'])) {
                // throw new \RuntimeException('Unknown wall item type');
                continue;
            }

            if ($isMultiple) {
                $sellerId = (int) $item['id_seller'];
                if(!isset($itemContext[$sellerId])) {
                    continue;
                }

                $itemContext = $itemContext[$sellerId];
                $itemContext['base_company_url'] = getCompanyURL($itemContext['company']);
            }

            $itemContext['base_company_url'] = getCompanyURL($itemContext['company']);
            $itemContext['data'] = json_decode($item['data'], true);
            $itemContext['wall_item'] = $item;
            $itemContext['is_link_user'] = $item;

            $output[] = $this->tmvc->view->fetch("new/user/seller/wall/{$this->getTypeFileName($item['type'])}", $itemContext);
        }

        return $output;
    }

    /**
     * Check if items beyond provied limits are exists
     *
     * @param int $user
     * @param int $take
     * @param int $from
     *
     * @return boolean
     */
    public function hasItemsBeyond($user, $take, $from)
    {
        $items = $this->provider->get_items(array(
            'id_sellers' => (int) $user,
            'offset'     => (int) $from + (int) $take,
            'limit'      => 1,
        ));

        return !empty($items);
    }

    /**
     * Indicates if type of the item is valid
     *
     * @param string $type
     *
     * @return boolean
     */
    public function isValidType($type)
    {
        return isset($this->types[$type]);
    }

    /**
     * Returns the file name for known type of the item
     *
     * @param string $type
     *
     * @return null|string
     */
    public function getTypeFileName($type)
    {
        return isset($this->types[$type]) ? $this->types[$type] : null;
    }

    /**
     * The process of adding the wall item
     * @param $params
     * @return bool
     * @throws Exception
     */
    private function addProcess($params) {
        if (empty($params['type'])) {
            throw new Exception('Please provide the type');
        }

        if (!empty($params['operation']) && !in_array($params['operation'], ['add', 'edit'])) {
            throw new Exception('Please provide the operation type (add or edit)');
        }

        if (empty($params['id_seller'])) {
            $params['id_seller'] = privileged_user_id();
        }


        $this->tmvc->load->model('Seller_Wall_Model', 'seller_wall');

        switch ($params['type']) {
            case 'photo':
                $this->processPhoto($params);
                break;
            case 'item':
                $this->processItem($params);
                break;
            case 'video':
                $this->processVideo($params);
                break;
            case 'news':
                $this->processNews($params);
                break;
            case 'update':
                $this->processUpdate($params);
                break;
            case 'document':
                $this->processDocument($params);
                break;
            case 'b2b_request':
                $this->processB2bRequest($params);
                break;
            case 'banner':
                $this->processBanner($params);
                break;
            case 'certification':
                $this->processCertification($params);
                break;
            default:
                throw new Exception('Unknown wall item type: ' . $params['type']);
                break;
        }

        return true;
    }


    /**
     * Create the directory where we keep all wall item files
     * @param $sellerId
     * @param $wallId
     * @return string
     */
    private function createWallDirectory($sellerId, $wallId) {
        $target_path = "public/wall/$sellerId/$wallId";
        if (!is_dir($target_path)) {
            create_dir($target_path);
        }

        return $target_path;
    }


    /**
     * @param $params
     * @throws Exception
     */
    private function processItem($params) {
        $this->tmvc->load->model('Items_Model', 'items');
        $item = $this->tmvc->items->get_items(array(
            'list_item' => $params['id_item'],
            'main_photo' => true,
            'item_columns' => 'it.id, it.title, it.price, it.discount, it.final_price'
        ));

        if (empty($item)) {
            throw new Exception('Item not found');
        }

        $item = $item[0];

        /** @var Items_Variants_Properties_Model $itemsVariantsPropertiesModel */
        $itemsVariantsPropertiesModel = model(Items_Variants_Properties_Model::class);

        /** @var Items_Variants_Model $itemsVariantsModel */
        $itemsVariantsModel = model(Items_Variants_Model::class);

        $itemPropertiesOptions = $itemsVariantsPropertiesModel->findAllBy([
            'conditions'    => [
                'itemId'    => (int) $item['id'],
            ],
            'with'  => [
                'propertyOptions',
            ],
        ]);

        $itemPropertiesOptions = $itemsVariantsModel->castVariantsDataToLegacyFormat($itemPropertiesOptions, [])['variant_groups'] ?: [];

        if (!empty($itemPropertiesOptions)) {
            $itemPropertiesOptions = array_map(
                function ($element) {
                    return ['name' => cleanOutput($element['group_name']),'variants' => $element['variants']];
                },
                $itemPropertiesOptions
            );
        }

        $item_photos = $this->tmvc->items->get_items_photos(array(
            'items_list' => $params['id_item'],
        ));

        $main_photo = null;
        $photos = array();
        foreach ($item_photos as $item_photo) {
            if ($item_photo['main_photo'] == 1) {
                $main_photo = $item_photo['photo_name'];
            } else {
                $photos[] = $item_photo['photo_name'];
            }
        }

        $photos_count = count($photos);
        $photos = array_slice($photos, 0, 3);

        $wall_id = $this->tmvc->seller_wall->insert(array(
            'id_seller' => $params['id_seller'],
            'id_item' => $params['id_item'],
            'type' => 'item',
            'operation' => $params['operation'],
            'data' => json_encode(array(
                'id_item' => $item['id'],
                'title' => $item['title'],
                'price' => $item['price'],
                'discount' => $item['discount'],
                'category_name' => $item['cat_name'],
                'final_price' => $item['final_price'],
                'variants' => $itemPropertiesOptions,
                'main_photo' => $main_photo,
                'photos_count' => $photos_count,
                'photos' => $photos
            )),
            'search_data' => $item['title'] . ' ' . $item['price'] . ' ' . $item['final_price'],
        ));

        $target_path = $this->createWallDirectory($params['id_seller'], $wall_id);

        $module_items = 'items.main';
        $main_photo_source = getImgSrc($module_items, 3, array('{ID}' => $params['id_item'], '{FILE_NAME}' => $main_photo));

        if (is_file($main_photo_source)) {
            copy($main_photo_source, "{$target_path}/thumb_3_{$main_photo}");
        }

        foreach ($photos as $photo) {
            $photo_source = getImgSrc($module_items, 1, array('{ID}' => $params['id_item'], '{FILE_NAME}' => $photo));
            if (!is_file($photo_source)) {
                continue;
            }

            copy($photo_source, "{$target_path}/thumb_1_{$photo}");
        }
    }


    /**
     * @param $params
     * @throws Exception
     */
    private function processPhoto($params) {
        $this->tmvc->load->model('Seller_Pictures_Model', 'seller_pictures');
        $photo = $this->tmvc->seller_pictures->get_picture($params['id_item']);

        if (empty($photo)) {
            throw new Exception('Seller photo not found');
        }

        $wall_id = $this->tmvc->seller_wall->insert(array(
            'id_seller' => $params['id_seller'],
            'id_item' => $params['id_item'],
            'type' => 'photo',
            'operation' => $params['operation'],
            'data' => json_encode(array(
                'id_photo'    => $photo['id_photo'],
                'id_company'  => $photo['id_company'],
                'id_category' => $photo['id_category'],
                'path_photo'  => $photo['path_photo'],
                'title'       => $photo['title_photo'],
            )),
            'search_data' => $photo['title_photo'],
        ));


        $target_path = $this->createWallDirectory($params['id_seller'], $wall_id);

        $source_photo = getImgSrc('companies.photos', 'original', array('{ID}' => $photo['id_company'], '{FILE_NAME}' => $photo['path_photo']));

        if (is_file($source_photo)) {
            copy($source_photo, "$target_path/{$photo['path_photo']}");
        }
    }


    /**
     * @param $params
     * @throws Exception
     */
    private function processVideo($params) {
        $this->tmvc->load->model('Seller_Videos_Model', 'seller_videos');
        $video = $this->tmvc->seller_videos->get_video($params['id_item']);

        if (empty($video)) {
            throw new Exception('Seller video not found');
        }


        $wall_id = $this->tmvc->seller_wall->insert(array(
            'id_seller' => $params['id_seller'],
            'id_item' => $params['id_item'],
            'type' => 'video',
            'operation' => $params['operation'],
            'data' => json_encode(array(
                'id_video'        => $video['id_video'],
                'id_seller'       => $params['id_seller'],
                'id_company'      => $video['id_company'],
                'title'           => $video['title_video'],
                'description'     => $video['description_video'],
                'image_video'     => $video['image_video'],
                'url_video'       => $video['url_video'],
                'short_url_video' => $video['short_url_video'],
                'source_video'    => $video['source_video'],
            )),
            'search_data' => $video['title_video'],
        ));


        $target_path = $this->createWallDirectory($params['id_seller'], $wall_id);

        $source_video_image = "public/storage/company/{$video['id_company']}/videos/{$video['image_video']}";
        if (is_file($source_video_image)) {
            copy($source_video_image, "$target_path/{$video['image_video']}");
        }
    }


    /**
     * @param $params
     * @throws Exception
     */
    private function processNews($params) {
        $this->tmvc->load->model('Seller_News_Model', 'seller_news');
        $article = $this->tmvc->seller_news->getNews($params['id_item']);

        if (empty($article)) {
            throw new Exception('Seller article not found');
        }


        $wall_id = $this->tmvc->seller_wall->insert(array(
            'id_seller' => $params['id_seller'],
            'id_item' => $params['id_item'],
            'type' => 'news',
            'operation' => $params['operation'],
            'data' => json_encode(array(
                'id_news' => $article['id_news'],
                'id_company' => $article['id_company'],
                'title' => $article['title_news'],
                'image' => $article['image_news'],
                'text' => strLimit(strip_tags($article['text_news']), 150),
            )),
            'search_data' => $article['title_news'] . ' ' . strip_tags($article['text_news']),
        ));


        $target_path = $this->createWallDirectory($params['id_seller'], $wall_id);

        $source_article_image = "public/img/company/{$article['id_company']}/news/{$article['image_news']}";
        if (is_file($source_article_image)) {
            copy($source_article_image, "$target_path/{$article['image_news']}");
        }
    }


    /**
     * @param $params
     * @throws Exception
     */
    private function processUpdate($params) {
        $this->tmvc->load->model('Seller_Updates_Model', 'seller_update');
        $update = $this->tmvc->seller_update->get_update($params['id_item']);

        if (empty($update)) {
            throw new Exception('Seller update not found');
        }


        $wall_id = $this->tmvc->seller_wall->insert(array(
            'id_seller' => $params['id_seller'],
            'id_item' => $params['id_item'],
            'type' => 'update',
            'operation' => $params['operation'],
            'data' => json_encode(array(
                'id_update' => $update['id_update'],
                'text' => $update['text_update'],
                'photo' => $update['photo_path']
            )),
            'search_data' => strip_tags($update['text_update']),
        ));

        $target_path = $this->createWallDirectory($params['id_seller'], $wall_id);

        $source_update_image = "public/img/company/{$update['id_company']}/updates/{$update['photo_path']}";
        $source_update_image_thumb = "public/img/company/{$update['id_company']}/updates/thumb_150xR_{$update['photo_path']}";
        if (is_file($source_update_image)) {
            copy($source_update_image, "$target_path/{$update['photo_path']}");
            copy($source_update_image_thumb, "$target_path/thumb_150xR_{$update['photo_path']}");
        }
    }

    /**
     * @param $params
     * @throws Exception
     */
    private function processDocument($params) {
        $this->tmvc->load->model('Seller_Library_Model', 'seller_library');
        $document = $this->tmvc->seller_library->get_document($params['id_item']);

        if (empty($document)) {
            throw new Exception('Document not found');
        }

        $wall_id = $this->tmvc->seller_wall->insert(array(
            'id_seller' => $params['id_seller'],
            'id_item' => $params['id_item'],
            'type' => 'document',
            'operation' => $params['operation'],
            'data' => json_encode(array(
                'id_file' => $document['id_file'],
                'title' => $document['title_file'],
                'description' => $document['description_file'],
                'file' => $document['path_file'],
                'extension' => $document['extension_file'],
            )),
            'search_data' => $document['title_file'] . ' ' . strip_tags($document['description_file']),
        ));

        $target_path = $this->createWallDirectory($params['id_seller'], $wall_id);

        $source_document_image = "public/img/company/{$document['id_company']}/library/{$document['path_file']}";
        if (is_file($source_document_image)) {
            copy($source_document_image, "$target_path/{$document['path_file']}");
        }
    }

    /**
     * @param $params
     * @throws Exception
     */
    private function processB2bRequest($params)
    {
        $request = $this->b2bProvider->getRequestWithRelationData($params['id_item']);
        if (empty($request)) {
            throw new Exception('B2B request not found');
        }
        if (null !== $request['countries']) {
            $request['countries'] = $request['countries']->toArray();
            $countryName = $request['countries'][0]['country'];
            $totalNrOfCountries = count($request['countries']);
        }

        $this->tmvc->seller_wall->insert([
            'id_seller' => $params['id_seller'],
            'id_item'   => $params['id_item'],
            'type'      => 'b2b_request',
            'operation' => $params['operation'],
            'data'      => json_encode([
                'id_request'          => $request['id_request'],
                'title'               => $request['b2b_title'],
                'message'             => $request['b2b_message'],
                'type_location'       => $request['type_location'],
                'country_name'        => $countryName,
                'total_countries'     => $totalNrOfCountries,
                'radius'              => $request['b2b_radius'],
            ]),
            'search_data' => $request['b2b_title'] . ' ' . $request['b2b_message'] . ' ' . $request['b2b_tags'],
        ]);

    }

    /**
     * @param $params
     */
    private function processCertification($params) {
        /** @var Seller_Wall_Model $sellerWallModel */
        $sellerWallModel = model(Seller_Wall_Model::class);

        $insert = [
            'id_seller' => $params['userId'],
            'type'      => $params['type'],
            'data'      => json_encode($params['data']),
        ];

        if (!empty($params['date'])) {
            $insert['date'] = $params['date'];
        }

        $sellerWallModel->insert($insert);
    }

    /**
     * @param $params
     * @throws Exception
     */
    private function processBanner($params) {
        $this->tmvc->load->model('Seller_Banners_Model', 'seller_banners');
        $banner = $this->tmvc->seller_banners->get_banner($params['id_item']);

        if (empty($banner)) {
            throw new Exception('Banner not found');
        }

        $wall_id = $this->tmvc->seller_wall->insert(array(
            'id_seller' => $params['id_seller'],
            'id_item' => $params['id_item'],
            'type' => 'banner',
            'operation' => $params['operation'],
            'data' => json_encode(array(
                'id_banner' => $banner['id'],
                'link' => $banner['link'],
                'image' => $banner['image'],
            )),
            'search_data' => $banner['link'],
        ));

        $target_path = $this->createWallDirectory($params['id_seller'], $wall_id);

        $source_banner_image = "{$this->tmvc->seller_banners->path_to_images}/{$banner['image']}";
        if (is_file($source_banner_image)) {
            copy($source_banner_image, "$target_path/{$banner['image']}");
        }
    }

    /**
     * Get the data provider
     *
     * @return \Seller_Wall_Model
     */
    private function makeDataProvider()
    {
        try {
            $providerAlias = 'seller_wall_model_instance_' . str_replace('-', '_', Uuid::uuid5(Uuid::NAMESPACE_DNS, 'Seller_Wall_Model')->toString());
        } catch (InvalidUuidStringException $exception) {
            $providerAlias = uniqid('seller_wall_model_instance_');
        }

        $this->tmvc->load->model('Seller_Wall_Model', $providerAlias);
        $model = isset($this->tmvc->{$providerAlias}) ? $this->tmvc->{$providerAlias} : null;
        $this->tmvc->{$providerAlias} = null;
        unset($this->tmvc->{$providerAlias});

        return $model;
    }

    public function remove($params) {
        try {
            $this->removeProcess($params);
        } catch (Exception $e) {
            // Or write error messages to a log file when implementing this as a background process

            return false;
        }
    }

    private function removeProcess($params) {
        if (empty($params['type'])) {
            throw new Exception('Please provide the type');
        }

        if (empty($params['id_item'])) {
            throw new Exception('Please provide the item id');
        }

        if (empty($params['id_seller'])) {
            $params['id_seller'] = privileged_user_id();
        }

        model('seller_wall')->update($params['id_item'], $params['type'], $params['id_seller'], array('is_removed' => 1), is_array($params['id_item']));

        return true;
    }
}

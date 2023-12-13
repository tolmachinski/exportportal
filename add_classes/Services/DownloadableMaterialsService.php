<?php

declare(strict_types=1);

namespace App\Services;

use Downloadable_Materials_Model;

final class DownloadableMaterialsService
{
    /**
     * The samples repository.
     *
     * @var Downloadable_Materials_Model
     */
    private $materialsModel;

    public function getTableContent(): array
    {
        $materialsModel = model(Downloadable_Materials_Model::class);

        $request = request();
        $limit = $request->request->getInt('iDisplayLength', 10);
        $skip = $request->request->getInt('iDisplayStart', 0);
        $page = $skip / $limit + 1;
        $with = array('author', 'resource');
        $joins = array();

        $conditions = dtConditions($_POST, [
            ['as' => 'id',             'key' => 'id',             'type' => 'intval'],
            ['as' => 'title',          'key' => 'title',          'type' => 'cleaninput|trim'],
            ['as' => 'content',        'key' => 'content',        'type' => 'cleaninput|trim'],
            ['as' => 'meta',           'key' => 'meta',           'type' => 'cleaninput|trim'],
            ['as' => 'images',         'key' => 'images',         'type' => 'cleaninput|trim'],
            ['as' => 'file',           'key' => 'file',           'type' => 'cleaninput|trim'],
            ['as' => 'covers',         'key' => 'covers',         'type' => 'cleaninput|trim']
        ]);

        $this->paginate(
            [
                'conditions' => $conditions,
                'order'      => "DESC",
            ],
            $per_page ?? $this->getPerPage(),
            $page ?? 1
        );

        return $response;
    }
}

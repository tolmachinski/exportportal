<?php

declare(strict_types=1);

namespace App\Common\Autocomplete;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Search_Autocomplete_Model;

final class LazyCollection extends AbstractLazyCollection
{
    /**
     * The user's reference.
     *
     * @var null|string
     */
    private $ref;

    /**
     * The records' repository.
     *
     * @var Search_Autocomplete_Model
     */
    private $repository;

    /**
     * Creates instance of collection.
     */
    public function __construct(Search_Autocomplete_Model $repository, ?string $ref = null)
    {
        $this->ref = $ref;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    protected function doInitialize(): void
    {
        $collection = new ArrayCollection();
        if (!empty($this->ref)) {
            $collection = (
                new ArrayCollection(
                    (array) $this->repository->findAllBy(array(
                        'columns'    => array('id', 'id_user AS user', 'user_ref as ref', 'type', 'text', 'token'),
                        'conditions' => array('user_ref' => $this->ref),
                        'order'      => array('date_hit' => 'DESC'),
                    ))
                )
            )->map(function (array $record) {
                $record['new'] = false;
                $record['type'] = (int) $record['type'] ?: null;
                $record['user'] = (int) $record['user'] ?: null;

                return $record;
            });
        }

        $this->collection = $collection;
    }
}

<?php

declare(strict_types=1);

namespace App\Common\Transformers;

use League\Fractal\TransformerAbstract;

class BlogListForBaiduTransformer extends TransformerAbstract
{
    public function transform(array $blog)
    {
        return [
            'id'          => $blog['id'],
            'title'       => $blog['title'],
            'author'      => 'user' == $blog['author_type'] ? trim("{$blog['fname']} {$blog['lname']}") : 'Export Portal',
            'link'        => getBlogUrl(['title' => $blog['title'], 'id' => $blog['id']]),
            'publish_on'  => getDateFormat($blog['publish_on'], 'Y-m-d', 'j M, Y'),
            'author_link' => 'user' == $blog['author_type'] ? __BLOG_URL . 'author/' . strForURL($blog['fname'] . ' ' . $blog['lname'] . ' ' . $blog['id_user']) : __BLOG_URL . 'author/export-portal',
            'photo'       => __IMG_URL . getImage('public/img/blogs/' . $blog['id'] . '/' . $blog['photo'], getLazyImage(776, 337, true)),
            //TODO change to storage url when there will be path generator for blog
        ];
    }
}

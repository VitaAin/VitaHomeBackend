<?php

namespace App\Transformer;

/**
 * Created by PhpStorm.
 * User: Vita
 * Date: 2017/11/27
 * Time: 16:47
 */
class ArticleLikesTransformer extends Transformer
{

    public function transform($item)
    {
        return [
            'id' => $item['id'],
            'name' => $item['name'],
            'avatar' => $item['avatar']
        ];
    }
}
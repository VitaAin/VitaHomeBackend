<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2017/11/28
 * Time: 10:50
 */

namespace App\Transformer;


class CommentsTransformer extends Transformer
{
    public function transform($item)
    {
        return [
            'body' => $item['body'],
            'created_at' => $item['created_at'],
            'commentable' => [
                'id' => $item['commentable']['id'],
                'title' => $item['commentable']['title'],
                'comments_count' => $item['commentable']['comments_count'],
                'likes_count' => $item['commentable']['comments_count'],
            ]
        ];
    }
}
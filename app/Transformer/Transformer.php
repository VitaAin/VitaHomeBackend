<?php

namespace App\Transformer;

/**
 * Created by PhpStorm.
 * User: Vita
 * Date: 2017/11/27
 * Time: 16:48
 */
abstract class Transformer
{
    public function transformCollection($items)
    {
        return array_map([$this, 'transform'], $items);
    }

    public abstract function transform($item);
}
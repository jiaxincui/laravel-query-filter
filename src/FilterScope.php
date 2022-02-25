<?php

namespace Jiaxincui\QueryFilter;

use Jiaxincui\QueryFilter\BaseFilter;
use Illuminate\Pipeline\Pipeline;

trait FilterScope
{
    public function scopeFilter($builder, BaseFilter ...$filters)
    {
        return (new Pipeline)->via('apply')->send($builder)->through($filters)->thenReturn();
    }
}

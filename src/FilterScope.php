<?php

namespace Jiaxincui\QueryFilter;

use Jiaxincui\QueryFilter\BaseFilter;

trait FilterScope
{

    public function scopeFilter($query, BaseFilter $filters)
    {
        return $filters->apply($query);
    }
}

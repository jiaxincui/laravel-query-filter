<?php

namespace Jiaxincui\QueryFilter;

use Jiaxincui\QueryFilter\Filter;

trait FilterScope
{
    public function scopeFilter($builder, Filter $filter)
    {
        return $filter->apply($builder);
    }
}

<?php

namespace Jiaxincui\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Jiaxincui\QueryFilter\Filter;

trait FilterScope
{
    /**
     * @param Builder $builder
     * @param Filter $filter
     * @return Builder
     */
    public function scopeFilter(Builder $builder, Filter $filter): Builder
    {
        return $filter->apply($builder);
    }
}

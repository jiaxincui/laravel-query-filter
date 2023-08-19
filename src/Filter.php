<?php

namespace Jiaxincui\QueryFilter;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    /**
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder): Builder;
}

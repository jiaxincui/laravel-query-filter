<?php

namespace Jiaxincui\QueryFilter;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    public function apply(Builder $builder);
}

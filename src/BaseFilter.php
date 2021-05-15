<?php

namespace Jiaxincui\QueryFilter;

use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{

    private static $filter;

    protected $builder;

    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        $query = static::$filter->getQuery();

        foreach ($query as $name => $value) {
            if ($value && method_exists($this, $name) && !isset((static::$filter)[$name])) {
                call_user_func_array([$this, $name], array_filter([$value]));
                (static::$filter)[$name] = $value;
            }
        }

        return $this->builder;
    }

    public static function resolveFilter($filter)
    {
        static::$filter = $filter;
    }
}

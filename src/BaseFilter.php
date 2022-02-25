<?php

namespace Jiaxincui\QueryFilter;

use Closure;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{

    private static $requestQuery;

    protected $builder;

    public function apply(Builder $builder, Closure $next)
    {
        $this->builder = $builder;

        $query = static::$requestQuery;

        foreach ($query as $name => $value) {
            if ($value && method_exists($this, $name)) {
                call_user_func_array([$this, $name], array_filter([$value]));
            }
        }

        return $next($this->builder);
    }

    public static function setQuery(array $requestQuery)
    {
        static::$requestQuery = $requestQuery;
    }
}

<?php

namespace Jiaxincui\QueryFilter;

use Closure;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter implements Filter
{
    private static array $requestQuery;

    /**
     * 禁止 request 调用的方法名
     * @var array|string[]
     */
    protected array $dontCallMethods = [
        'applyWhere',
        'parseWhere',
        'whereQuery',
        'orWhereQuery',
        'getFieldsQueryable',
        'getReleasable',
        'getSortable'
    ];

    protected Builder $builder;

    protected bool $trashed = false;

    /**
     * @var array
     */
    protected array $queryable;

    /**
     * @var array
     */
    protected array $releasable;

    /**
     * getFieldsQueryable
     *
     * @return array|string[]
     */
    abstract protected function getFieldsQueryable(): array;

    /**
     * getReleasable
     *
     * @return array|string[]
     */
    abstract protected function getReleasable(): array;

    /**
     * getSortable
     *
     * @return array|string[]
     */
    abstract protected function getSortable(): array;

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function addBaseScope(Builder $builder): Builder
    {
        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $this->addBaseScope($builder);

        $query = static::$requestQuery;

        foreach ($query as $name => $value) {
            if ($value && !in_array($name, $this->dontCallMethods) && method_exists($this, $name)) {
                call_user_func_array([$this, $name], array_filter([$value]));
            }
        }

        return $this->builder;
    }

    /**
     * @param array|string[] $requestQuery
     * @return void
     */
    public static function setQuery(array $requestQuery): void
    {
        static::$requestQuery = $requestQuery;
    }

    /**
     * @param string $trashed
     * @return void
     */
    public function trashed(string $trashed): void
    {
        if ($this->trashed) {
            if ($trashed === 'only') {
                $this->builder->onlyTrashed();
            }
            if ($trashed === 'with') {
                $this->builder->withTrashed();
            }
        }
    }

    /**
     * @param string $orderBy
     * @return void
     */
    public function orderBy(string $orderBy): void
    {
        if ($orderBy) {
            $arr = explode(',', $orderBy);
            if (in_array($by = $arr[0], $this->getSortable())) {
                $this->builder->orderBy($by, $arr[1] ?? 'asc');
            }
        }
    }

    /**
     * @param string $slice
     * @return void
     */
    public function slice(string $slice): void
    {
        if (count($arr = explode(',', $slice)) >= 2) {
            $offset = (int)($arr[0] ?? 0);
            $limit = (int)($arr[1] ?? 0);
            $this->builder->offset(max($offset, 0))->limit(max($limit, 0));
        }
    }

    /**
     * @param string $with
     * @return void
     */
    public function with(string $with): void
    {
        $this->releasable = $this->getReleasable();
        $with = explode(',', $with);
        $with = array_filter($with, function ($v) {
            return in_array($v, $this->releasable);
        });

        if (count($with) > 0) {
            $this->builder->with($with);
        }
    }

    /**
     * @param string|array|string[] $where
     * @return void
     */
    public function where(string|array $where): void
    {
        $this->queryable = $this->getFieldsQueryable();
        if (is_array($where)) {
            foreach ($where as $v) {
                $this->applyWhere($v);
            }
        }
        if (is_string($where)) {
            $this->applyWhere($where);
        }
    }

    /**
     * @param string $where
     * @return void
     */
    protected function applyWhere(string $where): void
    {
        $this->builder->where(function ($query) use ($where) {
            $parseWhere = $this->parseWhere($where);
            $first = true;
            foreach ($parseWhere as $or) {
                $relation = null;
                $relation_field = null;
                if (stripos($or[0], '.')) {
                    $explode = explode('.', $or[0]);
                    $relation_field = array_pop($explode);
                    $relation = implode('.', $explode);
                }
                if ($first) {
                    if (!is_null($relation)) {
                        $func = $this->whereQuery($relation_field, $or[1] ?? null, $or[2] ?? null);
                        $query->whereHas($relation, $func);
                        $first = false;
                    } else {
                        $func = $this->whereQuery($or[0], $or[1] ?? null, $or[2] ?? null);
                        $func($query);
                        $first = false;
                    }
                } else {
                    if (!is_null($relation)) {
                        $func = $this->whereQuery($relation_field, $or[1] ?? null, $or[2] ?? null);
                        $query->orWhereHas($relation, $func);
                    } else {
                        $func = $this->orWhereQuery($or[0], $or[1] ?? null, $or[2] ?? null);
                        $func($query);
                    }
                }
            }
        });
    }

    /**
     * @param string $data
     * @return array
     */
    protected function parseWhere(string $data): array
    {
        $result = [];
        foreach (explode(';', $data) as $v) {
            $item = explode(':', $v, 3);
            if (count($item) < 2 || !in_array($item[0], $this->queryable)) {
                continue;
            }
            if (count($item) === 2 && !in_array(strtolower($item[1]), ['null', 'notnull'])) {
                $result[] = [$item[0], '=', $item[1]];
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * @param string $field
     * @param string $separator
     * @param string|null $value
     * @return Closure
     */
    protected function whereQuery(string $field, string $separator, ?string $value): Closure
    {
        return function ($query) use ($field, $separator, $value) {
            switch (strtolower($separator)) {
                case 'in':
                    $query->whereIn($field, explode(',', $value));
                    break;
                case 'notin':
                    $query->whereNotIn($field, explode(',', $value));
                    break;
                case 'between':
                    $query->whereBetween($field, explode(',', $value, 2));
                    break;
                case 'notbetween':
                    $query->whereNotBetween($field, explode(',', $value, 2));
                    break;
                case 'null':
                    $query->whereNull($field);
                    break;
                case 'notnull':
                    $query->whereNotNull($field);
                    break;
                case 'like':
                    $query->where($field, 'like', "%{$value}%");
                    break;
                default:
                    $query->where($field, $separator, $value);
            }
        };
    }

    /**
     * @param string $field
     * @param string $separator
     * @param string $value
     * @return Closure
     */
    protected function orWhereQuery(string $field, string $separator, string $value): Closure
    {
        return function ($query) use ($field, $separator, $value) {
            switch (strtolower($separator)) {
                case 'in':
                    $query->orWhereIn($field, explode(',', $value));
                    break;
                case 'notin':
                    $query->orWhereNotIn($field, explode(',', $value));
                    break;
                case 'between':
                    $query->orWhereBetween($field, explode(',', $value, 2));
                    break;
                case 'notbetween':
                    $query->orWhereNotBetween($field, explode(',', $value, 2));
                    break;
                case 'null':
                    $query->orWhereNull($field);
                    break;
                case 'notnull':
                    $query->orWhereNotNull($field);
                    break;
                case 'like':
                    $query->orWhere($field, 'like', "%{$value}%");
                    break;
                default:
                    $query->orWhere($field, $separator, $value);
            }
        };
    }
}

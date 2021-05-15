<?php

namespace Jiaxincui\QueryFilter;

use Jiaxincui\QueryFilter\BaseFilter;

abstract class Filter extends BaseFilter
{
    /**
     * @var array
     */
    protected $queryable, $releasable;

    /**
     * getFieldsQueryable
     *
     * @return array
     */
    abstract protected function getFieldsQueryable();

    /**
     * getReleasable
     *
     * @return array
     */
    abstract protected function getReleasable();

    public function trashed($trashed)
    {
        if ($trashed === 'only') {
            $this->builder->onlyTrashed();
        }
        if ($trashed === 'with') {
            $this->builder->withTrashed();
        }
    }

    public function orderBy($orderBy)
    {
        $arr = explode(',', $orderBy);
        $by = $arr[0];
        $sort = $arr[1] ?? 'asc';
        $this->builder->orderBy($by, $sort);
    }

    public function slice($slice)
    {
        $arr = explode(',', $slice);
        $offset = (int)($arr[0] ?? 0);
        $limit = (int)($arr[1] ?? 0);
        $this->builder->offset($offset < 0 ? 0 : $offset)->limit($limit < 0 ? 0 : $limit);
    }

    public function with($with)
    {
        $this->releasable = $this->getReleasable();
        $with = explode(',', $with);
        $with = array_filter($with, function ($v) {
            return in_array($v, $this->releasable);
        });

        $this->builder->with($with);
    }

    public function where($where)
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

    protected function applyWhere($where)
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

    protected function parseWhere($data)
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

    protected function whereQuery($field, $separator, $value)
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

    protected function orWhereQuery($field, $separator, $value)
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

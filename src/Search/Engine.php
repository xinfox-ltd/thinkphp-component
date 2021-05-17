<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\ThinkPHP\Search;

use think\db\Query;
use think\helper\Str;

abstract class Engine implements EngineInterface
{
    protected int $page = 1;

    protected int $pageSize = 10;

    protected Query $query;

    protected array $sortAlias = [];

    protected array $fields = [];

    public function __construct(array $data)
    {
        if (isset($data['page'])) {
            $this->page = intval($data['page']);
        }

        $this->pageSize = $data['pageSize'] ?? $data['page_size'] ??= 1;

        if ($this->pageSize > 100) {
            throw new \InvalidArgumentException('pageSize 不能超过100');
        }

        unset($data['page'], $data['pageSize']);

        $this->query = $this->initialize();

        foreach ($data as $key => $val) {
            if (empty($val) && $val !== 0) {
                continue;
            }
            if ($key === 'sort') {
                $this->sort($val);
                continue;
            }
            $propertyName = 'screenBy' . Str::studly($key);
            if (method_exists($this, $propertyName)) {
                $this->$propertyName($val);
            }
        }
    }

    protected function sort(string $sort)
    {
        $arrSort = explode(',', $sort);
        foreach ($arrSort as $item) {
            [$field, $order] = explode('_', $item);
            if (!empty($this->sortAlias) && isset($this->sortAlias[$field])) {
                $field = $this->sortAlias[$field];
            }
            $this->query->order($field, strtolower($order));
        }
    }

    /**
     * @throws \think\db\exception\DbException
     */
    public function search(): \think\Paginator
    {
        return $this->query->field($this->fields)
            ->paginate($this->pageSize);
    }
}
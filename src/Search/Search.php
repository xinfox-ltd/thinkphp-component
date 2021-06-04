<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */

declare(strict_types=1);

namespace XinFox\ThinkPHP\Search;

use think\helper\Str;

abstract class Search implements SearchInterface
{
    private EngineInterface $engine;

    public function __construct(array $searchItems)
    {
        $this->engine = $this->createEngine($searchItems);
    }

    public function createEngine(array $searchItems): EngineInterface
    {
        $namespace = $this();
        $className = $namespace . '\\DefaultEngine';
        $client = app()->request->header('Client-ID');

        if ($client !== null) {
            $_className = $namespace . "\\" . Str::studly($client) . "Engine";
            if (class_exists($_className)) {
                $className = $_className;
            }
        }


        return new $className($searchItems);
    }

    /**
     * @param bool $paginate 是否分页
     * @return mixed
     */
    public function execute(bool $paginate = true)
    {
        return $this->engine->search($paginate);
    }
}

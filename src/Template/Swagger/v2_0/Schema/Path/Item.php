<?php

namespace JsonSchema\Template\Swagger\v2_0\Schema\Path;

use JsonSchema\AbstractSchema;
use JsonSchema\Template\Swagger;

class Item extends AbstractSchema
{
    /**
     * @var Item\Operation $connect
     */
    public $connect;

    /**
     * @var Item\Operation $connect
     */
    public $delete;

    /**
     * @var Item\Operation $connect
     */
    public $get;

    /**
     * @var Item\Operation $connect
     */
    public $head;

    /**
     * @var Item\Operation $connect
     */
    public $options;

    /**
     * @var Item\Operation $connect
     */
    public $patch;

    /**
     * @var Item\Operation $connect
     */
    public $post;

    /**
     * @var Item\Operation $connect
     */
    public $put;

    /**
     * @uniqueItems
     * @var Swagger\v2_0\Parameter\Body[]
     */
    public $parameters;
}

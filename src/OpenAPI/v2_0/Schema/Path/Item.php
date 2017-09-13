<?php

namespace Bildr\API\Json\v2_0\OpenAPI\Path;

use JsonSchema\AbstractSchema;

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
     * @var API\Json\v2_0\OpenAPI\AbstractParameter[]
     */
    public $parameters;
}

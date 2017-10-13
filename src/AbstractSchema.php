<?php

namespace JsonSchema;

use stdClass;

abstract class AbstractSchema implements SchemaInterface
{
    /**
     * @uniqueItems
     * @minItems 1
     * @var string[]|int[] $enum
     */
    public $enum;

    /**
     * @required
     * @uniqueItems
     * @minItems 1
     * @enum array|boolean|integer|number|null|object|string
     * @var string|string[] $type
     */
    public $type;

    /**
     * @patternProperties .* SchemaInterface
     * @var stdClass $definitions
     */
    public $definitions;

    /**
     * @var string $title
     */
    public $title;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var SchemaInterface $default
     */
    public $default;

    /**
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        if (defined("static::TYPE")) {
            $this->type = static::TYPE;
        }

        foreach ($annotations as $annotation) {
            $args = preg_split('/[\s]+/', $annotation, 2);

            $keyword = array_shift($args);
            switch ($keyword) {
                case "@enum":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->enum = explode("|", $args[0]);
                    break;

            }
        }
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        // FIXME We need to adjust for type. If array or string.

        // Trick to only return public properties from this scope.
        $schema = call_user_func("get_object_vars", $this);

        $schema = array_filter($schema, function ($property) {
            return $property !== null;
        });

        return $schema;
    }

    /**
     * @inheritdoc
     */
    public static function schemaSerialize()
    {
        /** @var SchemaInterface $schema */
        $schema = Factory::create(static::class);

        return $schema;
    }
}

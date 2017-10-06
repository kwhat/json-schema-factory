<?php

namespace JsonSchema;

use stdClass;

abstract class AbstractSchema implements SchemaInterface
{
    /**
     * @uniqueItems
     * @minItems 1
     * @var SchemaInterface[] $enum
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
     * @patternProperties .* AbstractSchema
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
     * @var mixed $default
     */
    public $default;

    /**
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
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
        if (defined(static::TYPE)) {
            $this->type = static::TYPE;
        }

        // Trick to only return public properties from this scope.
        $schema = call_user_func("get_object_vars", $this);

        /*
        $schema = array_filter($schema, function ($property) {
            return $property !== null;
        });
        */

        return $schema;
    }

    /**
     * @inheritdoc
     */
    public static function schemaSerialize()
    {
        return Factory::create(static::class);
    }
}

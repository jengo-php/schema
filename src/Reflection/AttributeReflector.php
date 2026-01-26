<?php

declare(strict_types=1);

namespace Jengo\Schema\Reflection;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

final class AttributeReflector
{
    public static function class(
        ReflectionClass $class,
        string $attribute,
    ): ?object {
        $attrs = $class->getAttributes($attribute);

        return $attrs ? $attrs[0]->newInstance() : null;
    }

    public static function property(
        ReflectionProperty $property,
        string $attribute,
    ): ?object {
        $attrs = $property->getAttributes($attribute);

        return $attrs ? $attrs[0]->newInstance() : null;
    }

    public static function method(
        ReflectionMethod $method,
        string $attribute,
    ): ?object {
        $attrs = $method->getAttributes($attribute);

        return $attrs ? $attrs[0]->newInstance() : null;
    }
}

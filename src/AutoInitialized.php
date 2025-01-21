<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Chronolog;

use Chronolog\Helper\StringHelper;
use ReflectionClass;
use RuntimeException;

/**
 * AutoInitialized
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 11:20:55
 */
class AutoInitialized
{
    use InitTrait;
    public function __construct(array $config = [])
    {
        $this->initialize($config);
    }

    public static function turnInto(mixed $input): mixed
    {
        $result = null;

        $class_name = null;
        $config = [];
        if (is_string($input)) {
            $class_name = $input;
        } elseif (is_array($input) && count($input) > 0) {
            $class_name = $input['class'] ?? null;
            $config = $input['config'] ?? array_diff_key($input, array_fill_keys(['class', 'config'], 'empty'));
        }

        if (!class_exists((string)$class_name)) {
            throw new RuntimeException(sprintf('%s: failed retrieving class name "%s" via mixed "%s"; class does not exist', __METHOD__, StringHelper::className($class_name, true), gettype($class_name)));
        }


        $reflection = new ReflectionClass($class_name);
        if (empty($config)) {
            return $reflection->newInstance();
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $parameters = $constructor->getParameters();

        if (count($parameters) === 1 && $parameters[0]->getType() && $parameters[0]->getType()->getName() === 'array') {
            // Constructor expects an array
            return $reflection->newInstance($config);
        }

        $args = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $config)) {
                $args[] = $config[$name];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                throw new RuntimeException(sprintf('Missing required parameter "%s" for "%s"', $name, $class_name));
            }
        }

        return $reflection->newInstanceArgs($args);
    }
}
/** End of AutoInitialized **/

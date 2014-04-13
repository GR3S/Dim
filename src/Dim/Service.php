<?php
/**
 * Dim - the PHP dependency injection manager.
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 *
 * @author    Dmitry Gres <dm.gres@gmail.com>
 * @copyright 2014 Dmitry Gres
 * @link      https://github.com/GR3S/Dim
 * @license   https://github.com/GR3S/Dim/blob/master/LICENSE MIT license
 * @version   1.0.0
 * @package   Dim
 */

namespace Dim;

/**
 * Class Service
 * @package Dim
 */
class Service implements ServiceInterface
{
    /**
     * @var string
     */
    protected $class;
    /**
     * @var array
     */
    protected $arguments;

    /**
     * @param $class
     * @param null $arguments
     */
    public function __construct($class, $arguments = null)
    {
        if (!is_string($class) || !class_exists($class)) {
            throw new \InvalidArgumentException('A class name expected.');
        }
        $this->class = $class;
        $this->arguments = (array)$arguments;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param null $arguments
     * @param Container $dim
     * @return object
     */
    public function get($arguments = null, Container $dim = null)
    {
        return static::resolveClass($this->class, (array)$arguments + $this->arguments, $dim);
    }

    /**
     * @param null $arguments
     * @param Container $dim
     * @return object
     */
    public function __invoke($arguments = null, Container $dim = null)
    {
        return $this->get($arguments, $dim);
    }

    /**
     * @param $class
     * @param array $arguments
     * @param Container $dim
     * @return object
     * @throws \InvalidArgumentException
     */
    protected static function resolveClass($class, array $arguments = array(), Container $dim = null)
    {
        $reflectionClass = new \ReflectionClass($class);
        if (!$reflectionClass->isInstantiable()) {
            throw new \InvalidArgumentException($class . ' class is not instantiable.');
        }
        $reflectionMethod = $reflectionClass->getConstructor();
        if ($reflectionMethod) {
            return $reflectionClass->newInstanceArgs(
                static::getReflectionParameters($reflectionMethod, $arguments, $dim)
            );
        }
        return $reflectionClass->newInstance();
    }

    /**
     * @param $callable
     * @param array $arguments
     * @param Container $dim
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected static function resolveCallable($callable, array $arguments = array(), Container $dim = null)
    {
        if (is_array($callable)) {
            list($class, $method) = $callable;
        } elseif (is_string($callable) && strpos($callable, '::') !== false) {
            list($class, $method) = explode('::', $callable, 2);
        } elseif (method_exists($callable, '__invoke')) {
            $class = $callable;
            $method = '__invoke';
        }
        if (isset($class) && isset($method)) {
            $reflection = new \ReflectionMethod($class, $method);
            if (!$reflection->isPublic()) {
                throw new \InvalidArgumentException(
                    'Can not access to non-public method ' .
                    (is_object($class) ? get_class($class) : $class) . '::' . $method . '.'
                );
            }
        } else {
            $reflection = new \ReflectionFunction($callable);
        }
        return call_user_func_array($callable, static::getReflectionParameters($reflection, $arguments, $dim));
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     * @param array $arguments
     * @param Container $dim
     * @return array
     * @throws \BadMethodCallException
     */
    protected static function getReflectionParameters(
        \ReflectionFunctionAbstract $reflection,
        array $arguments = array(),
        Container $dim = null
    ) {
        $parameters = array();
        foreach ($reflection->getParameters() as $reflectionParameter) {
            if (array_key_exists($reflectionParameter->getName(), $arguments)) {
                $parameters[] = $arguments[$reflectionParameter->getName()];
            } elseif (array_key_exists($reflectionParameter->getPosition(), $arguments)) {
                $parameters[] = $arguments[$reflectionParameter->getPosition()];
            } elseif ($reflectionParameter->isDefaultValueAvailable()) {
                $parameters[] = $reflectionParameter->getDefaultValue();
            } else {
                $classReflection = $reflectionParameter->getClass();
                if (!is_object($classReflection) || $dim === null || !$dim->has($classReflection->getName())) {
                    throw new \BadMethodCallException('Not enough arguments.');
                }
                $parameters[] = $dim->get($classReflection->getName());
            }
        }
        return $parameters ? $parameters : $arguments;
    }
} 
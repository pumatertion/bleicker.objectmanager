<?php

namespace Bleicker\ObjectManager;

use Bleicker\Container\AbstractContainer;
use Bleicker\ObjectManager\Exception\NotInstantiableException;
use Closure;
use ReflectionClass;

/**
 * Class ObjectManager
 *
 * @package Bleicker\ObjectManager
 */
class ObjectManager extends AbstractContainer implements ObjectManagerInterface {

	public static $storage = [];

	/**
	 * @param string $alias
	 * @param mixed $constructorArgument ...argument
	 * @param mixed $fallback
	 * @return object
	 */
	public static function get($alias, $fallback = NULL, $constructorArgument = NULL) {
		if (static::has($alias)) {
			$arguments = func_get_args();
			unset($arguments[1]);
			return call_user_func_array(array(new static, 'getRegistered'), func_get_args());
		}

		if ($fallback !== NULL) {
			return call_user_func_array(array(new static, 'getFallback'), func_get_args());
		}

		$arguments = func_get_args();
		unset($arguments[1]);
		return call_user_func_array(array(new static, 'getInstance'), $arguments);
	}

	/**
	 * @param string $alias
	 * @param mixed $fallback
	 * @param mixed $constructorArgument
	 * @return object
	 */
	protected static function getFallback($alias, $fallback, $constructorArgument = NULL) {
		static::add($alias, $fallback);
		$arguments = func_get_args();
		unset($arguments[1]);
		return call_user_func_array(array(new static, 'get'), $arguments);
	}

	/**
	 * @param $alias
	 * @param mixed $constructorArgument
	 * @return object
	 */
	protected static function getRegistered($alias, $constructorArgument = NULL) {
		$implementation = parent::get($alias);
		$constructorArguments = array_slice(func_get_args(), 1);

		if ($implementation instanceof Closure) {
			$object = call_user_func_array($implementation, $constructorArguments);
			return $object;
		}

		if (is_object($implementation)) {
			return $implementation;
		}

		$classReflection = new ReflectionClass($implementation);
		return $classReflection->newInstanceArgs($constructorArguments);
	}

	/**
	 * @param $alias
	 * @param mixed $constructorArgument
	 * @return object
	 * @throws NotInstantiableException
	 */
	protected static function getInstance($alias, $constructorArgument = NULL) {
		$classReflection = new ReflectionClass($alias);
		$constructorArguments = array_slice(func_get_args(), 1);
		if (!$classReflection->isInstantiable()) {
			throw new NotInstantiableException('Can not get instance of "' . $alias . '" ', 1431187178);
		}
		return $classReflection->newInstanceArgs($constructorArguments);
	}
}

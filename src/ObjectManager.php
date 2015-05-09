<?php

namespace Bleicker\ObjectManager;

use Bleicker\Container\AbstractContainer;
use Bleicker\Container\Exception\AliasAlreadyExistsException;
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
	 * @api
	 */
	public static function get($alias, $fallback = NULL, $constructorArgument = NULL) {
		if (static::has($alias)) {
			$arguments = func_get_args();
			unset($arguments[1]);
			return call_user_func_array(array(new static, 'getRegistered'), array_values($arguments));
		}

		if ($fallback !== NULL) {
			return call_user_func_array(array(new static, 'getFallback'), func_get_args());
		}

		$arguments = func_get_args();
		unset($arguments[1]);
		return call_user_func_array(array(new static, 'getInstance'), array_values($arguments));
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
		$arguments[1] = NULL;
		return call_user_func_array(array(new static, 'get'), array_values($arguments));
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

	/**
	 * @param string $alias
	 * @param mixed $data
	 * @param boolean $force
	 * @return static
	 * @throws AliasAlreadyExistsException
	 */
	public static function add($alias, $data, $force = FALSE) {
		if ($force === FALSE && static::has($alias)) {
			throw new AliasAlreadyExistsException('The alias "' . $alias . '" already exists. If you want to overwrite it please do first: \\' . static::class . '::remove(\'' . $alias . '\');', 1431000561);
		}
		static::$storage[$alias] = $data;
		return new static;
	}

	/**
	 * @param string $alias
	 * @return static
	 * @api
	 */
	public static function remove($alias) {
		return parent::remove($alias);
	}

	/**
	 * @param string $alias
	 * @return boolean
	 * @api
	 */
	public static function has($alias) {
		return parent::has($alias);
	}

	/**
	 * @return static
	 * @api
	 */
	public static function prune() {
		return parent::prune();
	}

	/**
	 * @return array
	 * @api
	 */
	public static function storage() {
		return parent::storage();
	}
}

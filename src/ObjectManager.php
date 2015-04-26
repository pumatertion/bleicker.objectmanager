<?php

namespace Bleicker\ObjectManager;

use Bleicker\ObjectManager\Exception\ArgumentsGivenButImplementationIsAlreadyAnObjectException;
use Bleicker\ObjectManager\Exception\ExistingClassOrInterfaceNameExpectedException;
use Closure;

/**
 * Class ObjectManager
 *
 * @package Bleicker\ObjectManager
 */
class ObjectManager implements ObjectManagerInterface {

	public static $singletons = [];

	/**
	 * @param $alias
	 * @param mixed $argument ...argument
	 * @return object
	 * @throws ExistingClassOrInterfaceNameExpectedException
	 * @throws ArgumentsGivenButImplementationIsAlreadyAnObjectException
	 */
	public static function get($alias, $argument = NULL) {

		$implementation = static::getImplementation($alias);

		if ($argument !== NULL && is_object($implementation) && !($implementation instanceof Closure)) {
			throw new ArgumentsGivenButImplementationIsAlreadyAnObjectException('Object already exists as implementation and can not have arguments', 1429683991);
		}

		if ($implementation instanceof Closure) {
			$arguments = array_slice(func_get_args(), 1);
			$object = call_user_func_array($implementation, $arguments);
			if (static::isSingleton($alias)) {
				static::register($alias, $object);
			}
			return $object;
		}

		if (is_object($implementation)) {
			return $implementation;
		}

		if ($argument !== NULL) {
			$arguments = array_slice(func_get_args(), 1);
			return static::getObjectWithContructorArguments($alias, $arguments);
		}

		return static::getObject($alias);
	}

	/**
	 * @param string $alias
	 * @return boolean
	 */
	public static function isRegistered($alias) {
		return Container::has($alias);
	}

	/**
	 * @param string $alias
	 * @param string $implementation
	 * @return static
	 */
	public static function register($alias, $implementation) {
		Container::add($alias, $implementation);
		return new static;
	}

	/**
	 * @param string $alias
	 * @return static
	 */
	public static function unregister($alias) {
		Container::remove($alias);
		return new static;
	}

	/**
	 * @param string $alias
	 * @return void
	 */
	public static function makeSingleton($alias) {
		static::$singletons[$alias] = TRUE;
	}

	/**
	 * @param string $alias
	 * @return void
	 */
	public static function makePrototype($alias) {
		unset(static::$singletons[$alias]);
	}

	/**
	 * @param $alias
	 * @return boolean
	 */
	public static function isSingleton($alias) {
		return array_key_exists($alias, static::$singletons) ? static::$singletons[$alias] : FALSE;
	}

	/**
	 * @param $alias
	 * @return boolean
	 */
	public static function isPrototype($alias) {
		return !static::isSingleton($alias);
	}

	/**
	 * @param $alias
	 * @return mixed
	 */
	public static function getImplementation($alias) {
		return Container::get($alias);
	}

	/**
	 * @param string $className
	 * @param array $arguments
	 * @return object
	 */
	protected static function getObjectWithContructorArguments($className, array $arguments) {
		$class = new \ReflectionClass($className);
		return $class->newInstanceArgs($arguments);
	}

	/**
	 * @param string $className
	 * @return object
	 */
	protected static function getObject($className) {
		return new $className();
	}

	/**
	 * @return void
	 */
	public static function prune() {
		static::$singletons = [];
		Container::prune();
	}
}

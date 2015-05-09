<?php
namespace Bleicker\ObjectManager;

/**
 * Class ObjectManager
 *
 * @package Bleicker\ObjectManager
 */
interface ObjectManagerInterface {

	/**
	 * @return array
	 */
	public static function storage();

	/**
	 * @param string $alias
	 * @return boolean
	 */
	public static function has($alias);

	/**
	 * @param string $alias
	 * @param mixed $data
	 * @return static
	 */
	public static function add($alias, $data);

	/**
	 * @return static
	 */
	public static function prune();

	/**
	 * @param string $alias
	 * @param mixed $constructorArgument ...argument
	 * @param mixed $fallback
	 * @return object
	 */
	public static function get($alias, $fallback = NULL, $constructorArgument = NULL);

	/**
	 * @param string $alias
	 * @return static
	 */
	public static function remove($alias);
}
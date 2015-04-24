<?php
namespace Bleicker\ObjectManager;

/**
 * Interface ObjectManager
 *
 * @package Bleicker\ObjectManager
 */
interface ObjectManagerInterface {

	/**
	 * @param string $alias
	 * @param $argument ...$argument
	 */
	public static function get($alias, $argument = NULL);

	/**
	 * @param string $alias
	 * @param string $implementation
	 * @return void
	 */
	public static function register($alias, $implementation);

	/**
	 * @param string $alias
	 * @return void
	 */
	public static function unregister($alias);

	/**
	 * @param string $alias
	 * @return void
	 */
	public static function makeSingleton($alias);

	/**
	 * @param string $alias
	 * @return void
	 */
	public static function makePrototype($alias);

	/**
	 * @param string $alias
	 * @return boolean
	 */
	public static function isSingleton($alias);

	/**
	 * @param string $alias
	 * @return boolean
	 */
	public static function isPrototype($alias);

	/**
	 * @param string $alias
	 * @return boolean
	 */
	public static function isRegistered($alias);

	/**
	 * @return void
	 */
	public static function prune();

}
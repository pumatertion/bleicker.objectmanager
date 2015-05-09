<?php

namespace Tests\Bleicker\ObjectManager\Unit;

use Bleicker\ObjectManager\ObjectManager;
use Tests\Bleicker\ObjectManager\Unit\Fixtures\AbstractClass;
use Tests\Bleicker\ObjectManager\Unit\Fixtures\AnInterface;
use Tests\Bleicker\ObjectManager\Unit\Fixtures\ATrait;
use Tests\Bleicker\ObjectManager\Unit\Fixtures\SimpleClass;
use Tests\Bleicker\ObjectManager\Unit\Fixtures\SimpleClassHavingConstructorArgument;
use Tests\Bleicker\ObjectManager\UnitTestCase;

/**
 * Class ObjectManagerTest
 *
 * @package Tests\Bleicker\ObjectManager\Unit
 */
class ObjectManagerTest extends UnitTestCase {

	protected function setUp() {
		parent::setUp();
		ObjectManager::prune();
	}

	protected function tearDown() {
		parent::tearDown();
		ObjectManager::prune();
	}

	/**
	 * @test
	 * @expectedException \ReflectionException
	 */
	public function unregisteredWithoutFallbackAndClassNotExistsTestTest() {
		ObjectManager::get('foo');
	}

	/**
	 * @test
	 * @expectedException \Bleicker\ObjectManager\Exception\NotInstantiableException
	 */
	public function unregisteredWithoutFallbackAndAliasIsInterfaceTest() {
		ObjectManager::get(AnInterface::class);
	}

	/**
	 * @test
	 * @expectedException \Bleicker\ObjectManager\Exception\NotInstantiableException
	 */
	public function unregisteredWithoutFallbackAndAliasIsTraitTest() {
		ObjectManager::get(ATrait::class);
	}

	/**
	 * @test
	 * @expectedException \Bleicker\ObjectManager\Exception\NotInstantiableException
	 */
	public function unregisteredWithoutFallbackAndAliasIsAbstractTest() {
		ObjectManager::get(AbstractClass::class);
	}

	/**
	 * @test
	 */
	public function unregisteredWithoutFallbackAndAliasIsClassTest() {
		$this->assertInstanceOf(SimpleClass::class, ObjectManager::get(SimpleClass::class));
	}

	/**
	 * @test
	 */
	public function unregisteredWithoutFallbackAndAliasIsClassReturnsInstanceTest() {
		$object1 = ObjectManager::get(SimpleClassHavingConstructorArgument::class);
		$object2 = ObjectManager::get(SimpleClassHavingConstructorArgument::class);
		$this->assertEquals($object1, $object2);
	}

	/**
	 * @test
	 */
	public function unregisteredWithoutFallbackAndAliasIsClassHavingConstructorArgumentsTest() {
		/** @var SimpleClassHavingConstructorArgument $object */
		$object = ObjectManager::get(SimpleClassHavingConstructorArgument::class, NULL, 'Hello world!');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertEquals('Hello world!', $object->getTitle());
	}

	/**
	 * @test
	 */
	public function unregisteredHavingFallbackAndAliasIsClassTest() {
		$object = ObjectManager::get(SimpleClassHavingConstructorArgument::class, SimpleClass::class);
		$this->assertInstanceOf(SimpleClass::class, $object);
	}

	/**
	 * @test
	 */
	public function unregisteredHavingFallbackWithConstructorArgumentTest() {
		/** @var SimpleClassHavingConstructorArgument $object */
		$object = ObjectManager::get(SimpleClass::class, SimpleClassHavingConstructorArgument::class, 'Hello world!');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertEquals('Hello world!', $object->getTitle());
	}

	/**
	 * @test
	 */
	public function unregisteredHavingFallbackWithConstructorArgumentSingletonTest() {
		/** @var SimpleClassHavingConstructorArgument $object1 */
		$object1 = ObjectManager::get(SimpleClass::class, SimpleClassHavingConstructorArgument::class, 'Hello world!');
		/** @var SimpleClassHavingConstructorArgument $object2 */
		$object2 = ObjectManager::get(SimpleClass::class, SimpleClassHavingConstructorArgument::class, 'Hello world!');
		$this->assertEquals($object1, $object2);
	}

	/**
	 * @test
	 */
	public function unregisteredHavingFallbackClosureConstructorArgumentTest() {
		/** @var SimpleClassHavingConstructorArgument $object */
		$object = ObjectManager::get(SimpleClass::class, function($title){
			$this->assertEquals('Hello world!', $title);
			return new SimpleClassHavingConstructorArgument($title);
		}, 'Hello world!');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertEquals('Hello world!', $object->getTitle());
	}

	/**
	 * @test
	 */
	public function unregisteredHavingFallbackClosureConstructorArgumentAndResultsInSingletonTest() {
		/** @var SimpleClassHavingConstructorArgument $object */
		$object = ObjectManager::get(SimpleClass::class, function($title){
			$this->assertEquals('Hello world!', $title);
			$object = new SimpleClassHavingConstructorArgument($title);
			ObjectManager::add(SimpleClass::class, $object);
			return $object;
		}, 'Hello world!');
		$instance1 = ObjectManager::get(SimpleClass::class);
		$instance2 = ObjectManager::get(SimpleClass::class);
		$this->assertEquals($object, $instance1);
		$this->assertEquals($instance1, $instance2);
	}

	/**
	 * @test
	 */
	public function registeredTest(){
		ObjectManager::add('foo', SimpleClass::class);
		$this->assertInstanceOf(SimpleClass::class, ObjectManager::get('foo'));
	}

	/**
	 * @test
	 */
	public function registeredClassHavingConstructorArgumentsTest(){
		ObjectManager::add('foo', SimpleClassHavingConstructorArgument::class);
		/** @var SimpleClassHavingConstructorArgument $object */
		$object = ObjectManager::get('foo', NULL, 'Hello world!');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertEquals('Hello world!', $object->getTitle());
	}

	/**
	 * @test
	 */
	public function registeredClassHavingConstructorArgumentsSkipsFallbackTest(){
		ObjectManager::add('foo', SimpleClassHavingConstructorArgument::class);
		/** @var SimpleClassHavingConstructorArgument $object */
		$object = ObjectManager::get('foo', SimpleClass::class, 'Hello world!');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertEquals('Hello world!', $object->getTitle());
	}

	/**
	 * @test
	 */
	public function registeredClosureConstructorArgumentsTest(){
		ObjectManager::add('foo', function($title){
			$this->assertEquals('Hello world!', $title);
			return new SimpleClassHavingConstructorArgument($title);
		});
		/** @var SimpleClassHavingConstructorArgument $object */
		$object = ObjectManager::get('foo', NULL, 'Hello world!');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertEquals('Hello world!', $object->getTitle());
	}

	/**
	 * @test
	 */
	public function registeredHavingFallbackClosureConstructorArgumentAndResultsInSingletonTest() {
		ObjectManager::add('foo', function($title){
			$this->assertEquals('Hello world!', $title);
			$object = new SimpleClassHavingConstructorArgument($title);
			ObjectManager::add(SimpleClass::class, $object);
			return $object;
		});
		$instance1 = ObjectManager::get('foo', NULL, 'Hello world!');
		$instance2 = ObjectManager::get('foo', NULL, 'Hello world!');
		$this->assertEquals($instance1, $instance2);
	}
}

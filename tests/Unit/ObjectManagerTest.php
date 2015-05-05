<?php

namespace Tests\Bleicker\ObjectManager\Unit;

use Bleicker\ObjectManager\ObjectManager;
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
	 */
	public function pruneTest() {
		ObjectManager::makeSingleton(SimpleClass::class);
		$this->assertTrue(ObjectManager::isSingleton(SimpleClass::class));
		ObjectManager::prune();
		$this->assertFalse(ObjectManager::isSingleton(SimpleClass::class));
	}

	/**
	 * @test
	 */
	public function getClassWithoutAnyContructorArgumentReturnsInstance() {
		$object = ObjectManager::get(SimpleClass::class);
		$this->assertInstanceOf(SimpleClass::class, $object);
	}

	/**
	 * @test
	 */
	public function getClassIfRegisteredAsStringTest() {
		ObjectManager::register('foo', SimpleClass::class);
		$object = ObjectManager::get('foo');
		$this->assertInstanceOf(SimpleClass::class, $object);
	}

	/**
	 * @test
	 * @expectedException \Bleicker\ObjectManager\Exception\ArgumentsGivenButImplementationIsAlreadyAnObjectException
	 */
	public function getClassOrInterfaceThrowsExceptionIfImplementationIsAlreadyAnObjectAndArgumentsGiven() {
		ObjectManager::register(SimpleClassHavingConstructorArgument::class, new SimpleClassHavingConstructorArgument('foo'));
		ObjectManager::get(SimpleClassHavingConstructorArgument::class, 'foo');
	}

	/**
	 * @test
	 */
	public function getClassWithoutOneContructorArgumentReturnsInstance() {
		$object = ObjectManager::get(SimpleClassHavingConstructorArgument::class, 'foo');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertEquals('foo', $object->getTitle());
	}

	/**
	 * @test
	 */
	public function getClassFromRegistriesImplementationReturnsRegistryInstance() {
		ObjectManager::register(SimpleClass::class, new SimpleClass());
		$object = ObjectManager::get(SimpleClass::class);
		$this->assertTrue(ObjectManager::getImplementation(SimpleClass::class) === $object);
	}

	/**
	 * @test
	 */
	public function getClassFromRegistriesImplementationReturnsRegistryInstanceIfImplemantationIsAClosure() {
		ObjectManager::register(SimpleClassHavingConstructorArgument::class, function ($title) {
			return new SimpleClassHavingConstructorArgument($title);
		});
		$object = ObjectManager::get(SimpleClassHavingConstructorArgument::class, 'foo');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertEquals('foo', $object->getTitle());
	}

	/**
	 * @test
	 */
	public function getSingletonClosureIsRegistedAsConcreteImplementation() {
		ObjectManager::register(SimpleClassHavingConstructorArgument::class, function ($title) {
			return new SimpleClassHavingConstructorArgument($title);
		});
		ObjectManager::makeSingleton(SimpleClassHavingConstructorArgument::class);
		$object = ObjectManager::get(SimpleClassHavingConstructorArgument::class, 'foo');
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, $object);
		$this->assertInstanceOf(SimpleClassHavingConstructorArgument::class, ObjectManager::getImplementation(SimpleClassHavingConstructorArgument::class));
		$this->assertTrue($object === ObjectManager::getImplementation(SimpleClassHavingConstructorArgument::class));
	}
}

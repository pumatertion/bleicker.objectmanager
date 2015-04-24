# Info #

ObjectManager is a container to register objects

## Usage ##

* ObjectManager::register(MyClassInterface::class, new MyClass('foo', 'bar'));
* Getting the Object everywhere in your Code with ObjectManager::get(MyClassInterface::class);

### Registering a Closure as Factory ###

* ObjectManager::register(MyClassInterface::class, function(){new MyClass()});
* To make it a singleton just register it as this: ObjectManager::makeSingleton(MyClassInterface::class);
* Getting the Object everywhere in your Code with ObjectManager::get(MyClassInterface::class);

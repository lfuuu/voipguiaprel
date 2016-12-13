<?php
namespace app\classes;

abstract class Singleton
{
    private static $instances = array();

    protected function __construct() {/* you can't create me */}

    public static function me($args = null /* , ... */)
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            // for Singleton::getInstance('class_name', $arg1, ...) calling
            if (2 < func_num_args()) {
                $args = func_get_args();
                array_shift($args);


                // emulat`ion of ReflectionClass->newInstanceWithoutConstructor
                $object =
                    unserialize(
                        sprintf('O:%d:"%s":0:{}', strlen($class), $class)
                    );

                call_user_func_array(
                    array($object, '__construct'),
                    $args
                );
            } else {
                $object =
                    $args
                        ? new $class($args)
                        : new $class();
            }

            Assert::isTrue(
                $object instanceof Singleton,
                "Class '{$class}' is something not a Singleton's child"
            );

            self::$instances[$class] = $object;
        }

        return self::$instances[$class];
    }

    final private function __clone() {/* do not clone me */}
    final private function __sleep() {/* restless class */}
}
<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit18287bb3d4c2a127d5c3e6f06f18d167
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit18287bb3d4c2a127d5c3e6f06f18d167', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit18287bb3d4c2a127d5c3e6f06f18d167', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit18287bb3d4c2a127d5c3e6f06f18d167::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}

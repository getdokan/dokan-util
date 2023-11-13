<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitddfc721fa73a93f52ea1f929a40cb744
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

        spl_autoload_register(array('ComposerAutoloaderInitddfc721fa73a93f52ea1f929a40cb744', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitddfc721fa73a93f52ea1f929a40cb744', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitddfc721fa73a93f52ea1f929a40cb744::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
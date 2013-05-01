<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Config
{

    /**
     * The global package configuration manager (default is AssetsManager\DefaultConfig)
     * @var object
     */
    private static $__configurator;

    /**
     * The configuration entries registry
     * @var array
     */
    private static $__registry;

    /**
     * The real configuration entries
     * @var array
     */
    protected static $_requires = array(
        'package-type',
        'vendor-dir',
        'assets-dir',
        'assets-vendor-dir',
        'document-root',
        'assets-db-filename',
        'use-statements',
    );

    /**
     * Load a config object
     * @return void
     */
    public static function load($class_name = 'AssetsManager\Config\DefaultConfig')
    {
        // init the registry
        if (empty(self::$__registry)) {
            self::$__registry = array_combine(self::$_requires, array_pad(array(), count(self::$_requires), null));
        }

        // init the configurator object
        if (empty(self::$__configurator) || $class_name!=get_class(self::$__configurator)) {
            if (@class_exists($class_name)) {
                $interfaces = class_implements($class_name);
                if (in_array('AssetsManager\Config\ConfiguratorInterface', $interfaces)) {
                    self::$__configurator = new $class_name;
                    $defaults = self::$__configurator->getDefaults();
                    $diff = array_diff(array_keys($defaults), self::$_requires);
                    if (empty($diff)) {
                        foreach ($defaults as $var=>$val) {
                            self::set($var, $val);
                        }
                    } else {
                        throw new \Exception(
                            sprintf('Configuration class "%s" do not define all required values!', $class_name)
                        );
                    }
                } else {
                    throw new \DomainException(
                        sprintf('Configuration class "%s" must implements interface "%s"!',
                            $class_name, 'AssetsManager\Config\ConfiguratorInterface')
                    );
                }
            } else {
                throw new \DomainException(
                    sprintf('Configuration class "%s" not found!', $class_name)
                );
            }
        }
    }

    /**
     * @param string $name
     * @param misc $value
     * @return void
     */
    public static function set($name, $value)
    {
        self::load();
        if (array_key_exists($name, self::$__registry)) {
            self::$__registry[$name] = $value;
        }
    }

    /**
     * @param string $name
     * @param misc $default
     * @return misc
     */
    public static function get($name, $default = null)
    {
        self::load();
        return isset(self::$__registry[$name]) ? self::$__registry[$name] : $default;
    }

    /**
     * @param string $name
     * @return misc
     */
    public static function getDefault($name)
    {
        self::load();
        $cls = get_class(self::$__configurator);
        $configs = $cls::$_defaults;
        return isset($configs[$name]) ? $configs[$name] : null;
    }

    /**
     * @return array
     */
    public static function getRegistry()
    {
        self::load();
        return self::$__registry;
    }

}

// Endfile
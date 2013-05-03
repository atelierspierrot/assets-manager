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
        'config-class',
        'assets-package-installer-class',
        'assets-package-class',
        'assets-preset-class',
    );

    /**
     * The internal configuration entries
     * @var array
     */
    private static $__internals = array(
        'composer-db' => 'composer.json',
        'assets-config-class' => 'AssetsManager\Config\DefaultConfig',
        'assets-config-interface' => 'AssetsManager\Config\ConfiguratorInterface',
        'assets-package-interface' => 'AssetsManager\Package\AssetsPackageInterface',
        'assets-preset-interface' => 'AssetsManager\Package\AssetsPresetInterface',
        'assets-preset-adapter-interface' => 'AssetsManager\Package\PresetAdapterInterface',
        'assets-package-installer-interface' => 'AssetsManager\Composer\Installer\AssetsInstallerInterface',
    );

    /**
     * Load a config object
     * @param bool $safe
     * @return void
     */
    public static function load($class_name = null, $safe = false)
    {
        if (empty($class_name)) $class_name = self::getInternal('assets-config-class');
    
        // init the registry
        if (empty(self::$__registry)) {
            self::$__registry = array_combine(self::$_requires,
                array_pad(array(), count(self::$_requires), null));
        }

        // init the configurator object
        if (empty(self::$__configurator) || $class_name!=get_class(self::$__configurator)) {

            if ($safe && !class_exists($class_name)) {
                $class_name = self::getInternal('assets-config-class');
            }

            if (class_exists($class_name)) {
                $interfaces = class_implements($class_name);
                $config_interface = self::getInternal('assets-config-interface');
                if (in_array($config_interface, $interfaces)) {
                    self::$__configurator = new $class_name;
                    $defaults = self::$__configurator->getDefaults();
                    $diff = array_diff(array_keys($defaults), self::$_requires);
                    if (empty($diff)) {
                        foreach ($defaults as $var=>$val) {
                            self::set($var, $val);
                        }
                    } else {
                        Error::thrower(
                            sprintf('Configuration class "%s" do not define all required values!', 
                                $class_name),
                            '\Exception', __CLASS__, __METHOD__, __LINE__
                        );
                    }
                } else {
                    Error::thrower(
                        sprintf('Configuration class "%s" must implements interface "%s"!',
                            $class_name, $config_interface),
                        '\DomainException', __CLASS__, __METHOD__, __LINE__
                    );
                }
            } else {
                Error::thrower(
                    sprintf('Configuration class "%s" not found!', $class_name),
                    '\DomainException', __CLASS__, __METHOD__, __LINE__
                );
            }
        }
    }

    /**
     * Overload a config registry
     * @return void
     */
    public static function overload(array $settings)
    {
        self::load();
        foreach ($settings as $var=>$val) {
            self::set($var, $val);
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
        return isset(self::$__registry[$name]) ? (
            is_string(self::$__registry[$name]) ?
                trim(self::$__registry[$name]) : self::$__registry[$name]
        ) : $default;
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
        return isset($configs[$name]) ? (
            is_string($configs[$name]) ? trim($configs[$name]) : $configs[$name]
        ) : null;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getInternal($name)
    {
        $configs = self::$__internals;
        return isset($configs[$name]) ? (
            is_string($configs[$name]) ? trim($configs[$name]) : $configs[$name]
        ) : null;
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
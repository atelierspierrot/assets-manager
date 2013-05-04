<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager\Composer\Autoload;

use AssetsManager\Composer\Installer\AssetsInstaller;

use Composer\Package\PackageInterface;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
abstract class AbstractAutoloadGenerator
{

    /**
     * @var AssetsManager\Composer\Installer\AssetsInstaller
     */
    protected $assets_installer;

    /**
     * @var Array
     */
    protected $assets_db;

    /**
     * @var self
     */
    private static $_instance;

    /**
     * @var callable
     */
    private static $_generator;

    /**
     * Get a singleton instance
     * @param object $installer
     * @return object
     */
    public static function getInstance(AssetsInstaller $installer)
    {
        if (empty(self::$_instance)) {
            $cls = __CLASS__;
            self::$_instance = new $cls($installer);
        }
        return self::$_instance;
    }

    /**
     * Construct instance
     * @param object $installer
     * @return void
     */
    public function __construct(AssetsInstaller $installer)
    {
        $this->assets_installer = $installer;
        $this->assets_db = array();
        self::setGenerator(array($this, 'generate'));
    }

    /**
     * Load the assets database file generation
     * @return void
     */
    public function __destruct()
    {
        call_user_func(self::$_generator);
    }

    /**
     * Get the current assets database
     * @return array
     */
    public static function getRegistry()
    {
        $_this = self::getInstance($installer);
        return $_this->assets_db;
    }

    /**
     * Set the generator called at object destruction
     * @param callable $callable
     * @return array
     */
    public static function setGenerator($callable)
    {
        if (is_callable($callable)) {
            self::$_generator = $callable;
        }
    }

// --------------------------
// Abstracts methods
// --------------------------

    /**
     * This must generate the Assets database JSON file
     */
    abstract public function generate();
    
    /**
     * Add a new installed package in the Assets database
     * @param object $package
     * @param string $target
     * @param object $installer
     * @return void
     */
    abstract public static function registerPackage(PackageInterface $package, $target, AssetsInstaller $installer);

    /**
     * Remove an uninstalled package from the Assets database
     * @param object $package
     * @param object $installer
     * @return void
     */
    abstract public static function unregisterPackage(PackageInterface $package, AssetsInstaller $installer);

}

// Endfile
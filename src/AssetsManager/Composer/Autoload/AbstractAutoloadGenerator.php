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

use Composer\Package\PackageInterface,
    Composer\Json\JsonFile;

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
            $cls = get_called_class();
            self::$_instance = new $cls($installer);
        }
        return self::$_instance;
    }

    /**
     * Construct instance
     * @param object $installer
     * @return void
     */
    protected function __construct(AssetsInstaller $installer)
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
     * Writes the assets database in a JSON file
     * @param array $full_db
     * @return false|string
     */
    public function writeJsonDatabase(array $full_db)
    {
        $assets_file = $this->assets_installer->getVendorDir() . '/' . $this->assets_installer->getAssetsDbFilename();
        $this->assets_installer->getIo()->write( 
            sprintf('Writing assets json DB to <info>%s</info>',
                str_replace(dirname($this->assets_installer->getVendorDir()).'/', '', $assets_file)
            )
        );
        try {
            $json = new JsonFile($assets_file);
            $json->write($full_db);
            return $assets_file;
        } catch(\Exception $e) {
            if (file_put_contents($assets_file, json_encode($full_db, version_compare(PHP_VERSION, '5.4')>0 ? JSON_PRETTY_PRINT : 0))) {
                return $assets_file;
            }
        }        
        return false;
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

    /**
     * Add a new installed package in the Assets database
     * @param object $package
     * @param string $target
     * @param object $installer
     * @return void
     */
    public static function registerPackage(PackageInterface $package, $target, AssetsInstaller $installer)
    {
        $_this = self::getInstance($installer);
        $_this->addPackage($package, $target);
    }

    /**
     * Remove an uninstalled package from the Assets database
     * @param object $package
     * @param object $installer
     * @return void
     */
    public static function unregisterPackage(PackageInterface $package, AssetsInstaller $installer)
    {
        $_this = self::getInstance($installer);
        $this->removePackage($package);
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
     * @return void
     */
    abstract protected function addPackage(PackageInterface $package, $target);

    /**
     * Remove an uninstalled package from the Assets database
     * @param object $package
     * @return void
     */
    abstract protected function removePackage(PackageInterface $package);

}

// Endfile
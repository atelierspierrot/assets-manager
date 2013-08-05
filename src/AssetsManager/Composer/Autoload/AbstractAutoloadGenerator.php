<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager\Composer\Autoload;

use AssetsManager\Composer\Installer\AssetsInstallerInterface;

use Composer\Package\PackageInterface,
    Composer\Json\JsonFile;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
abstract class AbstractAutoloadGenerator
{

    /**
     * @var AssetsManager\Composer\Installer\AssetsInstallerInterface
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
     * @param object $installer AssetsManager\Composer\Installer\AssetsInstallerInterface
     * @return object
     */
    public static function getInstance(AssetsInstallerInterface $installer = null)
    {
        if (empty(self::$_instance)) {
            if (empty($installer)) {
                throw new \InvalidArgumentException(
                    sprintf('Can not instanciate autoloader generator singelton object "%s"' .
                        ' without an "AssetsInstallerInterface" object argument!',
                        get_called_class())
                );
            }
            $cls = get_called_class();
            self::$_instance = new $cls($installer);
        }
        return self::$_instance;
    }

    /**
     * Construct instance
     * @param object $installer AssetsManager\Composer\Installer\AssetsInstallerInterface
     * @return void
     */
    protected function __construct(AssetsInstallerInterface $installer)
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
     * @return object AssetsManager\Composer\Installer\AssetsInstallerInterface
     */
    public function getAssetsInstaller()
    {
        return $this->assets_installer;
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
     * Set the current assets database
     * @param array
     * @param object $installer AssetsManager\Composer\Installer\AssetsInstallerInterface
     * @return void
     */
    public static function setRegistry(array $assets_db, AssetsInstallerInterface $installer = null)
    {
        $_this = self::getInstance($installer);
        $_this->assets_db = $assets_db;
    }

    /**
     * Get the current assets database
     * @param object $installer AssetsManager\Composer\Installer\AssetsInstallerInterface
     * @return array
     */
    public static function getRegistry(AssetsInstallerInterface $installer = null)
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
     * @param object $package Composer\Package\PackageInterface
     * @param string $target
     * @param object $installer AssetsManager\Composer\Installer\AssetsInstallerInterface
     * @return void
     */
    public static function registerPackage(PackageInterface $package, $target, AssetsInstallerInterface $installer = null)
    {
        $_this = self::getInstance($installer);
        $_this->addPackage($package, $target);
    }

    /**
     * Remove an uninstalled package from the Assets database
     * @param object $package Composer\Package\PackageInterface
     * @param object $installer AssetsManager\Composer\Installer\AssetsInstallerInterface
     * @return void
     */
    public static function unregisterPackage(PackageInterface $package, AssetsInstallerInterface $installer = null)
    {
        $_this = self::getInstance($installer);
        $_this->removePackage($package);
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
     * @param object $package Composer\Package\PackageInterface
     * @param string $target
     * @return void
     */
    abstract protected function addPackage(PackageInterface $package, $target);

    /**
     * Remove an uninstalled package from the Assets database
     * @param object $package Composer\Package\PackageInterface
     * @return void
     */
    abstract protected function removePackage(PackageInterface $package);

}

// Endfile
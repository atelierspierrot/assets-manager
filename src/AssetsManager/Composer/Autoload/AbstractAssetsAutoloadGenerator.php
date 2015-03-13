<?php
/**
 * This file is part of the AssetsManager package.
 *
 * Copyleft (ↄ) 2013-2015 Pierre Cassat <me@e-piwi.fr> and contributors
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * The source code of this package is available online at 
 * <http://github.com/atelierspierrot/assets-manager>.
 */

namespace AssetsManager\Composer\Autoload;

use \AssetsManager\Composer\Installer\AssetsInstallerInterface;
use \Composer\Package\PackageInterface;
use \Composer\Json\JsonFile;

/**
 * @author  piwi <me@e-piwi.fr>
 */
abstract class AbstractAssetsAutoloadGenerator
{

    /**
     * @var \AssetsManager\Composer\Installer\AssetsInstallerInterface
     */
    protected $assets_installer;

    /**
     * @var array
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
     *
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     * @return object
     * @throws \InvalidArgumentException if no argument received while it was required
     */
    public static function getInstance(AssetsInstallerInterface $installer = null)
    {
        if (empty(self::$_instance)) {
            if (empty($installer)) {
                throw new \InvalidArgumentException(
                    sprintf('Can not instantiate autoloader generator singleton object "%s"' .
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
     *
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     */
    protected function __construct(AssetsInstallerInterface $installer)
    {
        $this->assets_installer = $installer;
        $assets_db = $this->readJsonDatabase();
        $this->assets_db = isset($assets_db['packages']) ? $assets_db['packages'] : array();
        self::setGenerator(array($this, 'generate'));
    }

    /**
     * Load the assets database file generation
     *
     * @return void
     */
    public function __destruct()
    {
        call_user_func(self::$_generator);
    }

    /**
     * @return \AssetsManager\Composer\Installer\AssetsInstallerInterface
     */
    public function getAssetsInstaller()
    {
        return $this->assets_installer;
    }

    /**
     * Reads the assets database from JSON file
     *
     * @return false|string
     */
    public function readJsonDatabase()
    {
        $assets_file = $this->assets_installer->getVendorDir() . '/' . $this->assets_installer->getAssetsDbFilename();
        if (file_exists($assets_file)) {
            $json = new JsonFile($assets_file);
            $assets = $json->read();
            return $assets;
        }        
        return false;
    }

    /**
     * Writes the assets database in a JSON file
     *
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
            if ($json->exists()) {
                unlink($assets_file);
            }
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
     *
     * @param array
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     * @return void
     */
    public static function setRegistry(array $assets_db, AssetsInstallerInterface $installer = null)
    {
        $_this = self::getInstance($installer);
        $_this->assets_db = $assets_db;
    }

    /**
     * Get the current assets database
     *
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     * @return array
     */
    public static function getRegistry(AssetsInstallerInterface $installer = null)
    {
        $_this = self::getInstance($installer);
        return $_this->assets_db;
    }

    /**
     * Set the generator called at object destruction
     *
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
     *
     * @param \Composer\Package\PackageInterface $package
     * @param string $target
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     * @return void
     */
    public static function registerPackage(PackageInterface $package, $target, AssetsInstallerInterface $installer = null)
    {
        $_this = self::getInstance($installer);
        $_this->addPackage($package, $target);
    }

    /**
     * Remove an uninstalled package from the Assets database
     *
     * @param \Composer\Package\PackageInterface $package
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
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
     *
     * @param \Composer\Package\PackageInterface $package
     * @param string $target
     * @return void
     */
    abstract protected function addPackage(PackageInterface $package, $target);

    /**
     * Remove an uninstalled package from the Assets database
     *
     * @param \Composer\Package\PackageInterface $package
     * @return void
     */
    abstract protected function removePackage(PackageInterface $package);

}

// Endfile
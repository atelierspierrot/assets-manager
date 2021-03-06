<?php
/**
 * This file is part of the AssetsManager package.
 *
 * Copyleft (ↄ) 2013-2016 Pierre Cassat <me@e-piwi.fr> and contributors
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

namespace AssetsManager;

use \AssetsManager\Package\AbstractAssetsPackage;
use \AssetsManager\Config;
use \AssetsManager\Package\AssetsPackage;
use \AssetsManager\Package\Preset;
use \Library\Helper\Directory as DirectoryHelper;
use \Library\Helper\Filesystem as FilesystemHelper;
use \Library\Helper\Url as UrlHelper;

/**
 * Class to manage assets paths
 *
 * The class is based on three paths:
 * 
 * - `root_dir`: the package root directory (must be the directory containing the `composer.json` file)
 * - `assets_dir`: the package asssets directory related to `root_dir`
 * - `document_root`: the path in the filesystem of the web assets root directory ; this is used
 * to build all related assets paths to use in HTTP.
 *
 * For these three paths, their defaults values are defined on a default package structure:
 *
 *     package_name/
 *     |----------- src/
 *     |----------- www/
 *
 *     $loader->root_dir = realpath(package_name)
 *     $loader->assets_dir = www
 *     $loader->document_root = www or the server DOCUMENT_ROOT
 *
 * NOTE - These paths are stored in the object without the trailing slash.
 *
 * @author  piwi <me@e-piwi.fr>
 */
class Loader
    extends AbstractAssetsPackage
{

    /**
     * Flag to use to avoid throwing an exception in case of presets conflicts
     */
    const PRESETS_NO_CONFLICT   = 1;
    
    /**
     * Flag to use to throw exception in case of presets conflicts (default)
     */
    const PRESETS_CONFLICT      = 2;
    
    /**
     * @var string The document root path (absolute - used to build assets web path - no trailing slash)
     */
    protected $document_root;

    /**
     * @var array Project assets DB array
     * This is populated parsing the package's `ASSETS_DB_FILENAME`.
     */
    protected $assets_db;

    /**
     * @var array
     */
    protected $packages_instances;

    /**
     * Table of all presets, each entry like :
     *
     *     preset name => array(
     *         package=>original package name,
     *         data=>array,
     *         instance=>Preset object
     *     )
     *
     * @var array
     */
    protected $presets_data;

    /**
     * @var self singleton self instance
     */
    private static $__instance;

    /**
     * @var bool flag for instance creation
     */
    private static $__isStaticInstance = false;

    /**
     * @var int
     */
    private $conflict_flag;

// ---------------------
// Construction
// ---------------------

    /**
     * Loader static instance constructor
     *
     * @param   string  $root_dir       The project package root directory
     * @param   string  $assets_dir     The project package assets directory, related from `$root_dir`
     * @param   string  $document_root  The project assets root directory to build web accessible assets paths
     * @param   int     $conflict_flag  Define if the class must throw exceptions in case of presets conflicts
     * @return  self
     */
    public static function getInstance(
        $root_dir = null, $assets_dir = null, $document_root = null, $conflict_flag = self::PRESETS_CONFLICT
    ) {
        if (empty(self::$__instance)) {
            $cls = get_called_class();
            self::$__isStaticInstance = true;
            self::$__instance = new $cls($root_dir, $assets_dir, $document_root, $conflict_flag);
        }
        return self::$__instance;
    }
    
    /**
     * Loader protected constructor, use the class as a Singleton 
     *
     * @param   string  $root_dir       The project package root directory
     * @param   string  $assets_dir     The project package assets directory, related from `$root_dir`
     * @param   string  $document_root  The project assets root directory to build web accessible assets paths
     * @param   int     $conflict_flag  Define if the class must throw exceptions in case of presets conflicts
     * @throws  \Exception if the object is not called as a singleton
     */
    public function __construct(
        $root_dir = null, $assets_dir = null, $document_root = null, $conflict_flag = self::PRESETS_CONFLICT
    ) {
        if (false===self::$__isStaticInstance) {
            throw new \Exception(
                sprintf('Object of class "%s" must be used as a singleton!', __CLASS__)
            );
            return;
        }
        $this->conflict_flag = $conflict_flag;
        $this->init($root_dir, $assets_dir, $document_root);
    }

    /**
     * Initializing a new loader populating all paths & packages
     *
     * @param   string  $root_dir       The project package root directory
     * @param   string  $assets_dir     The project package assets directory, related from `$root_dir`
     * @param   string  $document_root  The project assets root directory to build web accessible assets paths
     * @return  void
     * @throws  \Exception : any caught exception
     * @throws  \Exception if the package's `ASSETS_DB_FILENAME` was not found
     */
    public function init($root_dir = null, $assets_dir = null, $document_root = null)
    {
        try {
            $this->setRootDirectory(!is_null($root_dir) ? $root_dir : __DIR__.'/../../../../../');

            $composer = $this->getRootDirectory() . '/' . Config::getInternal('composer-db');
            $vendor_dir = Config::get('vendor-dir');
            if (file_exists($composer)) {
                $json_package = json_decode(file_get_contents($composer), true);
                if (isset($json_package['config']) && isset($json_package['config']['vendor-dir'])) {
                    $vendor_dir = $json_package['config']['vendor-dir'];
                }
            } else {
                throw new \Exception(
                    sprintf('Composer json "%s" not found!', $composer)
                );
            }
            $this->setVendorDirectory($vendor_dir);

            $extra = isset($json_package['extra']) ? $json_package['extra'] : array();
            if (isset($extra['assets-config-class'])) {
                Config::load($extra['assets-config-class']);
            }
            if (!empty($extra)) {
                Config::overload($extra);
            }

            $assets_db_filename = isset($extra['assets-db-filename']) ? $extra['assets-db-filename'] : Config::get('assets-db-filename');
            $db_file = $this->getRootDirectory() . '/' . $this->getVendorDirectory() . '/' . $assets_db_filename;
            if (file_exists($db_file)) {
                $json_assets = json_decode(file_get_contents($db_file), true);
                $this
                    ->setAssetsDirectory(
                        !is_null($assets_dir) ? $assets_dir : (
                            isset($json_assets['assets_dir']) ? $json_assets['assets_dir'] : Config::get('assets-dir')
                        )
                    )
                    ->setAssetsVendorDirectory(
                        isset($json_assets['assets_vendor_dir']) ? $json_assets['assets_vendor_dir'] : Config::get('assets-vendor-dir')
                    )
                    ->setDocumentRoot(
                        !is_null($document_root) ? $document_root : (
                            isset($json_assets['document_root']) ? $json_assets['document_root'] : Config::get('document-root')
                        )
                    )
                    ->setAssetsDb(!empty($json_assets['packages']) ? $json_assets['packages'] : array());
            } else {
                throw new \Exception(
                    sprintf('Assets json DB "%s" not found!', $db_file)
                );
            }

            $this->validatePresets();
        } catch (\Exception $e) {
            throw $e;
        }
    }

// ---------------------
// Setters / Getters
// ---------------------

    /**
     * Set the document root directory
     *
     * @param   string  $path   The path of the document root directory
     * @return  self
     * @throws  \InvalidArgumentException if the directory was not found
     */
    public function setDocumentRoot($path)
    {
        if (file_exists($path)) {
            $this->document_root = realpath($path);
        } elseif (file_exists(DirectoryHelper::slashDirname($this->getRootDirectory()) . $path)) {
            $this->document_root = DirectoryHelper::slashDirname($this->getRootDirectory()) . $path;
        } else {
            throw new \InvalidArgumentException(
                sprintf('Document root path "%s" doesn\'t exist!', $path)
            );
        }
        return $this;
    }
    
    /**
     * Get the document root directory
     *
     * @return  string
     */
    public function getDocumentRoot()
    {
        return $this->document_root;
    }

    /**
     * Set the package's assets database
     *
     * @param   array   $db     The array of package's assets as written in package's `ASSETS_DB_FILENAME`
     * @return  self
     */
    public function setAssetsDb(array $db)
    {
        foreach ($db as $package_name=>$package) {
            if (empty($package['path']) && isset($package['relative_path'])) {
                $db[$package_name]['path'] = DirectoryHelper::slashDirname(
                    DirectoryHelper::slashDirname($this->getRootDirectory()) .
                    DirectoryHelper::slashDirname($this->getAssetsDirectory()) .
                    DirectoryHelper::slashDirname($this->getAssetsVendorDirectory()) .
                    $package['relative_path']
                );
            }
        }
        $this->assets_db = $db;
        return $this;
    }
    
    /**
     * Get the package's assets database
     *
     * @return  array
     */
    public function getAssetsDb()
    {
        return $this->assets_db;
    }

// ---------------------
// Global getters
// ---------------------

    /**
     * Get the web path for assets
     *
     * @return  string
     * @see     \AssetsManager\Package\Loader::buildWebPath()
     */
    public function getAssetsWebPath()
    {
        return $this->buildWebPath($this->getAssetsRealPath());
    }
    
    /**
     * Get the assets full path for a specific package
     *
     * @param   string  $package_name   The name of the package to get assets path from
     * @return  string
     */
    public function getPackageAssetsPath($package_name)
    {
        $package = $this->getPackage($package_name);
        return $package->getAssetsPath();
    }
    
    /**
     * Get the web path for assets of a specific package
     *
     * @param   string  $package_name   The name of the package to get assets path from
     * @return  string
     * @see     \AssetsManager\Package\Loader::buildWebPath()
     */
    public function getPackageAssetsWebPath($package_name)
    {
        $package = $this->getPackage($package_name);
        return $this->buildWebPath($package->getAssetsPath());
    }
    
// ---------------------
// Packages manager
// ---------------------

    /**
     * Test if a package exists
     *
     * @param   string $package_name
     * @return  bool
     */
    public function hasPackage($package_name)
    {
        if (!isset($this->packages_instances[$package_name])) {
            try {
                $this->packages_instances[$package_name] = $this->_buildNewPackage($package_name);
            } catch (\Exception $e) {
            }
        }
        return isset($this->packages_instances[$package_name]);
    }
    
    /**
     * Get a package instance
     *
     * @param   string $package_name
     * @return  \AssetsManager\Package\AssetsPackage
     * @throws  \Exception : any caught exception
     * @see     self::_buildNewPackage()
     */
    public function getPackage($package_name)
    {
        if (!isset($this->packages_instances[$package_name])) {
            try {
                $this->packages_instances[$package_name] = $this->_buildNewPackage($package_name);
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return $this->packages_instances[$package_name];
    }
    
    /**
     * Build a new package instance
     *
     * @param   string $package_name
     * @return  \AssetsManager\Package\AssetsPackage
     * @throws  \DomainException if the package class can't be found or doesn't implement required interface
     * @throws  \InvalidArgumentException if the package can't be found
     */
    protected function _buildNewPackage($package_name)
    {
        $package = isset($this->assets_db[$package_name]) ? $this->assets_db[$package_name] : null;
        if (!empty($package)) {
            $cls_name = Config::get('assets-package-class');
            if (@class_exists($cls_name)) {
                $interfaces = class_implements($cls_name);
                $config_interface = Config::getInternal('assets-package-interface');
                if (in_array($config_interface, $interfaces)) {
                    $package_object = $cls_name::createFromAssetsLoader($this);
                    $package_object->loadFromArray($package);
                    return $package_object;
                } else {
                    throw new \DomainException(
                        sprintf('Package class "%s" must implement interface "%s"!',
                            $cls_name, $config_interface)
                    );
                }
            } else {
                throw new \DomainException(
                    sprintf('Package class "%s" not found!', $cls_name)
                );
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf('Unknown package "%s"!', $package_name)
            );
        }
        return null;
    }
    
// ---------------------
// Presets manager
// ---------------------

    /**
     * Load and validate all packages presets in one table
     *
     * @return  void
     * @throws  \Exception if the `$conflict_flag` is set on `self::PRESETS_CONFLICT` in case of duplicate preset name
     */
    public function validatePresets()
    {
        $this->presets_data = array();
        if (!empty($this->assets_db)) {
            foreach ($this->assets_db as $package_name=>$package_data) {
                if (!empty($package_data['assets_presets'])) {
                    foreach ($package_data['assets_presets'] as $preset_name=>$preset_data) {
                        if (array_key_exists($preset_name, $this->presets_data) && ($this->conflict_flag & self::PRESETS_CONFLICT)) {
                            throw new \Exception(
                                sprintf('Presets conflict: duplicate entry named "%s"!', $preset_name)
                            );
                        } else {
                            $this->presets_data[$preset_name] = array(
                                'data'=>$preset_data,
                                'package'=>$package_name
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Test if a preset exists
     *
     * @param   string  $preset_name
     * @return  bool
     */
    public function hasPreset($preset_name)
    {
        return isset($this->presets_data[$preset_name]);
    }
    
    /**
     * Get a preset instance
     *
     * @param   string $preset_name
     * @return  \AssetsManager\Package\Preset
     * @throws  \InvalidArgumentException if the preset can't be found
     * @throws  \Exception : any caught exception
     * @see     self::_buildNewPreset()
     */
    public function getPreset($preset_name)
    {
        if (isset($this->presets_data[$preset_name])) {
            if (!isset($this->presets_data[$preset_name]['instance'])) {
                try {
                    $this->presets_data[$preset_name]['instance'] = $this->_buildNewPreset($preset_name);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            return $this->presets_data[$preset_name]['instance'];
        } else {
            throw new \InvalidArgumentException(
                sprintf('Preset "%s" not found!', $preset_name)
            );
        }
    }
    
    /**
     * Build a new preset instance
     *
     * @param   string $preset_name
     * @return  \AssetsManager\Package\Preset
     * @throws  \DomainException if the preset class can't be found or doesn't implement required interface
     * @throws  \InvalidArgumentException if the preset can't be found
     */
    protected function _buildNewPreset($preset_name)
    {
        $preset = isset($this->presets_data[$preset_name]) ? $this->presets_data[$preset_name]['data'] : null;
        if (!empty($preset)) {
            $package = $this->getPackage($this->presets_data[$preset_name]['package']);
            $cls_name = Config::get('assets-preset-class');
            if (@class_exists($cls_name)) {
                $interfaces = class_implements($cls_name);
                $config_interface = Config::getInternal('assets-preset-interface');
                if (in_array($config_interface, $interfaces)) {
                    $preset_object = new $cls_name(
                        $preset_name, $preset, $package
                    );
                    return $preset_object;
                } else {
                    throw new \DomainException(
                        sprintf('Preset class "%s" must implement interface "%s"!',
                            $cls_name, $config_interface)
                    );
                }
            } else {
                throw new \DomainException(
                    sprintf('Preset class "%s" not found!', $cls_name)
                );
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf('Unknown preset "%s"!', $preset_name)
            );
        }
        return null;
    }
    
// ---------------------
// Static usage
// ---------------------

    /**
     * Get the package's assets database
     *
     * @return  array
     */
    public static function getAssets()
    {
        $_this = self::getInstance();
        return $_this->getAssetsDb();
    }
    
    /**
     * Get a preset instance from static loader
     *
     * @param   string  $preset_name
     * @return  \AssetsManager\Package\Preset
     */
    public static function findPreset($preset_name)
    {
        $_this = self::getInstance();
        return $_this->getPreset($preset_name);
    }
    
    /**
     * Get a package instance from static loader
     *
     * @param   string  $package_name
     * @return  \AssetsManager\Package\AssetsPackage
     */
    public static function findPackage($package_name)
    {
        $_this = self::getInstance();
        return $_this->getPackage($package_name);
    }
    
    /**
     * Build a web path ready to use in HTML
     *
     * This will build a relative path related to the object `$document_root` and ready-to-use
     * in HTML attributes. It uses the "smart resolving" feature of the `Library\Helper\Filesystem`
     * class: path is returned relative to `$document_root` even if it is not in it in the
     * filesystem.
     *
     * @param   string  $path   The path to transform
     * @return  string
     * @see     \Library\Helper\Filesystem::resolveRelatedPath()
     */
    public static function buildWebPath($path)
    {
        $_this = self::getInstance();
        return trim(FilesystemHelper::resolveRelatedPath($_this->getDocumentRoot(), realpath($path)), '/');
    }
    
    /**
     * Find an asset file in the filesystem
     *
     * @param   string  $filename   The asset filename to find
     * @param   string  $package    The name of a package to search in (optional)
     * @return  string|null         The web path of the asset if found, `null` otherwise
     */
    public static function find($filename, $package = null)
    {
        $_this = self::getInstance();
        if (!is_null($package)) {
            return self::findInPackage($filename, $package);
        } else {
            return self::findInPath($filename, $_this->getAssetsRealPath());
        }
    }

    /**
     * Find an asset file in the filesystem of a specific package
     *
     * @param   string  $filename   The asset filename to find
     * @param   string  $package    The name of a package to search in
     * @return  string|null         The web path of the asset if found, `null` otherwise
     */
    public static function findInPackage($filename, $package)
    {
        $_this = self::getInstance();
        $package_path = DirectoryHelper::slashDirname($_this->getPackageAssetsPath($package));
        if (!is_null($package_path)) {
            $asset_path = $package_path . $filename;
            if (file_exists($asset_path)) {
                return self::buildWebPath($asset_path);
            }
        }
        return null;
    }

    /**
     * Find an asset file in a package's path
     *
     * @param   string  $filename   The asset filename to find
     * @param   string  $path       The path to search from
     * @return  string|null         The web path of the asset if found, `null` otherwise
     */
    public static function findInPath($filename, $path)
    {
        $asset_path = DirectoryHelper::slashDirname($path) . $filename;
        if (file_exists($asset_path)) {
            return self::buildWebPath($asset_path);
        }
        return null;
    }

    /**
     * Test if a string is a classic url or an url like `//domain.ext/asset`
     *
     * @param   string $str
     * @return  bool
     */
    public static function isUrl($str)
    {
        return (bool) (UrlHelper::isUrl($str) || UrlHelper::isUrl('http'.$str));
    }
}

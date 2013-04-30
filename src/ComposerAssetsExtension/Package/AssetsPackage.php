<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace ComposerAssetsExtension\Package;

use InvalidArgumentException;

use Library\Helper\Directory as DirectoryHelper;

use ComposerAssetsExtension\Loader as AssetsLoader,
    ComposerAssetsExtension\Package\AbstractAssetsPackage,
    ComposerAssetsExtension\Package\Preset;

/**
 * Cluster
 *
 * This class handles dependencies packages assets from a global root directory.
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class AssetsInstaller
    extends AbstractAssetsPackage
{

    /**
     * Current package name
     * @var string
     */
    protected $name;

    /**
     * Current package version number
     * @var string
     */
    protected $version;

    /**
     * Current package relative path (relative to `$_root_dir`)
     * @var string
     */
    protected $relative_path;

    /**
     * Current package assets paths (relative to `$relative_path`)
     * @var string
     */
    protected $assets_path;

    /**
     * Current package views paths (relative to `$relative_path`)
     * @var array
     */
    protected $views_paths;

    /**
     * Current package views aliases files (relative to `$relative_path`)
     * @var array
     */
    protected $views_functions_paths;

    /**
     * Current package presets
     * @var array
     */
    protected $assets_presets;

    /**
     * Cluster contruction
     *
     * @param string $_root_dir The global package root directory (must exist)
     * @param string $_assets_dir The global package assets directory (must exist in `$_root_dir`)
     * @param string $_vendor_dir The global package vendor directory (must exist in `$_root_dir`)
     * @param string $_assets_vendor_dir The global package assets vendor directory (must exist in `$_assets_dir`)
     */
    public function __construct(
        $_root_dir,
        $_assets_dir = AbstractAssetsPackage::DEFAULT_ASSETS_DIR,
        $_vendor_dir = AbstractAssetsPackage::DEFAULT_VENDOR_DIR,
        $_assets_vendor_dir = AbstractAssetsPackage::DEFAULT_VENDOR_DIR
    ) {
        $this
            ->setRootDirectory($_root_dir)
            ->setAssetsDirectory($_assets_dir)
            ->setVendorDirectory($_vendor_dir)
            ->setAssetsVendorDirectory($_assets_vendor_dir)
            ->reset()
            ;
    }

    /**
     * Create a new Cluster object from an `Assets\Loader` instance
     * @return object
     */
    public static function newAssetsPackageFromAssetsLoader(AssetsLoader $loader)
    {
        return new AssetsPackage(
            $loader->getRootDirectory(),
            $loader->getAssetsDirectory(),
            $loader->getVendorDirectory(),
            $loader->getAssetsVendorDirectory()
        );
    }

    /**
     * Reset the cluster to empty values (except for global package)
     *
     * @return void
     */
    public function reset()
    {
        $this->name                     = null;
        $this->version                  = null;
        $this->relative_path            = null;
        $this->assets_path              = null;
        $this->views_paths              = array();
        $this->views_functions_paths    = array();
        $this->assets_presets           = array();
    }

    /**
     * Reset the cluster when clone
     *
     * @return void
     */
    public function __clone()
    {
        $this->reset();
    }

// -------------------------
// Setters / Getters
// -------------------------

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $version
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $path
     * @return self
     */
    public function setRelativePath($path)
    {
        $this->relative_path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relative_path;
    }

    /**
     * @param string $path
     * @return self
     * @throws `InvalidArgumentException` if the path doesn't exist
     */
    public function setAssetsPath($path)
    {
        $realpath = $this->getFullPath($path);
        if (@file_exists($realpath) && is_dir($realpath)) {
            $this->assets_path = $path;
        } else {
            $relative_path = DirectoryHelper::slashDirname($this->getRelativePath()) . $path;
            $realpath = $this->getFullPath($relative_path);
            if (@file_exists($realpath) && is_dir($realpath)) {
                $this->assets_path = $relative_path;
            } else {
                throw new InvalidArgumentException(
                    sprintf('Assets directory "%s" for cluster "%s" not found !', $path, $this->getName())
                );
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAssetsPath()
    {
        return $this->assets_path;
    }

    /**
     * @param array $paths
     * @param string $type Type of the original relative path (can be `asset` or `vendor` or `null` - default is `vendor`)
     * @return self
     */
    public function setViewsPaths(array $paths, $type = 'vendor')
    {
        foreach ($paths as $path) {
            $this->addViewsPath($path, $type);
        }
        return $this;
    }

    /**
     * @param string $path Relative to `vendor`
     * @param string $type Type of the original relative path (can be `asset` or `vendor` or `null` - default is `vendor`)
     * @return self
     * @throws `InvalidArgumentException` if the path doesn't exist
     */
    public function addViewsPath($path, $type = 'vendor')
    {
        $realpath = $this->getFullPath($path, $type);
        if (@file_exists($realpath) && is_dir($realpath)) {
            if (!in_array($path, $this->views_paths)) {
                $this->views_paths[] = $path;
            }
        } else {
            $relative_path = DirectoryHelper::slashDirname($this->getRelativePath()) . $path;
            $realpath = $this->getFullPath($relative_path, null);
            if (@file_exists($realpath) && is_dir($realpath)) {
                if (!in_array($relative_path, $this->views_paths)) {
                    $this->views_paths[] = $relative_path;
                }
            } else {
                throw new InvalidArgumentException(
                    sprintf('Views path directory "%s" for cluster "%s" not found !', $path, $this->getName())
                );
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getViewsPaths()
    {
        return $this->views_paths;
    }

    /**
     * @param array $paths
     * @param string $type Type of the original relative path (can be `asset` or `vendor` or `null` - default is `vendor`)
     * @return self
     */
    public function setViewsFunctionsPaths(array $paths, $type = 'vendor')
    {
        foreach ($paths as $path) {
            $this->addViewsFunctionsPath($path, $type);
        }
        return $this;
    }

    /**
     * @param string $path Relative to `vendor`
     * @param string $type Type of the original relative path (can be `asset` or `vendor` or `null` - default is `vendor`)
     * @return self
     * @throws `InvalidArgumentException` if the path doesn't exist
     */
    public function addViewsFunctionsPath($path, $type = 'vendor')
    {
        $realpath = $this->getFullPath($path, $type);
        if (@file_exists($realpath) && is_file($realpath)) {
            if (!in_array($path, $this->views_functions_paths)) {
                $this->views_functions_paths[] = $path;
            }
        } else {
            $relative_path = DirectoryHelper::slashDirname($this->getRelativePath()) . $path;
            $realpath = $this->getFullPath($relative_path, null);
            if (@file_exists($realpath) && is_file($realpath)) {
                if (!in_array($relative_path, $this->views_functions_paths)) {
                    $this->views_functions_paths[] = $relative_path;
                }
            } else {
                throw new InvalidArgumentException(
                    sprintf('Views functions file "%s" for cluster "%s" not found !', $path, $this->getName())
                );
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getViewsFunctionsPaths()
    {
        return $this->views_functions_paths;
    }

    /**
     * @param array $presets
     * @return self
     */
    public function setAssetsPresets(array $presets)
    {
        $this->assets_presets = $presets;
        return $this;
    }

    /**
     * @param string $preset_name
     * @param array $preset
     * @return self
     */
    public function addAssetsPreset($preset_name, array $preset)
    {
        $this->assets_presets[$preset_name] = $preset;
        return $this;
    }

    /**
     * @return array
     */
    public function getAssetsPresets()
    {
        return $this->assets_presets;
    }

    /**
     * @param string $preset_name
     * @return array|null
     */
    public function getAssetsPreset($preset_name)
    {
        return isset($this->assets_presets[$preset_name]) ? $this->assets_presets[$preset_name] : null;
    }

// -------------------------
// Utilities
// -------------------------

    /**
     * Get the relative path in the package
     *
     * @param string $path The relative path to complete
     * @return string
     */
    public function getRelativeFullPath($path)
    {
        return DirectoryHelper::slashDirname($this->getRelativePath()) . $path;
    }

// -------------------------
// Autobuilder
// -------------------------

    /**
     * Get all necessary arranged cluster infos as an array
     *
     * This is the data stored in the `Loader\Assets::ASSETS_DB_FILENAME`.
     *
     * @return array
     */    
    public function getArray()
    {
        $cluster = array(
            'name'=>$this->getName(),
            'version'=>$this->getVersion(),
            'relative_path'=>$this->getRelativePath(),
            'assets_path'=>$this->getAssetsPath(),
            'views_path'=>$this->getViewsPaths(),
            'views_functions'=>$this->getViewsFunctionsPaths(),
            'assets_presets'=>$this->getAssetsPresets(),
        );
        return $cluster;
    }

    /**
     * Load a new cluster from the `Loader\Assets::ASSETS_DB_FILENAME` entry
     *
     * @param array
     * @return self
     */
     public function loadFromArray(array $entries)
     {
        foreach ($entries as $var=>$val) {
            switch ($var) {
                case 'name': $this->setName($val); break;
                case 'version': $this->setVersion($val); break;
                case 'relative_path': $this->setRelativePath($val); break;
                case 'assets_path': $this->setAssetsPath($val); break;
                case 'views_path': $this->setViewsPaths(is_array($val) ? $val : array($val), null); break;
                case 'views_functions': $this->setViewsFunctionsPaths(is_array($val) ? $val : array($val), null); break;
                case 'assets_presets': $this->setAssetsPresets($val); break;
            }
        }
        return $this;
     }

    /**
     * Parse the `composer.json` "extra" block of a package and return its transformed data
     *
     * @param array $package The package, Composer\Package\PackageInterface
     * @param object $installer Assets\ComposerInstaller
     * @param bool $main_package Is this the global package
     * @return void
     */
    public function parseComposerExtra(\Composer\Package\PackageInterface $package, \Assets\ComposerInstaller $installer, $main_package = false)
    {
        $this->reset();
        $extra = $package->getExtra();
        if (!empty($extra) && isset($extra['assets'])) {
            $this->setVersion($package->getVersion());
            $this->setName($package->getPrettyName());
            $package_dir = $main_package ? '' : 
                str_replace(
                    DirectoryHelper::slashDirname($this->getRootDirectory()) .
                    DirectoryHelper::slashDirname($this->getAssetsDirectory()) .
                    DirectoryHelper::slashDirname($this->getAssetsVendorDirectory()),
                    '',
                    $installer->getInstallPath($package)
                );
            $this->setRelativePath($package_dir);
            $this->setAssetsPath($main_package ? '' : $extra['assets']);
            if (isset($extra['views'])) {
                $this->setViewsPaths(
                    is_array($extra['views']) ? $extra['views'] : array($extra['views']),
                    $main_package ? null : 'vendor'
                );
            }
            if (isset($extra['views_functions'])) {
                $this->setViewsFunctionsPaths(
                    is_array($extra['views_functions']) ? $extra['views_functions'] : array($extra['views_functions']),
                    $main_package ? null : 'vendor'
                );
            }
            if (isset($extra['assets_presets'])) {
                foreach ($extra['assets_presets'] as $index=>$item) {
                    $use_item = array();
                    foreach (Preset::$use_statements as $statement) {
                        if (isset($item[$statement])) {
                            $item_statement = is_array($item[$statement]) ?
                                $item[$statement] : array($item[$statement]);
                            $use_item[$statement] = array();
                            foreach ($item_statement as $path) {
                                $use_item[$statement][] = $path;
                            }
                        }
                        if (!empty($use_item)) {
                            $this->addAssetsPreset($index, $use_item);
                        }
                    }
                }
            }
        }
        return $this->getArray();
    }
    
}

// Endfile
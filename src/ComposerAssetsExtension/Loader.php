<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace ComposerAssetsExtension;

use ComposerAssetsExtension\Package\AbstractAssetsPackage,
    ComposerAssetsExtension\Package\AssetsPackage,
    ComposerAssetsExtension\Util\Filesystem;

use Library\Helper\Directory as DirectoryHelper,
    Library\Helper\Filesystem as FilesystemHelper;

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
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Loader extends AbstractAssetsPackage
{

    /**
     * The document root path (absolute - used to build assets web path - no trailing slash)
     * @var string
     */
    protected $document_root;

    /**
     * Project assets DB array
     *
     * This is retrieved parsing the package's `ASSETS_DB_FILENAME`.
     * @var array
     */
    protected $assets_db;

// ---------------------
// Construction
// ---------------------

    /**
     * Loader constructor
     *
     * @param string $root_dir The project package root directory
     * @param string $assets_dir The project package assets directory, related from `$root_dir`
     * @param string $document_root The project assets root directory to build web accessible assets paths
     * @throws Throws an Excpetion if the package's `ASSETS_DB_FILENAME` was not found
     */
    public function __construct($root_dir = null, $assets_dir = null, $document_root = null)
    {
        $this->setRootDirectory(!is_null($root_dir) ? $root_dir : __DIR__.'/../../../../../');

        $composer = $this->getRootDirectory() . '/composer.json';
        $vendor_dir = AbstractAssetsPackage::DEFAULT_VENDOR_DIR;
        if (file_exists($composer)) {
            $package = json_decode(file_get_contents($composer), true);
            if (isset($package['config']) && isset($package['config']['vendor-dir'])) {
                $vendor_dir = $package['config']['vendor-dir'];
            }
        }
        $this->setVendorDirectory($vendor_dir);

        $db_file = $this->getRootDirectory() . '/' . $this->getVendorDirectory() . '/' . AbstractAssetsPackage::ASSETS_DB_FILENAME;
        if (file_exists($db_file)) {
            $json_db = json_decode(file_get_contents($db_file), true);
            $this
                ->setAssetsDirectory(
                    !is_null($assets_dir) ? $assets_dir : (
                        isset($json_db['assets_dir']) ? $json_db['assets_dir'] : AbstractAssetsPackage::DEFAULT_ASSETS_DIR
                    )
                )
                ->setAssetsVendorDirectory(
                    isset($json_db['assets_vendor_dir']) ? $json_db['assets_vendor_dir'] : AbstractAssetsPackage::DEFAULT_ASSETS_VENDOR_DIR
                )
                ->setDocumentRoot(!is_null($document_root) ? $document_root : $json_db['document_root'])
                ->setAssetsDb($json_db['packages']);
        } else {
            throw new \Exception(
                sprintf('Assets json DB "%s" not found!', $db_file)
            );
        }
    }

// ---------------------
// Setters / Getters
// ---------------------

    /**
     * Sets the document root directory
     *
     * @param string $path The path of the document root directory
     * @return self Returns `$this` for chainability
     * @throws Throws an InvalidArgumentException if the directory was not found
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
     * Gets the document root directory
     *
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->document_root;
    }

    /**
     * Sets the package's assets database
     *
     * @param array $db The array of package's assets as written in package's `ASSETS_DB_FILENAME`
     * @return self Returns `$this` for chainability
     */
    public function setAssetsDb(array $db)
    {
        foreach ($db as $package_name=>$package) {
            if (empty($package['path'])) {
                $db[$package_name]['path'] = DirectoryHelper::slashDirname(
                    DirectoryHelper::slashDirname($this->getRootDirectory()) .
                    DirectoryHelper::slashDirname($this->getAssetsDirectory()) .
                    $package['relative_path']
                );
            }
        }
        $this->assets_db = $db;
        return $this;
    }
    
    /**
     * Gets the package's assets database
     *
     * @return array
     */
    public function getAssetsDb()
    {
        return $this->assets_db;
    }

// ---------------------
// Global getters
// ---------------------

    /**
     * Build a web path ready to use in HTML
     *
     * This will build a relative path related to the object `$document_root` and ready-to-use
     * in HTML attributes. It uses the "smart resolving" feature of the `Library\Helper\Filesystem`
     * class: path is returned relative to `$document_root` even if it is not in it in the
     * filesystem.
     *
     * @param string $path The path to transform
     * @return string
     * @see Library\Helper\Filesystem::resolveRelatedPath()
     */
    public function buildWebPath($path)
    {
        return trim(FilesystemHelper::resolveRelatedPath($this->getDocumentRoot(), realpath($path)), '/');
    }
    
    /**
     * Get the assets full path for a specific package
     *
     * @param string $package_name The name of the package to get assets path from
     * @return string
     */
    public function getPackageAssetsPath($package_name)
    {
        return isset($this->assets_db[$package_name]) ? $this->assets_db[$package_name]['path'] : null;
    }
    
    /**
     * Gets the package's assets database (alias of `getAssetsDb()`)
     *
     * @return array
     * @see Assets\Loader::getAssetsDb()
     */
    public function getAssets()
    {
        return $this->getAssetsDb();
    }
    
    /**
     * Gets the web path for assets
     *
     * @return string
     * @see Assets\Loader::buildWebPath()
     */
    public function getAssetsWebPath()
    {
        return $this->buildWebPath($this->getAssetsRealPath());
    }
    
    /**
     * Gets the web path for assets of a specific package
     *
     * @param string $package_name The name of the package to get assets path from
     * @return string
     * @see Assets\Loader::buildWebPath()
     */
    public function getPackageAssetsWebPath($package_name)
    {
        return isset($this->assets_db[$package_name]) ? $this->buildWebPath($this->assets_db[$package_name]['path']) : null;
    }
    
// ---------------------
// Assets finder
// ---------------------

    /**
     * Find an asset file in the filesystem
     *
     * @param string $filename The asset filename to find
     * @param string $package The name of a package to search in (optional)
     * @return string|null The web path of the asset if found, `null` otherwise
     */
    public function find($filename, $package = null)
    {
        if (!is_null($package)) {
            return $this->findInPackage($filename, $package);
        } else {
            return $this->findInPath($filename, $this->getAssetsRealPath());
        }
    }

    /**
     * Find an asset file in the filesystem of a specific package
     *
     * @param string $filename The asset filename to find
     * @param string $package The name of a package to search in
     * @return string|null The web path of the asset if found, `null` otherwise
     */
    public function findInPackage($filename, $package)
    {
        $package_path = DirectoryHelper::slashDirname($this->getPackageAssetsPath($package));
        if (!is_null($package_path)) {
            $asset_path = $package_path . $filename;
            if (file_exists($asset_path)) {
                return $this->buildWebPath($asset_path);
            }
        }
        return null;
    }

    /**
     * Find an asset file in a package's path
     *
     * @param string $filename The asset filename to find
     * @param string $path The path to search from
     * @return string|null The web path of the asset if found, `null` otherwise
     */
    public function findInPath($filename, $path)
    {
        $asset_path = DirectoryHelper::slashDirname($path) . $filename;
        if (file_exists($asset_path)) {
            return $this->buildWebPath($asset_path);
        }
        return null;
    }

}

// Endfile
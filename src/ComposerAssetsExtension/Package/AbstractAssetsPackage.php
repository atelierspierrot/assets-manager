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

use Library\Helper\Filesystem as FilesystemHelper,
    Library\Helper\Directory as DirectoryHelper;

@define('_SERVER_DOCROOT', $_SERVER['DOCUMENT_ROOT']);

/**
 * Class to manage assets paths
 *
 * The class is based on three paths:
 * 
 * - `base_dir`: the package root directory (must be the directory containing the `composer.json` file)
 * - `assets_dir`: the package asssets directory related to `base_dir`
 * - `document_root`: the path in the filesystem of the web assets root directory ; this is used
 * to build all related assets paths to use in HTTP.
 *
 * For these three paths, their defaults values are defined on a default package structure:
 *
 *     package_name/
 *     |----------- src/
 *     |----------- www/
 *
 *     $loader->base_dir = realpath(package_name)
 *     $loader->assets_dir = www
 *     $loader->document_root = www or the server DOCUMENT_ROOT
 *
 * NOTE - These paths are stored in the object without the trailing slash.
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
abstract class AbstractAssetsPackage
{

    /**
     * The default package type handles by the installer
     */
    const DEFAULT_PACKAGE_TYPE = 'library-assets';

    /**
     * The default package vendor directory name (related to package root dir)
     */
    const DEFAULT_VENDOR_DIR = 'vendor';

    /**
     * The default package assets directory name (related to package root dir)
     */
    const DEFAULT_ASSETS_DIR = 'www';

    /**
     * The default third-party packages'assets directory name (related to package assets dir)
     */
    const DEFAULT_ASSETS_VENDOR_DIR = 'vendor';

    /**
     * The default package root directory is set on `$_SERVER['DOCUMENT_ROOT']`
     */
    const DEFAULT_DOCUMENT_ROOT = _SERVER_DOCROOT;

    /**
     * The assets database file created on install
     */
    const ASSETS_DB_FILENAME = 'assets.json';

    /**
     * Project root directory (absolute - no trailing slash)
     * @var string
     */
    protected $_root_dir;

    /**
     * Project assets directory (relative to `$_root_dir` - no trailing slash)
     * @var string
     */
    protected $_assets_dir;

    /**
     * Project vendor directory (relative to `$_root_dir` - no trailing slash)
     * @var string
     */
    protected $_vendor_dir;

    /**
     * Project third-party packages'assets directory (relative to `$_assets_dir`)
     * @var string
     */
    protected $_assets_vendor_dir;

// ---------------------
// Construction
// ---------------------

    /**
     * @param string $root_dir
     * @param string $assets_dir
     * @param string $vendor_dir
     * @param string $assets_vendor_dir
     */
    public function __construct($root_dir = null, $assets_dir = null, $vendor_dir = null, $assets_vendor_dir = null)
    {
        if (!empty($root_dir)) {
            $this->setRootDirectory($root_dir);
            if (!empty($assets_dir)) $this->setAssetsDirectory($assets_dir);
            if (!empty($vendor_dir)) $this->setVendorDirectory($vendor_dir);
            if (!empty($assets_vendor_dir)) $this->setAssetsVendorDirectory($assets_vendor_dir);
        }
    }

// ---------------------
// Setters / Getters
// ---------------------

    /**
     * Set the project root directory absolute path
     *
     * @param string $path
     * @return self
     * @throws `InvalidArgumentException` if the path doesn't exist
     */
    public function setRootDirectory($path)
    {
        if (@file_exists($path) && is_dir($path)) {
            $this->_root_dir = $path;
        } else {
            throw new InvalidArgumentException(
                sprintf('Root package directory "%s" not found !', $path)
            );
        }
        return $this;
    }

    /**
     * Get the project root directory absolute path
     *
     * @return string
     */
    public function getRootDirectory()
    {
        return $this->_root_dir;
    }

    /**
     * Set the project's assets directory, relative to `$this->_root_dir`
     *
     * @param string $path
     * @return self
     * @throws `InvalidArgumentException` if the path doesn't exist
     */
    public function setAssetsDirectory($path)
    {
        $realpath = $this->getFullPath($path, null, true);
        if (@file_exists($realpath) && is_dir($realpath)) {
            $this->_assets_dir = $path;
        } else {
            throw new InvalidArgumentException(
                sprintf('Assets directory "%s" not found !', $path)
            );
        }
        return $this;
    }

    /**
     * Get the project's assets directory, relative to `$this->_root_dir`
     *
     * @return string
     */
    public function getAssetsDirectory()
    {
        return $this->_assets_dir;
    }

    /**
     * Set the project's vendor directory, relative to `$this->_root_dir`
     *
     * @param string $path
     * @return self
     * @throws `InvalidArgumentException` if the path doesn't exist
     */
    public function setVendorDirectory($path)
    {
        $realpath = $this->getFullPath($path, null, true);
        if (@file_exists($realpath) && is_dir($realpath)) {
            $this->_vendor_dir = $path;
        } else {
            throw new InvalidArgumentException(
                sprintf('Vendor directory "%s" not found !', $path)
            );
        }
        return $this;
    }

    /**
     * Get the project's vendor directory, relative to `$this->_root_dir`
     *
     * @return string
     */
    public function getVendorDirectory()
    {
        return $this->_vendor_dir;
    }

    /**
     * Set the project's assets vendor directory, relative to `$this->_assets_dir`
     *
     * @param string $path
     * @return self
     * @throws `InvalidArgumentException` if the path doesn't exist
     */
    public function setAssetsVendorDirectory($path)
    {
        $realpath = $this->getFullPath($path, 'assets', true);
        if (@file_exists($realpath) && is_dir($realpath)) {
            $this->_assets_vendor_dir = $path;
        } else {
            throw new InvalidArgumentException(
                sprintf('Assets vendor directory "%s" not found !', $path)
            );
        }
        return $this;
    }

    /**
     * Get the project's vendor directory, relative to `$this->_root_dir`
     *
     * @return string
     */
    public function getAssetsVendorDirectory()
    {
        return $this->_assets_vendor_dir;
    }

// ---------------------
// Global getters
// ---------------------

    /**
     * Get the absolute path in the package
     *
     * @param string $path The relative path to complete
     * @param string $type Type of the original relative path (can be `asset`, `vendor` or `assets_vendor` - default is `null`)
     * @param bool $out Must we search in `assets` and `vendor` (if `false`) or not (if `true`)
     * @return string
     */
    public function getFullPath($path, $type = null, $out = false)
    {
        $base = DirectoryHelper::slashDirname($this->getRootDirectory());
        if (in_array($type, array('asset', 'assets'))) {
            $base .= DirectoryHelper::slashDirname($this->getAssetsDirectory());
        } elseif ($type==='vendor') {
            $base .= DirectoryHelper::slashDirname($this->getVendorDirectory());
        } elseif ($type==='assets_vendor') {
            $base .= DirectoryHelper::slashDirname($this->getAssetsDirectory())
                . DirectoryHelper::slashDirname($this->getAssetsVendorDirectory());
        }
        $f = $base . $path;
        if (@file_exists($f)) {
            return $f;
        }
        if ($out) {
            return null;
        }
        if (!in_array($type, array('asset', 'assets'))) {
            $f = $this->getFullPath($path, 'asset', true);
            if (@file_exists($f)) {
                return $f;
            }
        }
        if ($type!=='vendor') {
            $f = $this->getFullPath($path, 'vendor', true);
            if (@file_exists($f)) {
                return $f;
            }
        }
    }

    /**
     * Get the assets full path
     *
     * @return string
     */
    public function getAssetsRealPath()
    {
        return DirectoryHelper::slashDirname($this->getRootDirectory()) . $this->getAssetsDirectory();
    }
    
    /**
     * Get the assets full path
     *
     * @return string
     */
    public function getVendorRealPath()
    {
        return DirectoryHelper::slashDirname($this->getRootDirectory()) . $this->getVendorDirectory();
    }
    
    /**
     * Get the assets vendor full path
     *
     * @return string
     */
    public function getAssetsVendorRealPath()
    {
        return DirectoryHelper::slashDirname($this->getAssetsRealPath()) . $this->getAssetsVendorDirectory();
    }
    
}

// Endfile
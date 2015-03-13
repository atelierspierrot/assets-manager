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

namespace AssetsManager\Package;

use \InvalidArgumentException;
use \Library\Helper\Directory as DirectoryHelper;

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
 * @author  piwi <me@e-piwi.fr>
 */
abstract class AbstractPackage
{

    /**
     * @var string Project root directory (absolute - no trailing slash)
     */
    protected $_root_dir;

    /**
     * @var string Project assets directory (relative to `$_root_dir` - no trailing slash)
     */
    protected $_assets_dir;

    /**
     * @var string Project vendor directory (relative to `$_root_dir` - no trailing slash)
     */
    protected $_vendor_dir;

    /**
     * @var string Project third-party packages'assets directory (relative to `$_assets_dir`)
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
     * @throws \Exception : any caught exception
     */
    public function __construct(
        $root_dir = null, $assets_dir = null, $vendor_dir = null, $assets_vendor_dir = null
    ) {
        if (!empty($root_dir)) {
            try {
                $this->setRootDirectory($root_dir);
                if (!empty($assets_dir)) $this->setAssetsDirectory($assets_dir);
                if (!empty($vendor_dir)) $this->setVendorDirectory($vendor_dir);
                if (!empty($assets_vendor_dir)) $this->setAssetsVendorDirectory($assets_vendor_dir);
            } catch (\Exception $e) {
                throw $e;
            }
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
     * @throws \InvalidArgumentException if the path doesn't exist
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
     * @param   string $path
     * @return  self
     * @throws  \InvalidArgumentException if the path doesn't exist
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
     * @param   string $path
     * @return  self
     * @throws  \InvalidArgumentException if the path doesn't exist
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
     * @param   string $path
     * @return  self
     * @throws  \InvalidArgumentException if the path doesn't exist
     */
    public function setAssetsVendorDirectory($path)
    {
        $realpath = $this->getFullPath($path, 'assets', true);
        if (@file_exists($realpath) && is_dir($realpath)) {
            $this->_assets_vendor_dir = $path;
        } else {
            throw new \InvalidArgumentException(
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
        if (@file_exists($path)) {
            return realpath($path);
        }
        
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
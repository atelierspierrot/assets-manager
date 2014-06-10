<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Package;

/**
 * AssetsPackageInterface
 *
 * Any Assets Package class must implement this interface methods.
 *
 * @author 		Piero Wbmstr <me@e-piwi.fr>
 */
interface AssetsPackageInterface
{

    /**
     * @param string $_root_dir The global package root directory (must exist)
     * @param string $_assets_dir The global package assets directory (must exist in `$_root_dir`)
     * @param string $_vendor_dir The global package vendor directory (must exist in `$_root_dir`)
     * @param string $_assets_vendor_dir The global package assets vendor directory (must exist in `$_assets_dir`)
     */
    public function __construct($_root_dir, $_assets_dir = null, $_vendor_dir = null, $_assets_vendor_dir = null);

    /**
     * Create a new instance from an `AssetsManager\Loader` instance
     * @param object AssetsManager\Loader
     * @return object
     */
    public static function createFromAssetsLoader(\AssetsManager\Loader $loader);

    /**
     * Load a new package from the `ASSETS_DB_FILENAME` entry
     *
     * @param array
     * @return self
     */
     public function loadFromArray(array $entries);

}

// Endfile
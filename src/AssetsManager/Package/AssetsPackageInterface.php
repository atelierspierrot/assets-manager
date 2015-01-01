<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (ↄ) 2013-2015 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
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
 */

namespace AssetsManager\Package;

/**
 * AssetsPackageInterface
 *
 * Any Assets Package class must implement this interface methods.
 *
 * @author  Piero Wbmstr <me@e-piwi.fr>
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
     * @param \AssetsManager\Loader $loader
     * @return object
     */
    public static function createFromAssetsLoader(\AssetsManager\Loader $loader);

    /**
     * Load a new package from the `ASSETS_DB_FILENAME` entry
     * @param array
     * @return self
     */
     public function loadFromArray(array $entries);

}

// Endfile
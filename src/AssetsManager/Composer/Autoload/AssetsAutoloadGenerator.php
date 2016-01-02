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

namespace AssetsManager\Composer\Autoload;

use \AssetsManager\Composer\Autoload\AbstractAssetsAutoloadGenerator;
use \AssetsManager\Composer\Installer\AssetsInstaller;
use \Composer\Package\PackageInterface;
use \Composer\Json\JsonFile;

/**
 * @author  piwi <me@e-piwi.fr>
 */
class AssetsAutoloadGenerator
    extends AbstractAssetsAutoloadGenerator
{

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        /*
// why this ever exists ??
        $assets_db = $this->readJsonDatabase();
        if (!empty($assets_db) && isset($assets_db['packages'])) {
            if (empty($this->assets_db)) {
                $this->assets_db = array();
            }
            $this->assets_db = array_merge($this->assets_db, $assets_db['packages']);
        }    
*/
        $app_base_path = $this->assets_installer->getAppBasePath();
        $assets_dir = str_replace($app_base_path . '/', '', $this->assets_installer->getAssetsDir());
        $assets_vendor_dir = str_replace($app_base_path . '/' . $assets_dir . '/', '', $this->assets_installer->getAssetsVendorDir());
        $full_db = array(
            'assets-dir' => $assets_dir,
            'assets-vendor-dir' => $assets_vendor_dir,
            'document-root' => $this->assets_installer->getDocumentRoot(),
            'packages' => $this->assets_db
        );
        return $this->writeJsonDatabase($full_db);
    }
    
    /**
     * {@inheritDoc}
     */
    protected function addPackage(PackageInterface $package, $target)
    {
        $this->assets_db[$package->getPrettyName()] = $this->assets_installer->parseComposerExtra($package, $target);
    }

    /**
     * {@inheritDoc}
     */
    protected function removePackage(PackageInterface $package)
    {
        unset($this->assets_db[$package->getPrettyName()]);
    }
}

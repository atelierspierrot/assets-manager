<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Composer\Autoload;

use \AssetsManager\Composer\Autoload\AbstractAssetsAutoloadGenerator,
    \AssetsManager\Composer\Installer\AssetsInstaller;

use \Composer\Package\PackageInterface,
    \Composer\Json\JsonFile;

/**
 * @author 		Piero Wbmstr <me@e-piwi.fr>
 */
class AssetsAutoloadGenerator
    extends AbstractAssetsAutoloadGenerator
{

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        $assets_db = $this->readJsonDatabase();
        if (!empty($assets_db) && isset($assets_db['packages'])) {
            if (empty($this->assets_db)) {
                $this->assets_db = array();
            }
            $this->assets_db = array_merge($this->assets_db, $assets_db['packages']);
        }    
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

// Endfile
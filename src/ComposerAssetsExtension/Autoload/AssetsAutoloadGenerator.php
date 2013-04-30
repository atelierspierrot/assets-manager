<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace ComposerAssetsExtension\Autoload;

use ComposerAssetsExtension\Installer\AssetsInstaller;

use Composer\Json\JsonFile;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class AssetsAutoloadGenerator
{

    protected $assets_installer;

    public function __construct(AssetsInstaller $installer)
    {
        $this->assets_installer = $installer;
    }

    protected function _getAssetsDbPath()
    {
        return ($this->assets_installer->vendorDir ? $this->assets_installer->vendorDir.'/' : '') . $this->assets_installer->assetsDbFilename;
    }

    public function generate()
    {
        $assets_file = $this->_getAssetsDbPath();
        $assets_db = $this->assets_installer->getAssetsDb();
        
        $full_db = array(
            'assets_dir' => $this->assets_installer->assetsDir,
            'assets_vendor_dir' => $this->assets_installer->assetsVendorDir,
            'document_root' => $this->assets_installer->documentRoot,
            'packages' => $assets_db
        );

        try {
            $json = new JsonFile($assets_file);
            $json->write($full_db);
            return $assets_file;
        } catch(\Exception $e) {
            if (file_put_contents($assets_file, json_encode($full_db, version_compare(PHP_VERSION, '5.4')>0 ? JSON_PRETTY_PRINT : 0))) {
                return $assets_file;
            }
        }        
        return false;
    }
    
}

// Endfile
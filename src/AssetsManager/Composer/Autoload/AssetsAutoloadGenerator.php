<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager\Composer\Autoload;

use AssetsManager\Composer\Installer\AssetsInstaller;

use Composer\Package\PackageInterface,
    Composer\Json\JsonFile;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class AssetsAutoloadGenerator
{

    protected $assets_installer;
    protected $assets_db;
    private static $_instance;

    public static function getInstance(AssetsInstaller $installer)
    {
        if (empty(self::$_instance)) {
            $cls = __CLASS__;
            self::$_instance = new $cls($installer);
        }
        return self::$_instance;
    }

    public function __construct(AssetsInstaller $installer)
    {
        $this->assets_installer = $installer;
        $this->assets_db = array();
    }

    public function __destruct()
    {
        $this->generate();
    }

    public function generate()
    {
        $app_base_path = $this->assets_installer->getAppBasePath();
        $assets_dir = str_replace($app_base_path . '/', '', $this->assets_installer->getAssetsDir());
        $assets_vendor_dir = str_replace($app_base_path . '/' . $assets_dir . '/', '', $this->assets_installer->getAssetsVendorDir());
        $full_db = array(
            'assets-dir' => $assets_dir,
            'assets-vendor-dir' => $assets_vendor_dir,
            'document-root' => $this->assets_installer->getDocumentRoot(),
            'packages' => $this->assets_db
        );

        $assets_file = $this->assets_installer->getVendorDir() . '/' . $this->assets_installer->getAssetsDbFilename();
        $this->assets_installer->getIo()->write( 
            sprintf('Writing assets json DB to <info>%s</info>',
                str_replace(dirname($this->assets_installer->getVendorDir()).'/', '', $assets_file)
            )
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
    
    public static function registerPackage(PackageInterface $package, $target, AssetsInstaller $installer)
    {
        $_this = self::getInstance($installer);
        $_this->assets_db[$package->getPrettyName()] = $_this->assets_installer->parseComposerExtra($package, $target);
    }

    public static function unregisterPackage(PackageInterface $package, AssetsInstaller $installer)
    {
        $_this = self::getInstance($installer);
        unset($_this->assets_db[$package->getPrettyName()]);
    }

}

// Endfile
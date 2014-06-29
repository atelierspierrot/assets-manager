<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Composer\Autoload;

use \Composer\Composer;
use \Composer\IO\IOInterface;
use \Composer\Autoload\AutoloadGenerator;
use \Composer\Package\PackageInterface;
use \Composer\Repository\RepositoryInterface;
use \Composer\Script\Event;
use \Composer\Script\EventDispatcher;
use \Library\Helper\Directory as DirectoryHelper;
use \AssetsManager\Config;
use \AssetsManager\Composer\Util\Filesystem;
use \AssetsManager\Composer\Installer\AssetsInstaller;
use \AssetsManager\Composer\Autoload\AssetsAutoloadGenerator;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class DumpAutoloadEventHandler
    extends AutoloadGenerator
{

    protected $_composer;
    protected $_autoloader;
    protected $_package;

    /**
     * @param \Composer\Package\PackageInterface $package
     * @param \Composer\Composer $composer
     */
    public function __construct(PackageInterface $package, Composer $composer)
    {
        parent::__construct($composer->getEventDispatcher());
        $this->_composer = $composer;
        $this->_autoloader = AssetsAutoloadGenerator::getInstance();
        $this->_autoloader->setGenerator(array($this, 'generate'));
        $this->_package = $package;
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        $full_db = $this->getFullDb();
        return $this->_autoloader->writeJsonDatabase($full_db);
    }

    /**
     * Build the complete database array
     *
     * @return array
     */
    public function getFullDb()
    {
        $filesystem         = new Filesystem();
        $config             = $this->_composer->getConfig();
        $assets_db          = $this->_autoloader->getRegistry();
        $vendor_dir         = $this->_autoloader->getAssetsInstaller()->getVendorDir();
        $app_base_path      = $this->_autoloader->getAssetsInstaller()->getAppBasePath();
        $assets_dir         = str_replace($app_base_path . '/', '', $this->_autoloader->getAssetsInstaller()->getAssetsDir());
        $assets_vendor_dir  = str_replace($app_base_path . '/' . $assets_dir . '/', '', $this->_autoloader->getAssetsInstaller()->getAssetsVendorDir());
        $document_root      = $this->_autoloader->getAssetsInstaller()->getDocumentRoot();
        $cahe_dir           = $this->_autoloader->getAssetsInstaller()->getCacheDir();
        $extra              = $this->_package->getExtra();

        $root_data          = $this->_autoloader->getAssetsInstaller()->parseComposerExtra($this->_package, $app_base_path, '');
        if (!empty($root_data)) {
            $root_data['relative_path'] = '../';
            $assets_db[$this->_package->getPrettyName()] = $root_data;
        }

        $vendor_path        = strtr(realpath($vendor_dir), '\\', '/');
        $rel_vendor_path    = $filesystem->findShortestPath(getcwd(), $vendor_path, true);

        $local_repo         = $this->_composer->getRepositoryManager()->getLocalRepository();
        $package_map        = $this->buildPackageMap($this->_composer->getInstallationManager(), $this->_package, $local_repo->getPackages());

        foreach ($package_map as $i=>$package) {
            if ($i===0) { continue; }
            $package_object = $package[0];
            $package_install_path = $package[1];
            if (empty($package_install_path)) {
                $package_install_path = $app_base_path;
            }
            $package_name = $package_object->getPrettyName();
            $data = $this->_autoloader->getAssetsInstaller()->parseComposerExtra(
                $package_object,
                $this->_autoloader->getAssetsInstaller()->getAssetsInstallPath($package_object),
                str_replace($app_base_path . '/', '', $vendor_path) . '/' . $package_object->getPrettyName()
            );
            if (!empty($data)) {
                $assets_db[$package_name] = $data;
            }
        }

        $full_db = array(
            'assets-dir' => $assets_dir,
            'assets-vendor-dir' => $assets_vendor_dir,
            'document-root' => $document_root,
            'cache-dir' => $cahe_dir,
            'packages' => $assets_db
        );
        return $full_db;
    }

}

// Endfile
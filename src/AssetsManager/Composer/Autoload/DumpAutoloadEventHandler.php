<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager\Composer\Autoload;

use \Composer\Composer,
    \Composer\IO\IOInterface,
    \Composer\Autoload\AutoloadGenerator,
    \Composer\Package\PackageInterface,
    \Composer\Repository\RepositoryInterface,
    \Composer\Script\Event,
    \Composer\Script\EventDispatcher;

use \Library\Helper\Directory as DirectoryHelper;

use \AssetsManager\Config,
    \AssetsManager\Composer\Util\Filesystem,
    \AssetsManager\Composer\Installer\AssetsInstaller,
    \AssetsManager\Composer\Autoload\AssetsAutoloadGenerator;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class DumpAutoloadEventHandler
    extends AutoloadGenerator
{

    protected $_composer;
    protected $_autoloader;
    protected $_package;

    /**
     * @param object $package Composer\Package\PackageInterface
     * @param object $composer Composer\Composer
     * @return void
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
     * @return array
     */
    public function getFullDb()
    {
        $filesystem = new Filesystem();
        $config = $this->_composer->getConfig();
        $assets_db = $this->_autoloader->getRegistry();
        $vendor_dir = $this->_autoloader->getAssetsInstaller()->getVendorDir();
        $app_base_path = $this->_autoloader->getAssetsInstaller()->getAppBasePath();
        $assets_dir = str_replace($app_base_path . '/', '', $this->_autoloader->getAssetsInstaller()->getAssetsDir());
        $assets_vendor_dir = str_replace($app_base_path . '/' . $assets_dir . '/', '', $this->_autoloader->getAssetsInstaller()->getAssetsVendorDir());
        $document_root = $this->_autoloader->getAssetsInstaller()->getDocumentRoot();
        $extra = $this->_package->getExtra();

        $root_data = $this->_autoloader->getAssetsInstaller()->parseComposerExtra($this->_package, $app_base_path, '');
        if (!empty($root_data)) {
            $root_data['relative_path'] = '../';
            $assets_db[$this->_package->getPrettyName()] = $root_data;
        }

        $vendor_path = strtr(realpath($vendor_dir), '\\', '/');
        $rel_vendor_path = $filesystem->findShortestPath(getcwd(), $vendor_path, true);

        $local_repo = $this->_composer->getRepositoryManager()->getLocalRepository();
        $package_map = $this->buildPackageMap($this->_composer->getInstallationManager(), $this->_package, $local_repo->getPackages());

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
            'packages' => $assets_db
        );
        return $full_db;
    }

}

// Endfile
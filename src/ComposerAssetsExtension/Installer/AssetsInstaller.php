<?php
/**
 * CarteBlanche - PHP framework package - Installers package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace ComposerAssetsExtension\Installer;

use Composer\Composer,
    Composer\IO\IOInterface,
    Composer\Package\PackageInterface,
    Composer\Repository\InstalledRepositoryInterface,
    Composer\Installer\LibraryInstaller;

use ComposerAssetsExtension\Package\AbstractAssetsPackage,
    ComposerAssetsExtension\Util\Filesystem as AssetsFilesystem;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class AssetsInstaller extends LibraryInstaller
{

    protected $assetsDir;
    protected $assetsVendorDir;

    /**
     * Initializes installer: creation of "assets-dir" directory if so.
     *
     * {@inheritDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library')
    {
        parent::__construct($io, $composer, $type);

        $this->filesystem = new AssetsFilesystem();
        $this->assetsDir = $this->getAssetsDir($composer->getPackage());
        $this->assetsVendorDir = $this->getAssetsVendorDir($composer->getPackage());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === AbstractAssetsPackage::DEFAULT_PACKAGE_TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $parent = parent::isInstalled($repo, $package);
        if (!$parent) return $parent;
        return file_exists($this->getAssetsInstallPath($package)) && is_readable($this->getAssetsInstallPath($package));
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $this->installAssets($package);
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $this->removeAssets($package);
        parent::update($repo, $package);
        $this->installAssets($package);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->removeAssets($package);
        parent::uninstall($repo, $package);
    }

    /**
     * Move the assets of a package
     *
     * @param object $package Composer\Package\PackageInterface
     * @return bool
     */
    protected function installAssets(PackageInterface $package)
    {
        $assets = $this->getAssetsDir($package);
        if (!$assets) {
            return;
        }

        $from = $this->getPackageAssetsBasePath($package) . '/' . $assets;
        $target = $this->getAssetsInstallPath($package);
        if (file_exists($from)) {
            $this->io->write( 
                sprintf('  - Installing assets of package <info>%s</info> to <info>%s</info>.', 
                    $package->getPrettyName(),
                    dirname(str_replace(dirname($this->assetsDir) . '/', '', $target))
                )
            );
            $this->filesystem->copy($from, $target);

        } else {
            throw new \InvalidArgumentException(
                'Unable to find assets in package "'.$package->getPrettyName().'"'
            );
        }
/*
            $this->assets_db[$package->getPrettyName()] = 
                $this->cluster->parseComposerExtra($package, $this);
        return dirname(str_replace(rtrim($this->appBasePath, '/') . '/', '', $target));
*/
    }

    protected function removeAssets(PackageInterface $package)
    {
        $assets = $this->getAssetsDir($package);
        if (!$assets) {
            return;
        }

        $target = $this->getAssetsInstallPath($package);
        if (file_exists($target)) {
            $this->io->write( 
                sprintf('  - Uninstalling assets of package <info>%s</info> from <info>%s</info>.', 
                    $package->getPrettyName(),
                    dirname(str_replace(dirname($this->assetsDir) . '/', '', $target))
                )
            );
            $this->filesystem->remove($target);

        } else {
            throw new \InvalidArgumentException(
                'Unable to find assets from package "'.$package->getPrettyName().'"'
            );
        }
    }

    public function getAssetsInstallPath(PackageInterface $package)
    {
        $targetDir = $package->getTargetDir();
        return $this->getPackageAssetsBasePath($package) . ($targetDir ? '/'.$targetDir : '');
    }

    public function getAssetsDir(PackageInterface $package)
    {
        $extra = $package->getExtra();
        return isset($extra['assets-dir']) ? $extra['assets-dir'] : AbstractAssetsPackage::DEFAULT_ASSETS_DIR;
    }

    public function getAssetsVendorDir(PackageInterface $package)
    {
        $extra = $package->getExtra();
        return isset($extra['assets-vendor-dir']) ? $extra['assets-vendor-dir'] : AbstractAssetsPackage::DEFAULT_ASSETS_VENDOR_DIR;
    }

    protected function getPackageAssetsBasePath(PackageInterface $package)
    {
        return $this->filesystem->slash($this->getRootPackageAssetsVendorPath()) . $package->getPrettyName();
    }

    protected function getRootPackageAssetsPath()
    {
        $this->initializeAssetsDir();
        return $this->assetsDir ? $this->assetsDir : '';
    }

    protected function getRootPackageAssetsVendorPath()
    {
        $this->initializeAssetsVendorDir();
        return $this->assetsVendorDir ? $this->assetsVendorDir : '';
    }

    protected function initializeAssetsDir()
    {
        $this->filesystem->ensureDirectoryExists($this->assetsDir);
        $this->assetsDir = realpath($this->assetsDir);
    }

    protected function initializeAssetsVendorDir()
    {
        $path = $this->getRootPackageAssetsPath() . '/' . ($this->assetsVendorDir ? $this->assetsVendorDir : '');
        $this->filesystem->ensureDirectoryExists($path);
        $this->assetsVendorDir = realpath($path);
    }

}

// Endfile
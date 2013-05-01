<?php
/**
 * CarteBlanche - PHP framework package - Installers package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace AssetsManager\Composer\Installer;

use Library\Helper\Directory as DirectoryHelper;

use Composer\Composer,
    Composer\IO\IOInterface,
    Composer\Package\PackageInterface,
    Composer\Repository\InstalledRepositoryInterface,
    Composer\Installer\LibraryInstaller;

use AssetsManager\Config,
    AssetsManager\Package\AbstractAssetsPackage,
    AssetsManager\Package\Preset,
    AssetsManager\Composer\Autoload\AssetsAutoloadGenerator,
    AssetsManager\Composer\Util\Filesystem as AssetsFilesystem;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class AssetsInstaller extends LibraryInstaller
{

    protected $assetsDir;
    protected $assetsVendorDir;
    protected $assetsDbFilename;
    protected $documentRoot;
    protected $appBasePath;

    /**
     * Initializes installer: creation of "assets-dir" directory if so.
     *
     * {@inheritDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library')
    {
        parent::__construct($io, $composer, $type);

        $this->guessConfigurator($composer->getPackage());
        $config = $composer->getConfig();
        $this->appBasePath = rtrim(str_replace($config->get('vendor-dir'), '', $this->getVendorDir()), '/');
        $this->filesystem = new AssetsFilesystem();
        $this->assetsDir = $this->guessAssetsDir($composer->getPackage());
        $this->assetsVendorDir = $this->guessAssetsVendorDir($composer->getPackage());
        $this->documentRoot = $this->guessDocumentRoot($composer->getPackage());
        $this->assetsDbFilename = $this->guessAssetsDbFilename($composer->getPackage());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === Config::get('package-type');
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
        $assets = $this->getPackageAssetsDir($package);
        if (!$assets) {
            return;
        }

        $from = $this->getPackageBasePath($package) . '/' . $assets;
        $target = $this->getAssetsInstallPath($package);
        if (file_exists($from)) {
            $this->io->write( 
                sprintf('  - Installing <info>%s</info> assets to <info>%s</info>', 
                    $package->getPrettyName(),
                    str_replace(dirname($this->assetsDir) . '/', '', $target)
                )
            );
            $this->filesystem->copy($from, $target);
            AssetsAutoloadGenerator::registerPackage($package, $target, $this);
            $this->io->write('');
        } else {
            throw new \InvalidArgumentException(
                'Unable to find assets in package "'.$package->getPrettyName().'"'
            );
        }
    }

    protected function removeAssets(PackageInterface $package)
    {
        $assets = $this->getPackageAssetsDir($package);
        if (!$assets) {
            return;
        }

        $target = $this->getAssetsInstallPath($package);
        if (file_exists($target)) {
            $this->io->write( 
                sprintf('  - Removing <info>%s</info> assets to <info>%s</info>', 
                    $package->getPrettyName(),
                    str_replace(dirname($this->assetsDir) . '/', '', $target)
                )
            );
            $this->filesystem->remove($target);
            AssetsAutoloadGenerator::unregisterPackage($package, $this);
            $this->io->write('');
        } else {
            throw new \InvalidArgumentException(
                'Unable to find assets from package "'.$package->getPrettyName().'"'
            );
        }
    }

    protected function guessConfigurator(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (isset($extra['config-class'])) {
            Config::load($extra['config-class']);
        }
    }

    protected function guessAssetsDir(PackageInterface $package)
    {
        $extra = $package->getExtra();
        return isset($extra['assets-dir']) ? $extra['assets-dir'] : Config::get('assets-dir');
    }

    protected function guessAssetsVendorDir(PackageInterface $package)
    {
        $extra = $package->getExtra();
        return isset($extra['assets-vendor-dir']) ? $extra['assets-vendor-dir'] : Config::get('assets-vendor-dir');
    }

    protected function guessDocumentRoot(PackageInterface $package)
    {
        $extra = $package->getExtra();
        return isset($extra['document-root']) ? $extra['document-root'] : Config::get('document-root');
    }

    protected function guessAssetsDbFilename(PackageInterface $package)
    {
        $extra = $package->getExtra();
        return isset($extra['assets-db-filename']) ? $extra['assets-db-filename'] : Config::get('assets-db-filename');
    }

    public function getIo()
    {
        return $this->io;
    }

    public function getAppBasePath()
    {
        return $this->appBasePath;
    }

    public function getVendorDir()
    {
        $this->initializeVendorDir();
        return $this->vendorDir;
    }

    public function getAssetsDir()
    {
        return $this->assetsDir;
    }

    public function getAssetsVendorDir()
    {
        return $this->assetsVendorDir;
    }

    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    public function getAssetsDbFilename()
    {
        return $this->assetsDbFilename;
    }

    protected function getPackageAssetsDir(PackageInterface $package)
    {
        $extra = $package->getExtra();
        return isset($extra['assets-dir']) ? $extra['assets-dir'] : Config::get('assets-dir');
    }

    protected function getPackageAssetsBasePath(PackageInterface $package)
    {
        return DirectoryHelper::slashDirname($this->getRootPackageAssetsVendorPath()) . $package->getPrettyName();
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
        $path = $this->getRootPackageAssetsPath() . '/' . (
            $this->assetsVendorDir ? str_replace($this->getRootPackageAssetsPath(), '', $this->assetsVendorDir) : ''
        );
        $this->filesystem->ensureDirectoryExists($path);
        $this->assetsVendorDir = realpath($path);
    }

    public function getAssetsInstallPath(PackageInterface $package)
    {
        return $this->getRootPackageAssetsVendorPath() . '/' . $package->getPrettyName();
    }

    /**
     * Parse the `composer.json` "extra" block of a package and return its transformed data
     *
     * @param array $package The package, Composer\Package\PackageInterface
     * @return void
     */
    public function parseComposerExtra(PackageInterface $package, $package_dir)
    {
        $presets = array();
        $extra = $package->getExtra();
        if (isset($extra['assets-presets'])) {
            foreach ($extra['assets-presets'] as $index=>$item) {
                $use_item = array();
                foreach (array_keys(Config::get('use-statements')) as $statement) {
                    if (isset($item[$statement])) {
                        $item_statement = is_array($item[$statement]) ?
                            $item[$statement] : array($item[$statement]);
                        $use_item[$statement] = array();
                        foreach ($item_statement as $path) {
                            $use_item[$statement][] = $path;
                        }
                    }
                    if (!empty($use_item)) {
                        $presets[$index] = $use_item;
                    }
                }
            }
        }

        return array(
            'name'          => $package->getPrettyName(),
            'version'       => $package->getVersion(),
            'relative_path' => str_replace($this->getAssetsVendorDir() . '/', '', $package_dir),
            'assets_presets'=> $presets
        );
    }

}

// Endfile
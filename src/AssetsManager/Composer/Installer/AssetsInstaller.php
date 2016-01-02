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

namespace AssetsManager\Composer\Installer;

use \Library\Helper\Directory as DirectoryHelper;
use \Composer\Composer;
use \Composer\IO\IOInterface;
use \Composer\Package\PackageInterface;
use \Composer\Repository\InstalledRepositoryInterface;
use \Composer\Installer\LibraryInstaller;
use \AssetsManager\Config;
use \AssetsManager\Error;
use \AssetsManager\Composer\Dispatch;
use \AssetsManager\Composer\Installer\AssetsInstallerInterface;
use \AssetsManager\Composer\Util\Filesystem as AssetsFilesystem;

/**
 * @author  piwi <me@e-piwi.fr>
 */
class AssetsInstaller
    extends LibraryInstaller
    implements AssetsInstallerInterface
{

    /**
     * @var string
     */
    protected $assets_dir;

    /**
     * @var string
     */
    protected $assets_vendor_dir;

    /**
     * @var string
     */
    protected $assets_db_filename;

    /**
     * @var string
     */
    protected $document_root;

    /**
     * @var string
     */
    protected $cache_dir;

    /**
     * @var string
     */
    protected $app_base_path;

    /**
     * Initializes installer: creation of `assets-dir` directory if so
     *
     * {@inheritDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library')
    {
        parent::__construct($io, $composer, $type);

        $this->guessConfigurator($composer->getPackage());
        $config                 = $composer->getConfig();
        $this->app_base_path    = rtrim(str_replace($config->get('vendor-dir'), '', $this->getVendorDir()), '/');
        if (empty($this->app_base_path) || $this->app_base_path=='/') {
            $this->app_base_path = getcwd();
        }
        $this->filesystem       = new AssetsFilesystem();
        $this->assets_dir       = $this->guessAssetsDir($composer->getPackage());
        $this->assets_vendor_dir = $this->guessAssetsVendorDir($composer->getPackage());
        $this->document_root    = $this->guessDocumentRoot($composer->getPackage());
        $this->assets_db_filename = $this->guessAssetsDbFilename($composer->getPackage());
        $this->cache_dir        = $this->guessCacheDir($composer->getPackage());
    }

// ----------------------------
// Extending \Composer\Installer\LibraryInstaller
// ----------------------------

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        $types = Config::get('package-type');
        $types = is_array($types) ? $types : array($types);
        if (in_array($packageType, $types)) {
            return true;
        }
        foreach ($types as $mask) {
            if (0!==preg_match('/'.$mask.'/', $packageType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $parent = parent::isInstalled($repo, $package);
        if (!$parent) {
            return $parent;
        }
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
        $this->removeAssets($initial);
        parent::update($repo, $initial, $target);
        $this->installAssets($target);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->removeAssets($package);
        parent::uninstall($repo, $package);
    }

// ----------------------------
// Assets management
// ----------------------------

    /**
     * Move the assets of a package
     *
     * @param \Composer\Package\PackageInterface $package
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
                    str_replace(dirname($this->assets_dir) . '/', '', $target)
                )
            );
            $this->filesystem->copy($from, $target);
            Dispatch::registerPackage($package, $target, $this);
            $this->io->write('');
        } else {
            Error::thrower(
                sprintf('Unable to find assets in package "%s"', $package->getPrettyName()),
                '\InvalidArgumentException', __CLASS__, __METHOD__, __LINE__
            );
        }
    }

    /**
     * Remove the assets of a package
     *
     * @param \Composer\Package\PackageInterface $package
     * @return bool
     */
    protected function removeAssets(PackageInterface $package)
    {
        $assets = $this->getPackageAssetsDir($package);
        if (!$assets) {
            return;
        }

        $target = $this->getAssetsInstallPath($package);
        if (file_exists($target)) {
            $this->io->write(
                sprintf('  - Removing <info>%s</info> assets from <info>%s</info>',
                    $package->getPrettyName(),
                    str_replace(dirname($this->assets_dir) . '/', '', $target)
                )
            );
            $this->filesystem->remove($target);
            Dispatch::unregisterPackage($package, $this);
            $this->io->write('');
        } else {
            Error::thrower(
                sprintf('Unable to find assets from package "%s"', $package->getPrettyName()),
                '\InvalidArgumentException', __CLASS__, __METHOD__, __LINE__
            );
        }
    }

// ----------------------------
// Parse configuration
// ----------------------------

    /**
     * Create defined configuration object
     *
     * @param \Composer\Package\PackageInterface $package
     * @return self
     */
    protected function guessConfigurator(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (isset($extra['assets-config-class'])) {
            Config::load($extra['assets-config-class'], true);
        }
//        Config::overload($extra);
        return $this;
    }

    /**
     * Guess and get the `assets-dir` configuration package entry
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function guessAssetsDir(PackageInterface $package)
    {
        return self::guessConfigurationEntry($package, 'assets-dir');
    }

    /**
     * Guess and get the `assets-vendor-dir` configuration package entry
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function guessAssetsVendorDir(PackageInterface $package)
    {
        return self::guessConfigurationEntry($package, 'assets-vendor-dir');
    }

    /**
     * Guess and get the `document-root` configuration package entry
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function guessDocumentRoot(PackageInterface $package)
    {
        return self::guessConfigurationEntry($package, 'document-root');
    }

    /**
     * Guess and get the `cache-dir` configuration package entry
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function guessCacheDir(PackageInterface $package)
    {
        return self::guessConfigurationEntry($package, 'cache-dir');
    }

    /**
     * Guess and get the `assets-db-filename` configuration package entry
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function guessAssetsDbFilename(PackageInterface $package)
    {
        return self::guessConfigurationEntry($package, 'assets-db-filename');
    }

// ----------------------------
// Setters / Getters
// ----------------------------

    /**
     * @return \Composer\IO\IOInterface
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @return string
     */
    public function getAppBasePath()
    {
        return $this->app_base_path;
    }

    /**
     * @return string
     */
    public function getVendorDir()
    {
        $this->initializeVendorDir();
        return $this->vendorDir;
    }

    /**
     * @return string
     */
    public function getAssetsDir()
    {
        return $this->assets_dir;
    }

    /**
     * @return string
     */
    public function getAssetsVendorDir()
    {
        return $this->assets_vendor_dir;
    }

    /**
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->document_root;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cache_dir;
    }

    /**
     * @return string
     */
    public function getAssetsDbFilename()
    {
        return $this->assets_db_filename;
    }

    /**
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function getPackageAssetsDir(PackageInterface $package)
    {
        return $this->guessAssetsDir($package);
    }

    /**
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function getPackageAssetsBasePath(PackageInterface $package)
    {
        return DirectoryHelper::slashDirname($this->getRootPackageAssetsVendorPath()) . $package->getPrettyName();
    }

    /**
     * @return string
     */
    protected function getRootPackageAssetsPath()
    {
        $this->initializeAssetsDir();
        return $this->assets_dir ? $this->assets_dir : '';
    }

    /**
     * @return string
     */
    protected function getRootPackageAssetsVendorPath()
    {
        $this->initializeAssetsVendorDir();
        return $this->assets_vendor_dir ? $this->assets_vendor_dir : '';
    }

    /**
     * Create the `assets_dir` if needed
     *
     * @return self
     */
    protected function initializeAssetsDir()
    {
        $this->filesystem->ensureDirectoryExists($this->assets_dir);
        $this->assets_dir = realpath($this->assets_dir);
        return $this;
    }

    /**
     * Create the `assets_vendor_dir` if needed
     *
     * @return self
     */
    protected function initializeAssetsVendorDir()
    {
        $path = $this->getRootPackageAssetsPath() . '/' . (
            $this->assets_vendor_dir ? str_replace($this->getRootPackageAssetsPath(), '', $this->assets_vendor_dir) : ''
        );
        $this->filesystem->ensureDirectoryExists($path);
        $this->assets_vendor_dir = realpath($path);
        return $this;
    }

    /**
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    public function getAssetsInstallPath(PackageInterface $package)
    {
        return $this->getRootPackageAssetsVendorPath() . '/' . $package->getPrettyName();
    }

    /**
     * Parse the `composer.json` "extra" block of a package and return its transformed data
     *
     * @param   \Composer\Package\PackageInterface $package
     * @param   string $package_dir
     * @return  array|null
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

            $relative_path = str_replace($this->getAssetsVendorDir() . '/', '', $package_dir);
            if ($relative_path===$package_dir) {
                $relative_path = str_replace($this->getAppBasePath(), '', $package_dir);
            }
            if (strlen($relative_path)) {
                $relative_path = trim($relative_path, '/');
            }

            return array(
                'name'          => $package->getPrettyName(),
                'version'       => $package->getVersion(),
                'relative_path' => $relative_path,
                'assets_presets'=> $presets
            );
        }
        return null;
    }

// ---------------------------
// Utilities
// ---------------------------

    /**
     * Search a configuration value in a package's config or the global config if so
     *
     * @param \Composer\Package\PackageInterface $package
     * @param string $config_entry
     * @return string
     */
    public static function guessConfigurationEntry(PackageInterface $package, $config_entry)
    {
        if (empty($config_entry)) {
            return array();
        }
        $extra = $package->getExtra();
        return isset($extra[$config_entry]) ? $extra[$config_entry] : Config::get($config_entry);
    }

    /**
     * Check if a package seems to contain some `$type` files
     *
     * If `$package_extra` is defined, this will test if concerned entry is defined in "extra"
     * configuration of the package.
     *
     * @param \Composer\Package\PackageInterface $package
     * @param string $type
     * @param string $package_extra
     * @return bool
     */
    public static function isPackageContains(PackageInterface $package, $type, $package_extra = null)
    {
        $extra = $package->getExtra();
        if (!is_null($package_extra)) {
            $files = self::guessConfigurationEntry($package, $package_extra);
            return (!empty($extra) && array_key_exists($type, $extra)) || (!empty($files));
        } else {
            return !empty($extra) && array_key_exists($type, $extra);
        }
    }
}

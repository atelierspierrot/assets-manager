<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace ComposerAssetsExtension\Installer;

use Composer\Composer,
    Composer\IO\IOInterface,
    Composer\Autoload\AutoloadGenerator,
    Composer\Package\PackageInterface,
    Composer\Repository\RepositoryInterface,
    Composer\Script\Event,
    Composer\Script\EventDispatcher;

use Library\Helper\Directory as DirectoryHelper;

use ComposerAssetsExtension\Util\Filesystem,
    ComposerAssetsExtension\Package\AbstractAssetsPackage,
    ComposerAssetsExtension\Autoload\AssetsAutoloadGenerator,
    ComposerAssetsExtension\Package\AssetsPackage;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class AssetsInstaller
    extends AutoloadGenerator
{

    /**
     * @var object Composer\Composer
     */
    protected $composer;

    /**
     * @var object Composer\IO\IOInterface
     */
    protected $io;

    /**
     * @var object Assets\Package\Cluster
     */
    protected $cluster;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var object Composer\Package\PackageInterface
     */
    protected $package;

    /**
     * @var object Assets\Util\Filesystem
     */
    protected $filesystem;

    /**
     * The package `composer.json` "extra" block
     * @var array
     */
    public $packageExtra;

    /**
     * The assets directory realpath
     * @var string
     */
    public $assetsDir;

    /**
     * The assets vendor directory realpath
     * @var string
     */
    public $assetsVendorDir;

    /**
     * The assets database file realpath
     * @var string
     */
    public $assetsDbFilename;

    /**
     * The vendor dir realpath
     * @var string
     */
    public $vendorDir;

    /**
     * The application base realpath
     * @var string
     */
    public $appBasePath;

    /**
     * The assets Document Root
     * @var string
     */
    public $documentRoot;

    /**
     * Array filled like 'package_name' => 'package assets infos' used to write the json AssetsDb file
     * @var array
     */
    protected $assets_db = array();

    /**
     * Method called at the creation of the Composer autoload file
     *
     * @param object Composer\Script\Event
     * @return void
     * @throws Throws errors to the IO interface
     */
    public static function postAutoloadDump(Event $event)
    {
        $_this = new AssetsInstaller($event->getComposer(), $event->getIO());
        if (false!==$ok_assets = $_this->moveAssets()) {
            if ($ok_assets>0) {
                if (false!==$_assetsDbPath = $_this->_generateAssetsDb()) {
                    $_this->io->write( 
                        sprintf('Writing assets json DB to <info>%s</info>.',
                        str_replace(rtrim($_this->appBasePath, '/').'/', '', $_assetsDbPath))
                    );
                } else {
                    $_this->io->write('ERROR while trying to create assets DB file!');
                }
            }
        } else {
            $_this->io->write('ERROR while trying to move assets!');
        }
    }

    /**
     * Construction of a new non-static ComposerInstaller
     *
     * @param object Composer\Composer
     * @param object Composer\IO\IOInterface
     * @return void
     */
    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->config = $composer->getConfig();
        $this->package = $composer->getPackage();
        parent::__construct($this->composer->getEventDispatcher());

        $this->filesystem = new Filesystem();
        $vendor_dir = $this->config->get('vendor-dir');
        $this->vendorDir = strtr(realpath($vendor_dir), '\\', '/');
        $this->appBasePath = rtrim(str_replace($vendor_dir, '', $this->vendorDir), '/');

        $extra = $this->package->getExtra();
        $this->packageExtra = $extra;
        $this->assetsDir = isset($extra['assets']) ? $extra['assets'] : AbstractAssetsPackage::DEFAULT_ASSETS_DIR;
        $this->assetsVendorDir = isset($extra['assets_vendor']) ? $extra['assets_vendor'] : AbstractAssetsPackage::DEFAULT_ASSETS_VENDOR_DIR;
        $this->documentRoot = isset($extra['document_root']) ? $extra['document_root'] : AbstractAssetsPackage::DEFAULT_DOCUMENT_ROOT;
        $this->assetsDbFilename = AbstractAssetsPackage::ASSETS_DB_FILENAME;

        // ensure the assets_vendor dir exists
        $this->getAssetsRootPath();

        $this->cluster = new Cluster(
            $this->appBasePath,
            $this->assetsDir,
            str_replace(DirectoryHelper::slashDirname($this->appBasePath), '', $this->vendorDir),
            $this->assetsVendorDir
        );
        if (!empty($this->packageExtra)) {
            $this->assets_db[$this->package->getPrettyName()] = 
                $this->cluster->parseComposerExtra($this->package, $this, true);
        }
    }

    /**
     * Get the assets database
     *
     * @return array
     */
    public function getAssetsDb()
    {
        return $this->assets_db;
    }

    /**
     * Get the install directory realpath of a package
     *
     * @param object $package Composer\Package\PackageInterface
     * @return string
     */
    public function getInstallPath(PackageInterface $package)
    {
        return $this->getAssetsRootPath() . '/' . $package->getPrettyName();
    }

    /**
     * Get the root directory realpath of package's assets
     *
     * @return string
     */
    public function getAssetsRootPath()
    {
        $path = rtrim($this->appBasePath, '/') . '/'
            . rtrim($this->assetsDir, '/') . '/'
            . $this->assetsVendorDir;
        $this->filesystem->ensureDirectoryExists($path);
        return $path;
    }

    /**
     * Get the base directory realpath of a package
     *
     * @param object $package Composer\Package\PackageInterface
     * @return string
     */
    public function getPackageBasePath(PackageInterface $package)
    {
        return ($this->vendorDir ? $this->vendorDir.'/' : '') . $package->getPrettyName();
    }

    /**
     * Get the relative assets directory of a package
     *
     * @param object $package Composer\Package\PackageInterface
     * @return string
     */
    public function getRelativePath(PackageInterface $package)
    {
        return str_replace($this->getAssetsRootPath(), '', $this->getPackageBasePath($package));
    }

    /**
     * Copy the assets of installed packages in the assets directory
     *
     * @return void
     */
    public function moveAssets()
    {
        $ok = 0;
        $must_install = false;

        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        foreach($localRepo->getPackages() as $packageitem) {
            $extra = $packageitem->getExtra();
            if (!empty($extra) && isset($extra['assets'])) {
                $must_install = true;
                if ($f = $this->_movePackageAssets($packageitem)) {
                    $ok++;
                    $this->io->write( 
                        sprintf('  - Installing assets of package <info>%s</info> to <info>%s</info>.', 
                            $packageitem->getPrettyName(), $f
                        )
                    );
                } else {
                    $this->io->write( 
                        sprintf('  !! An error occured trying to install assets of package <info>%s</info> to <info>%s</info>.', 
                            $packageitem->getPrettyName(),
                            rtrim($this->assetsDir, '/') . '/' . $this->assetsVendorDir
                        )
                    );
                }
                $this->io->write('');
            }
        }

        return true===$must_install ? $ok : true;
    }

    /**
     * Build the package installation database file
     *
     * @return bool
     * @see Assets\Autoload\AssetsAutoloaderGenerator
     */
    protected function _generateAssetsDb()
    {
        $generator = new AssetsAutoloadGenerator($this);
        return $generator->generate();
    }

    /**
     * Move the assets of a package
     *
     * @param object $package Composer\Package\PackageInterface
     * @return bool
     */
    protected function _movePackageAssets(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (!empty($extra) && isset($extra['assets'])) {
            $from = $this->getPackageBasePath($package) . '/' . $extra['assets'];
            $target = $this->getInstallPath($package);
            if (file_exists($from)) {
                $this->filesystem->copy($from, $target);
                $this->assets_db[$package->getPrettyName()] = 
                    $this->cluster->parseComposerExtra($package, $this);
            } else {
                throw new \Exception(
                    'Unable to find assets in package "'.$package->getPrettyName().'"'
                );
            }
            return dirname(str_replace(rtrim($this->appBasePath, '/') . '/', '', $target));
        }
        return false;
    }
    
}

// Endfile
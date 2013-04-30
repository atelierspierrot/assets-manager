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
    Composer\Installer\LibraryInstaller,
    Composer\Package\PackageInterface;

use ComposerAssetsExtension\Package\AbstractAssetsPackage;

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

$io->write(var_export($composer->getConfig()->getConfigSource(),1));

        $this->assetsDir = rtrim($composer->getConfig()->get('assets-dir'), '/');
        $this->assetsVendorDir = rtrim($composer->getConfig()->get('assets-vendor-dir'), '/');
        $this->filesystem->ensureDirectoryExists(CarteBlancheInstaller::CARTEBLANCHE_BUNDLES_DIR);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {

var_export($packageType);
var_export(AbstractAssetsPackage::DEFAULT_PACKAGE_TYPE);
exit('yo');
        return $packageType === AbstractAssetsPackage::DEFAULT_PACKAGE_TYPE;
    }

    /**
     * Determines the install path for templates,
     *
     * The installation path is determined by checking whether the package is included in another composer configuration
     * or installed as part of the normal CarteBlanche installation.
     *
     * When the package is included as part of a different project it will be installed in the `src/tools` folder
     * of phpDocumentor (thus `/atelierspierrot/carte-blanche/src/bundles`); if it is installed as part of
     * CarteBlanche it will be installed in the root of the project (thus `/src/bundles`).
     *
     * @param PackageInterface $package
     * @throws \InvalidArgumentException if the name of the package does not start with `carte-blanche/tool-`.
     * @return string a path relative to the root of the composer.json that is being installed.
     */
    public function getInstallPath(PackageInterface $package)
    {
        if ($this->extractPrefix($package) != CarteBlancheInstaller::CARTEBLANCHE_BUNDLENAME) {
            throw new \InvalidArgumentException(
                'Unable to install bundle, CarteBlanche bundles should always start their package name with '
                .'"'.CarteBlancheInstaller::CARTEBLANCHE_BUNDLENAME.'"'
            );
        }

        return $this->getBundleRootPath() . '/' . $this->extractShortName($package);
    }

    /**
     * Extract the first 21 characters ("carte-blanche/bundle-") of the package name; which is expected to be the prefix.
     *
     * @param PackageInterface $package
     * @return string
     */
    public static function extractPrefix(PackageInterface $package)
    {
        return substr($package->getPrettyName(), 0, strlen(CarteBlancheInstaller::CARTEBLANCHE_BUNDLENAME));
    }

    /**
     * Extract the everything after the first 21 characters of the package name; which is expected to be the short name.
     *
     * @param PackageInterface $package
     * @return string
     */
    public static function extractShortName(PackageInterface $package)
    {
        return substr($package->getPrettyName(), strlen(CarteBlancheInstaller::CARTEBLANCHE_BUNDLENAME));
    }

    /**
     * Returns the root installation path for templates.
     *
     * @return string a path relative to the root of the composer.json that is being installed where the templates
     *     are stored.
     */
    protected function getBundleRootPath()
    {
        return (file_exists($this->vendorDir . '/atelierspierrot/carte-blanche/composer.json'))
            ? $this->vendorDir . '/atelierspierrot/carte-blanche/src/bundles'
            : 'src/bundles';
    }

}

// Endfile
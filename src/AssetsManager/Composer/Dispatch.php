<?php
/**
 * CarteBlanche - PHP framework package - Installers package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace AssetsManager\Composer;

use Composer\Composer,
    Composer\IO\IOInterface,
    Composer\Package\PackageInterface,
    Composer\Repository\InstalledRepositoryInterface,
    Composer\Installer\LibraryInstaller,
    Composer\Installer\InstallerInterface;

use AssetsManager\Config,
    AssetsManager\Error;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Dispatch implements InstallerInterface
{

    private $__installer;

    /**
     * Initializes installer: creation of "assets-dir" directory if so.
     *
     * {@inheritDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library')
    {
        $package = $composer->getPackage();
        $extra = $package->getExtra();
        $config = $composer->getConfig();

        if (isset($extra['assets-config-class'])) {
            Config::load($extra['assets-config-class']);
        }
        Config::overload($extra);

        $installer = Config::get('assets-package-installer-class');

        if (!empty($installer)) {
            if (@class_exists($installer)) {
                $interfaces = class_implements($installer);
                $config_interface = Config::getInternal('assets-package-installer-interface');
                if (in_array($config_interface, $interfaces)) {
                    $this->__installer = new $installer($io, $composer, $type);
                } else {
                    Error::thrower(
                        sprintf('Assets package installer class "%s" must implements interface "%s"!',
                            $installer, $config_interface),
                        '\DomainException', __CLASS__, __METHOD__, __LINE__
                    );
                }
            } else {
                Error::thrower(
                    sprintf('Assets package installer class "%s" not found!', $installer),
                    '\DomainException', __CLASS__, __METHOD__, __LINE__
                );
            }
        } else {
            Error::thrower(
                'Assets package isntaller is not defined!', '\Exception', __CLASS__, __METHOD__, __LINE__
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $this->__installer->supports($packageType);
    }

    /**
     * {@inheritDoc}
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return $this->__installer->isInstalled($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return $this->__installer->install($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        return $this->__installer->update($repo, $initial, $target);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return $this->__installer->uninstall($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        return $this->__installer->getInstallPath($package);
    }
    
}

// Endfile
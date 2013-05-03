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

    private $__must_replay = array();
    private $__io;
    private $__composer;
    private $__type;
    private $__extra;
    private $__installer;

    /**
     * Initializes installer: creation of "assets-dir" directory if so.
     *
     * {@inheritDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library')
    {
        $this->__io = $io;
        $this->__composer = $composer;
        $this->__type = $type;

        $package = $this->__composer->getPackage();
        $config = $this->__composer->getConfig();
        $this->__extra = $package->getExtra();

        if (isset($extra['assets-config-class'])) {
            $config_class = $extra['assets-config-class'];
            if ($this->__validateConfig($config_class)) {
                $this->__loadConfig($config_class);
            } else {
                $this->__must_replay['config'] = $config_class;
            }
        }

        $installer_class = Config::get('assets-package-installer-class');
        if (!empty($installer_class)) {
            if ($this->__validateInstaller($installer_class)) {
                $this->__newInstaller($installer_class);
            } else {
                $this->__must_replay['installer'] = $installer_class;
                $installer_class = Config::getInernal('assets-package-installer-class');
                $this->__newInstaller($installer_class);
            }
        } else {
            Error::thrower(
                'Assets package installer is not defined!', '\Exception', __CLASS__, __METHOD__, __LINE__
            );
        }
    }

    private function __replay()
    {
        // config
        if (isset($this->__must_replay['config'])) {
            if ($this->__validateConfig($this->__must_replay['config'])) {
                $this->__loadConfig($this->__must_replay['config']);
                unset($this->__must_replay['config']);
            }
        }

        // installer
        if (isset($this->__must_replay['installer'])) {
            if ($this->__validateInstaller($this->__must_replay['installer'])) {
                $this->__newInstaller($this->__must_replay['installer']);
                unset($this->__must_replay['installer']);
            }
        }
    }

    private function __validateConfig($config_class)
    {
        return class_exists($config_class);
    }

    private function __loadConfig($config_class)
    {
        Config::load($config_class);
        Config::overload($this->__extra);
    }

    private function __validateInstaller($installer_class)
    {
        if (class_exists($installer_class)) {
            $interfaces = class_implements($installer_class);
            $config_interface = Config::getInternal('assets-package-installer-interface');
            return in_array($config_interface, $interfaces)) {
        }
        return false;
    }

    private function __newInstaller($installer_class)
    {
        if (class_exists($installer_class)) {
            $interfaces = class_implements($installer_class);
            $config_interface = Config::getInternal('assets-package-installer-interface');
            if (in_array($config_interface, $interfaces)) {
                $this->__installer = new $installer($this->__io, $this->__composer, $this->__type);
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
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        $this->__replay();
        return $this->__installer->supports($packageType);
    }

    /**
     * {@inheritDoc}
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->__replay();
        return $this->__installer->isInstalled($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->__replay();
        return $this->__installer->install($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $this->__replay();
        return $this->__installer->update($repo, $initial, $target);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->__replay();
        return $this->__installer->uninstall($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $this->__replay();
        return $this->__installer->getInstallPath($package);
    }
    
}

// Endfile
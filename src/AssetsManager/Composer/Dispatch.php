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

    private $__io;
    private $__composer;
    private $__type;

    private $__installer;
    private $__autoloader;

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
        $extra = $package->getExtra();

        // Config
        if (isset($extra['assets-config-class'])) {
            $config_class = $extra['assets-config-class'];
            if ($this->validateConfig($config_class)) {
                Config::load($config_class);
                Config::overload($extra);
            }
        }

        // Installer
        $installer_class = Config::get('assets-package-installer-class');
        if (!empty($installer_class)) {
            if (!$this->validateInstaller($installer_class)) {
                $installer_class = Config::getInernal('assets-package-installer-class');
            }
            if (class_exists($installer_class)) {
                $interfaces = class_implements($installer_class);
                $installer_interface = Config::getInternal('assets-package-installer-interface');
                if (in_array($installer_interface, $interfaces)) {
                    $this->__installer = new $installer_class($this->__io, $this->__composer, $this->__type);
                } else {
                    Error::thrower(
                        sprintf('Assets package installer class "%s" must implements interface "%s"!',
                            $installer_class, $installer_interface),
                        '\DomainException', __CLASS__, __METHOD__, __LINE__
                    );
                }
            } else {
                Error::thrower(
                    sprintf('Assets package installer class "%s" not found!', $installer_class),
                    '\DomainException', __CLASS__, __METHOD__, __LINE__
                );
            }
        } else {
            Error::thrower(
                'Assets package installer is not defined!', '\Exception', __CLASS__, __METHOD__, __LINE__
            );
        }

        // AutoloadGenerator
        $autoload_class = Config::get('assets-autoload-generator-class');
        if (!empty($autoload_class)) {
            if (!$this->validateAutoloadGenerator($autoload_class)) {
                $autoload_class = Config::getInernal('assets-autoload-generator-class');
            }
            if (class_exists($autoload_class)) {
                $parents = class_parents($autoload_class);
                $autoload_abstract = Config::getInternal('assets-autoload-generator-abstract');
                if (in_array($autoload_abstract, $parents)) {
                    $this->__autoloader = $autoload_class::getInstance($this->__installer);
                } else {
                    Error::thrower(
                        sprintf('Assets autoload generator class "%s" must extend abstract class "%s"!',
                            $autoload_class, $autoload_abstract),
                        '\DomainException', __CLASS__, __METHOD__, __LINE__
                    );
                }
            } else {
                Error::thrower(
                    sprintf('Assets autoload generator class "%s" not found!', $installer_class),
                    '\DomainException', __CLASS__, __METHOD__, __LINE__
                );
            }
        } else {
            Error::thrower(
                'Assets autoload generator is not defined!', '\Exception', __CLASS__, __METHOD__, __LINE__
            );
        }

    }

// ---------------------------------------
// Config validators
// ---------------------------------------

    /**
     * @param string AssetsManager\Config\ConfiguratorInterface
     * @return bool
     */
    public static function validateConfig($config_class)
    {
        return class_exists($config_class);
    }

    /**
     * @param string AssetsManager\Composer\Installer\AssetsInstallerInterface
     * @return bool
     */
    public static function validateInstaller($installer_class)
    {
        if (class_exists($installer_class)) {
            $interfaces = class_implements($installer_class);
            $installer_interface = Config::getInternal('assets-package-installer-interface');
            return in_array($installer_interface, $interfaces);
        }
        return false;
    }

    /**
     * @param string AssetsManager\Composer\Autoload\AutoloadGeneratorInterface
     * @return bool
     */
    public static function validateAutoloadGenerator($generator_class)
    {
        if (class_exists($generator_class)) {
            $parents = class_parents($generator_class);
            $autoload_abstract = Config::getInternal('assets-autoload-generator-abstract');
            return in_array($autoload_abstract, $parents);
        }
        return false;
    }

// ---------------------------------------
// Composer\Installer\InstallerInterface
// ---------------------------------------

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
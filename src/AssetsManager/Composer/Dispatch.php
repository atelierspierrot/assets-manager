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

namespace AssetsManager\Composer;

use \Composer\Composer;
use \Composer\IO\IOInterface;
use \Composer\Package\PackageInterface;
use \Composer\Repository\InstalledRepositoryInterface;
use \Composer\Installer\LibraryInstaller;
use \Composer\Installer\InstallerInterface;
use \AssetsManager\Config;
use \AssetsManager\Error;
use \AssetsManager\Composer\Installer\AssetsInstallerInterface;

/**
 * @author  piwi <me@e-piwi.fr>
 */
class Dispatch
    implements InstallerInterface
{

    /**
     * @var \Composer\IO\IOInterface
     */
    private static $__io;

    /**
     * @var \Composer\Composer
     */
    private static $__composer;

    /**
     * @var string
     */
    private static $__type;

    /**
     * @var \AssetsManager\Composer\Installer\AssetsInstallerInterface
     */
    private static $__installer;

    /**
     * @var \AssetsManager\Composer\Autoload\AbstractAssetsAutoloadGenerator
     */
    private static $__autoloader;

    /**
     * Initializes installer: creation of all required objects and validating them
     *
     * {@inheritDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library')
    {
        self::$__io = $io;
        self::$__composer = $composer;
        self::$__type = $type;

        $package = self::$__composer->getPackage();
        $config = self::$__composer->getConfig();
        $extra = $package->getExtra();

        // Config
        if (isset($extra['assets-config-class'])) {
            $config_class = $extra['assets-config-class'];
            if ($this->validateConfig($config_class)) {
                Config::load($config_class);
                Config::overload($extra);
            } else {
                self::$__io->write(
                    sprintf('<warning>AssetsManager Notice: skipping configuration class "%s": class not found!</warning>',
                        $config_class)
                );
            }
        }

        // Installer
        $installer_class = Config::get('assets-package-installer-class');
        if (!empty($installer_class)) {
            if (!$this->validateInstaller($installer_class)) {
                self::$__io->write(
                    sprintf('<warning>AssetsManager Notice: skipping assets installer class "%s": class not found!</warning>',
                        $installer_class)
                );
                $installer_class = Config::getInternal('assets-package-installer-class');
            }
            if (class_exists($installer_class)) {
                $interfaces = class_implements($installer_class);
                $installer_interface = Config::getInternal('assets-package-installer-interface');
                if (in_array($installer_interface, $interfaces)) {
                    self::$__installer = new $installer_class(self::$__io, self::$__composer, self::$__type);
                } else {
                    Error::thrower(
                        sprintf('Assets package installer class "%s" must implement interface "%s"!',
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
                self::$__io->write(
                    sprintf('<warning>AssetsManager Notice: skipping autoload generator class "%s": class not found!</warning>',
                        $autoload_class)
                );
                $autoload_class = Config::getInternal('assets-autoload-generator-class');
            }
            if (class_exists($autoload_class)) {
                $parents = class_parents($autoload_class);
                $autoload_abstract = Config::getInternal('assets-autoload-generator-abstract');
                if (in_array($autoload_abstract, $parents)) {
                    self::$__autoloader = $autoload_class::getInstance(self::$__installer);
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
// Getters / Setters
// ---------------------------------------

    public function getComposer()
    {
        return self::$__composer;
    }

    public function getIo()
    {
        return self::$__io;
    }

// ---------------------------------------
// Config validators
// ---------------------------------------

    /**
     * Validating the configuration class to use
     *
     * @param string $config_class
     * @return bool
     */
    public static function validateConfig($config_class)
    {
        return class_exists($config_class);
    }

    /**
     * Validating the installer class to use
     *
     * @param string $installer_class
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
     * Validating the autoload generator class to use
     *
     * @param string $generator_class
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
        return self::$__installer->supports($packageType);
    }

    /**
     * {@inheritDoc}
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return self::$__installer->isInstalled($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return self::$__installer->install($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        return self::$__installer->update($repo, $initial, $target);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return self::$__installer->uninstall($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        return self::$__installer->getInstallPath($package);
    }
    
// --------------------------------------------
// AssetsManager\Composer\Autoload\AbstractAssetsAutoloadGenerator
// --------------------------------------------

    /**
     * Set the current assets database
     *
     * @param   array $assets_db
     * @param   \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     * @return  void
     */
    public static function setRegistry(array $assets_db, AssetsInstallerInterface $installer = null)
    {
        self::$__autoloader->setRegistry($assets_db, $installer);
    }

    /**
     * Get the current assets database
     *
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     * @return array
     */
    public static function getRegistry(AssetsInstallerInterface $installer = null)
    {
        return self::$__autoloader->getRegistry($installer);
    }

    /**
     * Set the generator called at object destruction
     *
     * @param callable $callable
     * @return void
     */
    public static function setGenerator($callable)
    {
        self::$__autoloader->setGenerator($callable);
    }

    /**
     * Get the generator called at object destruction
     *
     * @return object
     */
    public static function getGenerator()
    {
        return self::$__autoloader;
    }

    /**
     * Add a new installed package in the Assets database
     *
     * @param \Composer\Package\PackageInterface $package
     * @param string $target
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     * @return void
     */
    public static function registerPackage(PackageInterface $package, $target, AssetsInstallerInterface $installer = null)
    {
        self::$__autoloader->registerPackage($package, $target, $installer);
    }

    /**
     * Remove an uninstalled package from the Assets database
     *
     * @param \Composer\Package\PackageInterface $package
     * @param \AssetsManager\Composer\Installer\AssetsInstallerInterface $installer
     * @return void
     */
    public static function unregisterPackage(PackageInterface $package, AssetsInstallerInterface $installer = null)
    {
        self::$__autoloader->unregisterPackage($package, $installer);
    }
}

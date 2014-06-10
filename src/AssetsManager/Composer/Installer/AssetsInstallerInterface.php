<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Composer\Installer;

use \Composer\Composer,
    \Composer\IO\IOInterface,
    \Composer\Package\PackageInterface,
    \Composer\Repository\InstalledRepositoryInterface,
    \Composer\Installer\LibraryInstaller,
    \Composer\Installer\InstallerInterface;

/**
 * @author 		Piero Wbmstr <me@e-piwi.fr>
 */
interface AssetsInstallerInterface
    extends InstallerInterface
{

    /**
     * Parse the `composer.json` "extra" block of a package and return its transformed data
     *
     * @param array $package \Composer\Package\PackageInterface
     * @param string $package_dir The install directory of the package
     */
    public function parseComposerExtra(PackageInterface $package, $package_dir);

}

// Endfile
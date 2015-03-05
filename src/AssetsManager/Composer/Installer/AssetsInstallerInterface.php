<?php
/**
 * This file is part of the AssetsManager package.
 *
 * Copyleft (ↄ) 2013-2015 Pierre Cassat <me@e-piwi.fr> and contributors
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

use \Composer\Composer;
use \Composer\IO\IOInterface;
use \Composer\Package\PackageInterface;
use \Composer\Repository\InstalledRepositoryInterface;
use \Composer\Installer\LibraryInstaller;
use \Composer\Installer\InstallerInterface;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
interface AssetsInstallerInterface
    extends InstallerInterface
{

    /**
     * Parse the `composer.json` "extra" block of a package and return its transformed data
     *
     * @param \Composer\Package\PackageInterface $package
     * @param string $package_dir The install directory of the package
     */
    public function parseComposerExtra(PackageInterface $package, $package_dir);

}

// Endfile
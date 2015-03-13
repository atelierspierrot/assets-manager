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

namespace AssetsManager\Package;

/**
 * AssetsPresetInterface
 *
 * Any Assets Preset class must implement this interface methods.
 *
 * @author  piwi <me@e-piwi.fr>
 */
interface AssetsPresetInterface
{

    /**
     * @param string $preset_name
     * @param array $preset_data
     * @param \AssetsManager\Package\AssetsPackageInterface $package
     */
    public function __construct($preset_name, array $preset_data, \AssetsManager\Package\AssetsPackageInterface $package);

    /**
     * @return array
     */
    public function getStatements();

    /**
     * @param string $name
     * @return array
     */
    public function getStatement($name);

    /**
     * @return string
     */
    public function __toHtml();

}

// Endfile
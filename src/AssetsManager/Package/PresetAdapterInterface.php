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
 * PresetAdapterInterface
 *
 * Any Preset adapter must implement this interface methods.
 *
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
interface PresetAdapterInterface
{

    /**
     * @param array|string $data The preset data
     * @param \AssetsManager\Package\AssetsPresetInterface $preset
     */
    public function __construct(array $data, \AssetsManager\Package\AssetsPresetInterface $preset);

    /**
     * Return the parsed and transformed statement array
     * @return array
     */
    public function getData();

    /**
     * Parse and transform the preset statement to a ready-to-use information
     * @return void
     */
    public function parse();

    /**
     * Returns the transformed info of the preset statement
     * @return string
     */
    public function __toString();

    /**
     * Returns the transformed info of the preset statement as a ready-to-use HTML string
     * @return string
     */
    public function __toHtml();

}

// Endfile
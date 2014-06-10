<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Package;

/**
 * PresetAdapterInterface
 *
 * Any Preset adapter must implement this interface methods.
 *
 * @author 		Piero Wbmstr <me@e-piwi.fr>
 */
interface PresetAdapterInterface
{

    /**
     * @param array|string $data The preset data
     * @param object $preset AssetsManager\Package\Preset
     */
    public function __construct(array $data, \AssetsManager\Package\AssetsPresetInterface $preset);

    /**
     * Return the parsed and tranformed statement array
     * @return array
     */
    public function getData();

    /**
     * Parse and tranform the preset statement to a ready-to-use information
     * @return void
     */
    public function parse();

    /**
     * Returns the tranformed info of the preset statement
     * @return string
     */
    public function __toString();

    /**
     * Returns the tranformed info of the preset statement as a ready-to-use HTML string
     * @return string
     */
    public function __toHtml();

}

// Endfile
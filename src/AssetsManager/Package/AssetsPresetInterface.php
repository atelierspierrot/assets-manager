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
 * AssetsPresetInterface
 *
 * Any Assets Preset class must implement this interface methods.
 *
 * @author  Piero Wbmstr <me@e-piwi.fr>
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
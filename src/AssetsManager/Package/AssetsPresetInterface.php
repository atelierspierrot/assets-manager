<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager\Package;

/**
 * AssetsPresetInterface
 *
 * Any Assets Preset class must implement this interface methods.
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
interface AssetsPresetInterface
{

    /**
     * @param string $package_name
     * @param array $package_data
     * @param object $package AssetsManager\Package\AssetsPackageInterface
     */
    public function __construct($preset_name, array $preset_data, \AssetsManager\Package\AssetsPackageInterface $package);

    /**
     * @return array
     */
    public function getStatements();

    /**
     * @return array
     */
    public function getStatement($name);

    /**
     * @return string
     */
    public function __toHtml();

}

// Endfile
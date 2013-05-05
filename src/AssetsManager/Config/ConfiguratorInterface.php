<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager\Config;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
interface ConfiguratorInterface
{

    /**
     * @return array
     */
    public static function getDefaults();

}

// Endfile
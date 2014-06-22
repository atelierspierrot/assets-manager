<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Config;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
interface ConfiguratorInterface
{

    /**
     * @return array
     */
    public static function getDefaults();

}

// Endfile
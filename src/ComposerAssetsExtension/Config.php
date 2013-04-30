<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace ComposerAssetsExtension;

use Composer\Config as ComposerConfig;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Config
    extends ComposerConfig
{

    public static $defaultConfig = array(
        'assets-dir' => AbstractAssetsPackage::DEFAULT_ASSETS_DIR,
        'document-root' => AbstractAssetsPackage::DEFAULT_DOCUMENT_ROOT,
        'assets-vendor-dir' => AbstractAssetsPackage::DEFAULT_ASSETS_VENDOR_DIR,
        'assets-db-file' => AbstractAssetsPackage::ASSETS_DB_FILENAME,
        'assets-cache-dir' => '{$assets-dir}/tmp',
        'assets-persets' => array(),
    );
    
}

// Endfile
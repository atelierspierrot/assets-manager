<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager\Config;

use AssetsManager\Config\ConfiguratorInterface;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class DefaultConfig implements ConfiguratorInterface
{

    /**
     * The real configuration entries
     * @return array
     */
    public static function getDefaults()
    {
        return array(
            // The default package type handles by the installer
            'package-type' => array( 'library-assets', 'composer-installer' ),
            // The default package vendor directory name (related to package root dir)
            'vendor-dir' => 'vendor',
            // The default package assets directory name (related to package root dir)
            'assets-dir' => 'www',
            // The default third-party packages'assets directory name (related to package assets dir)
            'assets-vendor-dir' => 'vendor',
            // The default package root directory is set on `$_SERVER['DOCUMENT_ROOT']`
            'document-root' => $_SERVER['DOCUMENT_ROOT'],
            // The assets database file created on install
            'assets-db-filename' => 'assets.json',
            // Composition of an `assets-presets` statement in `composer.json`
            // array pairs like "statement name => adapter"
            'use-statements' => array(
                'css' => 'Css',
                'js' => 'Javascript',
                'jsfiles_footer' => 'Javascript',
                'jsfiles_header' => 'Javascript',
                'require' => 'Requirement'
            ),
            // the configuration class (this class, can be null but must be present)
            // must impelements AssetsManager\Config\ConfiguratorInterface
            'assets-config-class' => null,
            // the AssetsPackage class
            // must implements AssetsManager\Package\AssetsPackageInterface
            'assets-package-class' => 'AssetsManager\Package\AssetsPackage',
            // the AssetsPreset class
            // must implements AssetsManager\Package\AssetsPresetInterface
            'assets-preset-class' => 'AssetsManager\Package\Preset',
            // the AssetsInstaller class
            // must implements AssetsManager\Composer\Installer\AssetsInstallerInterface
            'assets-package-installer-class' => 'AssetsManager\Composer\Installer\AssetsInstaller',
            // the AssetsAutoloadGenerator class
            // must extends AssetsManager\Composer\Autoload\AbstractAutoloadGenerator
            'assets-autoload-generator-class' => 'AssetsManager\Composer\Autoload\AssetsAutoloadGenerator',
        );
    }

}

// Endfile
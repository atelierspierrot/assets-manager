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

namespace AssetsManager\Config;

use \AssetsManager\Config\ConfiguratorInterface;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class DefaultConfig
    implements ConfiguratorInterface
{

    /**
     * The real configuration entries
     * @return array
     */
    public static function getDefaults()
    {
        return array(
            // The default package type handles by the installer
            'package-type' => '^(.*)-assets$',
            // The default package vendor directory name (related to package root dir)
            'vendor-dir' => 'vendor',
            // The default package assets directory name (related to package root dir)
            'assets-dir' => 'www',
            // The default third-party packages'assets directory name (related to package assets dir)
            'assets-vendor-dir' => 'vendor',
            // The default package root directory is set on `$_SERVER['DOCUMENT_ROOT']`
            'document-root' => $_SERVER['DOCUMENT_ROOT'],
            // The cache directory web accessible (related to assets dir)
            'cache-dir' => 'tmp',
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
            // must implements \AssetsManager\Config\ConfiguratorInterface
            'assets-config-class' => null,
            // the Package class
            // must implements \AssetsManager\Package\PackageInterface
            'assets-package-class' => 'AssetsManager\Package\Package',
            // the AssetsPreset class
            // must implements \AssetsManager\Package\PresetInterface
            'assets-preset-class' => 'AssetsManager\Package\Preset',
            // the AssetsInstaller class
            // must implements \AssetsManager\Composer\Installer\AssetsInstallerInterface
            'assets-package-installer-class' => 'AssetsManager\Composer\Installer\AssetsInstaller',
            // the AssetsAutoloadGenerator class
            // must extends \AssetsManager\Composer\Autoload\AbstractAssetsAutoloadGenerator
            'assets-autoload-generator-class' => 'AssetsManager\Composer\Autoload\AssetsAutoloadGenerator',
        );
    }

}

// Endfile
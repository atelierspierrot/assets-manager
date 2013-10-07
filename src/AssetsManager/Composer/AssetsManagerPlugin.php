<?php
/**
 * CarteBlanche - PHP framework package - Installers package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace AssetsManager\Composer;

use Composer\Composer,
    Composer\EventDispatcher\EventSubscriberInterface,
    Composer\IO\IOInterface,
    Composer\Plugin\PluginInterface,
    Composer\Plugin\PluginEvents,
    Composer\Plugin\PreFileDownloadEvent;

use AssetsManager\Composer\Dispatch;

class AssetsManagerPlugin
    implements PluginInterface
{

    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new Dispatch($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }

}

// Endfile
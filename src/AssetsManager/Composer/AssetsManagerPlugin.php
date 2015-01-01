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

namespace AssetsManager\Composer;

use \Composer\Composer;
use \Composer\IO\IOInterface;
use \Composer\Script\Event;
use \Composer\Plugin\PluginInterface;
use \Composer\Plugin\PluginEvents;
use \Composer\EventDispatcher\EventSubscriberInterface;
use \Composer\Plugin\CommandEvent;
use \Composer\Plugin\PreFileDownloadEvent;
use \AssetsManager\Composer\Dispatch;
use \AssetsManager\Composer\Autoload\AssetsAutoloadGenerator;
use \AssetsManager\Composer\Autoload\DumpAutoloadEventHandler;

class AssetsManagerPlugin
    implements PluginInterface, EventSubscriberInterface
{

    /**
     * @var \AssetsManager\Composer\Dispatch
     */
    protected $__installer;

    /**
     * Add the `\AssetsManager\Composer\Dispatch` installer
     *
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->__installer = new Dispatch($io, $composer);
        $composer->getInstallationManager()->addInstaller($this->__installer);
    }

    /**
     * Composer events plugin's subscription
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::PRE_FILE_DOWNLOAD => array(
                array('onPreFileDownload', 0)
            ),
            PluginEvents::COMMAND => array(
                array('onCommand', 0)
            ),
        );
    }

    /**
     * Pre file download event dispatcher
     *
     * @param \Composer\Plugin\PreFileDownloadEvent $event
     */
    public function onPreFileDownload(PreFileDownloadEvent $event)
    {
/*
echo 'PRE FILE DOWNLOAD';
var_export(func_get_args());
*/
    }

    /**
     * Command event dispatcher
     *
     * @param \Composer\Plugin\CommandEvent $event
     */
    public function onCommand(CommandEvent $event)
    {
        switch ($event->getCommandName()) {
            case 'dump-autoload':
                $_this = new DumpAutoloadEventHandler(
                    $this->__installer->getComposer()->getPackage(),
                    $this->__installer->getComposer()
                );
                break;
            default: break;
        }
    }

}

// Endfile
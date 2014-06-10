<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Composer;

use \Composer\Composer,
    \Composer\IO\IOInterface,
    \Composer\Script\Event,
    \Composer\Plugin\PluginInterface,
    \Composer\Plugin\PluginEvents,
    \Composer\EventDispatcher\EventSubscriberInterface,
    \Composer\Plugin\CommandEvent,
    \Composer\Plugin\PreFileDownloadEvent;

use \AssetsManager\Composer\Dispatch,
    \AssetsManager\Composer\Autoload\AssetsAutoloadGenerator,
    \AssetsManager\Composer\Autoload\DumpAutoloadEventHandler;

class AssetsManagerPlugin
    implements PluginInterface, EventSubscriberInterface
{

    /**
     * @var object \AssetsManager\Composer\Dispatch
     */
    protected $__installer;

    /**
     * Add the `\AssetsManager\Composer\Dispatch` installer
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->__installer = new Dispatch($io, $composer);
        $composer->getInstallationManager()->addInstaller($this->__installer);
    }

    /**
     * Composer events plugin's subscription
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
     * @param object \Composer\Plugin\PreFileDownloadEvent
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
     * @param object \Composer\Plugin\CommandEvent
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
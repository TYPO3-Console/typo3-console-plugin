<?php
namespace Helhum\Typo3ConsolePlugin;

/*
 * This file is part of the typo3 console plugin package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

/**
 * Class Plugin
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Apply plugin modifications to composer
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->config = Config::load($io, $composer->getConfig());
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_AUTOLOAD_DUMP => array('onPostAutoloadDump')
        );
    }

    /**
     * Plugin callback for this script event, which calls the previously implemented static method
     *
     * @param \Composer\Script\Event $event
     * @return bool
     */
    public function onPostAutoloadDump(\Composer\Script\Event $event)
    {
        require $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
        \Helhum\Typo3Console\Composer\InstallerScripts::setupConsole($event, true);
    }
}

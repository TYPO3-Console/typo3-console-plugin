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

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use \TYPO3\CMS\Composer\Plugin\Config as Typo3PluginConfig;

/**
 * Class Plugin
 */
class PluginImplementation
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var ScriptDispatcher
     */
    private $scriptDispatcher;

    /**
     * @var IncludeFileWriter
     */
    private $includeFileWriter;

    /**
     * PluginImplementation constructor.
     *
     * @param Event $event
     * @param IncludeFileWriter $includeFileWriter
     * @param ScriptDispatcher $scriptDispatcher
     */
    public function __construct(Event $event, ScriptDispatcher $scriptDispatcher = null, IncludeFileWriter $includeFileWriter = null)
    {
        $this->event = $event;
        $this->scriptDispatcher = $scriptDispatcher ?: new ScriptDispatcher($event, Config::load($event->getIO(), $event->getComposer()->getConfig()));
        $this->includeFileWriter = $includeFileWriter ?: new IncludeFileWriter($event, Typo3PluginConfig::load($event->getComposer()), new Filesystem());
    }

    public function preAutoloadDump()
    {
        $this->includeFileWriter->write();
    }

    /**
     * Action called after autoload dump
     */
    public function postAutoloadDump()
    {
        $this->scriptDispatcher->executeScripts();
    }
}

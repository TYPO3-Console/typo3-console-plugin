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

use Composer\Script\Event;
use Composer\Util\Filesystem;
use Helhum\Typo3ConsolePlugin\IncludeFile\ActiveTypo3ExtensionsToken;
use Helhum\Typo3ConsolePlugin\IncludeFile\BaseDirToken;
use Helhum\Typo3ConsolePlugin\IncludeFile\WebDirToken;
use TYPO3\CMS\Composer\Plugin\Config as Typo3PluginConfig;

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
     * @var IncludeFile
     */
    private $includeFile;

    /**
     * PluginImplementation constructor.
     *
     * @param Event $event
     * @param IncludeFile $includeFile
     * @param ScriptDispatcher $scriptDispatcher
     */
    public function __construct(Event $event, ScriptDispatcher $scriptDispatcher = null, IncludeFile $includeFile = null)
    {
        $this->event = $event;
        $this->scriptDispatcher = $scriptDispatcher ?: new ScriptDispatcher($event);
        $this->includeFile = $includeFile
            ?: new IncludeFile($event->getIO(),
                [
                    new BaseDirToken($event->getIO(), Typo3PluginConfig::load($event->getComposer())),
                    new WebDirToken($event->getIO(), Typo3PluginConfig::load($event->getComposer())),
                    new ActiveTypo3ExtensionsToken($event->getIO(), $event->getComposer(), Config::load($event->getIO(), $event->getComposer()->getConfig()), $event->isDevMode()),
                ],
                new Filesystem()
            );
    }

    public function preAutoloadDump()
    {
        $this->includeFile->write();
    }

    /**
     * Action called after autoload dump
     */
    public function postAutoloadDump()
    {
        $this->scriptDispatcher->executeScripts();
    }
}

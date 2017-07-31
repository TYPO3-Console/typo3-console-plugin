<?php
declare(strict_types=1);
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

class PluginImplementation
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var IncludeFile
     */
    private $includeFile;

    /**
     * @param Event $event
     * @param IncludeFile $includeFile
     */
    public function __construct(Event $event, IncludeFile $includeFile = null)
    {
        $this->event = $event;
        $this->includeFile = $includeFile
            ?: new IncludeFile(
                $event->getIO(),
                $event->getComposer(),
                [
                    new ActiveTypo3ExtensionsToken($event->getIO(), $event->getComposer(), Config::load($event->getIO(), $event->getComposer()->getConfig()), $event->isDevMode()),
                ],
                new Filesystem()
            );
    }

    public function preAutoloadDump()
    {
        $this->includeFile->register();
    }

    /**
     * Action called after autoload dump
     */
    public function postAutoloadDump()
    {
    }
}

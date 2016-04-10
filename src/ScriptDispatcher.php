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

/**
 * Class ScriptDispatcher
 */
class ScriptDispatcher
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var Config
     */
    private $config;
    
    public function __construct(Event $event, Config $config)
    {
        $this->event = $event;
        $this->config = $config;
    }

    public function executeScripts()
    {
        \Helhum\Typo3Console\Composer\InstallerScripts::setupConsole($this->event, true);
    }
}

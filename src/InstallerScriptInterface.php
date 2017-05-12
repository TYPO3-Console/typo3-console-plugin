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

use Composer\Script\Event as ScriptEvent;

interface InstallerScriptInterface
{
    /**
     * This method is called first. setupConsole is not called if this returns false
     *
     * @param ScriptEvent $event
     * @return bool
     */
    public function shouldRun(ScriptEvent $event);

    /**
     * This is executed, when shouldRun returned true
     *
     * @param ScriptEvent $event
     * @throws \RuntimeException
     * @return bool Return false if the script failed
     */
    public function run(ScriptEvent $event);
}

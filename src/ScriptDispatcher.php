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

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Composer\Composer;
use Composer\Script\Event;
use Helhum\Typo3Console\Composer\InstallerScripts;

class ScriptDispatcher
{
    /**
     * Scripts to execute when console is set up
     *
     * @var array
     */
    private static $scripts = [];

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var ComposerClassLoader
     */
    private $loader;

    /**
     * This registry method is meant to be called during composer preAutoloadDump event by other plugins
     *
     * @param string $installerScript Must be a class that implements InstallerScriptInterface
     * @param int $priority lower
     */
    public static function addInstallerScript($installerScript, $priority = 50)
    {
        self::$scripts[$priority] = $installerScript;
    }

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->composer = $event->getComposer();
    }

    public function executeScripts()
    {
        $this->registerLoader();
        $io = $this->event->getIO();
        InstallerScripts::setupConsole($this->event, true);

        ksort(self::$scripts, SORT_NUMERIC);
        foreach (array_reverse(self::$scripts) as $scriptClass) {
            /** @var InstallerScriptInterface $script */
            $script = new $scriptClass();
            if (!$script instanceof InstallerScriptInterface) {
                throw new \UnexpectedValueException(sprintf('Installer script "%s" does not implement "%s"', $scriptClass, InstallerScriptInterface::class), 1494599103);
            }
            if ($script->shouldRun($this->event)) {
                $io->writeError(sprintf('<info>Executing "%s": </info>', $scriptClass), true, $io::DEBUG);
                if (!$script->run($this->event)) {
                    $io->writeError(sprintf('<error>Executing "%s" failed!</error>', $scriptClass), true);
                }
            } else {
                $io->writeError(sprintf('<info>Skipped executing "%s": </info>', $scriptClass), true, $io::DEBUG);
            }
        }

        $this->unRegisterLoader();
    }

    private function registerLoader()
    {
        $package = $this->composer->getPackage();
        $generator = $this->composer->getAutoloadGenerator();
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $packageMap = $generator->buildPackageMap($this->composer->getInstallationManager(), $package, $packages);
        $map = $generator->parseAutoloads($packageMap, $package);
        $this->loader = $generator->createLoader($map);
        $this->loader->register();
    }

    private function unRegisterLoader()
    {
        $this->loader->unregister();
    }
}

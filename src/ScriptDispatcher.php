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
     * @var Composer
     */
    private $composer;

    /**
     * @var ClassLoader
     */
    private $loader;

    /**
     * ScriptDispatcher constructor.
     *
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
        \Helhum\Typo3Console\Composer\InstallerScripts::setupConsole($this->event, true);
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

    private function unRegisterLoader() {
        $this->loader->unregister();
    }
}

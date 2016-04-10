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
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool
     */
    private $isDev;

    /**
     * @var ClassLoader
     */
    private $loader;

    /**
     * @var ScriptDispatcher
     */
    private $scriptDispatcher;

    /**
     * PluginImplementation constructor.
     *
     * @param Event $event
     * @param ScriptDispatcher $scriptDispatcher
     */
    public function __construct(Event $event, ScriptDispatcher $scriptDispatcher = null)
    {
        $this->event = $event;
        $this->composer = $event->getComposer();
        $this->io = $event->getIO();
        $this->config = Config::load($this->io, $this->composer->getConfig());
        $this->isDev = $event->isDevMode();
        $this->scriptDispatcher = $scriptDispatcher ?: new ScriptDispatcher($event, $this->config);
    }

    /**
     * Action called after autoload dump
     *
     * @return bool
     */
    public function postAutoloadDump()
    {
        $this->registerLoader();
        $this->scriptDispatcher->executeScripts();
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

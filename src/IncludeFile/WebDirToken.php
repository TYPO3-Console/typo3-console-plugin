<?php
namespace Helhum\Typo3ConsolePlugin\IncludeFile;

/*
 * This file is part of the typo3 console plugin package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use TYPO3\CMS\Composer\Plugin\Config as Typo3PluginConfig;

/**
 * Class WebDirToken
 */
class WebDirToken implements TokenInterface
{
    /**
     * @var string
     */
    private $name = 'web-dir';

    /**
     * @var Typo3PluginConfig
     */
    private $typo3PluginConfig;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * WebDirToken constructor.
     *
     * @param IOInterface $io
     * @param Typo3PluginConfig $typo3PluginConfig
     * @param Filesystem $filesystem
     */
    public function __construct(IOInterface $io, Typo3PluginConfig $typo3PluginConfig,  Filesystem $filesystem = null)
    {
        $this->io = $io;
        $this->typo3PluginConfig = $typo3PluginConfig;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getContent()
    {
        $this->validateComposerConfiguration();
        $includeFileFolder = dirname(dirname(__DIR__)) . '/res/php';
        return $this->filesystem->findShortestPathCode(
            $includeFileFolder,
            $this->typo3PluginConfig->get('web-dir'),
            true
        );
    }

    /**
     * Validates the TYPO3 composer installer config and issues warning messages if appropriate
     */
    private function validateComposerConfiguration()
    {
        if ($this->typo3PluginConfig->get('prepare-web-dir') === false && $this->typo3PluginConfig->get('extensions-in-vendor-dir') === true) {
            $this->io->writeError(chr(10) . '<warning>TYPO3 plugin installer configuration "prepare-web-dir" was set to false and "extensions-in-vendor-dir" was set to true</warning>');
            $this->io->writeError(sprintf('<warning>TYPO3 Console will nevertheless assume "%s" to be the TYPO3 root directory.</warning>', $this->typo3PluginConfig->get('web-dir', Typo3PluginConfig::RELATIVE_PATHS)));
            $this->io->writeError('<warning>In case you chose to setup TYPO3 in a different directory, TYPO3 Console will not work.</warning>' . chr(10));
        }
        $normalizedWebDir = $this->filesystem->normalizePath($this->typo3PluginConfig->get('web-dir'));
        $normalizedBaseDir = $this->filesystem->normalizePath($this->typo3PluginConfig->getBaseDir());
        if ($normalizedWebDir === $normalizedBaseDir) {
            $this->io->writeError(chr(10) . '<warning>TYPO3 is configured to be installed in the composer root directory.</warning>');
            $this->io->writeError('<warning>Doing so is bad practice and can be a security risk.</warning>');
            $this->io->writeError('<warning>Please consider using the setting "web-dir" to configure an explicit web directory for TYPO3.</warning>' . chr(10));
        }
    }
}

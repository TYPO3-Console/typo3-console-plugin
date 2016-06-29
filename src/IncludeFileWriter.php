<?php
/**
 * This file is part of the typo3 console project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 */

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
use Composer\Util\Filesystem;
use \TYPO3\CMS\Composer\Plugin\Config as Typo3PluginConfig;

/**
 * Class IncludeFileWriter
 */
class IncludeFileWriter
{
    const RESOURCES_PATH = '/res/php';
    const INCLUDE_FILE = '/autoload-include.php';
    const INCLUDE_FILE_TEMPLATE = '/autoload-include.tmpl.php';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Typo3PluginConfig
     */
    private $typo3PluginConfig;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * IncludeFileWriter constructor.
     *
     * @param Config $config
     * @param Typo3PluginConfig $typo3PluginConfig
     * @param Filesystem $filesystem
     */
    public function __construct(Config $config, Typo3PluginConfig $typo3PluginConfig, Filesystem $filesystem = null)
    {
        $this->config = $config;
        $this->typo3PluginConfig = $typo3PluginConfig;
        $this->filesystem = $this->filesystem ?: new Filesystem();
    }

    public function write()
    {
        $includeFile = $this->filesystem->normalizePath(__DIR__ . '/../' . self::RESOURCES_PATH . '/' . self::INCLUDE_FILE);
        file_put_contents($includeFile, $this->getIncludeFileContent());
    }

    /**
     * Constructs the include file content
     *
     * @return string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function getIncludeFileContent()
    {
        $includeFileTemplate = $this->filesystem->normalizePath(__DIR__ . '/../' . self::RESOURCES_PATH . '/' . self::INCLUDE_FILE_TEMPLATE);
        $pathToTypo3WebCode = $this->filesystem->findShortestPathCode(
            dirname($includeFileTemplate),
            $this->typo3PluginConfig->get('web-dir'),
            true
        );
        $pathToProjectRoot = $this->filesystem->findShortestPathCode(
            dirname($includeFileTemplate),
            $this->typo3PluginConfig->getBaseDir(),
            true
        );
        $includeFileContent = file_get_contents($includeFileTemplate);
        $includeFileContent = self::replaceToken('web-dir', $pathToTypo3WebCode, $includeFileContent);
        $includeFileContent = self::replaceToken('root-dir', $pathToProjectRoot, $includeFileContent);
        $includeFileContent = self::replaceToken('active-typo3-extensions', var_export(implode(',', $this->config->get('active-typo3-extensions')), true), $includeFileContent);

        return $includeFileContent;
    }

    /**
     * Replaces a token in the subject (PHP code)
     *
     * @param string $name
     * @param string $content
     * @param string $subject
     * @return string
     */
    private static function replaceToken($name, $content, $subject)
    {
        return str_replace('\'{$' . $name . '}\'', $content, $subject);
    }
}

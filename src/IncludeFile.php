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

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Helhum\Typo3ConsolePlugin\IncludeFile\TokenInterface;

class IncludeFile
{
    const INCLUDE_FILE = '/helhum/console-autoload-include.php';
    const INCLUDE_FILE_TEMPLATE = '/res/php/autoload-include.tmpl.php';

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var TokenInterface[]
     */
    private $tokens;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * IncludeFile constructor.
     *
     * @param IOInterface $io
     * @param Composer $composer
     * @param TokenInterface[] $tokens
     * @param Filesystem $filesystem
     */
    public function __construct(IOInterface $io, Composer $composer, array $tokens, Filesystem $filesystem = null)
    {
        $this->io = $io;
        $this->composer = $composer;
        $this->tokens = $tokens;
        $this->filesystem = $this->filesystem ?: new Filesystem();
    }

    public function register()
    {
        $this->io->writeError('<info>Register typo3/console-plugin file in root package autoload definition</info>', true, IOInterface::VERBOSE);

        // Generate and write the file
        $includeFile = $this->composer->getConfig()->get('vendor-dir') . self::INCLUDE_FILE;
        file_put_contents($includeFile, $this->getIncludeFileContent());

        // Register the file in the root package
        $rootPackage = $this->composer->getPackage();
        $autoloadDefinition = $rootPackage->getAutoload();
        $autoloadDefinition['files'][] = $includeFile;
        $rootPackage->setAutoload($autoloadDefinition);

        // Load it to expose the paths to further plugin functionality
        require $includeFile;
    }

    /**
     * Constructs the include file content
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getIncludeFileContent(): string
    {
        $includeFileTemplate = $this->filesystem->normalizePath(__DIR__ . '/../' . self::INCLUDE_FILE_TEMPLATE);
        $includeFileContent = file_get_contents($includeFileTemplate);
        foreach ($this->tokens as $token) {
            $includeFileContent = self::replaceToken($token->getName(), $token->getContent(), $includeFileContent);
        }
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
    private static function replaceToken($name, $content, $subject): string
    {
        return str_replace('\'{$' . $name . '}\'', $content, $subject);
    }
}

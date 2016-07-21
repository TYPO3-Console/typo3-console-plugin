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

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Helhum\Typo3ConsolePlugin\IncludeFile\TokenInterface;

/**
 * Class IncludeFile
 */
class IncludeFile
{
    const RESOURCES_PATH = '/res/php';
    const INCLUDE_FILE = '/autoload-include.php';
    const INCLUDE_FILE_TEMPLATE = '/autoload-include.tmpl.php';

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
     * IncludeFile constructor.
     *
     * @param IOInterface $io
     * @param TokenInterface[] $tokens
     * @param Filesystem $filesystem
     */
    public function __construct(IOInterface $io, array $tokens, Filesystem $filesystem = null)
    {
        $this->io = $io;
        $this->tokens = $tokens;
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
    private static function replaceToken($name, $content, $subject)
    {
        return str_replace('\'{$' . $name . '}\'', $content, $subject);
    }
}

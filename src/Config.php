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

class Config
{
    const RELATIVE_PATHS = 1;

    /**
     * @var array
     */
    public static $defaultConfig = [
        'install-binary' => true,
        'install-extension-dummy' => true,
        'active-typo3-extensions' => [],
    ];

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @param string $baseDir
     */
    public function __construct($baseDir = null)
    {
        $this->baseDir = $baseDir;
        // load defaults
        $this->config = static::$defaultConfig;
    }

    /**
     * Merges new config values with the existing ones (overriding)
     *
     * @param array $config
     */
    public function merge($config)
    {
        // override defaults with given config
        if (!empty($config['extra']['helhum/typo3-console']) && is_array($config['extra']['helhum/typo3-console'])) {
            foreach ($config['extra']['helhum/typo3-console'] as $key => $val) {
                $this->config[$key] = $val;
            }
        }
    }

    /**
     * Returns a setting
     *
     * @param  string $key
     * @param  int $flags Options (see class constants)
     * @throws \RuntimeException
     * @return mixed
     */
    public function get(string $key, int $flags = 0)
    {
        switch ($key) {
            case 'some-dir':
                $val = rtrim($this->process($this->config[$key], $flags), '/\\');
                return ($flags & self::RELATIVE_PATHS === 1) ? $val : $this->realpath($val);
            default:
                if (!isset($this->config[$key])) {
                    return null;
                }
                if (!is_string($this->config[$key])) {
                    return $this->config[$key];
                }
                return $this->process($this->config[$key], $flags);
        }
    }

    /**
     * @param int $flags Options (see class constants)
     * @return array
     */
    public function all(int $flags = 0): array
    {
        $all = [];
        foreach (array_keys($this->config) as $key) {
            $all['config'][$key] = $this->get($key, $flags);
        }

        return $all;
    }

    /**
     * @return array
     */
    public function raw(): array
    {
        return [
            'config' => $this->config,
        ];
    }

    /**
     * Checks whether a setting exists
     *
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Replaces {$refs} inside a config string
     *
     * @param  string $value a config string that can contain {$refs-to-other-config}
     * @param  int $flags Options (see class constants)
     * @return string
     */
    protected function process(string $value, int $flags)
    {
        return preg_replace_callback('#\{\$(.+)\}#',
            function ($match) use ($flags) {
                return $this->get($match[1], $flags);
            },
            $value);
    }

    /**
     * Turns relative paths in absolute paths without realpath()
     *
     * Since the dirs might not exist yet we can not call realpath or it will fail.
     *
     * @param  string $path
     * @return string
     */
    protected function realpath(string $path): string
    {
        if ($path === '') {
            return $this->baseDir;
        }

        if ($path[0] === '/' || (!empty($path[1]) && $path[1] === ':')) {
            return $path;
        }

        return $this->baseDir . '/' . $path;
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @param \Composer\IO\IOInterface $io
     * @param \Composer\Config $composerConfig
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return Config
     */
    public static function load(\Composer\IO\IOInterface $io, \Composer\Config $composerConfig): Config
    {
        static $config;
        if ($config === null) {
            $baseDir = realpath(substr($composerConfig->get('vendor-dir'), 0, -strlen($composerConfig->get('vendor-dir', self::RELATIVE_PATHS))));
            $localConfig = \Composer\Factory::getComposerFile();
            $file = new \Composer\Json\JsonFile($localConfig, null, $io);

            $config = new static($baseDir);
            $config->merge($file->read());
        }
        return $config;
    }
}

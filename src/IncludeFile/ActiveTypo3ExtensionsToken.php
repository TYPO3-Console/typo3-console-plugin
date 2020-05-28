<?php
declare(strict_types=1);
namespace Helhum\Typo3ConsolePlugin\IncludeFile;

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
use Composer\Package\PackageInterface;
use Helhum\Typo3ConsolePlugin\Config;

class ActiveTypo3ExtensionsToken implements TokenInterface
{
    /**
     * @var string
     */
    private $name = 'active-typo3-extensions';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var bool
     */
    private $isDevMode;

    /**
     * ActiveTypo3ExtensionsToken constructor.
     *
     * @param IOInterface $io
     * @param Composer $composer
     * @param Config $config
     * @param bool $isDevMode
     */
    public function __construct(IOInterface $io, Composer $composer, Config $config, $isDevMode = false)
    {
        $this->io = $io;
        $this->config = $config;
        $this->composer = $composer;
        $this->isDevMode = $isDevMode;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @throws \RuntimeException
     * @return string
     */
    public function getContent(): string
    {
        $this->io->writeError('<info>Writing TYPO3_ACTIVE_FRAMEWORK_EXTENSIONS environment variable</info>', true, IOInterface::VERBOSE);
        $configuredActiveTypo3Extensions = $this->config->get('active-typo3-extensions');
        if (!is_array($configuredActiveTypo3Extensions)) {
            $this->io->writeError(sprintf('<error>Extra section "active-typo3-extensions" must be array, "%s" given!</error>', gettype($configuredActiveTypo3Extensions)));
            $configuredActiveTypo3Extensions = [];
        }
        if (count($configuredActiveTypo3Extensions) > 0) {
            $this->io->writeError('<warning>Extra section "active-typo3-extensions" has been deprecated!</warning>');
            $this->io->writeError('<warning>Please just add typo3/cms framework packages to the require section in your composer.json of any package.</warning>');
        }
        $activeTypo3Extensions = array_unique(array_merge($configuredActiveTypo3Extensions, $this->getActiveCoreExtensionKeysFromComposer()));
        asort($activeTypo3Extensions);
        $this->io->writeError('<info>The following TYPO3 core extensions are marked as active:</info> ' . implode(', ', $activeTypo3Extensions), true, IOInterface::VERBOSE);
        return var_export(implode(',', $activeTypo3Extensions), true);
    }

    /**
     * @return array
     */
    private function getActiveCoreExtensionKeysFromComposer(): array
    {
        $this->io->writeError('<info>Determine dependencies to typo3/cms framework packages.</info>', true, IOInterface::VERY_VERBOSE);
        $typo3Package = $this->composer->getRepositoryManager()->getLocalRepository()->findPackage('typo3/cms', '*');
        if ($typo3Package) {
            $coreExtensionKeys = $this->getCoreExtensionKeysFromTypo3Package($typo3Package);
        } else {
            $coreExtensionKeys = $this->getCoreExtensionKeysFromInstalledPackages();
        }
        return $coreExtensionKeys;
    }

    /**
     * @param PackageInterface $typo3Package
     * @return array
     */
    private function getCoreExtensionKeysFromTypo3Package(PackageInterface $typo3Package): array
    {
        $coreExtensionKeys = [];
        $frameworkPackages = [];
        foreach ($typo3Package->getReplaces() as $name => $_) {
            if (is_string($name) && strpos($name, 'typo3/cms-') === 0) {
                $frameworkPackages[] = $name;
            }
        }
        $installedPackages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $rootPackage = $this->composer->getPackage();
        $installedPackages[$rootPackage->getName()] = $rootPackage;
        foreach ($installedPackages as $package) {
            $requires = $package->getRequires();
            if ($package === $rootPackage && $this->isDevMode) {
                $requires = array_merge($requires, $package->getDevRequires());
            }
            foreach ($requires as $name => $link) {
                if (is_string($name) && in_array($name, $frameworkPackages, true)) {
                    $extensionKey = $this->determineExtKeyFromPackageName($name);
                    $this->io->writeError(sprintf('The package "%s" requires: "%s"', $package->getName(), $name), true, IOInterface::DEBUG);
                    $this->io->writeError(sprintf('The extension key for package "%s" is: "%s"', $name, $extensionKey), true, IOInterface::DEBUG);
                    $coreExtensionKeys[$name] = $extensionKey;
                }
            }
        }
        return $coreExtensionKeys;
    }

    /**
     * @return array
     */
    private function getCoreExtensionKeysFromInstalledPackages(): array
    {
        $corePackages = [];
        $installedPackages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        foreach ($installedPackages as $package) {
            if ($package->getType() === 'typo3-cms-framework') {
                $extensionKey = $this->determineExtKeyFromPackageName($package->getName());
                $this->io->writeError(sprintf('The framework package "%s" is installed.', $package->getName()), true, IOInterface::DEBUG);
                $this->io->writeError(sprintf('The extension key for package "%s" is: "%s"', $package->getName(), $extensionKey), true, IOInterface::DEBUG);
                $corePackages[$package->getName()] = $extensionKey;
            }
        }
        return $corePackages;
    }

    /**
     * @param string $packageName
     * @return string
     */
    private function determineExtKeyFromPackageName(string $packageName): string
    {
        return str_replace(['typo3/cms-', '-'], ['', '_'], $packageName);
    }
}

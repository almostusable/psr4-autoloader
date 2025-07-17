<?php

namespace AlmostUsable\Psr4Autoloader;

use RuntimeException;
use Throwable;

/**
 * PSR-4 Autoloader Class
 *
 * A minimalistic PSR-4 autoloader implementation for PHP projects.
 */
class Psr4Autoloader
{
    /**
     * An array of namespace prefixes and their base directories
     *
     * @var array
     */
    private array $namespaces = [];


    /**
     * Register the autoloader with SPL autoload stack
     *
     * @return void
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Add a namespace prefix and its base directory to the autoloader
     *
     * @param string $prefix The namespace prefix
     * @param string $baseDir The base directory for the namespace prefix
     * @return void
     */
    public function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = rtrim($prefix, '\\');
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        $this->namespaces[$prefix] = $baseDir;
    }

    /**
     * Load the class file for a given class name
     *
     * @param string $class The fully qualified class name
     * @return bool True if the class was loaded, false otherwise
     */
    public function loadClass(string $class): bool
    {
        // Get the first segment of the namespace
        $prefix = strtok($class, '\\');

        // Check if the namespace prefix exists in our mappings
        if (!array_key_exists($prefix, $this->namespaces)) {
            return false;
        }

        // Get the base directory for this namespace prefix
        $baseDirectory = $this->namespaces[$prefix];

        // Convert the class name to a file path
        $path = $this->fcqnToPath($class, $prefix);

        // Build the full file path
        $file = $baseDirectory . $path;

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
            return true;
        }

        return false;
    }

    /**
     * Convert a fully qualified class name to a file path
     *
     * @param string $fqcn The fully qualified class name
     * @param string $prefix The namespace prefix
     * @return string The file path
     */
    private function fcqnToPath(string $fqcn, string $prefix): string
    {
        // Remove the namespace prefix and leading backslash
        $relativeClass = ltrim(ltrim($fqcn, $prefix), '\\');

        // Convert namespace separators to directory separators
        return str_replace('\\', '/', $relativeClass) . '.php';
    }

    /**
     * Load namespace mappings from composer.json
     *
     * @param string|null $composerJsonPath Path to composer.json file
     * @return bool True if mappings were loaded successfully, false otherwise
     * @throws RuntimeException If there's an error loading the composer.json file
     */
    public function loadMappingsFromComposer(): bool
    {
        try {
            $dir = __DIR__;
            $composerJsonPath = '';

            // Go up the directory tree until we find composer.json or reach the filesystem root
            while ($dir !== '/' && $dir !== '') {
                $potentialPath = $dir . '/composer.json';
                if (file_exists($potentialPath)) {
                    $composerJsonPath = $potentialPath;
                    break;
                }
                // Go up one directory
                $dir = dirname($dir);
            }

            if (!file_exists($composerJsonPath)) {
                throw new RuntimeException('composer.json file not found at: ' . $composerJsonPath);
            }

            $content = file_get_contents($composerJsonPath);
            if ($content === false) {
                throw new RuntimeException('Failed to read composer.json from: ' . $composerJsonPath);
            }

            $configuration = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Invalid JSON in composer.json: ' . json_last_error_msg());
            }

            if (!isset($configuration['autoload']['psr-4']) || !is_array($configuration['autoload']['psr-4'])) {
                throw new RuntimeException('No PSR-4 configuration found in composer.json');
            }

            foreach ($configuration['autoload']['psr-4'] as $prefix => $baseDir) {
                $this->addNamespace($prefix, $baseDir);
            }

            return true;
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to load composer.json: ' . $e->getMessage());
        }
    }
}

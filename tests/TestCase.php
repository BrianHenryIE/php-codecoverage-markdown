<?php

namespace BrianHenryIE\CodeCoverageMarkdown;

use Composer\InstalledVersions;
use SebastianBergmann\CodeCoverage\CodeCoverage;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array<int,array{filePath:string, coverage:CodeCoverage}>
     */
    public static function coverageDataProvider(): array
    {
        $installedMajorVersionRegex = preg_match(
            '/v?(\d+)/',
            InstalledVersions::getVersion('phpunit/php-code-coverage'),
            $installedMajorVersionOutputArray
        );
        if (!$installedMajorVersionRegex) {
            return [];
        }

        $installedMajorVersion = (int) $installedMajorVersionOutputArray[1];

        // Set a writable cache directory for CI environments
        $cacheDir = sys_get_temp_dir() . '/phpunit-code-coverage-cache-' . getmypid();
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        // Ensure the directory is writable, fallback to sys temp dir if not
        if (!is_dir($cacheDir) || !is_writable($cacheDir)) {
            $cacheDir = sys_get_temp_dir();
        }

        if ($installedMajorVersion === 9) {
            $coverage = include __DIR__ . '/fixtures/unitphp.9.cov';
            self::configureCoverageCache($coverage, $cacheDir);
            return [
                9 => [
                    'filePath' => __DIR__ . '/fixtures/unitphp.9.cov',
                    'coverage' => $coverage,
                ],
            ];
        } else {
            $coverage10 = include __DIR__ . '/fixtures/unitphp.10.cov';
            $coverage11 = include __DIR__ . '/fixtures/unitphp.11.cov';
            $coverage12 = include __DIR__ . '/fixtures/unitphp.12.cov';

            self::configureCoverageCache($coverage10, $cacheDir);
            self::configureCoverageCache($coverage11, $cacheDir);
            self::configureCoverageCache($coverage12, $cacheDir);

            return [
                10 => [
                    'filePath' => __DIR__ . '/fixtures/unitphp.10.cov',
                    'coverage' => $coverage10,
                ],
                11 => [
                    'filePath' => __DIR__ . '/fixtures/unitphp.11.cov',
                    'coverage' => $coverage11,
                ],
                12 => [
                    'filePath' => __DIR__ . '/fixtures/unitphp.12.cov',
                    'coverage' => $coverage12,
                ],
            ];
        }
    }

    /**
     * Configure the coverage object to use a writable cache directory.
     *
     * This prevents errors in CI environments where the cached paths
     * from fixture files don't exist.
     *
     * @param CodeCoverage $coverage
     * @param string $cacheDir
     */
    private static function configureCoverageCache(CodeCoverage $coverage, string $cacheDir): void
    {
        try {
            // Use reflection to set the cache directory since there's no public setter
            $reflection = new \ReflectionClass($coverage);

            // Try common property names used in different versions
            $propertyNames = ['cacheDirectory', 'cache'];
            foreach ($propertyNames as $propertyName) {
                if ($reflection->hasProperty($propertyName)) {
                    $property = $reflection->getProperty($propertyName);
                    $property->setAccessible(true);
                    $property->setValue($coverage, $cacheDir);
                    return;
                }
            }
        } catch (\Throwable $e) {
            // Silently fail if we can't set the cache directory
            // Tests will handle the error if it occurs
        }
    }
}

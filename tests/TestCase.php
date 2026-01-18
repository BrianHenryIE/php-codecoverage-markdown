<?php

namespace BrianHenryIE\CodeCoverageMarkdown;

use Composer\InstalledVersions;
use SebastianBergmann\CodeCoverage\CodeCoverage;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array<int,array{coverage:CodeCoverage}>
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

        if ($installedMajorVersionRegex === 9) {
            return [
                9 => [
                    'coverage' => include __DIR__ . '/fixtures/unitphp.9.cov',
                ],
            ];
        } else {
            return [
                10 => [
                    'coverage' => include __DIR__ . '/fixtures/unitphp.10.cov',
                ],
                11 => [
                    'coverage' => include __DIR__ . '/fixtures/unitphp.11.cov',
                ],
                12 => [
                    'coverage' => include __DIR__ . '/fixtures/unitphp.12.cov',
                ],
            ];
        }
    }
}

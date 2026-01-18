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
        $installedMajorVersion = preg_match(
            '/v?(\d+)/',
            InstalledVersions::getVersion('phpunit/php-code-coverage'),
            $output_array
        );
        if (!$installedMajorVersion) {
            return [];
        }

        $fixtures = [
            9 => [
                'coverage' => include __DIR__ . '/fixtures/unitphp.9.cov',
            ],
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


        if ($installedMajorVersion == 9) {
            return [
                9 => $fixtures[9]
            ];
        } else {
            unset($fixtures[9]);
        }

        return $fixtures;
    }
}

<?php

namespace BrianHenryIE\CodeCoverageMarkdown;

use SebastianBergmann\CodeCoverage\CodeCoverage;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array<int,array{coverage:CodeCoverage}>
     */
    public static function coverageDataProvider(): array
    {
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

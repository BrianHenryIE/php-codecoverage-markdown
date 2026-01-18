<?php

namespace BrianHenryIE\CodeCoverageMarkdown;

use ReflectionProperty;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use Throwable;

class NullDriver extends Driver
{
    public function nameAndVersion(): string
    {
        return 'null';
    }

    public function start(): void
    {
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
    }

    /**
     * The deserialized php-code-coverage 12 .cov does not have a Driver set.
     *
     * We will use reflection and try to `getValue()` which when it fails means we need to set a driver.
     *
     * @see CodeCoverage::$driver
     */
    public static function maybeSetCoverageDriver(CodeCoverage $coverage): void
    {
        $property = new ReflectionProperty(CodeCoverage::class, 'driver');
        // `::setAccessible()` is no-op in PHP >8.1 and errors in PHP 8.5.
        if (!version_compare(PHP_VERSION, '8.5', '>=')) {
            $property->setAccessible(true);
        }
        try {
            $property->getValue($coverage); // @phpstan-ignore method.resultUnused
        } catch (Throwable $t) {
            $nullDriver = new NullDriver();
            $property->setValue($coverage, $nullDriver);
        }
    }
}

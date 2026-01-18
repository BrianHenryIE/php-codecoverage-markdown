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
}

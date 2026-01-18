<?php

namespace BrianHenryIE\CodeCoverageMarkdown;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;

class CoverageFilterTest extends \BrianHenryIE\CodeCoverageMarkdown\TestCase
{
    private function isXdebugCoverageEnabled(): bool
    {
        if (!extension_loaded('xdebug')) {
            return false;
        }

        $xdebugMode = getenv('XDEBUG_MODE') ?: ini_get('xdebug.mode');
        return $xdebugMode && str_contains($xdebugMode, 'coverage');
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testFilterCoverageWithEmptyList(string $filePath, CodeCoverage $coverage): void
    {
        $result = CoverageFilter::filterCoverage($coverage, []);

        $this->assertInstanceOf(CodeCoverage::class, $result);
        $this->assertSame($coverage, $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testFilterCoverageWithFullPath(string $filePath, CodeCoverage $coverage): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $data = $coverage->getData();
        $lineCoverage = $data->lineCoverage();
        $allFiles = array_keys($lineCoverage);

        if (empty($allFiles)) {
            $this->markTestSkipped('No coverage data available in fixture');
        }

        $firstFile = $allFiles[0];
        $result = CoverageFilter::filterCoverage($coverage, [$firstFile]);

        $this->assertInstanceOf(CodeCoverage::class, $result);

        $resultData = $result->getData();
        $resultLineCoverage = $resultData->lineCoverage();

        $this->assertArrayHasKey($firstFile, $resultLineCoverage);
        $this->assertCount(1, $resultLineCoverage);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testFilterCoverageWithRelativePath(string $filePath, CodeCoverage $coverage): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $data = $coverage->getData();
        $lineCoverage = $data->lineCoverage();
        $allFiles = array_keys($lineCoverage);

        if (empty($allFiles)) {
            $this->markTestSkipped('No coverage data available in fixture');
        }

        $firstFile = $allFiles[0];
        $relativePath = basename($firstFile);

        $result = CoverageFilter::filterCoverage($coverage, [$relativePath]);

        $this->assertInstanceOf(CodeCoverage::class, $result);

        $resultData = $result->getData();
        $resultLineCoverage = $resultData->lineCoverage();

        $this->assertNotEmpty($resultLineCoverage);

        $foundMatch = false;
        foreach (array_keys($resultLineCoverage) as $path) {
            if (str_ends_with($path, $relativePath)) {
                $foundMatch = true;
                break;
            }
        }

        $this->assertTrue($foundMatch, 'Expected to find file matching relative path');
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testFilterCoverageWithMultipleFiles(string $filePath, CodeCoverage $coverage): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $data = $coverage->getData();
        $lineCoverage = $data->lineCoverage();
        $allFiles = array_keys($lineCoverage);

        if (count($allFiles) < 2) {
            $this->markTestSkipped('Need at least 2 files in coverage data');
        }

        $filesToInclude = [$allFiles[0], $allFiles[1]];
        $result = CoverageFilter::filterCoverage($coverage, $filesToInclude);

        $this->assertInstanceOf(CodeCoverage::class, $result);

        $resultData = $result->getData();
        $resultLineCoverage = $resultData->lineCoverage();

        $this->assertCount(2, $resultLineCoverage);
        $this->assertArrayHasKey($allFiles[0], $resultLineCoverage);
        $this->assertArrayHasKey($allFiles[1], $resultLineCoverage);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testFilterCoverageWithNonexistentFile(string $filePath, CodeCoverage $coverage): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $result = CoverageFilter::filterCoverage($coverage, ['nonexistent/file.php']);

        $this->assertInstanceOf(CodeCoverage::class, $result);

        $resultData = $result->getData();
        $resultLineCoverage = $resultData->lineCoverage();

        $this->assertEmpty($resultLineCoverage);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testFilterCoveragePreservesBranchCoverage(string $filePath, CodeCoverage $coverage): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        NullDriver::maybeSetCoverageDriver($coverage);

        $hasBranchCoverage = $coverage->collectsBranchAndPathCoverage();

        $data = $coverage->getData();
        $lineCoverage = $data->lineCoverage();
        $allFiles = array_keys($lineCoverage);

        if (empty($allFiles)) {
            $this->markTestSkipped('No coverage data available in fixture');
        }

        $result = CoverageFilter::filterCoverage($coverage, [$allFiles[0]]);

        $this->assertSame($hasBranchCoverage, $result->collectsBranchAndPathCoverage());
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testFilterCoveragePreservesTests(string $filePath, CodeCoverage $coverage): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $originalTests = $coverage->getTests();

        $data = $coverage->getData();
        $lineCoverage = $data->lineCoverage();
        $allFiles = array_keys($lineCoverage);

        if (empty($allFiles)) {
            $this->markTestSkipped('No coverage data available in fixture');
        }

        $result = CoverageFilter::filterCoverage($coverage, [$allFiles[0]]);

        $this->assertSame($originalTests, $result->getTests());
    }
}

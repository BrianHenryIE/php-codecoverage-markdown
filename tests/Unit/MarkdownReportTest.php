<?php

namespace BrianHenryIE\CodeCoverageMarkdown;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Thresholds;

/**
 * @coversDefaultClass \BrianHenryIE\CodeCoverageMarkdown\MarkdownReport
 */
class MarkdownReportTest extends \BrianHenryIE\CodeCoverageMarkdown\TestCase
{
    protected string $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectRoot = dirname(__DIR__, 2) . '/';
    }

    private function isXdebugCoverageEnabled(): bool
    {
        if (!extension_loaded('xdebug')) {
            return false;
        }

        $xdebugMode = getenv('XDEBUG_MODE') ?: ini_get('xdebug.mode');
        return $xdebugMode && str_contains($xdebugMode, 'coverage');
    }

    public function testConstructWithDefaults(): void
    {
        $report = new MarkdownReport();

        $this->assertInstanceOf(MarkdownReport::class, $report);
    }

    public function testConstructWithCustomGenerator(): void
    {
        $generator = 'Custom Generator v1.0';
        $report = new MarkdownReport($generator);

        $this->assertInstanceOf(MarkdownReport::class, $report);
    }

    public function testConstructWithCustomThresholds(): void
    {
        $thresholds = class_exists(Thresholds::class) ? Thresholds::from(60, 80) : null;
        $report = new MarkdownReport('', $thresholds);

        $this->assertInstanceOf(MarkdownReport::class, $report);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessReturnsString(string $filePath, CodeCoverage $coverage): void
    {
        $report = new MarkdownReport();

        $result = $report->process($coverage, $this->projectRoot);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessWithoutBaseUrl(string $filePath, CodeCoverage $coverage): void
    {
        $report = new MarkdownReport();
        $result = $report->process($coverage, $this->projectRoot, null);

        $this->assertIsString($result);
        $this->assertStringNotContainsString('http', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessWithBaseUrl(string $filePath, CodeCoverage $coverage): void
    {
        $report = new MarkdownReport();
        $baseUrl = 'https://github.com/user/repo/blob/main/%s';
        $result = $report->process($coverage, $this->projectRoot, $baseUrl);

        $this->assertIsString($result);
        $this->assertStringContainsString('https://github.com', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessWithCoveredFilesList(string $filePath, CodeCoverage $coverage): void
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

        $report = new MarkdownReport();
        $result = $report->process($coverage, $this->projectRoot, null, [$allFiles[0]]);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessOutputContainsMarkdownTable(string $filePath, CodeCoverage $coverage): void
    {
        $report = new MarkdownReport();
        $result = $report->process($coverage, $this->projectRoot);

        $this->assertStringContainsString('|', $result);
        $this->assertStringContainsString('Total', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessOutputContainsCoveragePercentages(string $filePath, CodeCoverage $coverage): void
    {
        $report = new MarkdownReport();
        $result = $report->process($coverage, $this->projectRoot);

        $this->assertMatchesRegularExpression('/\d+\.?\d*%/', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessOutputContainsFileNames(string $filePath, CodeCoverage $coverage): void
    {
        $data = $coverage->getData();
        $lineCoverage = $data->lineCoverage();
        $allFiles = array_keys($lineCoverage);

        if (empty($allFiles)) {
            $this->markTestSkipped('No coverage data available in fixture');
        }

        $report = new MarkdownReport();
        $result = $report->process($coverage, $this->projectRoot);

        $fileName = basename($allFiles[0]);
        $this->assertStringContainsString($fileName, $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessWithEmptyCoveredFilesListShowsAllFiles(string $filePath, CodeCoverage $coverage): void
    {
        $report = new MarkdownReport();
        $resultAll = $report->process($coverage, $this->projectRoot, null, []);
        $resultNone = $report->process($coverage, $this->projectRoot);

        $this->assertEquals($resultAll, $resultNone);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessUsesGeneratorInOutput(string $filePath, CodeCoverage $coverage): void
    {
        $report = new MarkdownReport();
        $result = $report->process($coverage, $this->projectRoot);

        $this->assertMatchesRegularExpression('/<!-- autogenerated.*-->/', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testProcessIncludesDateInOutput(string $filePath, CodeCoverage $coverage): void
    {
        $report = new MarkdownReport();
        $result = $report->process($coverage, $this->projectRoot);

        $this->assertMatchesRegularExpression('/\w{3}, \w{3} \d{1,2}, \d{4}/', $result);
    }
}

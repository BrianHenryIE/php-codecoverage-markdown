<?php

namespace BrianHenryIE\CodeCoverageMarkdown;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Thresholds;

class DirectoryTest extends \BrianHenryIE\CodeCoverageMarkdown\TestCase
{
    protected string $projectRoot;
    protected string $templatePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectRoot = dirname(__DIR__, 2) . '/';
        $this->templatePath = dirname(__DIR__, 2) . '/src/MarkdownTemplate/';
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testConstructor(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $this->assertInstanceOf(Directory::class, $directory);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testRenderReturnsString(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $result = $directory->render($report);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testRenderContainsMarkdownTable(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $result = $directory->render($report);

        $this->assertStringContainsString('|', $result);
        $this->assertStringContainsString('|-|-|-|-|', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testRenderContainsTotalRow(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $result = $directory->render($report);

        $this->assertStringContainsString('Total', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testRenderWithBaseUrl(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';
        $baseUrl = 'https://github.com/user/repo/blob/main/%s';

        $directory = new Directory(
            $this->projectRoot,
            $baseUrl,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $result = $directory->render($report);

        $this->assertStringContainsString('https://github.com', $result);
        $this->assertStringContainsString('[', $result);
        $this->assertStringContainsString('](', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testRenderWithoutBaseUrl(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $result = $directory->render($report);

        $this->assertStringNotContainsString('http', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testRenderContainsCoveragePercentages(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $result = $directory->render($report);

        $this->assertMatchesRegularExpression('/\d+\.?\d*%/', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testRenderWithGenerator(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $result = $directory->render($report);

        // Just verify generator comment is present
        $this->assertMatchesRegularExpression('/<!-- autogenerated.*-->/', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testRenderContainsDate(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';
        $date = 'Mon, Jan 17, 2026, 12:00:00 PST';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            $date,
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $result = $directory->render($report);

        $this->assertStringContainsString($date, $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testCoverageBarWithFullCoverage(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $reflection = new \ReflectionClass($directory);
        $method = $reflection->getMethod('coverageBar');
        $method->setAccessible(true);

        $result = $method->invoke($directory, 100.0);

        $this->assertStringContainsString('🟩', $result);
        $this->assertStringNotContainsString('⬜', $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testCoverageBarWithNoCoverage(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $reflection = new \ReflectionClass($directory);
        $method = $reflection->getMethod('coverageBar');
        $method->setAccessible(true);

        $result = $method->invoke($directory, 0.0);

        $this->assertStringNotContainsString('🟩', $result);
        $this->assertStringNotContainsString('🟧', $result);
        $this->assertStringNotContainsString('🟥', $result);
        $this->assertEquals(str_repeat('⬜', 10), $result);
    }

    /**
     * @dataProvider \BrianHenryIE\CodeCoverageMarkdown\TestCase::coverageDataProvider
     */
    public function testCoverageBarWithPartialCoverage(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();
        $basePath = $report->pathAsString() . '/';

        $directory = new Directory(
            $this->projectRoot,
            null,
            $basePath,
            $this->templatePath,
            'Test Generator',
            date('D, M j, Y, G:i:s T'),
            class_exists(Thresholds::class) ? Thresholds::default() : null,
            false
        );

        $reflection = new \ReflectionClass($directory);
        $method = $reflection->getMethod('coverageBar');
        $method->setAccessible(true);

        $result = $method->invoke($directory, 50.0);

        $this->assertNotEmpty($result);
        $this->assertEquals(10, mb_strlen($result));
    }
}

<?php

/**
 * Generates markdown coverage reports from PHPUnit CodeCoverage objects.
 *
 * Based on phpunit/php-code-coverage HTML report.
 *
 * @see \SebastianBergmann\CodeCoverage\Report\Html\Facade
 */

declare(strict_types=1);

namespace BrianHenryIE\CodeCoverageMarkdown;

use ReflectionProperty;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Thresholds;
use Throwable;

class MarkdownReport
{
    /** @var string $templatePath */
    private $templatePath;
    /** @var string $generator */
    private $generator;
    /** @var ?Thresholds $thresholds */
    private $thresholds;

    /**
     * @param ?Thresholds $thresholds
     */
    public function __construct(
        string $generator = '',
        $thresholds = null
    ) {
        $this->generator     = $generator;
        $this->thresholds    = $thresholds ?? (class_exists(Thresholds::class) ? Thresholds::default() : null);
        $this->templatePath  = __DIR__ . '/MarkdownTemplate/';
    }

    /**
     * Generate a markdown report from a CodeCoverage object.
     *
     * @param CodeCoverage $coverage The coverage data to report on
     * @param string $projectRoot The file path string to remove before prepending the base URL
     * @param string|null $baseUrl The URL to prefix to each path (optional)
     * @param string[] $coveredFilesList List of files to include in the report (empty for all files)
     * @return string The markdown report content
     */
    public function process(
        CodeCoverage $coverage,
        string $projectRoot,
        ?string $baseUrl = null,
        array $coveredFilesList = []
    ): string {
        $filteredCoverage = CoverageFilter::filterCoverage($coverage, $coveredFilesList);

        $report = $filteredCoverage->getReport();

        $basePath = $report->pathAsString() . '/';

        $date   = date('D, M j, Y, G:i:s T');

        $this->maybeSetMockCoverageDriver($coverage);

        $directory = new Directory(
            $projectRoot,
            $baseUrl,
            $basePath,
            $this->templatePath,
            $this->generator,
            $date,
            $this->thresholds,
            $coverage->collectsBranchAndPathCoverage(),
        );

        return $directory->render($report);
    }

    /**
     * The deserialized php-code-coverage 12 .cov does not have a Driver set.
     *
     * We will use reflection and try to `getValue()` which when it fails means we need to set a driver.
     *
     * @see CodeCoverage::$driver
     */
    protected function maybeSetMockCoverageDriver(CodeCoverage $coverage): void
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

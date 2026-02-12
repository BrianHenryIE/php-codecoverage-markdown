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
    private $thresholds; // @phpstan-ignore class.notFound

    /**
     * @param ?Thresholds $thresholds
     */
    public function __construct(
        string $generator = '',
        $thresholds = null // @phpstan-ignore class.notFound
    ) {
        $this->generator     = $generator;
        $this->thresholds    = $thresholds ?? (class_exists(Thresholds::class) ? Thresholds::default() : null);
        $this->templatePath  = __DIR__ . '/MarkdownTemplate/';
    }

    /**
     * Generate a markdown report from a CodeCoverage object.
     *
     * @param CodeCoverage $coverage The coverage data to report on
     * @param string|null $baseUrl The URL to prefix to each path (optional)
     * @param string[] $coveredFilesList List of files to include in the report (empty for all files)
     * @return string The markdown report content
     */
    public function process(
        CodeCoverage $coverage,
        ?string $baseUrl = null,
        array $coveredFilesList = []
    ): string {
        $filteredCoverage = CoverageFilter::filterCoverage($coverage, $coveredFilesList);

        $report = $filteredCoverage->getReport();

        $basePath = $report->pathAsString() . '/';

        $date   = date('D, M j, Y, G:i:s T');

        SetNullDriver::maybeSetCoverageDriver($coverage);

        $directory = new Directory(
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
}

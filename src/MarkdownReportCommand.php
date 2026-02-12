<?php

/**
 * CLI command to generate markdown coverage reports.
 *
 * Intended for GitHub Actions to output code coverage in PR comments.
 */

namespace BrianHenryIE\CodeCoverageMarkdown;

use Exception;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownReportCommand extends Command
{
    protected MarkdownReport $markdownReport;

    /**
     * Current working directory, with trailing slash.
     *
     * @see getcwd()
     */
    protected string $cwd;

    /**
     * @param ?string $name Symfony parameter.
     * @param ?MarkdownReport $markdownReport
     * @throws Exception
     */
    public function __construct(
        ?string $name = null,
        ?MarkdownReport $markdownReport = null
    ) {
        parent::__construct($name);

        $this->markdownReport = $markdownReport ?? new MarkdownReport();
    }

    /**
     * Configure the command options.
     *
     * @used-by Command::run()
     * @see Command::configure()
     * @return void
     */
    protected function configure()
    {
        $this->setName('generate');
        $this->setDescription('Generate a markdown report from PHPUnit code coverage data.');

        $this->addOption(
            'input-file',
            'i',
            InputArgument::OPTIONAL,
            'Path to a .cov PHP code coverage file.',
        );

        // To link to the file in a PR at the last commit:
        // https://github.com/<company/project>/blob/<sha>/<path/to/file.php>
        // Use %s in the string to replace with the file path. Or omit it and the path will be appended.
        // The path is relative to the project root, currently the working directory.
         $this->addOption(
             'base-url',
             'b',
             InputArgument::OPTIONAL,
             'Base URL where source files are hosted (e.g., GitHub blob URL).',
         );

         // or null to output to stdout
        $this->addOption(
            'output-file',
            'o',
            InputArgument::OPTIONAL,
            'Output file path (default: stdout).',
        );

         // or absent to include all files in the report
        $this->addOption(
            'covered-files',
            'c',
            InputArgument::OPTIONAL,
            'Comma-separated list of files to include in the report.',
        );
    }

    /**
     * Execute the command.
     *
     * @used-by Command::run()
     * @see Command::execute()
     *
     * @param InputInterface $input {@see ArgvInput}
     * @param OutputInterface $output
     *
     * @return int Shell exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $coverageFilePath = $input->getOption('input-file');
        $baseUrl = $input->getOption('base-url');
        $outputFile = $input->getOption('output-file');
        $coveredFilesList = $input->getOption('covered-files') ?: '';

        if (!$coverageFilePath) {
            $output->writeln('<error>Missing required option: --input-file</error>');
            return Command::FAILURE;
        }

        if (!is_readable($coverageFilePath)) {
            $output->writeln('<error>Unable to read coverage file: ' . $coverageFilePath . '</error>');
            return Command::FAILURE;
        }

        try {
            $coverage = include $coverageFilePath;

            if (!$coverage instanceof CodeCoverage) {
                $output->writeln(
                    sprintf(
                        '<error>Coverage file %s did not return a CodeCoverage object. ' .
                        'It may have been created with an incompatible PHPUnit version.</error>',
                        $coverageFilePath
                    )
                );
                return Command::FAILURE;
            }
        } catch (\Throwable $exception) {
            $output->writeln(
                sprintf(
                    '<error>Failed to load coverage file %s: %s</error>',
                    $coverageFilePath,
                    $exception->getMessage()
                )
            );
            return Command::FAILURE;
        }

        // At this point, we don't care that the files exist or not. We will check str_ends_with() to filter later.
        $coveredFilesList = array_map('trim', explode(',', $coveredFilesList));

        if ($baseUrl && !str_contains($baseUrl, '%s')) {
            $baseUrl = $baseUrl . '%s';
        }

        try {
            $result = $this->markdownReport->process($coverage, $baseUrl, $coveredFilesList);

            if ($outputFile) {
                if (file_put_contents($outputFile, $result) === false) {
                    $output->writeln(sprintf('<error>Failed to write to file: %s</error>', $outputFile));
                    return Command::FAILURE;
                }
                $output->writeln(sprintf('<info>Markdown report written to: %s</info>', $outputFile));
            } else {
                $output->write($result);
            }

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            return Command::FAILURE;
        }
    }
}

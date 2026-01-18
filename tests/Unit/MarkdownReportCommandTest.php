<?php

namespace BrianHenryIE\CodeCoverageMarkdown;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MarkdownReportCommandTest extends \BrianHenryIE\CodeCoverageMarkdown\TestCase
{
    protected string $coverageFile;

    private function isXdebugCoverageEnabled(): bool
    {
        if (!extension_loaded('xdebug')) {
            return false;
        }

        $xdebugMode = getenv('XDEBUG_MODE') ?: ini_get('xdebug.mode');
        return $xdebugMode && str_contains($xdebugMode, 'coverage');
    }

    public function testCommandConstructor(): void
    {
        $command = new MarkdownReportCommand();

        $this->assertInstanceOf(MarkdownReportCommand::class, $command);
    }

    public function testCommandConstructorWithCustomMarkdownReport(): void
    {
        $markdownReport = new MarkdownReport();
        $command = new MarkdownReportCommand(null, $markdownReport);

        $this->assertInstanceOf(MarkdownReportCommand::class, $command);
    }

    public function testCommandConfiguration(): void
    {
        $command = new MarkdownReportCommand();

        $this->assertEquals('generate', $command->getName());
        $this->assertNotEmpty($command->getDescription());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('input-file'));
        $this->assertTrue($definition->hasOption('base-url'));
        $this->assertTrue($definition->hasOption('output-file'));
        $this->assertTrue($definition->hasOption('covered-files'));
    }

    public function testExecuteWithMissingInputFile(): void
    {
        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $this->assertEquals(1, $commandTester->getStatusCode());
        $this->assertStringContainsString('Missing required option: --input-file', $commandTester->getDisplay());
    }

    public function testExecuteWithNonReadableFile(): void
    {
        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--input-file' => '/nonexistent/file.cov',
        ]);

        $this->assertEquals(1, $commandTester->getStatusCode());
        $this->assertStringContainsString('Unable to read coverage file', $commandTester->getDisplay());
    }

    public function testExecuteWithValidInputFileToStdout(): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--input-file' => $this->coverageFile,
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('|', $output);
        $this->assertStringContainsString('Total', $output);
    }

    public function testExecuteWithOutputFile(): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $outputFile = sys_get_temp_dir() . '/coverage-test-' . uniqid() . '.md';

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        try {
            $commandTester->execute([
                '--input-file' => $this->coverageFile,
                '--output-file' => $outputFile,
            ]);

            $this->assertEquals(0, $commandTester->getStatusCode());
            $this->assertFileExists($outputFile);

            $content = file_get_contents($outputFile);
            $this->assertStringContainsString('|', $content);
            $this->assertStringContainsString('Total', $content);

            $this->assertStringContainsString('Markdown report written to', $commandTester->getDisplay());
        } finally {
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
        }
    }

    public function testExecuteWithBaseUrl(): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--input-file' => $this->coverageFile,
            '--base-url' => 'https://github.com/user/repo/blob/main/',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('https://github.com', $output);
    }

    public function testExecuteWithBaseUrlWithoutPlaceholder(): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--input-file' => $this->coverageFile,
            '--base-url' => 'https://github.com/user/repo/blob/main',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('https://github.com', $output);
    }

    public function testExecuteWithCoveredFiles(): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $coverage = include $this->coverageFile;
        $data = $coverage->getData();
        $lineCoverage = $data->lineCoverage();
        $allFiles = array_keys($lineCoverage);

        if (empty($allFiles)) {
            $this->markTestSkipped('No coverage data available in fixture');
        }

        $firstFile = basename($allFiles[0]);

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--input-file' => $this->coverageFile,
            '--covered-files' => $firstFile,
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithMultipleCoveredFiles(): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $coverage = include $this->coverageFile;
        $data = $coverage->getData();
        $lineCoverage = $data->lineCoverage();
        $allFiles = array_keys($lineCoverage);

        if (count($allFiles) < 2) {
            $this->markTestSkipped('Need at least 2 files in coverage data');
        }

        $filesList = basename($allFiles[0]) . ',' . basename($allFiles[1]);

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--input-file' => $this->coverageFile,
            '--covered-files' => $filesList,
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithInvalidCoverageFile(): void
    {
        $invalidFile = sys_get_temp_dir() . '/invalid-' . uniqid() . '.cov';
        file_put_contents($invalidFile, '<?php return "not a coverage object";');

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        try {
            $commandTester->execute([
                '--input-file' => $invalidFile,
            ]);

            $this->assertEquals(1, $commandTester->getStatusCode());
            $this->assertStringContainsString('did not return a CodeCoverage object', $commandTester->getDisplay());
        } finally {
            if (file_exists($invalidFile)) {
                unlink($invalidFile);
            }
        }
    }

    public function testExecuteWithCorruptedCoverageFile(): void
    {
        $corruptedFile = sys_get_temp_dir() . '/corrupted-' . uniqid() . '.cov';
        file_put_contents($corruptedFile, '<?php throw new Exception("Error loading file");');

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        try {
            $commandTester->execute([
                '--input-file' => $corruptedFile,
            ]);

            $this->assertEquals(1, $commandTester->getStatusCode());
            $this->assertStringContainsString('Failed to load coverage file', $commandTester->getDisplay());
        } finally {
            if (file_exists($corruptedFile)) {
                unlink($corruptedFile);
            }
        }
    }

    public function testExecuteWithShortOptions(): void
    {
        if (!$this->isXdebugCoverageEnabled()) {
            $this->markTestSkipped('Xdebug coverage mode is not enabled');
        }

        $application = new Application();
        $application->add(new MarkdownReportCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '-i' => $this->coverageFile,
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}

# PHP Code Coverage Markdown

[![PHP 8.1](https://img.shields.io/badge/PHP-8.1-8892BF.svg?logo=php)](https://php.net)

Generate markdown coverage reports from PHPUnit code coverage data, perfect for GitHub PR comments.

## Features

* 📊 Convert PHPUnit `.cov` coverage files to markdown
* 🔗 Link files to GitHub blob URLs
* 📝 Filter reports to specific files
* 🎨 Visual coverage bars using emojis (🟩🟧🟥⬜)
* 🚀 GitHub Actions integration

## Installation

Via Composer:

```bash
composer require --dev brianhenryie/php-codecoverage-markdown
```

Or via Phive:

```bash
phive install brianhenryie/php-codecoverage-markdown
```

## Usage

### CLI

Generate a markdown report from a PHPUnit coverage file:

```bash
php-codecoverage-markdown \
  --input-file tests/_reports/php.cov \
  --output-file coverage-report.md
```

With GitHub links:

```bash
php-codecoverage-markdown \
  --input-file tests/_reports/php.cov \
  --base-url "https://github.com/user/repo/blob/main/%s" \
  --output-file coverage-report.md
```

Filter to specific files:

```bash
php-codecoverage-markdown \
  --input-file tests/_reports/php.cov \
  --covered-files "src/MyClass.php,src/AnotherClass.php" \
  --output-file coverage-report.md
```

### Programmatic Usage

```php
use BrianHenryIE\CodeCoverageMarkdown\MarkdownReport;
use SebastianBergmann\CodeCoverage\CodeCoverage;

/** @var CodeCoverage $coverage */
$coverage = include 'path/to/coverage.cov';

$report = new MarkdownReport();
$markdown = $report->process(
    coverage: $coverage,
    projectRoot: '/path/to/project/',
    baseUrl: 'https://github.com/user/repo/blob/main/%s',
    coveredFilesList: ['src/MyClass.php']
);

file_put_contents('coverage-report.md', $markdown);
```

## GitHub Actions Example

Use this tool to post coverage reports as PR comments:

```yaml
name: Code Coverage Report

on:
  pull_request:
    types: [opened, synchronize]

jobs:
  coverage:
    runs-on: ubuntu-latest

    permissions:
      pull-requests: write

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          coverage: xdebug

      - name: Install dependencies
        run: composer install

      - name: Run tests with coverage
        run: XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-php coverage.cov

      - name: Generate markdown report
        run: |
          vendor/bin/php-codecoverage-markdown \
            --input-file coverage.cov \
            --base-url "https://github.com/${{ github.repository }}/blob/${{ github.event.pull_request.head.sha }}/%s" \
            --output-file coverage-report.md

      - name: Comment on PR
        uses: mshick/add-pr-comment@v2
        with:
          message-path: coverage-report.md
```

## Options

| Option | Short | Description |
|--------|-------|-------------|
| `--input-file` | `-i` | Path to PHPUnit `.cov` coverage file (required) |
| `--output-file` | `-o` | Output file path (default: stdout) |
| `--base-url` | `-b` | Base URL for source file links (use `%s` as placeholder) |
| `--covered-files` | `-c` | Comma-separated list of files to include |

## Output Format

The markdown report includes:

* Total coverage summary
* Per-file coverage details with:
  * Lines coverage percentage
  * Visual coverage bar
  * Methods coverage
  * Classes coverage
  * Clickable file links (when `--base-url` is provided)

Example output:

```markdown
| File | Lines | Methods | Classes |
|------|-------|---------|---------|
| **Total** | 85.5% 🟩🟩🟩🟩🟩🟩🟩🟩⬜⬜ | 75% | 100% |
| [src/MyClass.php](https://github.com/user/repo/blob/main/src/MyClass.php) | 90% 🟩🟩🟩🟩🟩🟩🟩🟩🟩⬜ | 80% | 100% |
```

## Requirements

* PHP 8.1 or higher
* phpunit/php-code-coverage
* sebastian/template
* symfony/console

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run code quality checks
composer cs

# Run static analysis
composer analyze
```

## License

MIT

## Author

[BrianHenryIE](https://github.com/BrianHenryIE)

[![PHP 7.4](https://img.shields.io/badge/PHP-8.1-8892BF.svg?logo=php)](https://php.net)

> ⚠️ Tests pass locally but not in GitHub Actions. Please test and give feedback, don't assume this is working 100%.

# PHP Code Coverage Markdown Report Printer

Generate Markdown coverage reports from PHPUnit code coverage data, perfect for GitHub PR comments.

[![PHPUnit ](.github/example-github-pr-comment.png)](https://github.com/BrianHenryIE/strauss/pull/139#issuecomment-2614192979)

## Features

* 📊 Convert PHPUnit `.cov` coverage files to Markdown
* 🔗 Link files to GitHub blob URLs
* 📝 Filter reports to specific files
* 🎨 Visual coverage bars using emojis (🟩🟧🟥⬜)

## Installation

```bash
composer require --dev brianhenryie/php-codecoverage-markdown
```

## Usage

### CLI

Generate a Markdown report from a PHPUnit coverage file:

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

Use this to post coverage reports as PR comments:

```yaml
name: Code Coverage Report Comment

on:
  pull_request:
    types: [opened, synchronize]

jobs:
  coverage:
    runs-on: ubuntu-latest

    permissions:
      pull-requests: write

    strategy:
      matrix:
        php: [ '8.1', '8.2', '8.3', '8.4', '8.5' ]

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: xdebug

      - name: Install dependencies
        run: composer install

      - name: Run tests with coverage
        if: ${{ matrix.php == '8.1' }} # We only need the coverage data once
        run: XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-php coverage.cov

      - name: Run tests without coverage
        if: ${{ matrix.php != '8.1' }}
        run: vendor/bin/phpunit

      - name: Get changed files
        if: ${{ matrix.php == '8.1' }} # We only need it once
        id: changed-files
        uses: tj-actions/changed-files@v47
        with:
          separator: ','
          files: '**/**.php'

      - name: Generate markdown report
        if: ${{ matrix.php == '8.1' }} # We only need it once
        run: |
          vendor/bin/php-codecoverage-markdown \
            --input-file coverage.cov \
            --covered-files=${{ steps.changed-files.outputs.all_changed_files }} \
            --base-url "https://github.com/${{ github.repository }}/blob/${{ github.event.pull_request.head.sha }}/%s" \
            --output-file coverage-report.md

      - name: Comment on PR
        uses: mshick/add-pr-comment@v2
        if: ${{ matrix.php == '8.1' }} # We only need it added once
        with:
          message-id: coverage-report # Causes it to update the same PR comment each time.
          message-path: coverage-report.md
        continue-on-error: true # When a PR is opened by a non-member, there are no write permissions (and no access to secrets), so this step will always fail.
```

## Options

| Option | Short | Description |
|--------|-------|-------------|
| `--input-file` | `-i` | Path to PHPUnit `.cov` coverage file (required) |
| `--output-file` | `-o` | Output file path (default: stdout) |
| `--base-url` | `-b` | Base URL for source file links (use `%s` as placeholder) |
| `--covered-files` | `-c` | Comma-separated list of files to include |

## Output Format

The Markdown report includes:

* Total coverage summary
* Per-file coverage details with:
  * Lines coverage percentage
  * Visual coverage bar
  * Methods coverage
  * Classes coverage
  * Clickable file links (when `--base-url` is provided)


## Requirements

* PHP 8.1 or higher
* Coverage file in PHP format from [phpunit/php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage):`^9|^10|^11|^12`

## License

MIT

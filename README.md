[![PHP 7.4](https://img.shields.io/badge/PHP-8.1-8892BF.svg?logo=php)](https://php.net)

# PHP Code Coverage Markdown Report Printer

Generate Markdown coverage reports from PHPUnit code coverage data, perfect for GitHub PR comments.

[![PHPUnit ](.github/example-github-pr-comment.png)](https://github.com/BrianHenryIE/strauss/pull/139#issuecomment-2614192979)

## Features

* 📊 Convert PHPUnit `.cov` coverage files to Markdown
* 🔗 Link files to GitHub blob URLs
* 📝 Filter reports to specific files
* 🎨 Visual coverage bars using emojis (🟩🟧🟥⬜)

The Markdown report includes:

* Total coverage summary
* Per-file coverage details with:
  * Lines coverage percentage
  * Visual coverage bar
  * Methods coverage
  * Classes coverage
  * Clickable source/HTML report links (when `--base-url` is provided) 

### Options

Primary input: coverage file in PHP format from [phpunit/php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage):`^9|^10|^11|^12`

| Option | Short | Description                                                             |
|--------|-------|-------------------------------------------------------------------------|
| `--input-file` | `-i` | Path to PHPUnit `.cov` coverage file (required)                         |
| `--output-file` | `-o` | Output file path (default: stdout)                                      |
| `--base-url` | `-b` | Base URL for source file links (use `%s` as placeholder)                |
| `--covered-files` | `-c` | Comma-separated list of files to include (i.e. the files in the PR) |

### Limitation

GitHub Actions permissions limit new comments to users with write access to the repo. I.e. this works great for teams using private repos and works ok for PRs on public repos. There is a workaround at [mshick/add-pr-comment-proxy](https://github.com/mshick/add-pr-comment-proxy) which runs on Google Cloud.

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

### GitHub Actions

Use this to post coverage reports as PR comments:

```yaml
name: PHP Tests with Code Coverage Report Comment

on:
  pull_request:
    types: [opened, synchronize]

env:
  COVERAGE_PHP_VERSION: '8.4'

jobs:
  php-tests:
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
          coverage: ${{ matrix.php == env.COVERAGE_PHP_VERSION && 'xdebug' || 'none' }}

      - name: Install dependencies
        run: composer install

      - name: Run tests with coverage
        if: ${{ matrix.php == env.COVERAGE_PHP_VERSION }} # We only need the coverage data once
        run: XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-php coverage.cov

      - name: Run tests without coverage
        if: ${{ matrix.php != env.COVERAGE_PHP_VERSION }}
        run: vendor/bin/phpunit

      - name: Get changed files
        if: ${{ matrix.php == env.COVERAGE_PHP_VERSION }} # We only need it once
        id: changed-files
        uses: tj-actions/changed-files@v47
        with:
          separator: ','
          files: '**/**.php'

      - name: Generate markdown report
        if: ${{ matrix.php == env.COVERAGE_PHP_VERSION }} # We only need it once
        run: |
          vendor/bin/php-codecoverage-markdown \
            --input-file coverage.cov \
            --covered-files=${{ steps.changed-files.outputs.all_changed_files }} \
            --base-url "https://github.com/${{ github.repository }}/blob/${{ github.event.pull_request.head.sha }}/%s" \
            --output-file coverage-report.md

      - name: Comment on PR
        uses: mshick/add-pr-comment@v2
        if: ${{ matrix.php == env.COVERAGE_PHP_VERSION }} # We only need it added once
        with:
          message-id: coverage-report # Causes it to update the same PR comment each time.
          message-path: coverage-report.md
        continue-on-error: true # When a PR is opened by a non-member, there are no write permissions (and no access to secrets), so this step will always fail.
```

## Status

Ironically, this project's tests pass locally but fail in GitHub Actions! I'll tag 1.0 when I figure that out.

## See Also

* [mshick/add-pr-comment-proxy](https://github.com/mshick/add-pr-comment-proxy)
* [BrianHenryIE/php-diff-test](https://github.com/BrianHenryIE/php-diff-test)

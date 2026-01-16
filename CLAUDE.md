# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**php-codecoverage-markdown** is a standalone tool that generates markdown-formatted code coverage reports from PHPUnit `.cov` files. It's designed primarily for GitHub Actions workflows to display coverage reports in PR comments.

## Development Commands

### Testing
```bash
# Run tests
vendor/bin/phpunit

# Run tests with coverage
XDEBUG_MODE=coverage composer test-coverage
# View: open ./tests/_reports/html/index.html

# Run a single test
vendor/bin/phpunit tests/Unit/MarkdownReportTest.php
```

### Code Quality
```bash
# Run all quality checks
composer cs

# Fix code style issues
composer cs-fix

# Run PHPStan static analysis
composer analyze
```

### CLI Testing
```bash
# Generate a markdown report from a coverage file
./bin/php-codecoverage-markdown \
  --input-file path/to/coverage.cov \
  --output-file report.md
```

## Architecture

### Core Components

This is a focused library with three main components:

1. **CoverageFilter** (`src/CoverageFilter.php`): Filters CodeCoverage objects to include only specified files. This is a standalone utility extracted from the php-diff-test project's DiffCoverage class.

2. **MarkdownReport** (`src/MarkdownReport.php`): Main class that orchestrates the report generation. Takes a CodeCoverage object and produces markdown output.

3. **Directory** (`src/Directory.php`): Extends PHPUnit's HTML Renderer to produce markdown instead of HTML. Uses templates from `src/MarkdownTemplate/` to format the output.

4. **MarkdownReportCommand** (`src/MarkdownReportCommand.php`): Symfony Console command that provides the CLI interface. Handles file I/O and option parsing.

### Data Flow

```
PHPUnit .cov file → CoverageFilter (optional) → MarkdownReport → Directory → Markdown output
                                                                      ↓
                                                                  Templates
```

### Templates

Templates in `src/MarkdownTemplate/` define the output format:
- `directory.html.dist` - Main report template
- `directory_branch.html.dist` - Branch coverage template
- `directory_item.html.dist` - Individual file item
- `directory_item_branch.html.dist` - Branch coverage item

Despite `.html` extension, these produce markdown using Sebastian Bergmann's template system.

## Key Design Decisions

**Independence**: This project is intentionally separated from php-diff-test to:
- Serve as a standalone markdown report generator
- Have minimal dependencies (only PHPUnit coverage libs + Symfony Console)
- Be usable by any project that generates `.cov` files

**Filtering**: The `CoverageFilter` class allows filtering reports to specific files, which is useful when you only want to show coverage for changed files in a PR.

**GitHub Integration**: The `--base-url` option enables clickable file links in GitHub PR comments, linking directly to source code.

## PHP Version Support

Currently targeting PHP 8.1+.

## Configuration Files

- `phpunit.xml`: PHPUnit configuration, bootstrap is `tests/bootstrap.php`
- `phpcs.xml`: PSR-12 coding standard
- `phpstan.neon`: Level 8 static analysis on `src/` only
- `composer.json`: Includes version ranges for PHPUnit coverage versions 10-12

## Origin

This project was extracted from [brianhenryie/php-diff-test](https://github.com/brianhenryie/php-diff-test) to make the markdown reporting functionality available as a standalone tool.

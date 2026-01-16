# Project Split Summary

## Overview

Successfully split the markdown report functionality from **php-diff-test** into a standalone project: **php-codecoverage-markdown**.

## What Was Created

### New Project: php-codecoverage-markdown

**Location:** `/Users/brian.henry/Sites/php-codecoverage-markdown`

A standalone PHP library that generates markdown-formatted code coverage reports from PHPUnit `.cov` files.

### Files Created

```
php-codecoverage-markdown/
├── bin/
│   └── php-codecoverage-markdown          # CLI entry point
├── src/
│   ├── CoverageFilter.php                 # Filters CodeCoverage objects by file list
│   ├── MarkdownReport.php                 # Main report generator
│   ├── Directory.php                      # Markdown renderer (extends PHPUnit's HTML renderer)
│   ├── MarkdownReportCommand.php          # Symfony Console command
│   └── MarkdownTemplate/                  # Template files
│       ├── directory.html.dist
│       ├── directory_branch.html.dist
│       ├── directory_item.html.dist
│       └── directory_item_branch.html.dist
├── tests/
│   └── bootstrap.php
├── composer.json                          # Dependencies and configuration
├── phpunit.xml                            # PHPUnit configuration
├── phpcs.xml                              # PHP CodeSniffer configuration
├── phpstan.neon                           # PHPStan configuration
├── .gitignore
├── README.md                              # User documentation
└── CLAUDE.md                              # AI assistant documentation
```

## Key Changes from Original

### 1. Namespace Change
- **Old:** `BrianHenryIE\PhpDiffTest\MarkdownReport`
- **New:** `BrianHenryIE\CodeCoverageMarkdown`

### 2. Extracted filterCoverage Method
Created standalone `CoverageFilter::filterCoverage()` method to remove dependency on `DiffCoverage` class from php-diff-test.

### 3. Improved CLI Command
- Renamed from `MarkdownReportCLI` to `MarkdownReportCommand`
- Enhanced error handling
- Better validation of CodeCoverage object
- Clearer command name: `generate` instead of `markdown-report`
- Added short option flags (`-i`, `-o`, `-b`, `-c`)

### 4. Better Documentation
- Comprehensive README with usage examples
- GitHub Actions integration examples
- Clear API documentation
- CLAUDE.md for AI assistant guidance

## Dependencies

### Required Packages
```json
{
  "phpunit/php-code-coverage": "^10.1.16|^11.0.12|^12.5.2|*",
  "phpunit/php-text-template": "^3.0|^4.0|*",
  "symfony/console": "^6.5.30|^7.4.1|^8.0.1|*"
}
```

### Development Packages
```json
{
  "mockery/mockery": "*",
  "phpstan/phpstan": "*",
  "phpunit/phpunit": "^10.5.60|^11.5.46|^12.5.4|*",
  "squizlabs/php_codesniffer": "*"
}
```

## Usage Examples

### CLI
```bash
# Basic usage
php-codecoverage-markdown -i coverage.cov -o report.md

# With GitHub links
php-codecoverage-markdown \
  -i coverage.cov \
  -b "https://github.com/user/repo/blob/main/%s" \
  -o report.md

# Filter specific files
php-codecoverage-markdown \
  -i coverage.cov \
  -c "src/MyClass.php,src/Another.php" \
  -o report.md
```

### Programmatic
```php
use BrianHenryIE\CodeCoverageMarkdown\MarkdownReport;

$coverage = include 'coverage.cov';
$report = new MarkdownReport();
$markdown = $report->process($coverage, '/project/root/', $baseUrl);
```

## Integration with Original Project

The original **php-diff-test** project can now:

1. **Remove** the `src/MarkdownReport/` directory
2. **Remove** the `src/MarkdownReportCLI.php` file
3. **Add dependency** (optional):
   ```json
   {
     "require": {
       "brianhenryie/php-codecoverage-markdown": "^1.0"
     }
   }
   ```
4. **Update** `bin/php-diff-test` to remove markdown-report command

## Benefits of Split

### Independence
- Each project has a single, focused responsibility
- Markdown reporting can be used without diff/filter functionality
- Easier to maintain and test

### Reduced Dependencies
- **php-diff-test** no longer needs template library
- **php-codecoverage-markdown** doesn't need git libraries

### Better Distribution
- Users who only want markdown reports don't need the full diff-test tool
- Smaller package size for markdown-only users

### Clearer Documentation
- Each project has focused, relevant documentation
- Easier onboarding for new contributors

## Quality Checks

### PHPStan
✅ Level 8 - No errors
```bash
composer analyze
# [OK] No errors
```

### Git
✅ Initial commit created
```
Initial commit: PHP CodeCoverage Markdown
- 17 files changed, 975 insertions(+)
```

## Next Steps

1. **Publish to Packagist** (optional)
   - Register package on packagist.org
   - Set up automatic updates

2. **Add Tests**
   - Unit tests for CoverageFilter
   - Unit tests for MarkdownReport
   - Integration tests with real coverage files

3. **Update php-diff-test**
   - Remove MarkdownReport code
   - Add php-codecoverage-markdown as optional dependency
   - Update documentation

4. **GitHub Setup**
   - Create repository
   - Add CI/CD workflows
   - Set up issue templates

## Testing

To test the new project:

```bash
cd /Users/brian.henry/Sites/php-codecoverage-markdown

# Install dependencies
composer install

# Run static analysis
composer analyze

# Run code style checks
composer cs

# Test CLI
./bin/php-codecoverage-markdown --help
```

## Notes

- PHP version requirement: **8.1+**
- Supports PHPUnit coverage versions: **10, 11, 12**
- Template system uses `phpunit/php-text-template`
- All templates use `.html.dist` extension but output markdown

# Reproducer for Issue #156: GitHub Pull Request URLs Not Supported

This directory contains a reproducer for the issue described in: 
https://github.com/staabm/phpstan-todo-by/issues/156

## Problem Description

Currently, phpstan-todo-by supports GitHub issue URLs in TODO comments:
```php
// TODO: https://github.com/owner/repo/issues/123 fix this
```

However, it does not support GitHub pull request URLs:
```php
// TODO: https://github.com/owner/repo/pull/456 merge this PR
```

Both should work the same way since GitHub issues and pull requests have similar status states.

## Root Cause

The issue is in the regex pattern in `src/TodoByIssueUrlRule.php` (line 29):

```php
(?P<url>https://github.com/(?P<owner>[\S]{2,})/(?P<repo>[\S]+)/issues/(?P<issueNumber>\d+))
```

This pattern only matches `/issues/` but not `/pull/`.

## Reproducer Files

### 1. Simple Standalone Test
- **File**: `tests/Issue156ReproducerTest.php`
- **Description**: A standalone PHP script that demonstrates the regex issue
- **How to run**: `php tests/Issue156ReproducerTest.php`

### 2. PHPStan Test Framework Style Test  
- **File**: `tests/TodoByIssueAndPrUrlRuleTest.php`
- **Data File**: `tests/data/issue-and-pr-urls.php`
- **Description**: A proper PHPStan test that shows issue URLs work but PR URLs are ignored
- **How to run**: `vendor/bin/phpunit tests/TodoByIssueAndPrUrlRuleTest.php` (requires dependencies)

### 3. Interactive Regex Demo
- **File**: `/tmp/test_regex_issue.php`
- **Description**: Shows the regex matching behavior for different URL types
- **How to run**: `php /tmp/test_regex_issue.php`

## Expected Behavior

Both issue and pull request URLs should work identically:

```php
// All of these should work:
// TODO: https://github.com/owner/repo/issues/123 fix this issue
// TODO: https://github.com/owner/repo/pull/456 merge this PR
// FIXME: https://github.com/owner/repo/issues/789
// XXX: https://github.com/owner/repo/pull/101
```

## Current Behavior

Only issue URLs work; pull request URLs are completely ignored (no errors generated).

## Test Results

Running the reproducer confirms the issue:

```
✓ Issue URLs: Work correctly
✗ Pull Request URLs: Completely ignored (no regex match)
```

This demonstrates that the regex pattern needs to be updated to support both `/issues/` and `/pull/` URL patterns.
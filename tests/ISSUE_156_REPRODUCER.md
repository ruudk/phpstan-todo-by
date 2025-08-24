# Issue #156: GitHub Pull Request URLs Not Supported - FIXED! ✅

This directory contains tests that demonstrate the fix for the issue described in: 
https://github.com/staabm/phpstan-todo-by/issues/156

## Problem Description (RESOLVED)

Previously, phpstan-todo-by only supported GitHub issue URLs in TODO comments:
```php
// TODO: https://github.com/owner/repo/issues/123 fix this
```

It did not support GitHub pull request URLs:
```php
// TODO: https://github.com/owner/repo/pull/456 merge this PR
```

**This issue has now been fixed! Both URL types work identically.**

## Root Cause and Fix

The issue was in the regex pattern in `src/TodoByIssueUrlRule.php` (line 29):

**Before (broken):**
```php
(?P<url>https://github.com/(?P<owner>[\S]{2,})/(?P<repo>[\S]+)/issues/(?P<issueNumber>\d+))
```

**After (fixed):**
```php
(?P<url>https://github.com/(?P<owner>[\S]{2,})/(?P<repo>[\S]+)/(issues|pull)/(?P<issueNumber>\d+))
```

The fix was minimal: changed `/issues/` to `/(issues|pull)/` to support both URL patterns.

## Test Files (Updated to Show Fix Works)

### 1. Verification Test
- **File**: `tests/Issue156ReproducerTest.php`
- **Description**: Verifies that both issue and pull request URLs now work
- **How to run**: `php tests/Issue156ReproducerTest.php`

### 2. PHPStan Test Framework Test  
- **File**: `tests/TodoByIssueAndPrUrlRuleTest.php`
- **Data File**: `tests/data/issue-and-pr-urls.php`
- **Description**: PHPStan test that verifies both URL types generate errors correctly
- **How to run**: `vendor/bin/phpunit tests/TodoByIssueAndPrUrlRuleTest.php` (requires dependencies)

## Current Behavior (FIXED!)

Both issue and pull request URLs now work identically:

```php
// All of these now work correctly:
// TODO: https://github.com/owner/repo/issues/123 fix this issue
// TODO: https://github.com/owner/repo/pull/456 merge this PR
// FIXME: https://github.com/owner/repo/issues/789
// XXX: https://github.com/owner/repo/pull/101
```

## Test Results

Running the tests confirms the fix:

```
✅ Issue URLs: Work correctly (existing functionality preserved)
✅ Pull Request URLs: Now work correctly (issue #156 fixed!)
```

The regex pattern now supports both `/issues/` and `/pull/` URL patterns as expected.
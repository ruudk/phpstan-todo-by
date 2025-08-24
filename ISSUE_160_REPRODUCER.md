# Issue #160 - Reproducer Test Summary

## Bug Description
`// TODO php:8.5` incorrectly triggers when running on PHP 8.4 (or 8.3), reporting:
```
"php" version requirement ">=8.5" satisfied
```

This is wrong because PHP 8.4 does NOT satisfy `>=8.5`.

## Root Cause  
The bug is in `src/TodoByPackageVersionRule.php`, method `satisfiesPhpPlatformPackage()`.

**Current (incorrect) logic:**
1. Reads PHP requirement from composer.json: `"php": "^7.4 || ^8.0"`
2. Checks if this composer constraint "matches" the TODO constraint `>=8.5`
3. Since `^8.0` includes 8.0-8.999, it incorrectly considers `>=8.5` as satisfied

**Correct logic should be:**
1. Get current runtime PHP version (e.g., 8.3.6)
2. Check if runtime version satisfies the TODO constraint `>=8.5`
3. Since 8.3.6 < 8.5, it should NOT trigger

## Test Files Created

### `tests/data/issue160.php`
Test data file with various PHP version TODOs to demonstrate the bug.

### `tests/TodoByPackageVersionRuleTest.php::testIssue160PhpVersionBug()`
Test method that reproduces the bug by documenting the current (incorrect) behavior.

**When running on PHP 8.3.6:**
- ✗ `php:8.5` - Currently triggers (BUG), should not trigger  
- ✗ `php:8.6` - Currently triggers (BUG), should not trigger
- ✓ `php:9.0` - Correctly doesn't trigger
- ✓ `php:7.4` - Correctly triggers 
- ✓ `php:8.0` - Correctly triggers

## Verification
Run `/tmp/simple_reproduction.php` to see the bug in action without dependencies.

## Next Steps
When this bug is fixed, the test `testIssue160PhpVersionBug()` should be updated to expect only `php:7.4` and `php:8.0` errors, not the `php:8.5`, `php:8.6`, `php:9.0` errors.
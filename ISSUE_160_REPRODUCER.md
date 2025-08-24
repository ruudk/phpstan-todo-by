# Issue #160 - FIXED ✅

## Bug Description
`// TODO php:8.5` **was incorrectly triggering** when running on PHP 8.4 (or 8.3), reporting:
```
"php" version requirement ">=8.5" satisfied
```

This was wrong because PHP 8.4 does NOT satisfy `>=8.5`.

## Root Cause (FIXED)
The bug **was** in `src/TodoByPackageVersionRule.php`, method `satisfiesPhpPlatformPackage()`.

**Old (incorrect) logic:**
1. Read PHP requirement from composer.json: `"php": "^7.4 || ^8.0"`
2. Check if this composer constraint "matches" the TODO constraint `>=8.5`
3. Since `^8.0` includes 8.0-8.999, it incorrectly considered `>=8.5` as satisfied

**New (correct) logic:**
1. Get current runtime PHP version (e.g., 8.3.6)
2. Check if runtime version satisfies the TODO constraint `>=8.5`
3. Since 8.3.6 < 8.5, it correctly does NOT trigger

## Fix Applied ✅

### Changes Made:
1. **Modified `satisfiesPhpPlatformPackage()` method** to use `PHP_VERSION` instead of composer.json constraints
2. **Removed unused `readPhpPlatformVersion()` method** and `$phpPlatformVersion` property  
3. **Updated test** to expect correct behavior

### Code Changes:
```php
// OLD (buggy):
$phpPlatformVersion = $this->readPhpPlatformVersion($comment, $wholeMatchStartOffset);
$provided = $versionParser->parseConstraints($phpPlatformVersion);

// NEW (fixed):
$provided = $versionParser->parseConstraints(PHP_VERSION);
```

## Test Files

### `tests/data/issue160.php`
Test data file with various PHP version TODOs to verify the fix.

### `tests/TodoByPackageVersionRuleTest.php::testIssue160PhpVersionBug()`
Test method now verifies the **correct** behavior after the fix.

**When running on PHP 8.3.6 (current runtime):**
- ✅ `php:8.5` - Correctly does NOT trigger (8.3.6 < 8.5)  
- ✅ `php:8.6` - Correctly does NOT trigger (8.3.6 < 8.6)
- ✅ `php:9.0` - Correctly does NOT trigger (8.3.6 < 9.0)
- ✅ `php:7.4` - Correctly triggers (8.3.6 >= 7.4) 
- ✅ `php:8.0` - Correctly triggers (8.3.6 >= 8.0)

## Verification ✅
The fix has been verified with both unit tests and manual testing to ensure correct behavior.
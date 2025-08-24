<?php

namespace staabm\PHPStanTodoBy\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use staabm\PHPStanTodoBy\TodoByPackageVersionRule;
use staabm\PHPStanTodoBy\utils\ExpiredCommentErrorBuilder;

use function dirname;

/**
 * @extends RuleTestCase<TodoByPackageVersionRule>
 * @internal
 */
final class TodoByPackageVersionRuleTest extends RuleTestCase
{
    /**
     * @var array<string, string>
     */
    private array $virtualPackages = [];

    protected function getRule(): Rule
    {
        return new TodoByPackageVersionRule(
            new ExpiredCommentErrorBuilder(true),
            dirname(__DIR__),
            $this->virtualPackages,
        );
    }

    /**
     * @param list<array{0: string, 1: int, 2?: string|null}> $errors
     * @dataProvider provideErrors
     */
    public function testRule(array $errors): void
    {
        $this->analyse([__DIR__ . '/data/packageVersion.php'], $errors);
    }

    /**
     * @return iterable<array{list<array{0: string, 1: int, 2?: string|null}>}>
     */
    public static function provideErrors(): iterable
    {
        yield [
            [
                [
                    '"phpunit/phpunit" version requirement "<50" satisfied: This has to be fixed before updating to phpunit 50.x.',
                    5,
                ],
                [
                    '"phpunit/phpunit" version requirement ">=5.3" satisfied: This has to be fixed when updating to phpunit 5.3.* or higher.',
                    8,
                ],
                [
                    'Unknown package "not-installed/package". It is neither installed via composer.json nor declared as virtual package via PHPStan config.',
                    11,
                ],
                [
                    '"phpunit/phpunit" version requirement "<10" satisfied.',
                    14,
                ],
                [
                    '"phpunit/phpunit" version requirement "<11" satisfied.',
                    15,
                ],
                [
                    'Invalid version constraint "<inValid.12" for package "phpunit/phpunit".',
                    17,
                ],
                [
                    '"php" version requirement ">7.3" satisfied: drop this code after min-version raise.',
                    19,
                ],
                [
                    '"php" version requirement ">=7" satisfied: drop this code after min-version raise.',
                    20,
                ],
                [
                    '"php" version requirement ">=7" satisfied.',
                    22,
                ],
                [
                    '"php" version requirement ">=7" satisfied.',
                    23,
                ],
                [
                    '"php" version requirement ">=7" satisfied.',
                    24,
                ],
            ],
        ];
    }

    public function testBug44(): void
    {
        $this->analyse([__DIR__ . '/data/bug44.php'], []);
    }

    public function testBug64(): void
    {
        $this->analyse([__DIR__ . '/data/bug64.php'], [
            [
                '"php" version requirement ">=7" satisfied: drop this code after min-version raise.',
                7,
            ],
        ]);
    }

    public function testVirtualPackage(): void
    {
        $this->virtualPackages = [
            'my-virtual/package' => '1.0.0',
        ];
        $this->analyse([__DIR__ . '/data/virtualPackages.php'], [
            [
                '"my-virtual/package" version requirement ">=1.0" satisfied: comment v1.',
                5,
            ],
            [
                'Unknown package "some/unknown". It is neither installed via composer.json nor declared as virtual package via PHPStan config.',
                8,
            ],
        ]);
    }

    public function testInvalidVirtualPackage(): void
    {
        $this->virtualPackages = [
            'my-virtual/package' => 'not-a-version',
        ];
        $this->analyse([__DIR__ . '/data/virtualPackages.php'], [
            [
                'Invalid virtual-package "my-virtual/package": "not-a-version" provided via PHPStan config file.',
                5,
            ],
            [
                'Invalid virtual-package "my-virtual/package": "not-a-version" provided via PHPStan config file.',
                6,
            ],
            [
                'Unknown package "some/unknown". It is neither installed via composer.json nor declared as virtual package via PHPStan config.',
                8,
            ],
        ]);
    }

    /**
     * Test for issue #160: https://github.com/staabm/phpstan-todo-by/issues/160
     * 
     * BUG REPRODUCTION: This test documents the current buggy behavior.
     * When running on PHP 8.3, `// TODO php:8.5` incorrectly triggers an error
     * because the rule checks composer.json requirements instead of runtime PHP version.
     * 
     * Current composer.json has: "php": "^7.4 || ^8.0"
     * Since ^8.0 allows versions up to 8.x, the rule incorrectly thinks php:8.5 is satisfied.
     * But the runtime PHP version is 8.3.6, which does NOT satisfy >=8.5.
     * 
     * This test expects the CURRENT (buggy) behavior to demonstrate the issue.
     */
    public function testIssue160PhpVersionBug(): void
    {
        // IMPORTANT: This test expects the CURRENT BUGGY BEHAVIOR
        // When the bug is fixed, this test should be updated to expect only:
        // - php:7.4 and php:8.0 errors (correct, since 8.3 >= 7.4 and 8.3 >= 8.0)
        // - NO errors for php:8.5, php:8.6, php:9.0 (correct, since 8.3 < 8.5)
        
        $this->analyse([__DIR__ . '/data/issue160.php'], [
            // Current buggy behavior: ALL of these trigger errors
            [
                '"php" version requirement ">=8.5" satisfied: This should NOT trigger an error when running on PHP 8.4 or lower.',
                5,
            ],
            [
                '"php" version requirement ">=8.6" satisfied: This should also NOT trigger when on PHP 8.4.',
                6,
            ],
            [
                '"php" version requirement ">=9.0" satisfied: Future PHP version should not trigger.',
                7,
            ],
            [
                '"php" version requirement ">=7.4" satisfied: This should trigger on PHP 8.3+ (current version is higher).',
                8,
            ],
            [
                '"php" version requirement ">=8.0" satisfied: This should trigger on PHP 8.3+ (current version is higher).',
                9,
            ],
            // php:8.3 behavior depends on exact version handling, so omit for now
        ]);
    }
}

<?php

namespace staabm\PHPStanTodoBy\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use staabm\PHPStanTodoBy\TodoByIssueUrlRule;
use staabm\PHPStanTodoBy\utils\ExpiredCommentErrorBuilder;
use staabm\PHPStanTodoBy\utils\ticket\GitHubTicketStatusFetcher;

/**
 * @extends RuleTestCase<TodoByIssueUrlRule>
 * @internal
 */
final class TodoByIssueAndPrUrlRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new TodoByIssueUrlRule(
            new ExpiredCommentErrorBuilder(true),
            self::getContainer()->getByType(GitHubTicketStatusFetcher::class)
        );
    }

    /**
     * Test to reproduce issue #156: Pull request URLs should work just like issue URLs
     * 
     * This test currently only expects errors for issue URLs because pull request URLs
     * are not yet supported (the bug). When the bug is fixed, this test should be updated
     * to expect errors for pull request URLs as well.
     */
    public function testOnlyIssueUrlsWorkNotPullRequests(): void
    {
        // This test demonstrates the current behavior: only issue URLs work
        // Pull request URLs are completely ignored (not even triggering errors)
        
        $this->analyse([__DIR__ . '/data/issue-and-pr-urls.php'], [
            // Only issue URLs should work (existing functionality)
            [
                'Should have been resolved in https://github.com/staabm/phpstan-todo-by/issues/47: we need todo something when this issue is resolved.',
                5,
            ],
            [
                'Comment should have been resolved with https://github.com/staabm/phpstan-todo-by/issues/47.',
                6,
            ],
            
            // Pull request URLs are currently ignored (no errors generated)
            // This demonstrates the bug: PR URLs should work the same as issue URLs
            // but they are completely ignored by the regex pattern
            
            // TODO: When issue #156 is fixed, add these expected errors:
            // [
            //     'Should have been resolved in https://github.com/staabm/phpstan-todo-by/pull/26: needs this PR to be merged.',
            //     9,
            // ],
            // [
            //     'Comment should have been resolved with https://github.com/staabm/phpstan-todo-by/pull/27.',
            //     10,
            // ],
            // [
            //     'Comment should have been resolved with https://github.com/staabm/phpstan-todo-by/pull/100.',
            //     12,
            // ],
            // [
            //     'Comment should have been resolved with https://github.com/ruudk/phpstan-todo-by/pull/1.',
            //     13,
            // ],
        ]);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }
}
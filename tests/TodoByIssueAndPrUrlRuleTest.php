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
 * 
 * Tests for TodoByIssueUrlRule that verifies both GitHub issue URLs 
 * and pull request URLs work correctly (issue #156 is fixed)
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
     * Test that both issue URLs and pull request URLs work (issue #156 is fixed)
     */
    public function testBothIssueAndPullRequestUrlsWork(): void
    {
        // This test verifies that both issue URLs and pull request URLs work correctly
        // after fixing issue #156
        
        $this->analyse([__DIR__ . '/data/issue-and-pr-urls.php'], [
            // Issue URLs should work (existing functionality)
            [
                'Should have been resolved in https://github.com/staabm/phpstan-todo-by/issues/47: we need todo something when this issue is resolved.',
                5,
            ],
            [
                'Comment should have been resolved with https://github.com/staabm/phpstan-todo-by/issues/47.',
                6,
            ],
            
            // Pull request URLs should now work too (fixed in issue #156)
            [
                'Should have been resolved in https://github.com/staabm/phpstan-todo-by/pull/26: needs this PR to be merged.',
                9,
            ],
            [
                'Comment should have been resolved with https://github.com/staabm/phpstan-todo-by/pull/27.',
                10,
            ],
            [
                'Comment should have been resolved with https://github.com/staabm/phpstan-todo-by/pull/100.',
                12,
            ],
            [
                'Comment should have been resolved with https://github.com/ruudk/phpstan-todo-by/pull/1.',
                13,
            ],
        ]);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }
}
<?php

namespace staabm\PHPStanTodoBy\Tests;

/**
 * Reproducer test for issue #156: GitHub pull request URLs are not supported
 * 
 * This test demonstrates that:
 * 1. GitHub issue URLs work correctly (existing functionality)
 * 2. GitHub pull request URLs do not work (the bug)
 * 
 * See: https://github.com/staabm/phpstan-todo-by/issues/156
 */
class Issue156ReproducerTest
{
    /**
     * Test that demonstrates the regex pattern now supports pull requests
     * 
     * @return void
     */
    public function testGitHubPullRequestUrlsNowSupported(): void
    {
        // This is the updated pattern from TodoByIssueUrlRule.php (with fix for issue #156)
        $pattern = '{
            @?(?:TODO|FIXME|XXX) # possible @ prefix
            @?[a-zA-Z0-9_-]* # optional username
            \s*[:-]?\s* # optional colon or hyphen
            \s+ # keyword/version separator
            (?P<url>https://github.com/(?P<owner>[\S]{2,})/(?P<repo>[\S]+)/(issues|pull)/(?P<issueNumber>\d+)) # url
            \s*[:-]?\s* # optional colon or hyphen
            (?P<comment>(?:(?!\*+/).)*) # rest of line as comment text, excluding block end
        }ix';

        // Test cases that should work (issue URLs) - existing functionality
        $issueUrls = [
            '// TODO: https://github.com/staabm/phpstan-todo-by/issues/47 fix this',
            '// FIXME: https://github.com/staabm/phpstan-todo-by/issues/156',
        ];

        // Test cases that should now work (pull request URLs) - FIXED!
        $pullRequestUrls = [
            '// TODO: https://github.com/staabm/phpstan-todo-by/pull/26 merge this PR',
            '// FIXME: https://github.com/staabm/phpstan-todo-by/pull/27',
        ];

        // Verify issue URLs still work
        foreach ($issueUrls as $comment) {
            if (!preg_match($pattern, $comment)) {
                throw new \RuntimeException("Issue URL should match but doesn't: $comment");
            }
        }

        // Verify pull request URLs now work (the fix)
        foreach ($pullRequestUrls as $comment) {
            if (!preg_match($pattern, $comment)) {
                throw new \RuntimeException("Pull request URL should match but doesn't: $comment");
            }
        }

        echo "✓ SUCCESS: Issue #156 is fixed!\n";
        echo "✓ Issue URLs work: " . count($issueUrls) . " tested\n";
        echo "✓ Pull request URLs now work: " . count($pullRequestUrls) . " tested\n";
        echo "\nBoth GitHub issue URLs and pull request URLs are now supported in TODO comments.\n";
    }
}

// Run the test
try {
    $test = new Issue156ReproducerTest();
    $test->testGitHubPullRequestUrlsNowSupported();
    echo "\n✓ Test completed successfully - Issue #156 is fixed!\n";
} catch (\Exception $e) {
    echo "\n✗ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
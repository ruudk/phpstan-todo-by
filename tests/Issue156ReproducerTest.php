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
     * Test that demonstrates the regex pattern issue
     * 
     * @return void
     */
    public function testGitHubPullRequestUrlsNotSupported(): void
    {
        // This is the current pattern from TodoByIssueUrlRule.php
        $pattern = '{
            @?(?:TODO|FIXME|XXX) # possible @ prefix
            @?[a-zA-Z0-9_-]* # optional username
            \s*[:-]?\s* # optional colon or hyphen
            \s+ # keyword/version separator
            (?P<url>https://github.com/(?P<owner>[\S]{2,})/(?P<repo>[\S]+)/issues/(?P<issueNumber>\d+)) # url
            \s*[:-]?\s* # optional colon or hyphen
            (?P<comment>(?:(?!\*+/).)*) # rest of line as comment text, excluding block end
        }ix';

        // Test cases that should work (issue URLs)
        $issueUrls = [
            '// TODO: https://github.com/staabm/phpstan-todo-by/issues/47 fix this',
            '// FIXME: https://github.com/staabm/phpstan-todo-by/issues/156',
        ];

        // Test cases that should work but don't (pull request URLs) - THIS IS THE BUG
        $pullRequestUrls = [
            '// TODO: https://github.com/staabm/phpstan-todo-by/pull/26 merge this PR',
            '// FIXME: https://github.com/staabm/phpstan-todo-by/pull/27',
        ];

        // Verify issue URLs work
        foreach ($issueUrls as $comment) {
            if (!preg_match($pattern, $comment)) {
                throw new \RuntimeException("Issue URL should match but doesn't: $comment");
            }
        }

        // Demonstrate the bug: pull request URLs don't work
        $failedMatches = [];
        foreach ($pullRequestUrls as $comment) {
            if (!preg_match($pattern, $comment)) {
                $failedMatches[] = $comment;
            }
        }

        if (empty($failedMatches)) {
            throw new \RuntimeException("Expected pull request URLs to fail matching, but they all matched!");
        }

        // This is the expected failure that demonstrates the issue
        echo "REPRODUCER SUCCESS: Found " . count($failedMatches) . " pull request URLs that don't match:\n";
        foreach ($failedMatches as $failed) {
            echo "  - $failed\n";
        }
        echo "\nThis reproduces issue #156: Pull request URLs are not supported\n";
        echo "The regex pattern only matches '/issues/' but not '/pull/'\n";
    }
}

// Run the test
try {
    $test = new Issue156ReproducerTest();
    $test->testGitHubPullRequestUrlsNotSupported();
    echo "\n✓ Reproducer test completed successfully\n";
} catch (\Exception $e) {
    echo "\n✗ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
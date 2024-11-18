<?php

namespace App\Tests;

use App\SpamChecker;
use App\Entity\Comment;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SpamCheckerTest extends TestCase
{
    public function testSpamScoreWithInvalidRequest(): void
    {
        $comment = new Comment();
        $comment->setCreatedAtValue();
        $context = [];

        $client = new MockHttpClient([new MockResponse('invalid', ['response_headers' => ['X-Akismet-debug-help: invalid key']])]);
        $checker = new SpamChecker($client, 'abcde');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to check for spam: invalid (invalid key)');
        $checker->getSpamScore($comment, $context);

    }



    public function testSpamScore( int $expectedScore, ResponseInterface $response, Comment $comment, array $context){
        $client = new MockHttpClient([$response]);
        $checker = new SpamChecker($client, 'abcde');

        $score = $checker->getSpamScore($comment, $context);
        $this->assertSame($expectedScore, $score);
    }

    public static function provideComments(): iterable
    {
        $comment = new Comment();
        $comment->setCreatedAtValue();
        $context = [];

        $response = new MockResponse('', ['response_headers' => ['X-Akismet-pro-tip: discard']]);
        yield 'blatant_spam' => [2, $response, $comment, $context];


        $response = new MockResponse('true', ['response_headers' => []]);
        yield 'spam' => [1, $response, $comment, $context];

        $response = new MockResponse('false', ['response_headers' => []]);
        yield 'not_spam' => [0, $response, $comment, $context];
    }

}
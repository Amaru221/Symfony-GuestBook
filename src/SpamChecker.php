<?php

use App\Entity\Comment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker{

    private $endpoint;


    public function __construct(private HttpClientInterface $client, #[Autowire('%env(AKISMET_KEY)%')] string $akismetKey,){
    
        $this->endpoint = sprintf('https://%s.rest.akismet.com/1.1/comment-check', $akismetKey);
    
    }

    public function getSpamScore(Comment $comment, array $context): int{

        $response = $this->client->request('POST', $this->endpoint, [
            
            'body' => array_merge($context, [
            'comment_type' => 'comment',
            'comment_author' => $comment->getAuthor(),
            'comment_author_email' => $comment->getEmail(),
            'comment_content' => $comment->getText(),
            'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
            'blog_lang' => 'en',
            'blog_charset' => 'UTF-8',
            'is_test' => 'true',
            ]),
            
        ]);



        $headers = $response->getHeaders();

        if('discard' === $headers['X-Akismet-Server-Response'][0] ?? ''){ 
            return 2;
        }

        $content = $response->getContent();
        if(isset($headers['x-akismet-debug-help'][0])){
            throw new \RuntimeException(sprintf('Unable to chec for spam: %s (%s)', $content, $headers['x-akismet-debug-help'][0] ));

        return 'true' === $content? 1 : 0; 
        }

    }




}
<?php

namespace App\MessageHandler;

use App\SpamChecker;
use Psr\Log\LoggerInterface;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Handler for processing CommentMessage objects.
 * 
 * This class is responsible for managing the lifecycle of comments,
 * including spam checking, state transitions, and persistence.
 * It uses a workflow to manage the comment's state and dispatches
 * messages for further processing when necessary.
 */

#[AsMessageHandler]
class CommentMessageHandler{
    public function __construct(
        private EntityManagerInterface $em,
        private SpamChecker $spamChecker,
        private CommentRepository $commentRepository,
        private MessageBusInterface $bus,
        private WorkflowInterface $commentStateMachine,
        private MailerInterface $mailer,
        #[Autowire('%admin_email%')] private string $adminEmail,
        private ?LoggerInterface $logger = null,
    )
    {
        
    }

    public function __invoke(CommentMessage $message){
        $comment = $this->commentRepository->find($message->getId());

        if(!$comment){
            return;
        }

        if($this->commentStateMachine->can($comment, 'accept')){
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
            
            $transition = match ($score){
                2 => 'spam',
                1 => 'might_be_spam',
                default => 'accept',
            };

            $this->commentStateMachine->apply($comment, $transition);
            $this->em->flush();
            $this->bus->dispatch($message);
        }elseif($this->commentStateMachine->can($comment, 'publish')||$this->commentStateMachine->can($comment, 'publish_ham')){
            // $this->commentStateMachine->apply($comment, $this->commentStateMachine->can($comment, 'publish') ? 'publish' : 'publish_ham');
            // $this->em->flush();

            $this->mailer->send((new NotificationEmail())
                ->subject('New comment posted')
                ->htmlTemplate('emails/comment_notification.html.twig')
                ->from($this->adminEmail)
                ->to($this->adminEmail)
                ->context(['comment' => $comment])
                );
        }elseif($this->logger){
            $this->logger->debug('Dropping the comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }
        
    
    }

}
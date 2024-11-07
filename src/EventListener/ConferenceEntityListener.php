<?php

namespace App\EventListener;

use Doctrine\ORM\Events;
use App\Entity\Conference;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;


#[AsEntityListener(event: Events::prePersist, entity: Conference::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Conference::class)]
final class ConferenceEntityListener
{
    public function __construct(private SluggerInterface $slugger,)
    {}

    public function prePersist(Conference $conference, LifecycleEventArgs $event)
    {
            $conference->computeSlug($this->slugger);
    }

    public function preUpdate(Conference $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }
}

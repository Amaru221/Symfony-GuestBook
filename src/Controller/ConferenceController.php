<?php

namespace App\Controller;

use App\SpamChecker;
use Twig\Environment;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Entity\Conference;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ConferenceController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em, private MessageBusInterface $bus,){

    }


    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ])->setSharedMaxAge(3600);
    }

    #[Route('/conference_header', name: 'conference_header')]
    public function conferenceHeader(ConferenceRepository $commentRepository): Response
    {
        return $this->render('conference/header.html.twig',[
            'conferences' => $commentRepository->findAll(),
        ])->setMaxAge(3600);
    }

    #[Route('/conference/{slug}', name:'conference')]
    public function show(Request $request, Conference $conference, CommentRepository $commentRepository, #[Autowire('%photo_dir%')] string $photoDir) : Response
    {
        

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentsPaginator($conference, $offset);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $comment->setConference($conference);

            if($photo = $form->get('photo')->getData()){
                $filename = bin2hex(random_bytes(6)). '.' .$photo->guessExtension();
                $photo->move($photoDir,$filename);
                $comment->setPhotoFilename($filename);
            }

            $this->em->persist($comment);
            $this->em->flush();

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];

            // if(2 === $spamChecker->getSpamScore($comment, $context)){
            
            //     throw new \RuntimeException('Blantant spam detected! Comment not saved. 100% spam detection is not possible in this environment. 100% of comments are saved.');
            // }

            // $this->em->flush();

            $this->bus->dispatch(new CommentMessage($comment->getId(), $context));

            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        
        return $this->render('conference/show.html.twig',[
            'conference' => $conference,
            'comments' => $paginator,
            'previous' =>  $offset - $commentRepository::COMMENTS_PER_PAGE,
            'next' => min(count($paginator), $offset + $commentRepository::COMMENTS_PER_PAGE),
            'comment_form' => $form,
        ]);

    }

}

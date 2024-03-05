<?php

namespace App\Controller;
use App\Entity\Users;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use App\Entity\Publication;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\RecommendationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\ProfanityFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormView;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\React;
use Symfony\Component\Notifier\Notification\Notification;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use App\Service\QrCodeService; // Include the QrCodeService

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Color\Color;

use Symfony\Component\Notifier\Recipient\SlackRecipient;
use Symfony\Component\Notifier\NotifierInterface;


#[Route('publication')]
class PublicationController extends AbstractController
{
    private RecommendationService $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    #[Route('/', name: 'app_publication_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository, Request $request): Response
    {
        // Get the currently authenticated user
        $user = $this->getUser();
        
        // Generate recommendations for the user
        $recommendations = $this->recommendationService->generateRecommendations($user);

        // Your existing logic for fetching publications
        $type = $request->query->get('type');
        if ($type && in_array($type, ['Nutrition', 'Progrés'])) {
            $publications = $publicationRepository->findBy(['type' => $type]);
        } else {
            $publications = $publicationRepository->findAll();
        }

        return $this->render('front/publication/index.html.twig', [
            'publications' => $publications,
            'recommendations' => $recommendations, // Pass recommendations to the template
        ]);
    }

    
    
     
     #[Route('/publication/increment-view/{id}', name: 'app_increment_publication_view', methods: ['POST'])]
    public function incrementView(Publication $publication): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Increment views count
        $views = $publication->getViews() + 1;
        $publication->setViews($views);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }


   
    #[Route('/commentaire/{parentId}/reply', name: 'reply_to_comment', methods: ['GET', 'POST'])]
    public function replyToComment(Request $request, $parentId): Response
    {
        // Fetch the parent comment if needed, you can skip this if not required
        $parentComment = $this->getDoctrine()->getRepository(Commentaire::class)->find($parentId);
    
        // Create a new Commentaire entity
        $commentaire = new Commentaire();
        $currentUser = $this->getUser();
    
    


    $user = $this->getUser();
    if ($user instanceof User) {
        $commentaire->setIdUser($user);
    }

   
    
    $commentaire->setIdUser($currentUser);
        // Create the form, passing the Commentaire entity
        $form = $this->createForm(CommentaireType::class, $commentaire);
    
        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the parent comment ID
            $parentCommentEntity = $this->getDoctrine()->getRepository(Commentaire::class)->find($parentId);

            // Set the parent comment entity
            $commentaire->setParentComment($parentCommentEntity);
    
            // Save the reply to the database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentaire);
            $entityManager->flush();
    
            // Redirect to a success page or back to the comment thread
            return $this->redirectToRoute('app_publication_index', ['commentId' => $parentId]);
        }
    
        // Render the reply form, passing the form
        return $this->render('front/commentaire/reply.html.twig', [
            'form' => $form->createView(),
            'parentComment' => $parentComment, // Optional: pass the parent comment to the template
        ]);
    }
    





    #[Route('/{id}/qr-code', name: 'publication_qr_code')]
    public function generateQrCode(int $id): Response
    {
    
        $publication = $this->getDoctrine()->getRepository(Publication::class)->find($id);

        if (!$publication) {
            throw $this->createNotFoundException('The Publication does not exist');
        }

       
        $qrCodeString = sprintf(
            "Title: %s\nContent: %s",
            $publication->getTitre(),
            $publication->getDescription()
        );

    
        $qrCode = new QrCode($qrCodeString);

         $qrCode->setSize(100);
         $qrCode->setMargin(10);

        $writer = new PngWriter();

   
        $qrCodeData = $writer->write($qrCode)->getString();

    
        $response = new Response($qrCodeData, Response::HTTP_OK, ['Content-Type' => 'image/png']);

        return $response;
    }
    
    
    

    #[Route('/publication/{id}/like', name: 'like_publication', methods: ['POST'])]
public function likePublication($id, EntityManagerInterface $entityManager): Response
{

    $currentUser = $this->getUser();

 
    $publication = $entityManager->getRepository(Publication::class)->find($id);

   
    $react = $entityManager->getRepository(React::class)->findOneBy([
        'id_user' => $currentUser,
        'id_pub' => $publication,
    ]);

 
    if ($react) {
      
        $likeCount = $react->getLikeCount();

     
        if ($likeCount === 1) {
        
            $react->decrementLikeCount();
        } else {
            $react->incrementLikeCount();
           
            if ($react->getDislikeCount() === 1) {
                $react->decrementDislikeCount();
            }
        }
    } else {
       
        $react = new React();

     
        $react->setIdUser($currentUser);

    
        $react->setIdPub($publication);

    
        $react->setLikeCount(1);

       
        $entityManager->persist($react);
    }

    $entityManager->flush();

  
    return $this->redirectToRoute('app_publication_show', ['id' => $id]);
}

#[Route('/publication/{id}/dislike', name: 'dislike_publication', methods: ['POST'])]
public function dislikePublication($id, EntityManagerInterface $entityManager): Response
{
    
    $currentUser = $this->getUser();

   
    $publication = $entityManager->getRepository(Publication::class)->find($id);

   
    $react = $entityManager->getRepository(React::class)->findOneBy([
        'id_user' => $currentUser,
        'id_pub' => $publication,
    ]);

   
    if ($react) {
       
        $dislikeCount = $react->getDislikeCount();

       
        if ($dislikeCount === 1) {
            
            $react->decrementDislikeCount();
        } else {
           
            $react->incrementDislikeCount();
        
            if ($react->getLikeCount() === 1) {
                $react->decrementLikeCount();
            }
        }
    } else {
       
        $react = new React();

     
        $react->setIdUser($currentUser);

      
        $react->setIdPub($publication);

     
        $react->setDislikeCount(1);

        $entityManager->persist($react);
    }

   
    $entityManager->flush();

  
    return $this->redirectToRoute('app_publication_show', ['id' => $id]);
}


   

#[Route('/new', name: 'app_publication_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
{
    $publication = new Publication();
    $currentUser = $this->getUser();
    $publication->setIdUser($currentUser);

    $form = $this->createForm(PublicationType::class, $publication);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $imageName = md5(uniqid()) . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                $imageName
            );
            $publication->setImage('/uploads/images/' . $imageName);
        }

        $entityManager->persist($publication);
        $entityManager->flush();

        // Send email to all users
        $this->sendPublicationNotificationEmail($mailer, $currentUser, $publication);

        return $this->redirectToRoute('app_publication_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('front/publication/new.html.twig', [
        'publication' => $publication,
        'form' => $form->createView(),
    ]);
}

private function sendPublicationNotificationEmail(MailerInterface $mailer, UserInterface $currentUser, Publication $publication): void
{
    $users = $this->getDoctrine()->getRepository(Users::class)->findAll();
        foreach ($users as $user) {
        // Skip sending email to the user who created the publication
        if ($user->getId() === $currentUser->getId()) {
            continue;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($currentUser->getEmail(), $currentUser->getUsername()))
            ->to($user->getEmail())
            ->subject('New Publication Created')
            ->htmlTemplate('emails/new_publication_notification.html.twig')
            ->context([
                'currentUser' => $currentUser,
                'publication' => $publication,
                // Add any additional variables you need in the email template
            ]);

        $mailer->send($email);
    }
}




    

#[Route('/{id}', name: 'app_publication_show', methods: ['GET', 'POST'])]
public function show(Request $request, Publication $publication, EntityManagerInterface $entityManager,ProfanityFilter $profanityFilter ): Response
{   
    
    
    
    $views = $publication->getViews();
    $publication->setViews($views + 1);
    $entityManager->flush();
    $commentaire = new Commentaire();
    
    $currentUser = $this->getUser();
    
    $commentForm = $this->createForm(CommentaireType::class, $commentaire);
    $commentForm->handleRequest($request);


    $user = $this->getUser();
    if ($user instanceof User) {
        $commentaire->setIdUser($user);
    }

   
    $commentaire->setIdPub($publication);
    $commentaire->setIdUser($currentUser);

    $qrCode = null;

    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        $contenu = $commentaire->getContenu();
            $filteredContenu = $profanityFilter->filter($contenu);
            $commentaire->setContenu($filteredContenu);
        $entityManager->persist($commentaire);
        $entityManager->flush();

        
        return $this->redirectToRoute('app_publication_show', ['id' => $publication->getId()]);
    }

 
    

    return $this->render('front/publication/show.html.twig', [
        'publication' => $publication,
        'commentForm' => $commentForm->createView(),
        
    ]);
}





    #[Route('/{id}/edit', name: 'app_publication_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageName = md5(uniqid()) . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                    $imageName
                );
                $publication->setImage('/uploads/images/' . $imageName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_publication_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/publication/edit.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

  
    #[Route('/publication/{id}', name: 'app_publication_delete', methods: ['POST'])]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$publication) {
            throw $this->createNotFoundException('Publication not found');
        }

        if ($this->isCsrfTokenValid('delete' . $publication->getId(), $request->request->get('_token'))) {
            $entityManager->remove($publication);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_publication_index');
    }
    

    
    
}

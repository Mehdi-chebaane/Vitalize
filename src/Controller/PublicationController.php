<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Notifier\Recipient\SlackRecipient;
use Symfony\Component\Notifier\NotifierInterface;


#[Route('publication')]
class PublicationController extends AbstractController
{
  
    
    
    #[Route('/', name: 'app_publication_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository, Request $request): Response
    {
        $type = $request->query->get('type');

        // Check if the type query parameter is set and filter publications accordingly
        if ($type && in_array($type, ['Nutrition', 'Progrés'])) {
            $publications = $publicationRepository->findBy(['type' => $type]);
        } else {
            $publications = $publicationRepository->findAll();
        }

        return $this->render('front/publication/index.html.twig', [
            'publications' => $publications,
        ]);
    }

    #[Route('/publication/{id}/like', name: 'like_publication', methods: ['POST'])]
public function likePublication($id, EntityManagerInterface $entityManager): Response
{
    // Get the current user
    $currentUser = $this->getUser();

    // Find the publication by its ID
    $publication = $entityManager->getRepository(Publication::class)->find($id);

    // Find the existing react by the current user and publication
    $react = $entityManager->getRepository(React::class)->findOneBy([
        'id_user' => $currentUser,
        'id_pub' => $publication,
    ]);

    // If the react exists, toggle the like count
    if ($react) {
        // Get the current like count
        $likeCount = $react->getLikeCount();

        // Update the like count based on the current state
        if ($likeCount === 1) {
            // If the like count is 1, decrement it
            $react->decrementLikeCount();
        } else {
            // If the like count is 0 or null, increment it
            $react->incrementLikeCount();
        }
    } else {
        // Create a new React instance
        $react = new React();

        // Set the current user as the user for the react
        $react->setIdUser($currentUser);

        // Set the publication for the react
        $react->setIdPub($publication);

        // Set the like count to 1
        $react->setLikeCount(1);

        // Persist the react changes
        $entityManager->persist($react);
    }

    // Flush changes to the database
    $entityManager->flush();

    // Redirect to the publication page or wherever you want
    return $this->redirectToRoute('app_publication_show', ['id' => $id]);
}




#[Route('/publication/{id}/dislike', name: 'dislike_publication', methods: ['POST'])]
public function dislikePublication($id, EntityManagerInterface $entityManager): Response
{
    // Get the current user
    $currentUser = $this->getUser();

    // Find the publication by its ID
    $publication = $entityManager->getRepository(Publication::class)->find($id);

    // Find the existing react by the current user and publication
    $react = $entityManager->getRepository(React::class)->findOneBy([
        'id_user' => $currentUser,
        'id_pub' => $publication,
    ]);

    // If the react exists, toggle the dislike count
    if ($react) {
        // Get the current dislike count
        $dislikeCount = $react->getDislikeCount();

        // Update the dislike count based on the current state
        if ($dislikeCount === 1) {
            // If the dislike count is 1, decrement it
            $react->decrementDislikeCount();
        } else {
            // If the dislike count is 0 or null, increment it
            $react->incrementDislikeCount();
        }
    } else {
        // Create a new React instance
        $react = new React();

        // Set the current user as the user for the react
        $react->setIdUser($currentUser);

        // Set the publication for the react
        $react->setIdPub($publication);

        // Set the dislike count to 1
        $react->setDislikeCount(1);

        // Persist the react changes
        $entityManager->persist($react);
    }

    // Flush changes to the database
    $entityManager->flush();

    // Redirect to the publication page or wherever you want
    return $this->redirectToRoute('app_publication_show', ['id' => $id]);
}


   

    #[Route('/new', name: 'app_publication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $publication = new Publication();
        $currentUser = $this->getUser();
        $publication->setIdUser($currentUser); // Assuming you have a method like setIdUser
        
        

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

            return $this->redirectToRoute('app_publication_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/publication/new.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }


    

#[Route('/{id}', name: 'app_publication_show', methods: ['GET', 'POST'])]
public function show(Request $request, Publication $publication, EntityManagerInterface $entityManager,ProfanityFilter $profanityFilter ): Response
{
    $commentaire = new Commentaire();
    
    $currentUser = $this->getUser();
    
    $commentForm = $this->createForm(CommentaireType::class, $commentaire);
    $commentForm->handleRequest($request);

    // Check if the user is authenticated before setting the id_user
    $user = $this->getUser();
    if ($user instanceof User) {
        $commentaire->setIdUser($user);
    }

    // Automatically set the id_pub to the id of the current publication
    $commentaire->setIdPub($publication);
    $commentaire->setIdUser($currentUser);

    // Initialize $qrCode variable
    $qrCode = null;

    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        $contenu = $commentaire->getContenu();
            $filteredContenu = $profanityFilter->filter($contenu);
            $commentaire->setContenu($filteredContenu);
        $entityManager->persist($commentaire);
        $entityManager->flush();

        // Redirect back to the publication page after submitting the comment
        return $this->redirectToRoute('app_publication_show', ['id' => $publication->getId()]);
    }

    // Access the QR code factory service
    

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

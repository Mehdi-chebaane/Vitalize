<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Publication;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Entity\Users;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PublicationBackController extends AbstractController
{
    #[Route('/admin/publication', name: 'app_publication_back')]
    public function index(PublicationRepository $publicationRepository, Request $request): Response
    {
        $type = $request->query->get('type');

        // Check if the type query parameter is set and filter publications accordingly
        if ($type && in_array($type, ['Nutrition', 'Progrés'])) {
            $publications = $publicationRepository->findBy(['type' => $type]);
        } else {
            $publications = $publicationRepository->findAll();
        }

        return $this->render('admin/publication/index.html.twig', [
            'publications' => $publications,
        ]);
    }

    #[Route('/admin/publication/new', name: 'back_publication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        
        $currentUser = $this->getUser();
        $publication->setIdUser($currentUser); // Assuming you have a method like setIdUser
        $publication = new Publication();
        

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

            return $this->redirectToRoute('app_publication_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/publication/new.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }


    #[Route('admin/publication/{id}', name: 'back_publication_show', methods: ['GET', 'POST'])]
public function show(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
{
    $commentaire = new Commentaire();
    $commentForm = $this->createForm(CommentaireType::class, $commentaire);
    $commentForm->handleRequest($request);

    // Check if the user is authenticated before setting the id_user
    $user = $this->getUser();
    if ($user instanceof Users) {
        $commentaire->setIdUser($user);
    }

    // Automatically set the id_pub to the id of the current publication
    $commentaire->setIdPub($publication);

    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        // Persist the comment
        $entityManager->persist($commentaire);
        $entityManager->flush();

        // Redirect back to the publication page after submitting the comment
        return $this->redirectToRoute('back_publication_show', ['id' => $publication->getId()]);
    }

    return $this->render('admin/publication/show.html.twig', [
        'publication' => $publication,
        'commentForm' => $commentForm->createView(),
    ]);
}

#[Route('/admin/publication/{id}/edit', name: 'back_publication_edit', methods: ['GET', 'POST'])]
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

            return $this->redirectToRoute('app_publication_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/publication/edit.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }
    #[Route('admin/publication/{id}', name: 'back_publication_delete', methods: ['POST'])]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$publication) {
            throw $this->createNotFoundException('Publication not found');
        }

        if ($this->isCsrfTokenValid('delete' . $publication->getId(), $request->request->get('_token'))) {
            $entityManager->remove($publication);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_publication_back');
    }

}

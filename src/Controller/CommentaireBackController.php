<?php

namespace App\Controller;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CommentaireBackController extends AbstractController
{
    #[Route('/admin/commentaire', name: 'back_commentaire_back')]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('admin/commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    #[Route('/admin/commentaire/new', name: 'new_commentaire_back', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('back_commentaire_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/commentaire/{id}', name: 'back_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('admin/commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('admin/commentaire/{id}/edit', name: 'back_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('back_commentaire_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' =>$form->createView(),
        ]);
    }
    
    #[Route('admin/commentaire/{id}', name: 'back_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_commentaire_back', [], Response::HTTP_SEE_OTHER);
    }

}

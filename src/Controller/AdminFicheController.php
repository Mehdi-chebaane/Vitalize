<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Fichepatient;
use App\Form\FichepatientType;
use App\Repository\FichepatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/fiche')]
class AdminFicheController extends AbstractController
{
    #[Route('/', name: 'admin_app_fiche_index', methods: ['GET'])]
    public function index(FichepatientRepository $fichepatientRepository): Response
    {
        return $this->render('admin_fiche/index.html.twig', [
            'fichepatients' => $fichepatientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_app_fiche_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fichepatient = new Fichepatient();
        $form = $this->createForm(FichepatientType::class, $fichepatient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fichepatient);
            $entityManager->flush();

            return $this->redirectToRoute('admin_app_fiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_fiche/new.html.twig', [
            'fichepatient' => $fichepatient,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_app_fiche_show', methods: ['GET'])]
    public function show(Fichepatient $fichepatient): Response
    {
        return $this->render('admin_fiche/show.html.twig', [
            'fichepatient' => $fichepatient,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_app_fiche_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fichepatient $fichepatient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FichepatientType::class, $fichepatient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_app_fiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_fiche/edit.html.twig', [
            'fichepatient' => $fichepatient,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_app_fiche_delete', methods: ['POST'])]
    public function delete(Request $request, Fichepatient $fichepatient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fichepatient->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fichepatient);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_app_fiche_index', [], Response::HTTP_SEE_OTHER);
    }
    
}


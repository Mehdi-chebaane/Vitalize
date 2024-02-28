<?php

namespace App\Controller;

use App\Entity\React;
use App\Entity\Users;
use App\Entity\Publication;
use App\Form\ReactType;
use App\Repository\ReactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/react')]
class ReactController extends AbstractController
{
    #[Route('/', name: 'app_react_index', methods: ['GET'])]
    public function index(ReactRepository $reactRepository): Response
    {
        return $this->render('react/index.html.twig', [
            'reacts' => $reactRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_react_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $react = new React();
        $form = $this->createForm(ReactType::class, $react);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($react);
            $entityManager->flush();

            return $this->redirectToRoute('app_react_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('react/new.html.twig', [
            'react' => $react,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_react_show', methods: ['GET'])]
    public function show(React $react): Response
    {
        return $this->render('react/show.html.twig', [
            'react' => $react,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_react_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, React $react, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReactType::class, $react);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_react_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('react/edit.html.twig', [
            'react' => $react,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_react_delete', methods: ['POST'])]
    public function delete(Request $request, React $react, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$react->getId(), $request->request->get('_token'))) {
            $entityManager->remove($react);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_react_index', [], Response::HTTP_SEE_OTHER);
    }
}

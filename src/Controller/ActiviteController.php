<?php

namespace App\Controller;
use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/activite')]
class ActiviteController extends AbstractController
{
    #[Route('/', name: 'app_activite_index', methods: ['GET'])]
    public function index(ActiviteRepository $activiteRepository,Request $request): Response
    {       $sort = $request->query->get('sort', 'type_activite');
        $search = $request->query->get('search');

        $activite = $activiteRepository->searchAndSort($search, $sort);

            
        return $this->render('front/activite/index.html.twig', [
            'activites' => $activite,           
            'sort' => $sort, 
            'search'=>$search
        ]);
    }

    #[Route('/new', name: 'app_activite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageAct']->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName
            );
            $activite->setImageAct($fileName);
            $entityManager->persist($activite);
            $entityManager->flush();
            return $this->redirectToRoute('app_activite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/activite/new.html.twig', [
            'activite' => $activite,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_activite}', name: 'app_activite_show', methods: ['GET'])]
    public function show(Activite $activite): Response
    {
        return $this->render('front/activite/show.html.twig', [
            'activite' => $activite,
        ]);
    }

    #[Route('/{id_activite}/edit', name: 'app_activite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageAct']->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName
            );
            $activite->setImageAct($fileName);
            $entityManager->persist($activite);
            
            $entityManager->flush();

            return $this->redirectToRoute('app_activite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/activite/edit.html.twig', [
            'activite' => $activite,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_activite}', name: 'app_activite_delete', methods: ['POST'])]
    public function delete(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$activite->getId_activite(), $request->request->get('_token'))) {
            $entityManager->remove($activite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_activite_index', [], Response::HTTP_SEE_OTHER);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    #[Route('/n/admin', name: 'adminapp_activite_index', methods: ['GET'])]
    public function indexadmin(ActiviteRepository $activiteRepository,Request $request): Response
    {       $sort = $request->query->get('sort', 'type_activite');
        $search = $request->query->get('search');

        $activite = $activiteRepository->searchAndSort($search, $sort);

            
        return $this->render('admin/activite/index.html.twig', [
            'activites' => $activite,           
            'sort' => $sort, 
            'search'=>$search
        ]);
    }

    #[Route('/new/admin', name: 'adminapp_activite_new', methods: ['GET', 'POST'])]
    public function newadmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageAct']->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName
            );
            $activite->setImageAct($fileName);
            $entityManager->persist($activite);
            $entityManager->flush();
            return $this->redirectToRoute('adminapp_activite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/activite/new.html.twig', [
            'activite' => $activite,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_activite}/admin', name: 'adminapp_activite_show', methods: ['GET'])]
    public function showadmin(Activite $activite): Response
    {
        return $this->render('admin/activite/show.html.twig', [
            'activite' => $activite,
        ]);
    }

    #[Route('/{id_activite}/edit/admin', name: 'adminapp_activite_edit', methods: ['GET', 'POST'])]
    public function editadmin(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageAct']->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName
            );
            $activite->setImageAct($fileName);
            $entityManager->persist($activite);
            
            $entityManager->flush();

            return $this->redirectToRoute('adminapp_activite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/activite/edit.html.twig', [
            'activite' => $activite,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_activite}/admin', name: 'adminapp_activite_delete', methods: ['POST'])]
    public function deleteadmin(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('deleteadmin'.$activite->getId_activite(), $request->request->get('_token'))) {
            $entityManager->remove($activite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('adminapp_activite_index', [], Response::HTTP_SEE_OTHER);
    }
    
}


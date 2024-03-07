<?php

namespace App\Controller;
use App\Entity\Evenement;
use App\Entity\Activite;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository,Request $request): Response
    {
        $sort = $request->query->get('sort', 'nom');
        $search = $request->query->get('search');

        $evenements = $evenementRepository->searchAndSort($search, $sort);

            
        return $this->render('front/evenement/index.html.twig', [
            'evenementRepository'=>$evenementRepository,
            'evenements' => $evenements,
            'sort' => $sort, 
            'search'=>$search
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageEve']->getData();
            $name = $request->request->get('name');

            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName);
            $evenement->setImageEve($fileName);
            $evenement->setLocalisation($name);
            $entityManager->persist($evenement);
            $entityManager->flush();
            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('front/evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idevenement}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, EntityManagerInterface $entityManager,EvenementRepository $evenementRepository ): Response
    {   
        $activites = $entityManager->getRepository(Activite::class)->findBy(['idevenement' => $evenement]);

        return $this->render('front/evenement/show.html.twig', [
            'evenement' => $evenement,
            'activites' => $activites,
            'evenementRepository'=>$evenementRepository
        ]);
    }

    #[Route('/{idevenement}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
       
            
            
           
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageEve']->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName);
            $evenement->setImageEve($fileName);
            $entityManager->persist($evenement);
            $entityManager->flush();
            

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idevenement}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getIdevenement(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
    //////////////////////////////////////////////////////////////////////////
    #[Route('/n/admin', name: 'adminapp_evenement_index', methods: ['GET'])]
    public function indexadmin(EvenementRepository $evenementRepository,Request $request): Response
    {
        $sort = $request->query->get('sort', 'nom');
        $search = $request->query->get('search');

        $evenements = $evenementRepository->searchAndSort($search, $sort);

            
        return $this->render('back/evenement/index.html.twig', [
            'evenementRepository'=>$evenementRepository,
            'evenements' => $evenements,
            'sort' => $sort, 
            'search'=>$search
        ]);
    }

    #[Route('/new/admin', name: 'adminapp_evenement_new', methods: ['GET', 'POST'])]
    public function newadmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageEve']->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName);
            $evenement->setImageEve($fileName);
            $entityManager->persist($evenement);
            $entityManager->flush();
            return $this->redirectToRoute('adminapp_evenement_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('back/evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idevenement}/admin', name: 'adminapp_evenement_show', methods: ['GET'])]
    public function showadmin(Evenement $evenement, EntityManagerInterface $entityManager): Response
    {   
        $activites = $entityManager->getRepository(Activite::class)->findBy(['idevenement' => $evenement]);

        return $this->render('back/evenement/show.html.twig', [
            'evenement' => $evenement,
            'activites' => $activites,

        ]);
    }

    #[Route('/{idevenement}/edit/admin', name: 'adminapp_evenement_edit', methods: ['GET', 'POST'])]
    public function editadmin(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
       
            
            
           
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageEve']->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName);
            $evenement->setImageEve($fileName);
            $entityManager->persist($evenement);
            $entityManager->flush();
            

            return $this->redirectToRoute('adminapp_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/n/handle-reservation', name: 'handle_reservation', methods: ['POST'])]
    public function handleReservation(Request $request, EvenementRepository $evenementRepository)
    {
        $idevenement = $request->request->get('idevenement');
        $action = $request->request->get('action');

        $evenement = $evenementRepository->find($idevenement);

        if (!$evenement) {
            return new JsonResponse(['error' => 'Evenement not found'], 404);
        }

        if       ($action === 'add') {
            $evenementRepository->addReservation($idevenement,150);
        } elseif ($action === 'delete') {
            $evenementRepository->deleteReservation($idevenement,150);
        }
        

        return new JsonResponse(['success' => true]);
    }
        #[Route('/n/handle-reservation/{idevenement}/recherche', name: 'handle_reservation_recherche', methods: ['POST'])]

        public function handleReservationRecherche(Request $request, EvenementRepository $evenementRepository)
        {
            $idevenement = $request->attributes->get('idevenement');
            $userid = 150; // Replace with the actual user ID

            $reservationExists = $evenementRepository->Recherche($idevenement, $userid);

            return new JsonResponse(['success' => $reservationExists]);
        }
        

    #[Route('/{idevenement}/admin', name: 'adminapp_evenement_delete', methods: ['POST'])]
    public function deleteadmin(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('deleteadmin' . $evenement->getIdevenement(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('adminapp_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/n/calendar', name: 'calendar')]
    public function calendar(Request $request, EntityManagerInterface $entityManager,EvenementRepository $evenementRepository): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['imageEve']->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'), 
                $fileName);
            $evenement->setImageEve($fileName);
            $entityManager->persist($evenement);
            $entityManager->flush();
            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }
            
        return $this->render('front/evenement/Calendar.html.twig', [

            'evenement' => $evenement,
            'form' => $form->createView(),
            'evenementRepository'=>$evenementRepository

        ]);
    }
   
    
 }


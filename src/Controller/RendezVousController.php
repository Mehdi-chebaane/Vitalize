<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Users;
use App\Form\RendezVousType;
use App\Repository\RendezVousRepository;
use App\Repository\DoctorAvRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
class RendezVousController extends AbstractController
{
    #[Route('/rendez', name: 'app_r_d_v_index', methods: ['GET'])]
    public function index(RendezVousRepository $rendezVousRepository, UsersRepository $userRepository): Response
    {
        $medecins = $userRepository->findAll();

        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vouses' => $rendezVousRepository->findAll(),
            'medecins' => $medecins,
        ]);
    }
   
    #[Route('/rendez/new/{medic_id}', name: 'app_r_d_v_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ?int $medic_id = null): Response
{
    $rendezVou = new RendezVous();
    $form = $this->createForm(RendezVousType::class, $rendezVou);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            // Set the doctor for the rendezvous based on the provided ID
            if ($medic_id) {
                $doctor = $entityManager->getRepository(Users::class)->find($medic_id);
                if ($doctor instanceof Users) {
                    $rendezVou->setDoctor($doctor);
                }
            }
            $rendezVou->setIsAvailable(true);
            $entityManager->persist($rendezVou);
            $entityManager->flush();
            return $this->redirectToRoute('app_r_d_v_index');
        } catch (UniqueConstraintViolationException $e) {
            // Handle the exception gracefully
            $this->addFlash('error', 'This appointment is already booked. Please choose another date/hour.');
            return $this->redirectToRoute('app_r_d_v_new', ['medic_id' => $medic_id]);
        }
    }

    return $this->render('rendez_vous/new.html.twig', [
        'rendez_vou' => $rendezVou,
        'form' => $form->createView(),
    ]);
}




#[Route('/rendez/{id}', name: 'app_r_d_v_show', methods: ['GET'])]
public function show(RendezVous $rendezVou): Response
{
    // Fetch the doctor's information associated with this rendezvous
    $doctor = $rendezVou->getDoctor();
    
    return $this->render('rendez_vous/show.html.twig', [
        'rendez_vou' => $rendezVou,
        'doctor' => $doctor, // Pass the doctor's information to the template
    ]);
}


    #[Route('/rendez/{id}/edit', name: 'app_r_d_v_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_r_d_v_index');
        }
        return $this->render('rendez_vous/edit.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/rendez/{id}', name: 'app_r_d_v_delete', methods: ['POST'])]
    public function delete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rendezVou->getId(), $request->request->get('_token'))) {
            $entityManager->remove($rendezVou);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_r_d_v_index');
    }

    #[Route('/rendezvous', name: 'app_rendez_vous', methods: ['GET'])]
    public function rdv(Request $request, UsersRepository $userRepository): Response
    {
        $name = $request->query->get('name');
        $medecins = [];
            if ($name) {
            $medecins = $userRepository->findByNom($name);
        }
        return $this->render('rendez_vous/rendezvous.html.twig', [
            'controller_name' => 'RendezVousController',
            'medecins' => $medecins,
        ]);
    }

    #[Route('/api/users/{nom}', name: 'api_users_search_by_nom', methods: ['GET'])]
    public function searchUsersByNom(string $nom, UsersRepository $usersRepository): JsonResponse
    {
        $users = $usersRepository->findByNom($nom);
        return $this->json($users);
    }
}

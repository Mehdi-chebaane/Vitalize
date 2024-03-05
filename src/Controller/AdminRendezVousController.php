<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RendezVousRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\RendezVous;
use App\Entity\Users;
use App\Form\RendezVousType;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminRendezVousController extends AbstractController
{
    
    #[Route('/admin/rendezbook', name: 'Admin_rendez_index', methods: ['GET'])]
    public function rdvv(RendezVousRepository $rendezVousRepository, UsersRepository $userRepository): Response
    {
        $medecins = $userRepository->findAll();

        return $this->render('admin_rendezvous/index.html.twig', [
            'rendez_vouses' => $rendezVousRepository->findAll(),
            'medecins' => $medecins,
        ]);
    }
   
    #[Route('/admin/rendez/new/{medic_id}', name: 'Admin_rendez_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ?int $medic_id = null): Response
{
    $rendezVou = new RendezVous();
    $form = $this->createForm(RendezVousType::class, $rendezVou);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            if ($medic_id) {
                $doctor = $entityManager->getRepository(Users::class)->find($medic_id);
                if ($doctor instanceof Users) {
                    $rendezVou->setDoctor($doctor);
                }
            }
            $rendezVou->setIsAvailable(true);
            $entityManager->persist($rendezVou);
            $entityManager->flush();
            return $this->redirectToRoute('Admin_rendez_index');
        } catch (UniqueConstraintViolationException $e) {
            // Handle the exception gracefully
            $this->addFlash('error', 'This appointment is already booked. Please choose another date/hour.');
            return $this->redirectToRoute('Admin_rendez_new', ['medic_id' => $medic_id]);
        }
    }
    return $this->render('admin_rendezvous/new.html.twig', [
        'rendez_vou' => $rendezVou,
        'form' => $form->createView(),
    ]);
}


#[Route('/admin/rendez/{id}', name: 'Admin_rendez_show', methods: ['GET'])]
public function show(RendezVous $rendezVou): Response
{
    // Fetch the doctor's information associated with this rendezvous
    $doctor = $rendezVou->getDoctor();
    
    return $this->render('admin_rendezvous/show.html.twig', [
        'rendez_vou' => $rendezVou,
        'doctor' => $doctor, // Pass the doctor's information to the template
    ]);
}


    #[Route('/admin/rendez/{id}/edit', name: 'Admin_rendez_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('Admin_rendez_index');
        }
        return $this->render('admin_rendezvous/edit.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/rendez/{id}', name: 'Admin_rendez_delete', methods: ['POST'])]
    public function delete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rendezVou->getId(), $request->request->get('_token'))) {
            $entityManager->remove($rendezVou);
            $entityManager->flush();
        }
        return $this->redirectToRoute('Admin_rendez_index');
    }

    #[Route('/admin/rendezvous', name: 'app_admin_rendez_vous', methods: ['GET'])]
    public function rdv(Request $request, UsersRepository $userRepository): Response
    {
        $name = $request->query->get('name');
        $medecins = [];
            if ($name) {
            $medecins = $userRepository->findByNom($name);
        }
        return $this->render('admin_rendezvous/rendezvous.html.twig', [
            'controller_name' => 'AdminRendezVousController',
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
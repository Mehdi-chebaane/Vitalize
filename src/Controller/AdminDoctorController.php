<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RendezVousRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\RendezVous;
use App\Form\RendezVousType;
use Symfony\Component\HttpFoundation\JsonResponse;
class AdminDoctorController extends AbstractController
{
    #[Route('/admin/doctor', name: 'app_admin_doctor')]
    public function index(): Response
    {
        return $this->render('admin_doctor/index.html.twig', [
            'controller_name' => 'AdminDoctorController',
        ]);
    }

    #[Route('/admindoctorliste', name: 'doctor_liste_admin_index')]
    public function ListeAdmin(RendezVousRepository $rendezVousRepository, UsersRepository $userRepository): Response
    {
        $medecins = $userRepository->findAll();
        return $this->render('admin_doctor/index.html.twig', [
            'rendez_vouses' => $rendezVousRepository->findAll(),
            'controller_name' => 'DoctorController',
        ]);
    }

    
    #[Route('/admindoctorliste/{id}', name: 'doctor_liste_admin_show', methods: ['GET'])]
    public function show(RendezVous $rendezVou): Response
    {
        // Fetch the doctor's information associated with this rendezvous
        $doctor = $rendezVou->getDoctor();
        
        return $this->render('admin_doctor/show.html.twig', [
            'rendez_vou' => $rendezVou,
            'doctor' => $doctor, // Pass the doctor's information to the template
        ]);
    }
    
    
        #[Route('/admindoctorliste/{id}/edit', name: 'doctor_liste_admin_edit', methods: ['GET', 'POST'])]
        public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
        {
            $form = $this->createForm(RendezVousType::class, $rendezVou);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
    
                return $this->redirectToRoute('doctor_liste_admin_index');
            }
            return $this->render('admin_doctor/edit.html.twig', [
                'rendez_vou' => $rendezVou,
                'form' => $form->createView(),
            ]);
        }
    
        #[Route('/adminlistedoctor/{id}', name: 'doctor_liste_admin_delete', methods: ['POST'])]
        public function delete(Request $request, RendezVous $rendezvous): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rendezvous->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($rendezvous);
            $entityManager->flush();
        }

        return $this->redirectToRoute('doctor_liste_admin_index');
    }

}

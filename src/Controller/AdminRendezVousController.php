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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\LienMeet;

class AdminRendezVousController extends AbstractController
{
    
    #[Route('/admin/rendezvouslist', name: 'Admin_rendez_index', methods: ['GET'])]
    public function rdvv(RendezVousRepository $rendezVousRepository, UsersRepository $userRepository): Response
    {
        $medecins = $userRepository->findAll();

        return $this->render('admin_rendezvous/index.html.twig', [
            'rendez_vouses' => $rendezVousRepository->findAll(),
            'medecins' => $medecins,
        ]);
    }
   
    #[Route('/admin/rendez/new/{medic_id}', name: 'Admin_rendez_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer,  LienMeet $lienMeet,?int $medic_id = null): Response
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
            
            // Get the appointment type from the form
            $typeRdv = $form->get('type')->getData();

            // Initialize the meet link variable
            $meetLink = null;

            if ($typeRdv === 'en_ligne') {
                // Inclure le lien Meet dans l'e-mail
                $meetLinks = $lienMeet->genererLiensMeet();
                $meetLink = $meetLinks[array_rand($meetLinks)]; // Select a random meet link
                $this->sendMeetLinkEmail($mailer, $rendezVou, $this->getUser(), $meetLink);
            }

            // Set the appointment type in the rendezvous entity
            $rendezVou->setType($typeRdv);

            // Set other properties and persist the entity
            $rendezVou->setIsAvailable(true);
            $entityManager->persist($rendezVou);
            $entityManager->flush();

            // Send notification email to the patient
            $this->sendRdvNotificationEmail($mailer, $this->getUser(), $rendezVou, $meetLink);

            // Send appointment email to doctor
            $this->sendAppointmentNotificationEmail($mailer, $doctor, $rendezVou, $meetLink);
            
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

private function sendDoctorMeetLinkEmail(MailerInterface $mailer, Users $doctor, RendezVous $rendezvous, string $meetLink): void
{
    $email = (new TemplatedEmail())
        ->from("testp3253@gmail.com")
        ->to($doctor->getEmail())
        ->subject('Google Meet Link for Appointment')
        ->htmlTemplate('admin_rendezvous/doctor_meet_link_email.html.twig')
        ->context([
            'doctor' => $doctor,
            'rendezvous' => $rendezvous,
            'meetLink' => $meetLink,
        ]);

    $mailer->send($email);
}
    private function sendRdvNotificationEmail(MailerInterface $mailer, UserInterface $currentUser, RendezVous $rendezvous, string $meetLink = null): void
    {
        $email = (new TemplatedEmail())
            ->from("testp3253@gmail.com")
            ->to($currentUser->getEmail())
            ->subject('New Appointment Created')
            ->htmlTemplate('admin_rendezvous/email.html.twig')
            ->context([
                'currentUser' => $currentUser,
                'rendezvous' => $rendezvous,
                
            ]);

        $mailer->send($email);
    }
    
    private function sendAppointmentNotificationEmail(MailerInterface $mailer, Users $doctor, RendezVous $rendezvous,string $meetLink = null): void
    {
        $email = (new TemplatedEmail())
            ->from('testp3253@gmail.com') // Update with your email
            ->to($doctor->getEmail()) // Doctor's email
            ->subject('New Appointment Booked')
            ->htmlTemplate('admin_rendezvous/email2.html.twig')
            ->context([
                'doctor' => $doctor,
                'rendezvous' => $rendezvous,
            ]);

        $mailer->send($email);
    }
   private function sendMeetLinkEmail(MailerInterface $mailer, RendezVous $rendezvous, UserInterface $currentUser, string $meetLink): void
{
    $email = (new TemplatedEmail())
        ->from("testp3253@gmail.com")
        ->to($currentUser->getEmail())
        ->subject('Google Meet Link for Your Appointment')
        ->htmlTemplate('admin_rendezvous/EmailMeetEnligne.html.twig')
        ->context([
            'currentUser' => $currentUser,
            'rendezvous' => $rendezvous,
            'meetLink' => $meetLink,
        ]);

    $mailer->send($email);
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
public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager, ?int $medic_id = null): Response
{
    $form = $this->createForm(RendezVousType::class, $rendezVou);
    $form->handleRequest($request);

    try {
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('Admin_rendez_index');
        }
    } catch (UniqueConstraintViolationException $e) {
        // Handle the exception gracefully
        $this->addFlash('error', 'This appointment is already booked. Please choose another date/hour.');
        return $this->redirectToRoute('Admin_rendez_new', ['medic_id' => $medic_id]);
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
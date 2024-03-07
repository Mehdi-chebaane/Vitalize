<?php

namespace App\Controller;
use App\Entity\Reponse;
use App\Form\ReponseType;

use App\Entity\Reclamation;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReclamationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Dompdf\Dompdf;


class ReponseController extends AbstractController
{
    #[Route('/reponse', name: 'app_reponse')]
    public function index(Request $request,ReclamationRepository $reclamationRepository): Response
    {   $reclamation = new Reclamation();
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw $this->createAccessDeniedException('User not authenticated');
        }
        $reclamations = $reclamationRepository->findBy(['user' => $currentUser]);
        return $this->render('reponse/index.html.twig', [
            'controller_name' => 'ReponseController',
            'reclamation' => $reclamations
        ]);
    }


  

   #[Route('/reponse/new/{reclamationId}', name: 'newRep', methods: ['GET', 'POST'])]
    public function new(Request $request, $reclamationId, MailerInterface $mailer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Fetch the Reclamation entity by ID
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);

        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found for id: ' . $reclamationId);
        }

        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the Reclamation for the Reponse entity
            $reponse->setReclamation($reclamation);

            // Extract the ID from the User object and set it as the patient
            $patientId = $reclamation->getUser()->getId();
            $reponse->setPatient($patientId);

            $etat = $form->get('etat')->getData();
            $reclamation->setEtat($etat);

            $entityManager->persist($reponse);
            $entityManager->persist($reclamation);
            $entityManager->flush();

            // Set email subject based on Reclamation's Sujet
            $SujetReclamation = $reclamation->getSujet();
            $email = (new Email())
                ->from('malek.belhadjamor@gmail.com')
                ->to('testp3253@gmail.com')
                ->subject('Reponse sur votre reclamation : Sujet: ' . $SujetReclamation)
                ->text($reponse->getMessage());
            $mailer->send($email);

            // Flash message and redirect
            $this->addFlash('success', 'Reponse created successfully.');
            return $this->redirectToRoute('reponse_show', ['reclamationId' => $reclamationId]);
        }

        // Render the form template with necessary variables
        return $this->render('reponse/formRep.html.twig', [
            'form' => $form->createView(),
            'reclamationId' => $reclamationId,
            'sujet' => $reclamation->getSujet()
        ]);
    }
    


        #[Route("/reponse/{reclamationId}", name:"reponse_show")]
        public function show(int $reclamationId, ReclamationRepository $reclamationRepository ): Response
        {
            // Get the EntityManager
            $entityManager = $this->getDoctrine()->getManager();
    
            // Get the Reclamation entity by its ID
            $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);
    
            if (!$reclamation) {
                throw $this->createNotFoundException('Reclamation not found for id: ' . $reclamationId);
            }
    
            // Get all responses for the given Reclamation
            $reponses = $reclamation->getReponses();
            
            // Render the template with the Reclamation and its associated Reponses
            return $this->render('reponse/ShowReponse.html.twig', [
                'reclamation' => $reclamation,
                'reponses' => $reponses,
                'reclamations' => $reclamationRepository->findAll(),

            ]);
        }


        #[Route('/delete/response/{id}', name: 'app_response_delete', methods: ['GET'])]
        
        public function deleteResponse(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $response = $entityManager->getRepository(Reponse::class)->find($id);

    if (!$response) {
        throw $this->createNotFoundException('Response not found');
    }

    // Find the reclamation associated with the response
    $reclamation = $response->getReclamation();

    if (!$reclamation) {
        throw $this->createNotFoundException('Reclamation not found');
    }

    // Remove the response from the reclamation
    $reclamation->removeReponse($response);

    // Remove the response itself
    $entityManager->remove($response);
    $entityManager->flush();

    return new Response('Response deleted successfully', Response::HTTP_OK);
}

        
}
    

    
 


<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Entity\Reclamation;
use Symfony\Component\Mime\Email;
use App\Repository\ReclamationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/adminreclamation')]


class AdminReclamationController extends AbstractController
{

    #[Route('/showAll', name: 'admin_reclamation_index', methods: ['GET'])]
    public function findAll(ReclamationRepository $reclamationRepository): Response
    {   $reclamation = new Reclamation();
        return $this->render('admin/Reclamation/ListeReclamation.html.twig', [
            'reclamation' => $reclamationRepository->findAll(),
        ]);
    }


    #[Route('/chartAdmin', name: 'admin_reclamation_chart', methods: ['GET'])]
    public function pieChart(ReclamationRepository $reclamationRepository): Response
    {
        $reclamation = $reclamationRepository->findAll();
        $recNumber = count($reclamation);
        
        // Count the number of reclamations for each type
        $complaintCount = $reclamationRepository->countByType('plainte');
        $suggestionCount = $reclamationRepository->countByType('Suggestion');
        $informationRequestCount = $reclamationRepository->countByType('demande information');
        
        // Calculate percentages
        $complaintPercentage = ($complaintCount / $recNumber) * 100;
        $suggestionPercentage = ($suggestionCount / $recNumber) * 100;
        $informationRequestPercentage = ($informationRequestCount / $recNumber) * 100;
    
        $reclamationsResolues = count($reclamationRepository->findByEtat('Resolu'));
        $reclamationsEnAttente = count($reclamationRepository->findByEtat('En attente'));
        $reclamationsEnCours = count($reclamationRepository->findByEtat('En Cours'));
    
        // Render the view with the counts
        return $this->render('admin/Reclamation/Chart.html.twig', [
            'reclamation' => $reclamationRepository->findAll(),
            'complaintPercentage' => $complaintPercentage,
            'suggestionPercentage' => $suggestionPercentage,
            'informationRequestPercentage' => $informationRequestPercentage,
            'reclamationsResolues' => $reclamationsResolues,
            'reclamationsEnAttente' => $reclamationsEnAttente,
            'reclamationsEnCours' => $reclamationsEnCours,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_reclamation_delete', methods: ['GET'])]
public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
    if (!$reclamation) {
        throw $this->createNotFoundException('Reclamation not found');
    }

    // Find and delete related responses
    $responses = $reclamation->getReponses();
    foreach ($responses as $response) {
        $entityManager->remove($response);
    }

    // Delete the reclamation itself
    $entityManager->remove($reclamation);
    $entityManager->flush();

    return $this->redirectToRoute('admin_reclamation_index', [], Response::HTTP_SEE_OTHER);
}

#[Route('/reponse/new/{reclamationId}', name: 'newRepAdmin', methods: ['GET', 'POST'])]
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
        return $this->redirectToRoute('reponse_show_admin', ['reclamationId' => $reclamationId]);
    }

    // Render the form template with necessary variables
    return $this->render('admin/Reclamation/Reponse.html.twig', [
        'form' => $form->createView(),
        'reclamationId' => $reclamationId,
        'sujet' => $reclamation->getSujet()
    ]);
}

#[Route("/reponse/{reclamationId}", name:"reponse_show_admin")]
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
    return $this->render('admin/Reclamation/listeReponses.html.twig', [
        'reclamation' => $reclamation,
        'reponses' => $reponses,
        'reclamations' => $reclamationRepository->findAll(),

    ]);
}

}
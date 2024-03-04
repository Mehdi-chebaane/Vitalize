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
        return $this->render('reponse/index.html.twig', [
            'controller_name' => 'ReponseController',
            'reclamation' => $reclamationRepository->findAll(),
        ]);
    }


  

    #[Route('/reponse/new/{reclamationId}', name: 'newRep', methods: ['GET', 'POST'])]

    public function new(Request $request, $reclamationId, ReclamationRepository $reclamationRepository, MailerInterface $mailer): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
    
        // Define $SujetReclamation before the if block
        $entityManager = $this->getDoctrine()->getManager();
        $SujetReclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId)->getSujet();

    
        if ($form->isSubmitted() && $form->isValid()) {
            
    
            // Fetch the Reclamation entity by ID
            $reclamation_id = $entityManager->getRepository(Reclamation::class)->find($reclamationId);
    
            if (!$reclamation_id) {
                throw $this->createNotFoundException('Reclamation not found for id: ' . $reclamationId);
            }
    
            // Set the Reclamation for the Reponse entity
            $reponse->setReclamation($reclamation_id);
    
            $entityManager->persist($reponse);
            $entityManager->flush();
    
            // Set $SujetReclamation value
    
            $formData = $form->getData();
            $email = (new Email())
                ->from('malek.belhadjamor@gmail.com')
                ->to('testp3253@gmail.com')
                ->subject('Reponse sur votre reclamation : Sujet: ' . $SujetReclamation)
                ->text($formData->getMessage());
            $mailer->send($email);
            $this->addFlash('success', 'Reponse created successfully.');
        }
        
        return $this->render('reponse/formRep.html.twig', [
            'form' => $form->createView(),
            'reclamationId' => $reclamationId,
            'sujet'=>$SujetReclamation
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

        
}
    

    
 


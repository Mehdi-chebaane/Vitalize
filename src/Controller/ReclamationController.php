<?php
namespace App\Controller;
use Dompdf\Dompdf; 
use App\Entity\Users;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReclamationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ReclamationController extends AbstractController
{   #[Route('/reclamation', name: 'reclamation')]
    public function index(Request $request,ReclamationRepository $reclamationRepository,EntityManagerInterface $entityManager): Response
    {   $reclamation = new Reclamation();
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw $this->createAccessDeniedException('User not authenticated');
        }
        $reclamations = $reclamationRepository->findBy(['user' => $currentUser]);
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        return $this->render('reclamation/index.html.twig', [
            'controller_name' => 'ReclamationController',
            'form' => $form->createView(),
            'reclamation' => $reclamations
            
        ]);
       
    }
    #[Route('/rapport/{reclamationId}', name: 'rapport')]
public function generatePdfAction($reclamationId)
{
    // Fetch Reclamation entity
    $entityManager = $this->getDoctrine()->getManager();
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);
    $recImage = $entityManager->getRepository(Reclamation::class)->find($reclamationId)->getFile();
    if (!$reclamation) {
        throw $this->createNotFoundException('Reclamation not found');
    }
    $medecinId = $reclamation->getMedecin();
    $medecinUser = $this->getDoctrine()->getRepository(Users::class)->find($medecinId);
    $medecinName = $medecinUser->getNom();

    // Function to convert image to data URL
    function imageToDataUrl(string $filename): string
    {
        if (!file_exists($filename)) {
            throw new Exception('File not found.');
        }

        $mime = mime_content_type($filename);
        if ($mime === false) {
            throw new Exception('Illegal MIME type.');
        }

        $raw_data = file_get_contents($filename);
        if (empty($raw_data)) {
            throw new Exception('File not readable or empty.');
        }

        return "data:{$mime};base64," . base64_encode($raw_data);
    }

    // Render PDF using template with embedded image
    $html = $this->renderView('reclamation/rapport.html.twig', [
        'reclamation' => $reclamation,
        'imageDataUrl' => imageToDataUrl('uploads/' . $recImage),
        'medecinName' => $medecinName, // Include medecinName variable here
    ]);

    // Generate PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output PDF
    $pdfContent = $dompdf->output();

    return new Response($pdfContent, 200, [
        'Content-Type' => 'application/pdf',
    ]);
}




    #[Route('/showAll', name: 'app_reclamation_index', methods: ['GET'])]
    public function findAll(ReclamationRepository $reclamationRepository): Response
    {   $reclamation = new Reclamation();
        return $this->render('reclamation/showAll.html.twig', [
            'reclamation' => $reclamationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $currentUser = $this->getUser();
        $reclamation->setUser($currentUser);
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
        
       if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reclamation);
            $file = $form->get('file')->getData();
            if ($file instanceof UploadedFile) {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('upload_directory'),
                    $fileName
                );
                
                // Set the file path to the entity
                $reclamation->setFile($fileName);
            }
            $entityManager->flush();
            return $this->redirectToRoute('reclamation');
        } 
        
        return $this->render('reclamation/index.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
            
        ]);
    }

    


    #[Route('/edit/{id}', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
    if (!$reclamation) {
        throw $this->createNotFoundException('Reclamation not found');
    }

    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $file = $form->get('file')->getData();
        if ($file instanceof UploadedFile) {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move(
                $this->getParameter('upload_directory'),
                $fileName
            );
            
            // Set the file path to the entity
            $reclamation->setFile($fileName);
        }
        $entityManager->flush();
        return $this->redirectToRoute('reclamation', [], Response::HTTP_SEE_OTHER);
    } 

    return $this->render('reclamation/edit.html.twig', [
        'reclamation' => $reclamation,
        'form' => $form->createView(),
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

    return $this->redirectToRoute('reclamation', [], Response::HTTP_SEE_OTHER);
}


#[Route('/chart', name: 'reclamation_chart', methods: ['GET'])]
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
    return $this->render('reclamation/chartReclamation.html.twig', [
        'complaintPercentage' => $complaintPercentage,
        'suggestionPercentage' => $suggestionPercentage,
        'informationRequestPercentage' => $informationRequestPercentage,
        'reclamationsResolues' => $reclamationsResolues,
        'reclamationsEnAttente' => $reclamationsEnAttente,
        'reclamationsEnCours' => $reclamationsEnCours,
    ]);
}
}


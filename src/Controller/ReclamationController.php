<?php
namespace App\Controller;
use Dompdf\Dompdf; 
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;


class ReclamationController extends AbstractController
{   #[Route('/reclamation', name: 'reclamation')]
    public function index(Request $request,ReclamationRepository $reclamationRepository): Response
    {   $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
        return $this->render('reclamation/index.html.twig', [
            'controller_name' => 'ReclamationController',
            'form' => $form->createView(),
            'reclamation' => $reclamationRepository->findAll(),
        ]);
       
    }
    #[Route('/rapport/{reclamationId}', name: 'rapport')]
    public function generatePdfAction($reclamationId)
    {
        // Get EntityManager
        $entityManager = $this->getDoctrine()->getManager();

        // Fetch Reclamation entity
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);

        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found');
        }

        // Render PDF using template
        $html = $this->renderView('reclamation/rapport.html.twig', [
            'reclamation' => $reclamation,
        ]);
        
        // Generate PDF
        

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled',true);
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

    $entityManager->remove($reclamation);
    $entityManager->flush();

    return $this->redirectToRoute('reclamation', [], Response::HTTP_SEE_OTHER);
}
}

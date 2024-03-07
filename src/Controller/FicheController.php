<?php

namespace App\Controller;

use App\Entity\Fichepatient;
use App\Form\FichepatientType;
use App\Repository\FichepatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use App\Service\HealthAdvisor;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;


#[Route('/fiche')]
class FicheController extends AbstractController
{
    #[Route('/', name: 'app_fiche_index', methods: ['GET'])]
    public function index(FichepatientRepository $fichepatientRepository): Response
    {
        return $this->render('fiche/index.html.twig', [
            'fichepatients' => $fichepatientRepository->findAll(),
        ]);
    }
    private HealthAdvisor $healthAdvisor;

    public function __construct(HealthAdvisor $healthAdvisor)
    {
        $this->healthAdvisor = $healthAdvisor;
    }

    #[Route('/new', name: 'app_fiche_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
{
    $fichepatient = new Fichepatient();
    $form = $this->createForm(FichepatientType::class, $fichepatient);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($fichepatient);
        $entityManager->flush();

        // Send email notification
        $advice = $this->healthAdvisor->provideHealthAdvice($fichepatient);

        $this->sendFicheNotificationEmail($mailer, $this->getUser(), $fichepatient, $advice);


        return $this->redirectToRoute('app_fiche_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('fiche/new.html.twig', [
        'fichepatient' => $fichepatient,
        'form' => $form->createView(),
    ]);
}

private function sendFicheNotificationEmail(MailerInterface $mailer, UserInterface $currentUser, Fichepatient $fichepatient,string $advice): void
{
   
        $email = (new TemplatedEmail())
            ->from("testp3253@gmail.com") // Change this to your email address and name
            ->to($currentUser->getEmail())
            ->subject('New Fiche Created')
            ->htmlTemplate('fiche/email.html.twig') // Update the template path according to your file structure
            ->context([
                'fichepatient' => $fichepatient,
                'advice' => $advice, 
            ]);

        $mailer->send($email);
    }


    #[Route('/{id}', name: 'app_fiche_show', methods: ['GET'])]
    public function show(Fichepatient $fichepatient): Response
    {
        $attributes = $fichepatient->getAllAttributes();
        return $this->render('fiche/show.html.twig', [
            'fichepatient' => $fichepatient,
            'attributes' => $attributes,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fiche_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fichepatient $fichepatient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FichepatientType::class, $fichepatient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_fiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fiche/edit.html.twig', [
            'fichepatient' => $fichepatient,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_fiche_delete', methods: ['POST'])]
    public function delete(Request $request, Fichepatient $fichepatient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fichepatient->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fichepatient);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_fiche_index', [], Response::HTTP_SEE_OTHER);
    }
    
    /**
     * Get all attributes of the Fichepatient entity.
     *
     * @param Fichepatient $fichepatient The Fichepatient entity
     * @return array An associative array containing all attributes and their values
     */
    private function getAllAttributes(Fichepatient $fichepatient): array
    {
        return [
            'id' => $fichepatient->getId(),
            'weight' => $fichepatient->getWeight(),
            'muscle_mass' => $fichepatient->getMuscleMass(),
            'height' => $fichepatient->getHeight(),
            'allergies' => $fichepatient->getAllergies(),
            'illnesses' => $fichepatient->getIllnesses(),
            'breakfast' => $fichepatient->getBreakfast(),
            'midday' => $fichepatient->getMidday(),
            'dinner' => $fichepatient->getDinner(),
            'snacks' => $fichepatient->getSnacks(),
            'calories' => $fichepatient->getCalories(),
            'other' => $fichepatient->getOther(),
            'nom'=>$fichepatient->getNom(),
            'prenom'=>$fichepatient->getPrenom(),

        ];
    }

    /**
     * Generate PDF for the Fichepatient entity.
     *
     * @param Fichepatient $fichepatient The Fichepatient entity
     * @return Response The PDF response
     */
    private function generatePdf(Fichepatient $fichepatient): Response
    {
        $nom = $fichepatient->getNom();
    $prenom = $fichepatient->getPrenom();

        // Get all attributes of the Fichepatient entity
        $attributes = $fichepatient->getAllAttributes();
        $attributes['nom'] = $nom;
        $attributes['prenom'] = $prenom;
        // Render the PDF template with the fichepatient attributes
        $html = $this->renderView('fiche/rapport.html.twig', [
            'attributes' => $attributes,
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
    #[Route('/{id}/pdf', name: 'app_fiche_show_pdf', methods: ['GET'])]
public function showPdf(Request $request, Fichepatient $fichepatient): Response
{
    // Generate the PDF for the Fichepatient entity
    $pdfContent = $this->generatePdf($fichepatient);

    // Create a Response object with the PDF content
    $response = new Response($pdfContent);

    // Set the appropriate headers for download
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        'PatientSheet.pdf'
    ));

    return $response;
}
private function generateExcel(Fichepatient $fichepatient): Response
{
    $attributes = $this->getAllAttributes($fichepatient);
        $data = [
        array_keys($attributes), // Headers
        array_values($attributes) // Data
    ];
    $tempFilePath = tempnam(sys_get_temp_dir(), 'fichepatient') . '.xlsx';
    $writer = WriterEntityFactory::createXLSXWriter();
    $writer->openToFile($tempFilePath);
    $headerStyle = (new StyleBuilder())
        ->setFontBold()
        ->setFontColor('FFFFFFFF')
        ->setBackgroundColor('C8F0F5')
        ->setBorder((new BorderBuilder())
            ->setBorderBottom('thin')
            ->setBorderTop('thin')
            ->setBorderLeft('thin')
            ->setBorderRight('thin')
            ->build())
        ->setShouldWrapText(true)
        ->build();
    $cellStyle = (new StyleBuilder())
        ->setBorder((new BorderBuilder())
            ->setBorderBottom('thin')
            ->setBorderTop('thin')
            ->setBorderLeft('thin')
            ->setBorderRight('thin')
            ->build())
        ->build();
    foreach ($data as $index => $rowData) {
        $row = WriterEntityFactory::createRowFromArray($rowData);

        if ($index === 0) {
            $row->setStyle($headerStyle);
        } else {
            foreach ($row->getCells() as $cell) {
                $cell->setStyle($cellStyle);
            }
        }
        $writer->addRow($row);
    }

    $writer->close();
    $xlsxContent = file_get_contents($tempFilePath);

    unlink($tempFilePath);

    return new Response($xlsxContent, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="fichepatient.xlsx"',
    ]);
}

    #[Route('/{id}/excel', name: 'app_fiche_show_excel', methods: ['GET'])]
    public function showExcel(Request $request, Fichepatient $fichepatient): Response
    {
        // Generate the Excel file for the Fichepatient entity
        $xlsxContent = $this->generateExcel($fichepatient);
    
        // Return the Excel file as response
        return $xlsxContent;
    }

}
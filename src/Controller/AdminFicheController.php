<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Fichepatient;
use App\Form\FichepatientType;
use App\Repository\FichepatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Dompdf\Dompdf;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

#[Route('/adminfiche')]


class AdminFicheController extends AbstractController
{
    #[Route('/', name: 'admin_app_fiche_index', methods: ['GET'])]
    public function index(FichepatientRepository $fichepatientRepository): Response
    {
        return $this->render('admin_fiche/index.html.twig', [
            'fichepatients' => $fichepatientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_app_fiche_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fichepatient = new Fichepatient();
        $form = $this->createForm(FichepatientType::class, $fichepatient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fichepatient);
            $entityManager->flush();

            return $this->redirectToRoute('admin_app_fiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_fiche/new.html.twig', [
            'fichepatient' => $fichepatient,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_app_fiche_show', methods: ['GET'])]
    public function show(Fichepatient $fichepatient): Response
    {

        $attributes = $fichepatient->getAllAttributes();

        return $this->render('admin_fiche/show.html.twig', [
            'fichepatient' => $fichepatient,
            'attributes' => $attributes,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_app_fiche_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fichepatient $fichepatient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FichepatientType::class, $fichepatient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_app_fiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_fiche/edit.html.twig', [
            'fichepatient' => $fichepatient,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_app_fiche_delete', methods: ['POST'])]
    public function delete(Request $request, Fichepatient $fichepatient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fichepatient->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fichepatient);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_app_fiche_index', [], Response::HTTP_SEE_OTHER);
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
        // Get all attributes of the Fichepatient entity
        $attributes = $fichepatient->getAllAttributes();

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
        'Admin-PatientSheet.pdf'
    ));

    return $response;
}
private function generateExcel(Fichepatient $fichepatient): Response
{
    // Get all attributes of the Fichepatient entity
    $attributes = $this->getAllAttributes($fichepatient);
    
    // Prepare the data for the Excel file
    $data = [
        array_keys($attributes), // Headers
        array_values($attributes) // Data
    ];

    // Specify the path where the Excel file will be saved
    $tempFilePath = tempnam(sys_get_temp_dir(), 'fichepatient') . '.xlsx';

    // Create a new XLSX writer
    $writer = WriterEntityFactory::createXLSXWriter();

    // Open the Excel file for writing
    $writer->openToFile($tempFilePath);

    // Create a style for headers
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

    // Create a style for data cells
    $cellStyle = (new StyleBuilder())
        ->setBorder((new BorderBuilder())
            ->setBorderBottom('thin')
            ->setBorderTop('thin')
            ->setBorderLeft('thin')
            ->setBorderRight('thin')
            ->build())
        ->build();

    // Add rows to the Excel file
    foreach ($data as $index => $rowData) {
        // Create a new row entity from array data
        $row = WriterEntityFactory::createRowFromArray($rowData);

        // Apply header style to the first row
        if ($index === 0) {
            $row->setStyle($headerStyle);
        } else {
            // Apply cell style to data rows
            foreach ($row->getCells() as $cell) {
                $cell->setStyle($cellStyle);
            }
        }

        // Add the row entity to the Excel file
        $writer->addRow($row);
    }

    // Close the Excel file
    $writer->close();

    // Read the Excel file content
    $xlsxContent = file_get_contents($tempFilePath);

    // Remove the temporary file
    unlink($tempFilePath);

    // Create and return the response
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


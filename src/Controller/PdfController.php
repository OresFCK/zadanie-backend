<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\PersonalInfo;
use App\Entity\TextEntry;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    /**
     * @Route("/generate-pdf", name="generate_pdf", methods={"POST"})
     */
    public function generatePdf(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        $person = $em->getRepository(PersonalInfo::class)->find($data['personId']);
        $textEntry = $em->getRepository(TextEntry::class)->find($data['textEntryId']);
        
        $content = sprintf(
            "Imię: %s\nNazwisko: %s\nAdres: %s\n\n%s",
            $person->getFirstName(),
            $person->getLastName(),
            $person->getAdress(),
            $textEntry->getContent()
        );

        // Generate PDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $output = $dompdf->output();
        
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"'
        ]);
    }
}

<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PersonalInfo;
use App\Entity\TextEntry;
use Twig\Environment;

class PdfController extends AbstractController
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    #[Route('/api/generate-pdf', methods: ['POST', 'HEAD'])]
    public function generatePdf(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['personId']) || !isset($data['textEntryId'])) {
            return new Response('Invalid request data', 400);
        }

        $person = $em->getRepository(PersonalInfo::class)->find($data['personId']);
        $textEntry = $em->getRepository(TextEntry::class)->find($data['textEntryId']);

        if (!$person || !$textEntry) {
            return new Response('Person or Text Entry not found', 404);
        }

        $htmlContent = $this->twig->render('./pdf/generate_pdf.html.twig', [
            'person' => [
                'firstName' => $person->getFirstName(),
                'lastName' => $person->getLastName(),
                'address' => $person->getAdress(),
            ],
            'textEntry' => [
                'content' => $textEntry->getContent(),
            ],
        ]);

        $utf8Content = $this->ensureUtf8Encoding($htmlContent);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true); 
        $options->set('isPhpEnabled', true); 

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($utf8Content); 
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $output = $dompdf->output();
        
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"'
        ]);
    }

    /**
     * Ensure content is UTF-8 encoded
     * @param string $content
     * @return string
     */
    private function ensureUtf8Encoding(string $content): string
    {
        if (mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        $encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true);

        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        return $content;
    }
}

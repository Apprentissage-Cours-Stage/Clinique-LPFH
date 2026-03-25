<?php
require_once __DIR__ . '/../LIBRAIRIES/vendor/autoload.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService {
    private $dompdf;

    public function __construct() {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Indispensable pour charger le CSS et images locales

        $this->dompdf = new Dompdf($options);
        $this->dompdf->setPaper('A4', 'portrait');
    }

    /**
     * Génère le PDF et l'envoie au navigateur.
     */
    public function generer(string $html, string $filename = 'document.pdf', bool $download = false) {
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();

        // Le PDF est envoyé directement au navigateur du docteur
        $this->dompdf->stream($filename, [
            "Attachment" => $download
        ]);
    }

    public function genererDepuisTemplate(string $templatePath, array $data, string $filename = 'document.pdf') {
        
        if (!file_exists($templatePath)) {
            throw new Exception("Le fichier template spécifié est introuvable : " . $templatePath);
        }

        // 🟢 Démarre la capture mémoire tampon
        ob_start(); 
        
        // Rend l'array $data disponible sous forme de variable $data dans le template
        extract(['data' => $data]); 

        include $templatePath; // Exécute le fichier HTML/PHP
        
        $html = ob_get_clean(); // 🔴 Récupère le HTML généré et vide le tampon

        // Envoi vers le moteur Dompdf
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        $this->dompdf->stream($filename, ["Attachment" => false]);
    }
}
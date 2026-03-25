<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AdmissionExcelExporter {
    private $spreadsheet;
    private $sheet;
    private $serviceName;
    private $dataByDoctor;

    public function __construct(string $serviceName, array $dbResults) {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->serviceName = $serviceName;
        $this->dataByDoctor = $this->groupDataByDoctor($dbResults);
        
        $this->sheet->setTitle('Pré-admissions');
    }

    /**
     * Regroupe les données SQL par nom de médecin
     */
    private function groupDataByDoctor(array $results): array {
        $grouped = [];
        foreach ($results as $row) {
            $doctor = $row['Medecin'] ?? 'Médecin inconnu';
            $grouped[$doctor][] = $row;
        }
        return $grouped;
    }

    /**
     * Génère le fichier Excel et l'envoie au navigateur
     */
    public function renderAndDownload() {
        $this->buildHeader();
        $this->buildTableBody();
        $this->applyGlobalSettings();
        $this->sendHttpHeaders();

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function buildHeader() {
        // --- Titre Global ---
        $this->sheet->mergeCells('A1:F1');
        $this->sheet->setCellValue('A1', "PRÉ-ADMISSIONS DU SERVICE : " . mb_strtoupper($this->serviceName));
        $this->sheet->getStyle('A1')->applyFromArray($this->getHeaderStyle());
        $this->sheet->getRowDimension(1)->setRowHeight(35);

        // --- Sous-entêtes de colonnes ---
        $this->sheet->setCellValue('A2', 'Médecin Référent');
        $this->sheet->setCellValue('B2', 'Nom du Patient');
        $this->sheet->setCellValue('C2', 'Prénom');
        $this->sheet->setCellValue('D2', 'Date Hosp.');
        $this->sheet->setCellValue('E2', 'Heure');
        $this->sheet->setCellValue('F2', 'Type d\'hospitalisation');

        $this->sheet->getStyle('A2:F2')->applyFromArray($this->getSubHeaderStyle());
        $this->sheet->getRowDimension(2)->setRowHeight(25);
    }

    private function buildTableBody() {
        $currentRow = 3;

        foreach ($this->dataByDoctor as $doctor => $admissions) {
            $totalAdmissions = count($admissions);
            $startRow = $currentRow;

            // --- Écriture et fusion du médecin ---
            $this->sheet->setCellValue('A' . $currentRow, "Dr. " . $doctor);
            if ($totalAdmissions > 1) {
                $this->sheet->mergeCells("A{$startRow}:A" . ($startRow + $totalAdmissions - 1));
            }

            // Alignements verticaux de la fusion du médecin
            $this->sheet->getStyle("A{$startRow}:A" . ($startRow + $totalAdmissions - 1))
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // --- Écriture des patients rattachés ---
            foreach ($admissions as $admi) {
                $lastName = !empty($admi['Nom_Epouse']) ? $admi['Nom_Epouse'] : $admi['Nom_Naissance'];
                
                $this->sheet->setCellValue('B' . $currentRow, mb_strtoupper($lastName));
                $this->sheet->setCellValue('C' . $currentRow, $admi['Prénom_Patient']);
                $this->sheet->setCellValue('D' . $currentRow, date('d/m/Y', strtotime($admi['Date_Hospitalisation'])));
                $this->sheet->setCellValue('E' . $currentRow, substr($admi['Heure_Hospitalisation'], 0, 5));
                $this->sheet->setCellValue('F' . $currentRow, $admi['Libellé_TypeHospitalisation']);

                $currentRow++;
            }
        }

        // Application des bordures sur tout le tableau
        $this->sheet->getStyle('A2:F' . ($currentRow - 1))->applyFromArray($this->getBorderStyle());
    }

    private function applyGlobalSettings() {
        // Redimensionnement automatique de la largeur des colonnes
        foreach (range('A', 'F') as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function sendHttpHeaders() {
        $filename = "Export_Service_" . date('Y-m-d_H-i') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    }

    // ==========================================
    //   STYLES ET CHARTE GRAPHIQUE
    // ==========================================
    private function getHeaderStyle(): array {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007BFF']],
        ];
    }

    private function getSubHeaderStyle(): array {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => '005F99']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9F4FF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
    }

    private function getBorderStyle(): array {
        return [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];
    }
}
<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Genera PDFs con TCPDF cuando está instalado (composer require tecnickcom/tcpdf).
 * Si TCPDF no está disponible, devuelve un HTML formateado listo para imprimir
 * (Ctrl+P → "Guardar como PDF"), para no bloquear el flujo en entornos sin Composer.
 */
final class PdfGenerator
{
    public function available(): bool
    {
        return class_exists(\TCPDF::class);
    }

    /**
     * @return array{mime:string, filename:string, content:string}
     */
    public function render(string $title, string $html, string $filename): array
    {
        if ($this->available()) {
            return $this->renderWithTcpdf($title, $html, $filename);
        }
        return $this->renderAsHtml($title, $html, $filename);
    }

    private function renderWithTcpdf(string $title, string $html, string $filename): array
    {
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/logo.png';
        if (!file_exists($logoPath)) {
            $logoPath = dirname(__DIR__, 2) . '/public/assets/img/logo.png';
        }

        $pdf = new CadaPdf($logoPath, 'P', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('CADA');
        $pdf->SetAuthor('Club Atlético Deportivo Acarigua');
        $pdf->SetTitle($title);
        $pdf->SetMargins(12, 28, 12);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($html, true, false, true, false, '');

        $content = $pdf->Output('', 'S');
        return ['mime' => 'application/pdf', 'filename' => $filename . '.pdf', 'content' => $content];
    }

    private function renderAsHtml(string $title, string $html, string $filename): array
    {
        $full = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>
        <style>
            @media print { body { margin: 14mm; } .no-print { display: none; } }
            body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; color: #1F2937; max-width: 210mm; margin: 0 auto; padding: 20px; }
            h1, h2, h3 { color: #800020; }
            table { width: 100%; border-collapse: collapse; margin: 12px 0; }
            th, td { border: 1px solid #E5E7EB; padding: 8px; text-align: left; font-size: 13px; }
            th { background: #F9FAFB; }
            .header { border-bottom: 3px solid #800020; padding-bottom: 10px; margin-bottom: 20px; }
            .header-title { font-size: 22px; font-weight: 700; margin: 0; }
            .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
            .avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
            .badge { display:inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; background: #FEE2E2; color: #991B1B; font-weight:600; }
            .no-print { background: #800020; color: #fff; border: 0; padding: 8px 20px; border-radius: 6px; cursor: pointer; margin-bottom: 16px; }
        </style></head><body>
        <button class="no-print" onclick="window.print()">🖨️ Imprimir / Guardar como PDF</button>
        ' . $html . '
        </body></html>';
        return ['mime' => 'text/html; charset=utf-8', 'filename' => $filename . '.html', 'content' => $full];
    }
}

/**
 * Clase interna de TCPDF para manejar la cabecera y pie de página de la Ficha Técnica CADA.
 */
class CadaPdf extends \TCPDF
{
    private string $logoPath;

    public function __construct(
        string $logoPath,
        string $orientation = 'P',
        string $unit = 'mm',
        string $format = 'A4',
        bool $unicode = true,
        string $encoding = 'UTF-8',
        bool $diskcache = false,
        bool $pdfa = false
    ) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->logoPath = $logoPath;
    }

    public function Header()
    {
        if (file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 12, 8, 14, 14, 'PNG');
        }

        $this->SetY(8);
        $this->SetX(28);
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(128, 0, 32); // #800020
        $this->Cell(0, 5, 'Club Atlético Deportivo Acarigua', 0, 1, 'L');
        
        $this->SetX(28);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(85, 85, 85);
        $this->Cell(0, 4, 'Sistema de Gestión y Control Deportivo (CADA)', 0, 0, 'L');

        $this->SetY(10);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(136, 136, 136);
        $this->Cell(0, 4, date('d/m/Y - h:i A'), 0, 1, 'R');

        // Línea divisoria vino tinto
        $this->SetY(24);
        $this->SetDrawColor(128, 0, 32);
        $this->SetLineWidth(0.8);
        $this->Line(12, 24, $this->getPageWidth() - 12, 24);
    }

    public function Footer()
    {
        $this->SetY(-15);
        
        // Línea divisoria gris claro
        $this->SetDrawColor(220, 220, 220);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->GetY(), $this->getPageWidth() - 12, $this->GetY());
        
        $this->SetY($this->GetY() + 2);
        
        // Texto izquierdo
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(170, 170, 170);
        $this->Cell(0, 4, 'Generado por Sistema de Gestión CADA', 0, 0, 'L');

        // Paginación derecha
        $pageText = 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages();
        $this->Cell(0, 4, $pageText, 0, 0, 'R');
    }
}

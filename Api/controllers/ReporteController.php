<?php

class ReporteController extends Controller
{
    private ReporteModel  $reporteModel;
    private ContactoModel $contactoModel;

    private const ALLOWED_FIELDS = ['id', 'codigo_empleado', 'nombre', 'email', 'telefono', 'id_provincia', 'created_at'];

    public function __construct()
    {
        $this->reporteModel  = new ReporteModel();
        $this->contactoModel = new ContactoModel();
    }

    // GET /api/contactos/export?format=excel&orderBy=nombre&dir=asc
    public function export(): void
    {
        $format  = strtolower($_GET['format']  ?? 'excel');
        $orderBy = $_GET['orderBy'] ?? 'created_at';
        $dir     = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        if (!in_array($format,  ['excel', 'pdf'],       true)) $format  = 'excel';
        if (!in_array($orderBy, self::ALLOWED_FIELDS,   true)) $orderBy = 'created_at';

        $contactos = $this->contactoModel->getAllSorted($orderBy, $dir);
        $this->reporteModel->logExport($format, $orderBy, strtolower($dir), count($contactos));

        if ($format === 'pdf') {
            $this->exportPdf($contactos, $orderBy, strtolower($dir));
        } else {
            $this->exportExcel($contactos);
        }
    }

    // GET /api/reportes
    public function index(): void
    {
        $this->jsonResponse(Response::success($this->reporteModel->getAll()));
    }

    // PUT /api/reportes/{id}
    public function updateEstado(string $id): void
    {
        $existing = $this->reporteModel->getById((int) $id);
        if (!$existing) {
            $this->jsonResponse(Response::error('Reporte no encontrado', 404), 404);
            return;
        }

        $data   = $this->getJsonInput();
        $estado = $data['estado'] ?? '';

        if (!in_array($estado, ['pendiente', 'aprobado', 'rechazado'], true)) {
            $this->jsonResponse(
                Response::error('Estado inválido. Valores: pendiente, aprobado, rechazado', 422),
                422
            );
            return;
        }

        $this->jsonResponse(Response::success($this->reporteModel->updateEstado((int) $id, $estado)));
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function exportExcel(array $contactos): void
    {
        $filename = 'contactos_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $headers = ['ID', 'Código', 'Nombre', 'Email', 'Teléfono', 'Provincia', 'Región', 'F. Nacimiento', 'Observaciones', 'Fecha de registro'];

        $headerCells = '';
        foreach ($headers as $h) {
            $headerCells .= '<Cell ss:StyleID="header"><Data ss:Type="String">' . htmlspecialchars($h, ENT_XML1, 'UTF-8') . '</Data></Cell>';
        }

        $dataRows = '';
        foreach ($contactos as $c) {
            $dataRows .= '<Row>'
                . '<Cell><Data ss:Type="Number">'  . (int) $c['id']                                                        . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['codigo_empleado'] ?? '',      ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['nombre'],                     ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['email'],                      ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['telefono'],                   ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['nombre_provincia'] ?? '',     ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['nombre_region']    ?? '',     ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['fecha_nacimiento'] ?? '',     ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['observaciones']    ?? '',     ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">'  . htmlspecialchars($c['created_at'],                ENT_XML1, 'UTF-8') . '</Data></Cell>'
                . '</Row>';
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo <<<XML
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:x="urn:schemas-microsoft-com:office:excel">
  <Styles>
    <Style ss:ID="header">
      <Font ss:Bold="1" ss:Color="#FFFFFF"/>
      <Interior ss:Color="#0062CC" ss:Pattern="Solid"/>
    </Style>
  </Styles>
  <Worksheet ss:Name="Contactos">
    <Table>
      <Column ss:Width="40"/>
      <Column ss:Width="80"/>
      <Column ss:Width="120"/>
      <Column ss:Width="160"/>
      <Column ss:Width="100"/>
      <Column ss:Width="120"/>
      <Column ss:Width="100"/>
      <Column ss:Width="100"/>
      <Column ss:Width="180"/>
      <Column ss:Width="140"/>
      <Row>{$headerCells}</Row>
      {$dataRows}
    </Table>
  </Worksheet>
</Workbook>
XML;
    }

    private function exportPdf(array $contactos, string $orderBy, string $dir): void
    {
        header('Content-Type: text/html; charset=utf-8');

        $date  = date('d/m/Y H:i');
        $total = count($contactos);
        $rows  = '';

        foreach ($contactos as $c) {
            $rows .= '<tr>'
                . '<td>' . htmlspecialchars((string) $c['id'])            . '</td>'
                . '<td>' . htmlspecialchars($c['nombre'])                  . '</td>'
                . '<td>' . htmlspecialchars($c['email'])                   . '</td>'
                . '<td>' . htmlspecialchars($c['telefono'])                . '</td>'
                . '<td>' . htmlspecialchars($c['ciudad'] ?? '—')           . '</td>'
                . '<td>' . htmlspecialchars($c['created_at'])              . '</td>'
                . '</tr>';
        }

        $orderBy = htmlspecialchars($orderBy);
        $dir     = htmlspecialchars($dir);

        echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Contactos — {$date}</title>
  <style>
    body  { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #333; }
    h1    { font-size: 18px; margin-bottom: 4px; }
    .meta { color: #666; font-size: 11px; margin-bottom: 14px; }
    table { width: 100%; border-collapse: collapse; }
    th    { background: #0062cc; color: #fff; padding: 7px 10px; text-align: left; font-size: 11px; }
    td    { padding: 6px 10px; border-bottom: 1px solid #e0e0e0; font-size: 11px; }
    tr:nth-child(even) td { background: #f7f7f7; }
    .btn-print { margin-bottom: 14px; padding: 6px 16px; background: #0062cc; color: #fff;
                 border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
    @media print { .btn-print { display: none; } }
  </style>
</head>
<body>
  <h1>Reporte de Contactos</h1>
  <div class="meta">
    Generado el: <strong>{$date}</strong> &nbsp;|&nbsp;
    Ordenado por: <strong>{$orderBy}</strong> ({$dir}) &nbsp;|&nbsp;
    Total registros: <strong>{$total}</strong>
  </div>
  <button class="btn-print" onclick="window.print()">&#128438; Imprimir / Guardar PDF</button>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Nombre</th><th>Email</th>
        <th>Teléfono</th><th>Ciudad</th><th>Fecha de registro</th>
      </tr>
    </thead>
    <tbody>{$rows}</tbody>
  </table>
  <script>window.onload = function () { window.print(); };</script>
</body>
</html>
HTML;
    }
}

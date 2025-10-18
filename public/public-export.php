<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$pdo = getDbConnection();

// Get filter parameters (same as public.php)
$filterSector = $_GET['sector'] ?? '';
$filterMunicipality = $_GET['municipality'] ?? '';
$filterProgram = $_GET['program'] ?? '';
$filterTypeOfProgramme = $_GET['type_of_programme'] ?? '';
$filterStartYear = $_GET['start_year'] ?? '';
$filterEndYear = $_GET['end_year'] ?? '';
$filterBeneficiary = $_GET['beneficiary'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Build WHERE clause based on filters (case-insensitive)
$where = [];
$params = [];

if (!empty($filterSector)) {
    $where[] = "(UPPER(TRIM(sector_1)) = UPPER(?) OR UPPER(TRIM(sector_2)) = UPPER(?))";
    $params[] = $filterSector;
    $params[] = $filterSector;
}

if (!empty($filterMunicipality)) {
    $where[] = "UPPER(TRIM(municipality)) = UPPER(?)";
    $params[] = $filterMunicipality;
}

if (!empty($filterProgram)) {
    $where[] = "UPPER(TRIM(programme)) = UPPER(?)";
    $params[] = $filterProgram;
}

if (!empty($filterTypeOfProgramme)) {
    $where[] = "UPPER(TRIM(type_of_programme)) = UPPER(?)";
    $params[] = $filterTypeOfProgramme;
}

if (!empty($filterStartYear)) {
    $where[] = "EXTRACT(YEAR FROM start_date) = ?";
    $params[] = $filterStartYear;
}

if (!empty($filterEndYear)) {
    $where[] = "EXTRACT(YEAR FROM end_date) = ?";
    $params[] = $filterEndYear;
}

if (!empty($filterBeneficiary)) {
    $where[] = "UPPER(TRIM(contracting_party)) = UPPER(?)";
    $params[] = $filterBeneficiary;
}

if (!empty($filterStatus)) {
    if ($filterStatus === 'ongoing') {
        $where[] = "(end_date IS NULL OR end_date >= CURRENT_DATE)";
    } elseif ($filterStatus === 'completed') {
        $where[] = "end_date < CURRENT_DATE";
    }
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get all projects matching the filters (no pagination limit for export)
$sql = "SELECT * FROM projects $whereClause ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Create new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator('EU Projects in Montenegro')
    ->setTitle('EU Projects Export')
    ->setSubject('EU Projects Data')
    ->setDescription('Exported EU Projects data from public dashboard');

// Define headers
$headers = [
    'A' => 'ID',
    'B' => 'Contract Title',
    'C' => 'Financial Framework',
    'D' => 'Programme',
    'E' => 'Type of Programme',
    'F' => 'Sector 1',
    'G' => 'Sector 2',
    'H' => 'Contract Type',
    'I' => 'Commitment Year',
    'J' => 'Contract Year',
    'K' => 'Start Date',
    'L' => 'End Date',
    'M' => 'Contracting Party',
    'N' => 'Contracted EU Contribution',
    'O' => 'EU Contribution MNE',
    'P' => 'EU Contribution Overall',
    'Q' => 'Total EURO Value',
    'R' => 'Municipality',
    'S' => 'Short Description',
    'T' => 'Keywords',
    'U' => 'Project Link',
    'V' => 'Status'
];

// Set headers
foreach ($headers as $col => $header) {
    $sheet->setCellValue($col . '1', $header);
}

// Style header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '003399']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
];

$sheet->getStyle('A1:V1')->applyFromArray($headerStyle);

// Auto-size columns
foreach (range('A', 'V') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Fill data
$row = 2;
foreach ($projects as $project) {
    // Determine status
    $status = 'Ongoing';
    if ($project['end_date']) {
        $endDate = strtotime($project['end_date']);
        if ($endDate < time()) {
            $status = 'Completed';
        }
    }
    
    $sheet->setCellValue('A' . $row, $project['id']);
    $sheet->setCellValue('B' . $row, $project['contract_title']);
    $sheet->setCellValue('C' . $row, $project['financial_framework']);
    $sheet->setCellValue('D' . $row, $project['programme']);
    $sheet->setCellValue('E' . $row, $project['type_of_programme']);
    $sheet->setCellValue('F' . $row, $project['sector_1']);
    $sheet->setCellValue('G' . $row, $project['sector_2']);
    $sheet->setCellValue('H' . $row, $project['contract_type']);
    $sheet->setCellValue('I' . $row, $project['commitment_year']);
    $sheet->setCellValue('J' . $row, $project['contract_year']);
    $sheet->setCellValue('K' . $row, $project['start_date'] ? date('Y-m-d', strtotime($project['start_date'])) : '');
    $sheet->setCellValue('L' . $row, $project['end_date'] ? date('Y-m-d', strtotime($project['end_date'])) : '');
    $sheet->setCellValue('M' . $row, $project['contracting_party']);
    $sheet->setCellValue('N' . $row, $project['contracted_eu_contribution']);
    $sheet->setCellValue('O' . $row, $project['eu_contribution_mne']);
    $sheet->setCellValue('P' . $row, $project['eu_contribution_overall']);
    $sheet->setCellValue('Q' . $row, $project['total_euro_value']);
    $sheet->setCellValue('R' . $row, $project['municipality']);
    $sheet->setCellValue('S' . $row, $project['short_description']);
    $sheet->setCellValue('T' . $row, $project['keywords']);
    $sheet->setCellValue('U' . $row, $project['project_link']);
    $sheet->setCellValue('V' . $row, $status);
    
    $row++;
}

// Freeze header row
$sheet->freezePane('A2');

// Generate filename with timestamp and filter info
$filename = 'EU_Projects_Export_' . date('Y-m-d_His');
if (!empty($filterSector)) {
    $filename .= '_' . preg_replace('/[^A-Za-z0-9_-]/', '', substr($filterSector, 0, 20));
}
if (!empty($filterMunicipality)) {
    $filename .= '_' . preg_replace('/[^A-Za-z0-9_-]/', '', substr($filterMunicipality, 0, 20));
}
$filename .= '.xlsx';

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Write file to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

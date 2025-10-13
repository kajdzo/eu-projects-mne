<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../vendor/autoload.php';

requireAdmin();

use PhpOffice\PhpSpreadsheet\IOFactory;

$message = '';
$error = '';
$stats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $pdo = getDbConnection();
            
            $totalImported = 0;
            $totalSkipped = 0;
            $sheets = [];
            
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                $sheetImported = 0;
                $sheetSkipped = 0;
                
                $rows = $sheet->toArray();
                
                if (empty($rows)) {
                    continue;
                }
                
                // Get header row
                $headers = array_map('trim', $rows[0]);
                
                // Process data rows
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    // Map columns to database fields
                    $data = [];
                    foreach ($headers as $index => $header) {
                        $value = $row[$index] ?? '';
                        
                        // Map header to database column
                        switch (trim($header)) {
                            case 'Financial framework':
                                $data['financial_framework'] = $value;
                                break;
                            case 'Programme':
                                $data['programme'] = $value;
                                break;
                            case 'Type of programme':
                                $data['type_of_programme'] = $value;
                                break;
                            case 'Management mode':
                                $data['management_mode'] = $value;
                                break;
                            case 'Sector 1':
                                $data['sector_1'] = $value;
                                break;
                            case 'Sector 2':
                                $data['sector_2'] = $value;
                                break;
                            case 'Contract title':
                                $data['contract_title'] = $value;
                                break;
                            case 'Contract type':
                                $data['contract_type'] = $value;
                                break;
                            case 'Commitment year':
                                $data['commitment_year'] = $value;
                                break;
                            case 'Contract year':
                                $data['contract_year'] = $value;
                                break;
                            case 'Start date':
                                $data['start_date'] = parseDate($value);
                                break;
                            case 'End date':
                                $data['end_date'] = parseDate($value);
                                break;
                            case 'Contract number':
                                $data['contract_number'] = $value;
                                break;
                            case 'Contracting party':
                                $data['contracting_party'] = $value;
                                break;
                            case 'Decision number':
                                $data['decision_number'] = $value;
                                break;
                            case 'Contracted EU contribution':
                            case 'Contracted EU contribution  ':
                                $data['contracted_eu_contribution'] = parseDecimal($value);
                                break;
                            case 'EU contribution for MNE':
                                $data['eu_contribution_mne'] = parseDecimal($value);
                                break;
                            case 'EU contribution overall':
                                $data['eu_contribution_overall'] = parseDecimal($value);
                                break;
                            case 'Total EURO value':
                            case 'Total EURO value ':
                                $data['total_euro_value'] = parseDecimal($value);
                                break;
                            case 'Municipality':
                                $data['municipality'] = $value;
                                break;
                            case 'Short description':
                                $data['short_description'] = $value;
                                break;
                            case 'Keywords':
                                $data['keywords'] = $value;
                                break;
                            case 'Links to project page':
                                $data['project_link'] = $value;
                                break;
                        }
                    }
                    
                    try {
                        // Insert project
                        $stmt = $pdo->prepare("INSERT INTO projects (
                            financial_framework, programme, type_of_programme, management_mode,
                            sector_1, sector_2, contract_title, contract_type,
                            commitment_year, contract_year, start_date, end_date,
                            contract_number, contracting_party, decision_number,
                            contracted_eu_contribution, eu_contribution_mne, eu_contribution_overall, total_euro_value,
                            municipality, short_description, keywords, project_link
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        
                        $stmt->execute([
                            $data['financial_framework'] ?? null,
                            $data['programme'] ?? null,
                            $data['type_of_programme'] ?? null,
                            $data['management_mode'] ?? null,
                            $data['sector_1'] ?? null,
                            $data['sector_2'] ?? null,
                            $data['contract_title'] ?? null,
                            $data['contract_type'] ?? null,
                            $data['commitment_year'] ?? null,
                            $data['contract_year'] ?? null,
                            $data['start_date'] ?? null,
                            $data['end_date'] ?? null,
                            $data['contract_number'] ?? null,
                            $data['contracting_party'] ?? null,
                            $data['decision_number'] ?? null,
                            $data['contracted_eu_contribution'] ?? null,
                            $data['eu_contribution_mne'] ?? null,
                            $data['eu_contribution_overall'] ?? null,
                            $data['total_euro_value'] ?? null,
                            $data['municipality'] ?? null,
                            $data['short_description'] ?? null,
                            $data['keywords'] ?? null,
                            $data['project_link'] ?? null
                        ]);
                        
                        $sheetImported++;
                        $totalImported++;
                    } catch (Exception $e) {
                        $sheetSkipped++;
                        $totalSkipped++;
                    }
                }
                
                $sheets[] = [
                    'name' => $sheetName,
                    'imported' => $sheetImported,
                    'skipped' => $sheetSkipped
                ];
            }
            
            $stats = [
                'total_imported' => $totalImported,
                'total_skipped' => $totalSkipped,
                'sheets' => $sheets
            ];
            
            $message = "Import completed! Total projects imported: $totalImported";
            
        } catch (Exception $e) {
            $error = "Error reading Excel file: " . $e->getMessage();
        }
    } else {
        $error = "Error uploading file. Please try again.";
    }
}

// Helper function to parse dates
function parseDate($value) {
    if (empty($value)) {
        return null;
    }
    
    // Try to parse common date formats
    $formats = ['d M Y', 'Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y'];
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, trim($value));
        if ($date) {
            return $date->format('Y-m-d');
        }
    }
    
    // Try strtotime as fallback
    $timestamp = strtotime($value);
    if ($timestamp) {
        return date('Y-m-d', $timestamp);
    }
    
    return null;
}

// Helper function to parse decimal numbers
function parseDecimal($value) {
    if (empty($value)) {
        return null;
    }
    
    // Remove spaces and convert common separators
    $value = str_replace(' ', '', $value);
    $value = str_replace(',', '.', $value);
    
    // Remove any non-numeric characters except dots
    $value = preg_replace('/[^0-9.]/', '', $value);
    
    return $value ? floatval($value) : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Projects - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>Import Projects from Excel</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($message) ?>
                    <br><br>
                    <a href="/projects.php" class="btn btn-primary">View Projects</a>
                </div>
                
                <?php if (!empty($stats['sheets'])): ?>
                    <h3>Import Details:</h3>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Sheet Name</th>
                                <th>Imported</th>
                                <th>Skipped</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['sheets'] as $sheet): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sheet['name']) ?></td>
                                    <td><?= $sheet['imported'] ?></td>
                                    <td><?= $sheet['skipped'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="import-form">
                <h3>Upload Excel File</h3>
                <p>Upload an Excel file (.xlsx or .xls) containing project data. The file can have multiple sheets, and all will be imported.</p>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="excel_file">Select Excel File:</label>
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Import Projects</button>
                        <a href="/projects.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            
            <div class="import-info">
                <h3>Expected Excel Format:</h3>
                <p>The Excel file should have columns with these headers:</p>
                <ul>
                    <li>Financial framework</li>
                    <li>Programme</li>
                    <li>Type of programme</li>
                    <li>Management mode</li>
                    <li>Sector 1, Sector 2</li>
                    <li>Contract title, Contract type, Contract year, Contract number</li>
                    <li>Start date, End date (formats: 1 Jan 2023, 2023-01-01, etc.)</li>
                    <li>Contracting party, Decision number</li>
                    <li>Contracted EU contribution, EU contribution for MNE, EU contribution overall, Total EURO value</li>
                    <li>Municipality</li>
                    <li>Short description, Keywords</li>
                    <li>Links to project page</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>

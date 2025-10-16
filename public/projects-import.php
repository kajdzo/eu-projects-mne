<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../vendor/autoload.php';

requireAdmin();

use PhpOffice\PhpSpreadsheet\IOFactory;

// Note: Upload limits (50MB) are configured in the workflow command via PHP -d flags

$message = '';
$error = '';
$stats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $error = $uploadErrors[$file['error']] ?? 'Unknown upload error';
    } elseif ($file['error'] === UPLOAD_ERR_OK) {
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
                
                // Get header row - normalize headers by removing special spaces and trimming
                $headers = array_map(function($header) {
                    // Handle null/empty headers
                    if ($header === null || $header === '') {
                        return '';
                    }
                    // Replace non-breaking spaces with regular spaces
                    $header = str_replace("\xC2\xA0", ' ', $header);
                    // Trim regular spaces
                    return trim($header);
                }, $rows[0]);
                
                // Process data rows
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    // Skip empty rows (including rows with only whitespace or formatting)
                    $rowData = array_filter(array_map(function($cell) {
                        return is_string($cell) ? trim($cell) : $cell;
                    }, $row));
                    
                    if (empty($rowData)) {
                        continue;
                    }
                    
                    // Map columns to database fields
                    $data = [];
                    foreach ($headers as $index => $header) {
                        $value = $row[$index] ?? '';
                        
                        // Normalize header for comparison
                        if ($header === null || $header === '') {
                            continue;
                        }
                        $normalizedHeader = str_replace("\xC2\xA0", ' ', trim($header));
                        
                        // Helper to trim string values
                        $trimmedValue = is_string($value) ? trim($value) : $value;
                        
                        // Map header to database column
                        switch ($normalizedHeader) {
                            case 'Financial framework':
                            case 'Assistance framework':
                                $data['financial_framework'] = $trimmedValue;
                                break;
                            case 'Programme':
                                $data['programme'] = $trimmedValue;
                                break;
                            case 'Type of programme':
                                $data['type_of_programme'] = $trimmedValue;
                                break;
                            case 'Management mode':
                                $data['management_mode'] = $trimmedValue;
                                break;
                            case 'Sector 1':
                                $data['sector_1'] = $trimmedValue;
                                break;
                            case 'Sector 2':
                                $data['sector_2'] = $trimmedValue;
                                break;
                            case 'Contract title':
                                $data['contract_title'] = $trimmedValue;
                                break;
                            case 'Contract type':
                                $data['contract_type'] = $trimmedValue;
                                break;
                            case 'Commitment year':
                                $data['commitment_year'] = $trimmedValue;
                                break;
                            case 'Contract year':
                                $data['contract_year'] = $trimmedValue;
                                break;
                            case 'Start date':
                                $data['start_date'] = parseDate($value);
                                break;
                            case 'End date':
                                $data['end_date'] = parseDate($value);
                                break;
                            case 'Contract number':
                                $data['contract_number'] = $trimmedValue;
                                break;
                            case 'Contracting party':
                                $data['contracting_party'] = $trimmedValue;
                                break;
                            case 'Decision number':
                                $data['decision_number'] = $trimmedValue;
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
                                $data['municipality'] = $trimmedValue;
                                break;
                            case 'Short description':
                                $data['short_description'] = $trimmedValue;
                                break;
                            case 'Keywords':
                                $data['keywords'] = $trimmedValue;
                                break;
                            case 'Links to project page':
                                $data['project_link'] = $trimmedValue;
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

                //break;
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
    }
}

// Helper function to parse dates
function parseDate($value) {
    if (empty($value)) {
        return null;
    }
    
    // Check if it's an Excel serial date (numeric)
    if (is_numeric($value)) {
        try {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }
    
    // Try to parse common date formats
    $formats = ['d/m/Y', 'd M Y', 'Y-m-d', 'm/d/Y', 'd-m-Y'];
    
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
                <p><strong>Maximum file size: 50 MB</strong></p>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="excel_file">Select Excel File:</label>
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><span class="btn-text">Import Projects</span></button>
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
    
    <script>
        // Add spinner to button on form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('excel_file');
            if (fileInput.files.length > 0) {
                const btn = this.querySelector('button[type="submit"]');
                btn.classList.add('loading');
            }
        });
    </script>
</body>
</html>

# Excel Import & Export Guide

How to add Excel import/export to the PHP boilerplate using **PhpSpreadsheet**.

## Setup (2 minutes)

### 1. Install Composer & Package

```bash
cd c:\laragon\www\php-boilerplate
composer init --no-interaction
composer require phpoffice/phpspreadsheet
```

### 2. Add Autoloader

In `public/index.php`, add this line **at the top** (before session_start):

```php
// Add Composer autoloader (only if vendor/ exists)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
```

---

## Full Working Example: User Import & Export

### Step 1: Add Routes

Add to `routes.php`:

```php
// Excel import/export
Router::get('/users/export/excel',  [UserController::class, 'export']);
Router::post('/users/import/excel', [UserController::class, 'import']);
```

> **Important:** Place these BEFORE the `/users/{id}` routes so they don't get caught by the `{id}` parameter.

### Step 2: Add Controller Methods

Add these methods to `app/Controllers/UserController.php`:

```php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

// ... inside UserController class:

/**
 * GET /users/export/excel
 * Export all users to .xlsx file
 */
public function export(): void
{
    $users = $this->user->all();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Users');

    // Header row
    $headers = ['ID', 'Name', 'Email', 'Phone', 'Created At'];
    foreach ($headers as $col => $header) {
        $sheet->setCellValue([$col + 1, 1], $header);
    }

    // Style header row (bold)
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);

    // Data rows
    $row = 2;
    foreach ($users as $user) {
        $sheet->setCellValue([1, $row], $user['id']);
        $sheet->setCellValue([2, $row], $user['name']);
        $sheet->setCellValue([3, $row], $user['email']);
        $sheet->setCellValue([4, $row], $user['phone'] ?? '');
        $sheet->setCellValue([5, $row], $user['created_at']);
        $row++;
    }

    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Download
    $filename = 'users_' . date('Y-m-d_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/**
 * POST /users/import/excel
 * Import users from uploaded .xlsx/.xls/.csv file
 */
public function import(): void
{
    // Validate file upload
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        flash('message', 'Please select a valid file');
        flash('message_type', 'error');
        redirect(baseUrl('/users'));
    }

    $file = $_FILES['file'];

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
        flash('message', 'Only .xlsx, .xls, .csv files are allowed');
        flash('message_type', 'error');
        redirect(baseUrl('/users'));
    }

    try {
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Remove header row
        $header = array_shift($rows);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2; // +2 because row 1 is header, array is 0-indexed

            // Map columns: Name, Email, Phone (skip ID and Created At)
            $name  = trim($row[1] ?? $row[0] ?? '');
            $email = trim($row[2] ?? $row[1] ?? '');
            $phone = trim($row[3] ?? $row[2] ?? '');

            // Skip empty rows
            if (empty($name) || empty($email)) {
                $skipped++;
                continue;
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$rowNum}: Invalid email '{$email}'";
                $skipped++;
                continue;
            }

            // Check if email already exists
            $existing = $this->user->findBy('email', $email);
            if ($existing) {
                $errors[] = "Row {$rowNum}: Email '{$email}' already exists";
                $skipped++;
                continue;
            }

            // Insert user
            $this->user->create([
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone ?: null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $imported++;
        }

        $message = "Imported {$imported} users.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} rows.";
        }
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode('; ', array_slice($errors, 0, 5));
        }

        flash('message', $message);
        flash('message_type', $imported > 0 ? 'success' : 'error');

    } catch (\Exception $e) {
        flash('message', 'Failed to read file: ' . $e->getMessage());
        flash('message_type', 'error');
    }

    redirect(baseUrl('/users'));
}
```

### Step 3: Add Import/Export Buttons to View

Add this to `app/Views/users/index.php`, in the header area next to the "Add User" button:

```php
<!-- Export Button -->
<a href="<?= baseUrl('/users/export/excel') ?>"
   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
    Export Excel
</a>

<!-- Import Button (with hidden file input) -->
<div x-data="{ importing: false }" class="inline-block">
    <form action="<?= baseUrl('/users/import/excel') ?>" method="POST"
          enctype="multipart/form-data" class="inline-flex items-center gap-2">
        <?= csrfField() ?>
        <label class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm cursor-pointer">
            Choose File
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden"
                   @change="importing = true; $el.closest('form').submit()">
        </label>
        <span x-show="importing" class="text-sm text-gray-500">Importing...</span>
    </form>
</div>
```

---

## Expected Excel Format for Import

The importer expects this column order (matching the export format):

| ID | Name | Email | Phone | Created At |
|----|------|-------|-------|------------|
| (skip) | John Doe | john@example.com | 1234567890 | (skip) |
| (skip) | Jane Smith | jane@example.com | | (skip) |

- **ID** and **Created At** columns are ignored during import
- **Name** and **Email** are required
- **Phone** is optional
- Empty rows are skipped
- Duplicate emails are skipped with an error message

---

## CSV Export (Without PhpSpreadsheet)

If you don't want to install Composer at all, CSV export works with pure PHP:

```php
public function exportCsv(): void
{
    $users = $this->user->all();

    $filename = 'users_' . date('Y-m-d_His') . '.csv';

    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");

    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Created At']);

    // Data rows
    foreach ($users as $user) {
        fputcsv($output, [
            $user['id'],
            $user['name'],
            $user['email'],
            $user['phone'] ?? '',
            $user['created_at'],
        ]);
    }

    fclose($output);
    exit;
}
```

---

## CSV Import (Without PhpSpreadsheet)

```php
public function importCsv(): void
{
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        flash('message', 'Please select a valid file');
        flash('message_type', 'error');
        redirect(baseUrl('/users'));
    }

    $handle = fopen($_FILES['file']['tmp_name'], 'r');
    $header = fgetcsv($handle); // skip header row

    $imported = 0;
    while (($row = fgetcsv($handle)) !== false) {
        $name  = trim($row[1] ?? '');
        $email = trim($row[2] ?? '');
        $phone = trim($row[3] ?? '');

        if (empty($name) || empty($email)) continue;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
        if ($this->user->findBy('email', $email)) continue;

        $this->user->create([
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $imported++;
    }
    fclose($handle);

    flash('message', "Imported {$imported} users");
    flash('message_type', 'success');
    redirect(baseUrl('/users'));
}
```

---

## API Endpoint for Import (JSON response)

If you need an API version for the import:

```php
// In routes.php
Router::post('/api/users/import', [UserApiController::class, 'import']);

// In UserApiController
public function import(): void
{
    if (empty($_FILES['file'])) {
        $this->error('No file uploaded', 400);
    }

    $spreadsheet = IOFactory::load($_FILES['file']['tmp_name']);
    $rows = $spreadsheet->getActiveSheet()->toArray();
    array_shift($rows); // remove header

    $imported = 0;
    $errors = [];

    foreach ($rows as $i => $row) {
        $name  = trim($row[1] ?? '');
        $email = trim($row[2] ?? '');

        if (empty($name) || empty($email)) continue;

        try {
            (new User())->create([
                'name'  => $name,
                'email' => $email,
                'phone' => trim($row[3] ?? '') ?: null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $imported++;
        } catch (\Exception $e) {
            $errors[] = "Row " . ($i + 2) . ": " . $e->getMessage();
        }
    }

    $this->success([
        'imported' => $imported,
        'errors'   => $errors,
    ], "Imported {$imported} users");
}
```

Test with curl:

```bash
curl -X POST http://localhost:8000/api/users/import \
  -H "Authorization: Bearer your-token" \
  -F "file=@users.xlsx"
```

---

## Quick Reference

| Task | With PhpSpreadsheet | Without (CSV only) |
|------|--------------------|--------------------|
| Export .xlsx | `Xlsx` writer | Not possible |
| Export .csv | `Csv` writer | `fputcsv()` |
| Import .xlsx | `IOFactory::load()` | Not possible |
| Import .csv | `IOFactory::load()` | `fgetcsv()` |
| Styling (bold, colors) | Yes | No |
| Multiple sheets | Yes | No |
| Formulas | Yes | No |

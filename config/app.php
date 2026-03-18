<?php

/**
 * Load .env file into $_ENV and getenv()
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove surrounding quotes
        if (preg_match('/^"(.*)"$/', $value, $m)) {
            $value = $m[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $m)) {
            $value = $m[1];
        }

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

/**
 * Get env variable with optional default
 */
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

/**
 * Get base URL
 */
function baseUrl(string $path = ''): string
{
    $base = rtrim(env('APP_URL', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Redirect to a URL
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Dump and die
 */
function dd(mixed ...$vars): void
{
    echo '<pre>';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    exit;
}

/**
 * Escape HTML output
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get old input value (from session flash)
 */
function old(string $key, string $default = ''): string
{
    return $_SESSION['_old_input'][$key] ?? $default;
}

/**
 * Get flash message
 */
function flash(string $key, mixed $value = null): mixed
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

/**
 * CSRF token helpers
 */
function csrfToken(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf(): bool
{
    $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals(csrfToken(), $token);
}

/**
 * Handle file upload
 *
 * @param string $field    - Form field name (e.g., 'image', 'avatar')
 * @param string $folder   - Subfolder inside public/uploads/ (e.g., 'users', 'posts')
 * @param array  $allowed  - Allowed extensions
 * @param int    $maxSize  - Max file size in bytes (default 5MB)
 * @return string|null     - Relative path to saved file, or null on failure
 */
function uploadFile(
    string $field,
    string $folder = '',
    array $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    int $maxSize = 5 * 1024 * 1024
): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$field];

    // Validate size
    if ($file['size'] > $maxSize) {
        return null;
    }

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return null;
    }

    // Validate MIME type (security: don't trust extension alone)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'mp4'  => 'video/mp4',
    ];

    if (isset($allowedMimes[$ext]) && $mime !== $allowedMimes[$ext]) {
        return null;
    }

    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $ext;

    // Create folder if needed
    $uploadDir = __DIR__ . '/../public/uploads/' . ($folder ? $folder . '/' : '');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Move file
    $destination = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    // Return relative path (for storing in DB and displaying in views)
    return 'uploads/' . ($folder ? $folder . '/' : '') . $filename;
}

/**
 * Delete an uploaded file
 */
function deleteFile(?string $path): void
{
    if (!$path) return;
    $fullPath = __DIR__ . '/../public/' . $path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

<?php

class Controller
{
    /**
     * Render a view with layout
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);

        // Render the view content into $content variable
        ob_start();
        $viewPath = __DIR__ . '/../app/Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "<p>View not found: $view</p>";
        }
        $content = ob_get_clean();

        // Render layout with $content
        $layoutPath = __DIR__ . '/../app/Views/layouts/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Return JSON response
     */
    protected function json(mixed $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    /**
     * Redirect with optional flash message
     */
    protected function redirect(string $url, string $flashMsg = '', string $flashType = 'success'): void
    {
        if ($flashMsg) {
            flash('message', $flashMsg);
            flash('message_type', $flashType);
        }
        redirect(baseUrl($url));
    }

    /**
     * Redirect back with old input and errors
     */
    protected function back(array $errors = []): void
    {
        $_SESSION['_old_input'] = $_POST;
        if ($errors) {
            $_SESSION['_flash']['errors'] = $errors;
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? baseUrl('/');
        header("Location: $referer");
        exit;
    }
}

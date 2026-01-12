<?php

/**
 * DocsController – Handles all static & semi-static documentation pages
 * for the HubIT SaaS platform.
 */
class DocsController {
    
    /**
     * Renders the Step-by-Step Monitoring Dashboard Setup Guide
     */
    public function setupGuide() {
        $this->renderView('setup-guide.php');
    }

    /**
     * Renders the User Manual (example)
     */
    public function userManual() {
        $this->renderView('user-manual.html');
    }

    /**
     * Renders API Reference (example)
     */
    public function apiReference() {
        $this->renderView('api-reference.html');
    }

    // ----------------------------
    // PRIVATE HELPER
    // ----------------------------

    /**
     * Safely render a static HTML view from app/views/docs/
     * 
     * @param string $filename e.g. 'setup-guide.html'
     */
    private function renderView(string $filename): void {
        $viewPath = __DIR__ . '/../views/docs/' . $filename;

        if (!file_exists($viewPath)) {
            error_log("Documentation view not found: $viewPath");
            http_response_code(404);
            exit('Documentation page not found.');
        }

        header('Content-Type: text/html; charset=utf-8');
        readfile($viewPath);
        exit;
    }
}
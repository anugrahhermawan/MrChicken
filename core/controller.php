<?php
// core/Controller.php

class Controller {
    public function view(string $view, array $data = []): void {
        if (!empty($data)) {
            extract($data);
        }
        
        $viewFile = 'views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View halaman <b>{$view}</b> tidak ditemukan.");
        }
    }
}
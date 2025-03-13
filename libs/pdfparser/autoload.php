<?php
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/src/'; // Base directory for the PDFParser library

    // Map namespaces to file paths
    $classPath = str_replace('\\', '/', $class); // Convert namespace to file path
    $file = $baseDir . $classPath . '.php';

    if (file_exists($file)) {
        require_once $file; // Load the file if it exists
    } else {
        die("Autoloader failed to load: {$file}");
    }
});

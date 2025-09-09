<?php
// Simple migration runner for files in migrations/
// Usage: php run_migrations.php

require_once __DIR__ . '/config/bootstrap.php';

function println($msg) { echo $msg . PHP_EOL; }

try {
    // Ensure migrations table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Gather migration files
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';
    if (!is_dir($dir)) {
        throw new RuntimeException("Le dossier migrations est introuvable: $dir");
    }

    $files = array_values(array_filter(scandir($dir), function ($f) use ($dir) {
        return is_file($dir . DIRECTORY_SEPARATOR . $f) && preg_match('/\.sql$/i', $f);
    }));
    sort($files, SORT_NATURAL | SORT_FLAG_CASE);

    // Fetch already applied
    $applied = $pdo->query('SELECT filename FROM migrations')->fetchAll(PDO::FETCH_COLUMN) ?: [];
    $appliedSet = array_flip($applied);

    if (empty($files)) {
        println('Aucune migration à exécuter.');
        exit(0);
    }

    $executed = 0;
    foreach ($files as $file) {
        if (isset($appliedSet[$file])) {
            println("✅ Déjà appliquée: $file");
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $file;
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException("Impossible de lire: $path");
        }

        println("➡️  Exécution: $file ...");
        // Execute raw SQL (may contain multiple statements)
        $pdo->exec($sql);

        $stmt = $pdo->prepare('INSERT INTO migrations (filename) VALUES (:filename)');
        $stmt->execute([':filename' => $file]);
        println("✅ Appliquée: $file");
        $executed++;
    }

    println("Terminé. Migrations exécutées: $executed");
    exit(0);
} catch (Throwable $e) {
    println('❌ Erreur: ' . $e->getMessage());
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        println($e->getTraceAsString());
    }
    exit(1);
}
?>



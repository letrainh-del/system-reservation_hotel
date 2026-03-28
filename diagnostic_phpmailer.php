<?php
/**
 * Diagnostic - Vérifier si PHPMailer est installé correctement
 */

echo "<pre style='background:#111;color:#d4a72c;padding:20px;font-family:monospace;border-radius:8px;'>";
echo "=== DIAGNOSTIC PHPMAILER ===\n\n";

$vendor_path = __DIR__ . '/vendor/phpmailer/phpmailer';
$src_path = $vendor_path . '/src';

echo "🔍 Vérification des fichiers PHPMailer:\n";

$files = [
    'PHPMailer.php',
    'SMTP.php',
    'Exception.php',
    'autoload.php'
];

foreach ($files as $file) {
    $full_path = $src_path . '/' . $file;
    if (file_exists($full_path)) {
        echo "   ✅ $file\n";
    } else {
        echo "   ❌ $file - MANQUANT\n";
    }
}

// Vérifier composer.json
$composer_json = $vendor_path . '/composer.json';
if (file_exists($composer_json)) {
    echo "\n✅ composer.json trouvé\n";
} else {
    echo "\n❌ composer.json MANQUANT\n";
}

// Lister le contenu du dossier src
echo "\n📁 Contenu de $src_path:\n";
if (is_dir($src_path)) {
    $files = scandir($src_path);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "   - $file\n";
        }
    }
} else {
    echo "   ❌ Dossier src introuvable!\n";
}

echo "\n✅ Si tout est vert, le problème vient de la config Gmail\n";
echo "   - Vérifie que 'Less secure app access' est activé sur Gmail\n";
echo "   - Essaye avec une autre adresse email de test\n";
echo "</pre>";
?>

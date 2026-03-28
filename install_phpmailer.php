<?php
/**
 * Installeur PHPMailer - Télécharge et installe PHPMailer automatiquement
 */

echo "<pre style='background:#111;color:#d4a72c;padding:20px;font-family:monospace;border-radius:8px;'>";
echo "=== INSTALLATION PHPMAILER ===\n\n";

$vendor_path = __DIR__ . '/vendor';
$phpmailer_path = $vendor_path . '/phpmailer/phpmailer';

// Vérifier si déjà installé
if (file_exists($phpmailer_path . '/src/PHPMailer.php')) {
    echo "✅ PHPMailer déjà installé!\n\n";
    echo "Chemin: $phpmailer_path\n";
    echo "✅ Prêt à utiliser!\n";
    exit;
}

echo "📦 Téléchargement PHPMailer...\n\n";

// Créer les dossiers
if (!is_dir($vendor_path)) {
    mkdir($vendor_path, 0777, true);
    echo "✅ Dossier vendor créé\n";
}

// Télécharger PHPMailer
$zip_url = 'https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.8.0.zip';
$zip_file = $vendor_path . '/phpmailer.zip';
$extract_path = $vendor_path;

echo "⏳ Téléchargement du fichier ZIP...\n";

// Télécharger
$zip_content = @file_get_contents($zip_url);

if ($zip_content === false) {
    echo "❌ ERREUR: Impossible de télécharger PHPMailer\n\n";
    echo "Solutions alternatives:\n";
    echo "1. Installer Composer: https://getcomposer.org/\n";
    echo "2. Puis exécuter: composer require phpmailer/phpmailer\n";
    echo "3. Ou télécharger manuellement: https://github.com/PHPMailer/PHPMailer/releases\n";
    exit;
}

file_put_contents($zip_file, $zip_content);
echo "✅ ZIP téléchargé (" . round(strlen($zip_content) / 1024 / 1024, 2) . " MB)\n";

// Extraire
echo "⏳ Extraction...\n";

$zip = new ZipArchive;
if ($zip->open($zip_file) === true) {
    $zip->extractTo($extract_path);
    $zip->close();
    
    // Renommer le dossier
    $extracted_folder = $extract_path . '/PHPMailer-6.8.0';
    if (is_dir($extracted_folder)) {
        // Créer le dossier phpmailer s'il n'existe pas
        $phpmailer_dir = $extract_path . '/phpmailer';
        if (!is_dir($phpmailer_dir)) {
            mkdir($phpmailer_dir, 0777, true);
        }
        
        // Renommer
        rename($extracted_folder, $phpmailer_path);
    }
    
    unlink($zip_file);
    echo "✅ Extraction complète\n\n";
} else {
    echo "❌ Impossible d'extraire le ZIP\n";
    exit;
}

// Vérifier l'installation
if (file_exists($phpmailer_path . '/src/PHPMailer.php')) {
    echo "✅ PHPMailer installé avec succès!\n\n";
    echo "Chemin: $phpmailer_path\n";
    echo "📝 Prêt à utiliser\n";
} else {
    echo "❌ ERREUR: Problème lors de l'installation\n";
}

echo "</pre>";
?>

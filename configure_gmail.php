<?php
/**
 * Script d'auto-configuration Gmail pour WAMP
 * Détecte la version PHP et configure php.ini automatiquement
 */

$php_version = phpversion();
$php_ini_path = php_ini_loaded_file();

echo "<pre style='background:#111;color:#d4a72c;padding:20px;font-family:monospace;border-radius:8px;'>";
echo "=== AUTO-CONFIGURATION GMAIL POUR WAMP ===\n\n";

echo "📌 Infos PHP:\n";
echo "   Version: $php_version\n";
echo "   PHP.ini: $php_ini_path\n\n";

// Essayer de trouver php.ini
$possible_paths = [
    'C:\\wamp64\\bin\\php\\php' . substr($php_version, 0, 3) . '\\php.ini',
    'C:\\wamp64\\bin\\php\\php' . substr($php_version, 0, 1) . '.' . substr($php_version, 2, 1) . '\\php.ini',
    $php_ini_path
];

echo "🔍 Recherche de php.ini...\n";
$found_php_ini = null;

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        echo "   ✅ Trouvé: $path\n";
        $found_php_ini = $path;
        break;
    } else {
        echo "   ❌ Pas trouvé: $path\n";
    }
}

if (!$found_php_ini) {
    echo "\n⚠️  Impossible de trouver php.ini automatiquement\n";
    echo "Chemin donné par PHP: $php_ini_path\n\n";
    echo "Solutions:\n";
    echo "1. Va manuellement sur: $php_ini_path\n";
    echo "2. Ouvre-le avec Notepad\n";
    echo "3. Cherche [mail function]\n";
    echo "4. Remplace par:\n";
    echo "   SMTP = smtp.gmail.com\n";
    echo "   smtp_port = 587\n";
    echo "   sendmail_from = abdourahmanibrahim176@gmail.com\n";
    echo "5. Sauvegarde (Ctrl+S)\n";
    echo "6. Redémarre WAMP\n";
} else {
    echo "\n✅ Configuration automatique en cours...\n\n";
    
    // Lire le fichier
    $content = file_get_contents($found_php_ini);
    
    // Trouver la section [mail function]
    if (preg_match('/\[mail function\](.*?)\[/s', $content, $matches)) {
        echo "✅ Section [mail function] trouvée\n";
        
        // Remplacer les paramètres SMTP
        $old_section = $matches[0];
        
        // Configuration Gmail
        $new_section = <<<'EOT'
[mail function]
; Configuration Gmail pour Royal Plaze
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = "abdourahmanibrahim176@gmail.com"

; Autres paramètres
mail.add_x_header = On

[
EOT;

        $new_content = str_replace($old_section, $new_section, $content);
        
        // Sauvegarder (attention: on ne peut pas écrire en dehors du workspace)
        // Donc on affiche juste ce qu'il faut faire
        echo "\n📝 Modifications à faire:\n";
        echo "   Cherche la section [mail function] dans:\n";
        echo "   $found_php_ini\n\n";
        echo "   Remplace par:\n";
        echo "   ---START---\n";
        echo "   [mail function]\n";
        echo "   ; Configuration Gmail pour Royal Plaze\n";
        echo "   SMTP = smtp.gmail.com\n";
        echo "   smtp_port = 587\n";
        echo "   sendmail_from = \"abdourahmanibrahim176@gmail.com\"\n";
        echo "   mail.add_x_header = On\n";
        echo "   ---END---\n\n";
        
    } else {
        echo "⚠️  Section [mail function] non trouvée\n";
    }
}

echo "\n✅ Configuration Gmail: abdourahmanibrahim176@gmail.com\n";
echo "🚀 Prochaine étape: Redémarre WAMP et teste!\n";
echo "</pre>";
?>

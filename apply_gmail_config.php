<?php
/**
 * Script de configuration automatique de php.ini
 * Modifie directement le fichier php.ini pour Gmail
 */

$php_ini_path = 'C:\\wamp64\\bin\\apache\\apache2.4.51\\bin\\php.ini';

echo "<pre style='background:#111;color:#d4a72c;padding:20px;font-family:monospace;border-radius:8px;'>";
echo "=== CONFIGURATION AUTOMATIQUE DE php.ini ===\n\n";

if (!file_exists($php_ini_path)) {
    echo "❌ ERREUR: Fichier php.ini introuvable à:\n";
    echo "   $php_ini_path\n\n";
    echo "Vérifie le chemin et réessaye.\n";
    exit;
}

// Vérifier les permissions
if (!is_writable($php_ini_path)) {
    echo "⚠️  ATTENTION: Le fichier n'est pas accessible en écriture\n";
    echo "   Chemin: $php_ini_path\n\n";
    echo "Solutions:\n";
    echo "1. Lance VS Code en administrateur\n";
    echo "2. Ou ouvre php.ini manuellement:\n";
    echo "   - Clique droit → Notepad\n";
    echo "   - Sélectionne 'Exécuter en tant qu'administrateur'\n";
    echo "3. Puis lance ce script\n\n";
    exit;
}

// Lire le fichier
$content = file_get_contents($php_ini_path);

// Pattern pour trouver [mail function]
$pattern = '/\[mail function\](.*?)(?=\n\[|\Z)/s';

if (preg_match($pattern, $content, $matches)) {
    echo "✅ Section [mail function] trouvée\n\n";
    
    // Remplacer la section entière
    $old_section = $matches[0];
    
    $new_section = "[mail function]\n";
    $new_section .= "; Configuration Gmail pour Royal Plaze\n";
    $new_section .= "SMTP = smtp.gmail.com\n";
    $new_section .= "smtp_port = 587\n";
    $new_section .= "sendmail_from = \"abdourahmanibrahim176@gmail.com\"\n";
    $new_section .= "mail.add_x_header = On\n";
    
    // Remplacer dans le contenu
    $new_content = str_replace($old_section, $new_section, $content);
    
    // Sauvegarder
    if (file_put_contents($php_ini_path, $new_content)) {
        echo "✅ php.ini modifié avec succès!\n\n";
        echo "📝 Configuration appliquée:\n";
        echo "   SMTP = smtp.gmail.com\n";
        echo "   smtp_port = 587\n";
        echo "   sendmail_from = abdourahmanibrahim176@gmail.com\n\n";
        
        echo "🚀 Prochaines étapes:\n";
        echo "   1. Redémarre WAMP (Restart All Services)\n";
        echo "   2. Va sur: http://localhost/hotel_reservation/test_email.php\n";
        echo "   3. Envoie un email de test\n";
        echo "   4. Vérifie ton Gmail!\n";
    } else {
        echo "❌ ERREUR: Impossible de modifier php.ini\n";
        echo "   Assure-toi que:\n";
        echo "   - WAMP est arrêté\n";
        echo "   - Tu as les permissions administrateur\n";
        echo "   - Le fichier n'est pas verrouillé\n";
    }
} else {
    echo "❌ Section [mail function] non trouvée\n";
    echo "   Le fichier peut être corrompu ou différent\n";
}

echo "</pre>";
?>

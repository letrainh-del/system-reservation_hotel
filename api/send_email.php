<?php
// SIMPLE EMAIL FUNCTION - WORKS ON WAMP
// No PHPMailer, no authentication needed

function send_confirmation_email($email, $nom, $prenom, $chambre_type, $numero, $arrive, $depart, $prix, $reservation_id, $nuits) {
    $subject = "Confirmation de réservation #$reservation_id - Royal Plaze Hotel";
    
    $body = "
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family: Arial; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto;'>
            <div style='background: #d4a72c; padding: 20px; text-align: center;'>
                <h1>🏨 Royal Plaze Hotel</h1>
            </div>
            <div style='background: #fff; padding: 30px; border: 1px solid #ddd;'>
                <h2>Bienvenue $prenom $nom!</h2>
                <p>Confirmation de votre réservation</p>
                
                <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #d4a72c; margin: 20px 0;'>
                    <h3>Détails:</h3>
                    <p><strong>Numéro réservation:</strong> #$reservation_id</p>
                    <p><strong>Chambre:</strong> $chambre_type (N° $numero)</p>
                    <p><strong>Arrivée:</strong> $arrive</p>
                    <p><strong>Départ:</strong> $depart</p>
                    <p><strong>Nuits:</strong> $nuits</p>
                    <p style='font-size: 20px; color: #d4a72c;'><strong>Total: " . number_format($prix, 2, ',', ' ') . " FDJ</strong></p>
                </div>
                
                <h3>Informations:</h3>
                <ul>
                    <li>Arrivée: 14:00</li>
                    <li>Départ: 11:00</li>
                    <li>Pièce d'identité requise</li>
                </ul>
                
                <p style='color: #666; font-size: 12px;'>
                    Questions? Contactez-nous au +253 77 000 000<br>
                    &copy; 2025 Royal Plaze Hotel
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers pour HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Royal Plaze Hotel <noreply@royalplaze.com>\r\n";
    
    // Envoyer
    $sent = mail($email, $subject, $body, $headers);
    
    // Log
    $logs_dir = __DIR__ . '/../logs';
    @mkdir($logs_dir, 0755, true);
    $log = "[$arrive] RÉSERVATION #$reservation_id | Email: $email | Statut: " . ($sent ? "ENVOYÉ" : "ÉCHEC") . "\n";
    @file_put_contents($logs_dir . '/confirmations.log', $log, FILE_APPEND);
    
    return $sent;
}
?>



# Configuration Gmail App Password

## Problème actuel:
```
SMTP Error: Could not authenticate.
```

## Solution: Utiliser un App Password au lieu du mot de passe du compte

### Étapes à suivre:

1. **Accéder à Google Account**
   - Va sur: https://myaccount.google.com
   - Connecte-toi avec: abdourahmanibrahim176@gmail.com

2. **Sécurité (2-Step Verification)**
   - Clique sur "Security" dans le menu gauche
   - Assure-toi que "2-Step Verification" est ACTIVÉ
   - Si non, active-le d'abord

3. **App Passwords**
   - Clique sur "App passwords" (visible seulement si 2FA est activé)
   - Sélectionne:
     - App: "Mail"
     - Device: "Windows Computer"
   - Google générera un password de 16 caractères (ex: xxxx xxxx xxxx xxxx)

4. **Utilise ce password dans le code**
   - Remplace "VIADUCHOLHOL123" par le password généré
   - Ex: $mail->Password = 'xxxxxxxxxbcdefghij';

5. **Teste à nouveau**
   - Réexécute test_phpmailer_direct.php
   - Ou accède à: http://localhost/hotel_reservation/test_phpmailer_direct.php

## Alternative temporaire (moins sécurisé):
Si tu n'as pas 2FA:
- Accede: https://myaccount.google.com/lesssecureapps
- Active "Allow less secure apps"
- Mais les App Passwords sont préférés

## Après avoir le bon password:
Mets à jour `api/send_email.php` avec le nouveau password d'application Gmail.


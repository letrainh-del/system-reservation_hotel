<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Royal Plaze Hôtel</title>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
    />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <div class="container">
      <div class="hero">
        <nav class="navbar">
          <div class="logo">
            <h2>Royal Plaze</h2>
          </div>
          <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="#">À propos</a></li>
            <li><a href="rooms.html">Chambres</a></li>
            <<li><a href="#about">À propos</a></li>
            <li><a href="contact.html">Contact</a></li>
          </ul>
          <div class="navright">
            <?php if(!isset($_SESSION['client_id']) && !isset($_SESSION['admin_id'])) { ?>
              <a href="login.html" class="hero-btn btn">Connexion</a>
            <?php } else { ?>
              <a href="logout.php" class="hero-btn btn">Déconnexion</a>
            <?php } ?>
            <img src="assets/images/moon (1).png" class="moon" />
            <img src="assets/images/menu.png" class="menu" alt="" />
          </div>
        </nav>

        <!-- Home Section -->
        <div class="homePage">
          <div class="homeContent">
            <h1>Bienvenue à <span>l'Hôtel Royal Plaze</span></h1>
            <p>
              Découvrez le luxe et le confort au cœur de la ville. <br />
              Réservez votre séjour dès aujourd’hui !
            </p>
            <button class="btn">RÉSERVER MAINTENANT</button>

            <div class="booking-form">
              <div class="form-item">
                <label>Date d'arrivée</label>
                <input type="text" id="checkin" placeholder="Choisir une date" />
              </div>

              <div class="form-item">
                <label>Date de départ</label>
                <input type="text" id="checkout" placeholder="Choisir une date" />
              </div>

              <div class="form-item">
                <label>Personnes</label>
                <input type="number" id="guest" placeholder="1" min="1" value="1" />
              </div>

              <button type="button" class="check-btn" id="check-btn-main">Vérifier disponibilité</button>
              <div id="result-message" style="margin-top: 10px; display: none; padding: 10px; border-radius: 6px;"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Rooms Preview -->
      <div class="rooms">
        <p class="title">Découvrez nos <span>Chambres</span></p>
        <div class="roomCards">
          <div class="card">
            <img src="assets/images/room1.jpg" class="RoomImage" />
            <div class="cardContent">
              <h5 class="roomName">Chambre Simple</h5>
              <p class="roomPrice">50$</p>
            </div>
            <div class="roomRate">
              <div class="rates">
                <p class="rateCounte">4.5</p>
                <img src="assets/images/rating.png" class="rateImg" />
              </div>
              <a href="roomDetaill.html" class="cardbtn">Réserver</a>
            </div>
          </div>

          <div class="card">
            <img src="assets/images/room2.jpg" class="RoomImage" />
            <div class="cardContent">
              <h5 class="roomName">Chambre Deluxe</h5>
              <p class="roomPrice">750$</p>
            </div>
            <div class="roomRate">
              <div class="rates">
                <p class="rateCounte">4.4</p>
                <img src="assets/images/rating.png" class="rateImg" />
              </div>
              <a href="#" class="cardbtn">Réserver</a>
            </div>
          </div>

          <div class="card">
            <img src="assets/images/room3.jpg" class="RoomImage" />
            <div class="cardContent">
              <h5 class="roomName">Chambre Familiale</h5>
              <p class="roomPrice">50$ / nuit</p>
            </div>
            <div class="roomRate">
              <div class="rates">
                <p class="rateCounte">4.3</p>
                <img src="assets/images/rating.png" class="rateImg" />
              </div>
              <a href="#" class="cardbtn">Réserver</a>
            </div>
          </div>

          <div class="card">
            <img src="assets/images/room4.jpg" class="RoomImage" />
            <div class="cardContent">
              <h5 class="roomName">Chambre Premium</h5>
              <p class="roomPrice">120$</p>
            </div>
            <div class="roomRate">
              <div class="rates">
                <p class="rateCounte">4.1</p>
                <img src="assets/images/rating.png" class="rateImg" />
              </div>
              <a href="#" class="cardbtn">Réserver</a>
            </div>
          </div>
        </div>

        <a href="rooms.html" class="roombtn btn">Voir plus</a>
      </div>

    <section id="about" class="about">
  <div class="container">
    <h2 class="sectionTitle">À propos de <span>Royal Plaze</span></h2>
    <p class="about-text">
      Bienvenue sur notre plateforme de réservation d’hôtels, conçue pour offrir
      une expérience simple, rapide et sécurisée. Notre objectif est de faciliter 
      l’accès aux meilleurs hébergements, en garantissant un service fiable et 
      une interface intuitive.
    </p>

    <p class="about-text">
      Ce projet a été développé dans le cadre d’un travail universitaire par des
      étudiants passionnés par le web, le design moderne et la gestion des systèmes 
      informatiques. Grâce à cette plateforme, nous mettons en pratique nos 
      connaissances en développement web full-stack, gestion de bases de données 
      et expérience utilisateur.
    </p>

    <p class="about-text">
      Nous espérons que vous apprécierez votre expérience sur notre site et
      nous vous remercions de votre confiance.
    </p>

    <h3 class="about-subtitle">✨ Nos valeurs</h3>
    <ul class="about-values">
      <li>✔ Simplicité d’utilisation</li>
      <li>✔ Sécurité des données</li>
      <li>✔ Rapidité des réservations</li>
      <li>✔ Service de qualité</li>
    </ul>
  </div>
</section>

      <!-- Footer -->
      <div class="footer">
        <p class="footerCopy">
          &copy; 2024 Hôtel Royal Plaze. Tous droits réservés.
        </p>
      </div>
    </div>

    <script>
      flatpickr("#checkin", {
        dateFormat: "d/m/Y",
        minDate: "today",
        theme: "light",
      });

      flatpickr("#checkout", {
        dateFormat: "d/m/Y",
        minDate: new Date().fp_incr(1),
        theme: "light",
      });

      // Vérifier disponibilité
      document.getElementById('check-btn-main').addEventListener('click', async function() {
        const dateArrive = document.getElementById('checkin').value;
        const dateDepart = document.getElementById('checkout').value;
        const capacite = document.getElementById('guest').value;
        const msgDiv = document.getElementById('result-message');

        if (!dateArrive || !dateDepart) {
          msgDiv.style.display = 'block';
          msgDiv.style.background = '#2b0000';
          msgDiv.style.color = '#ff4444';
          msgDiv.textContent = '❌ Veuillez sélectionner les deux dates';
          return;
        }

        try {
          const response = await fetch('api/check_availability.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `date_arrive=${encodeURIComponent(dateArrive)}&date_depart=${encodeURIComponent(dateDepart)}&capacite=${capacite}`
          });

          const result = await response.json();

          if (result.success) {
            msgDiv.style.display = 'block';
            msgDiv.style.background = '#003000';
            msgDiv.style.color = '#5fd35f';
            msgDiv.innerHTML = `✅ <strong>${result.chambres.length} chambre(s) disponible(s)</strong> pour ${result.nuits} nuit(s)`;
            
            // Stocker les données pour affichage
            sessionStorage.setItem('search_results', JSON.stringify(result));
            
            // Rediriger vers la page des chambres après 2 secondes
            setTimeout(() => {
              window.location.href = 'rooms_results.php?checkin=' + encodeURIComponent(dateArrive) + '&checkout=' + encodeURIComponent(dateDepart) + '&guests=' + capacite;
            }, 1500);
          } else {
            msgDiv.style.display = 'block';
            msgDiv.style.background = '#2b0000';
            msgDiv.style.color = '#ff4444';
            msgDiv.textContent = '❌ ' + result.message;
          }
        } catch (error) {
          msgDiv.style.display = 'block';
          msgDiv.style.background = '#2b0000';
          msgDiv.style.color = '#ff4444';
          msgDiv.textContent = '❌ Erreur: ' + error.message;
        }
      });
    </script>
  </body>
</html>

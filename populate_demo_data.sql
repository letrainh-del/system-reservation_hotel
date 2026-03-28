-- Script d'initialisation et de remplissage de la base Royal Plaze

-- 1. Chambres
INSERT INTO chambre (ID_Chambre, Numero_Chambre, Type_Chambre, Prix, Disponible)
VALUES
(1, 101, 'Suite Royale', 250000, 1),
(2, 102, 'Double Deluxe', 150000, 0),
(3, 103, 'Simple', 90000, 1),
(4, 104, 'Double Deluxe', 150000, 0),
(5, 105, 'Suite Junior', 180000, 1),
(6, 106, 'Simple', 90000, 1),
(7, 107, 'Suite Royale', 250000, 0),
(8, 108, 'Double Deluxe', 150000, 1),
(9, 109, 'Simple', 90000, 0),
(10, 110, 'Suite Junior', 180000, 1);

-- 2. Clients
INSERT INTO client (ID_Client, Prenom_Client, Nom_Client, Email_Client)
VALUES
(1, 'Marvin', 'McKinney', 'marvin@example.com'),
(2, 'Albert', 'Flores', 'albert@example.com'),
(3, 'Guy', 'Hawkins', 'guy@example.com'),
(4, 'Brooklyn', 'Simmons', 'brooklyn@example.com'),
(5, 'Cody', 'Fisher', 'cody@example.com'),
(6, 'Darlene', 'Robertson', 'darlene@example.com');

-- 3. Réservations (statuts variés)
INSERT INTO reserver (ID_Reserver, ID_Client, ID_Chambre, Date_Reservation_Reserver, Date_Arrive_Reserver, Date_Dapart_Reserver, Etat_Reserver, Prix_Total, Mode_Paiement)
VALUES
(1, 1, 105, '2025-12-14', '2025-12-14', '2025-12-16', 'Occupée', 360000, 'Carte'),
(2, 2, 106, '2025-12-14', '2025-12-14', '2025-12-15', 'En attente', 90000, 'Espèces'),
(3, 3, 107, '2025-12-13', '2025-12-13', '2025-12-15', 'Départ', 180000, 'Carte'),
(4, 4, 108, '2025-12-12', '2025-12-12', '2025-12-14', 'Disponible', 300000, 'Espèces'),
(5, 5, 109, '2025-12-12', '2025-12-12', '2025-12-13', 'En maintenance', 90000, 'Carte'),
(6, 6, 110, '2025-12-11', '2025-12-11', '2025-12-14', 'Occupée', 540000, 'Espèces');

-- 4. Mettre à jour les chambres pour refléter les statuts
UPDATE chambre SET Disponible = 0 WHERE ID_Chambre IN (102, 104, 107, 109);
UPDATE chambre SET Disponible = 1 WHERE ID_Chambre IN (101, 103, 105, 106, 108, 110);

-- (Ajoute d'autres chambres/réservations si besoin pour plus de volume)

-- ENRICHISSEMENT DE LA BASE HOTEL_RESERVATION
-- Chambres supplémentaires
INSERT INTO `chambre` (`Numero_Chambre`, `Type_Chambre`, `Prix`, `Capacite`, `Description`, `Image`, `Disponible`, `Statut`) VALUES
('108', 'Chambre Simple', 80000, 1, 'Chambre simple, vue cour.', 'assets/images/room1.jpg', 1, 'Disponible'),
('109', 'Suite Junior', 180000, 2, 'Suite junior, balcon.', 'assets/images/room2.jpg', 1, 'Occupée'),
('110', 'Appartement Deluxe', 320000, 5, 'Appartement familial, 2 salles de bain.', 'assets/images/room3.jpg', 1, 'En attente'),
('111', 'Studio Premium', 120000, 2, 'Studio moderne, kitchenette.', 'assets/images/room3.jpg', 1, 'Disponible'),
('112', 'Chambre Double', 140000, 2, 'Double, vue piscine.', 'assets/images/room4.jpg', 1, 'Départ'),
('113', 'Chambre Simple', 90000, 1, 'Simple, clim, wifi.', 'assets/images/room1.jpg', 1, 'Maintenance');

-- Clients supplémentaires
INSERT INTO `client` (`Nom_Client`, `Prenom_Client`, `Email_Client`, `Telephone_Client`, `Nationnalite_Client`, `Adresse_Client`, `Date_Naissance_Client`, `MotDePasse_Client`) VALUES
('Ibrahim', 'Abdourahman', 'abdourahman.ibrahim@gmail.com', '77303529', 'Djiboutien', 'Balbala, PK12', '2004-08-04', 'azerty'),
('Ali', 'Hassan', 'ali.hassan@gmail.com', '77777777', 'Djiboutien', 'Balbala', '1999-01-01', 'azerty'),
('Amina', 'Ali', 'amina.ali@gmail.com', '77888888', 'Djiboutienne', 'Haramous', '2000-05-10', 'azerty'),
('Fatima', 'Nour', 'fatima.nour@gmail.com', '77111111', 'Djiboutienne', 'Héron', '1998-04-12', 'azerty'),
('Hassan', 'Abdi', 'hassan.abdi@gmail.com', '77222222', 'Somalien', 'Ambouli', '1995-09-22', 'azerty'),
('Leyla', 'Mahad', 'leyla.mahad@gmail.com', '77333333', 'Djiboutienne', 'Balbala Sud', '2000-06-18', 'azerty'),
('Yacoub', 'Ahmed', 'yacoub.ahmed@gmail.com', '77444444', 'Djiboutien', 'PK13', '1997-03-15', 'azerty'),
('Omar', 'Dahir', 'omar.dahir@gmail.com', '77555555', 'Djiboutien', 'Salines Ouest', '1996-12-22', 'azerty'),
('Guedi', 'Hassan', 'guedi.hassan@gmail.com', '77666666', 'Djiboutien', 'Quartier 7', '1993-11-11', 'azerty'),
('Moumin', 'Barkat', 'moumin.barkat@gmail.com', '77777778', 'Djiboutien', 'Quartier 6', '1992-10-10', 'azerty'),
('Rahma', 'Ismail', 'rahma.ismail@gmail.com', '77888889', 'Djiboutienne', 'Haramous', '1994-09-09', 'azerty'),
('Souad', 'Ali', 'souad.ali@gmail.com', '77999999', 'Djiboutienne', 'PK12', '1991-08-08', 'azerty'),
('Abdoulkader', 'Robleh', 'abdoulkader.robleh@gmail.com', '77121212', 'Djiboutien', 'Quartier 3', '1989-07-07', 'azerty'),
('Mouna', 'Hassan', 'mouna.hassan@gmail.com', '77232323', 'Djiboutienne', 'Quartier 4', '1990-06-06', 'azerty'),
('Idriss', 'Farah', 'idriss.farah@gmail.com', '77343434', 'Djiboutien', 'Quartier 5', '1988-05-05', 'azerty'),
('Khadra', 'Omar', 'khadra.omar@gmail.com', '77454545', 'Djiboutienne', 'Quartier 6', '1993-04-04', 'azerty'),
('Mahamoud', 'Youssouf', 'mahamoud.youssouf@gmail.com', '77565656', 'Djiboutien', 'Quartier 7', '1992-03-03', 'azerty'),
('Aicha', 'Abdi', 'aicha.abdi@gmail.com', '77676767', 'Djiboutienne', 'Quartier 8', '1991-02-02', 'azerty'),
('Ismail', 'Ali', 'ismail.ali@gmail.com', '77787878', 'Djiboutien', 'Quartier 9', '1990-01-01', 'azerty'),
('Hodan', 'Moussa', 'hodan.moussa@gmail.com', '77898989', 'Djiboutienne', 'Quartier 10', '1989-12-12', 'azerty');


-- Réservations variées (statuts et dates récentes, clients djiboutiens)
INSERT INTO `reserver` (`ID_Client`, `ID_Chambre`, `Date_Reservation_Reserver`, `Date_Arrive_Reserver`, `Date_Dapart_Reserver`, `Etat_Reserver`, `Prix_Total`, `Mode_Paiement`) VALUES
(1, 108, '2025-12-10', '2025-12-15', '2025-12-18', 'Confirmée', 240000, 'Carte'),
(2, 109, '2025-12-11', '2025-12-16', '2025-12-19', 'En attente', 540000, 'Espèces'),
(3, 110, '2025-12-12', '2025-12-17', '2025-12-20', 'Annulée', 960000, 'Carte'),
(4, 111, '2025-12-13', '2025-12-18', '2025-12-21', 'Occupée', 360000, 'Espèces'),
(5, 112, '2025-12-14', '2025-12-19', '2025-12-22', 'Départ', 420000, 'Carte'),
(6, 113, '2025-12-15', '2025-12-20', '2025-12-23', 'En maintenance', 270000, 'Espèces'),
(7, 108, '2025-12-10', '2025-12-12', '2025-12-14', 'Confirmée', 160000, 'Carte'),
(8, 109, '2025-12-11', '2025-12-13', '2025-12-15', 'En attente', 360000, 'Espèces'),
(9, 110, '2025-12-12', '2025-12-14', '2025-12-16', 'Annulée', 640000, 'Carte'),
(10, 111, '2025-12-13', '2025-12-15', '2025-12-17', 'Occupée', 240000, 'Espèces'),
(11, 112, '2025-12-14', '2025-12-16', '2025-12-18', 'Départ', 280000, 'Carte'),
(12, 113, '2025-12-15', '2025-12-17', '2025-12-19', 'En maintenance', 180000, 'Espèces'),
(13, 108, '2025-12-16', '2025-12-21', '2025-12-24', 'Confirmée', 320000, 'Carte'),
(14, 109, '2025-12-17', '2025-12-22', '2025-12-25', 'En attente', 540000, 'Espèces'),
(15, 110, '2025-12-18', '2025-12-23', '2025-12-26', 'Annulée', 960000, 'Carte'),
(16, 111, '2025-12-19', '2025-12-24', '2025-12-27', 'Occupée', 360000, 'Espèces'),
(17, 112, '2025-12-20', '2025-12-25', '2025-12-28', 'Départ', 420000, 'Carte'),
(18, 113, '2025-12-21', '2025-12-26', '2025-12-29', 'En maintenance', 270000, 'Espèces'),
(19, 108, '2025-12-22', '2025-12-27', '2025-12-30', 'Confirmée', 160000, 'Carte'),
(20, 109, '2025-12-23', '2025-12-28', '2025-12-31', 'En attente', 360000, 'Espèces'),
(21, 110, '2025-12-24', '2025-12-29', '2026-01-01', 'Annulée', 640000, 'Carte'),
(22, 111, '2025-12-25', '2025-12-30', '2026-01-02', 'Occupée', 240000, 'Espèces'),
(23, 112, '2025-12-26', '2025-12-31', '2026-01-03', 'Départ', 280000, 'Carte'),
(24, 113, '2025-12-27', '2026-01-01', '2026-01-04', 'En maintenance', 180000, 'Espèces');

-- Tu peux relancer ce script plusieurs fois pour encore plus de volume (modifie les dates si besoin)

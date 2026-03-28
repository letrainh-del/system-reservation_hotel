<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: ../login.php"); exit; }
include "../../config/db.php";

$conn->prepare("DELETE FROM Chambre WHERE ID_Chambre=?")->execute([$_GET['id']]);
header("Location: index.php");

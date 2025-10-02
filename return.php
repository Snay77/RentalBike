<?php
require_once "database.php";
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bike_id = (int) $_POST['bike_id'];

    $sql = "SELECT * FROM rentals WHERE bike_id = :bike_id AND end_time IS NULL"; // vérifie que le vélo est bien loué
    $stmt = $db->prepare($sql);
    $stmt->execute(['bike_id' => $bike_id]);
    $rental = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$rental) {
        die("Ce vélo n'est pas en cours de location.");
    }

    $sql = "UPDATE rentals SET end_time = NOW() WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $rental->id]);

    // 3. Remettre le vélo en dispo
    $sql = "UPDATE bikes SET in_use = 0 WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $bike_id]);

    echo "<h1>Vélo rendu ✅</h1>";
    echo "<a href='index.php'>⬅ Retour à l'accueil</a>";
} else {
    echo "Méthode non autorisée.";
}

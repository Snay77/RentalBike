<?php
require_once "database.php";

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bike_id = (int) $_POST['bike_id'];
    $renter_name = trim($_POST['renter_name']);
    $expected_end_time = $_POST['expected_end_time'];

    $sql = "SELECT * FROM bikes WHERE id = :id AND in_use = 0 AND status = 'available'"; // vérifie que le vélo est pas encore loué
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $bike_id]);
    $bike = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$bike) {
        die("Ce vélo n'est pas disponible.");
    }

    $sql = "INSERT INTO rentals (bike_id, renter_name, start_time, expected_end_time, end_time)
            VALUES (:bike_id, :renter_name, NOW(), :expected_end_time, NULL)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'bike_id' => $bike_id,
        'renter_name' => $renter_name,
        'expected_end_time' => $expected_end_time
    ]);

    $sql = "UPDATE bikes SET in_use = 1 WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $bike_id]);

    echo "<h1>Réservation confirmée ✅</h1>";
    echo "<p>Vélo : " . $bike->code . "</p>";
    echo "<p>Loué par : " . $renter_name . "</p>";
    echo "<p>Retour prévu : " . $expected_end_time . "</p>";
    echo "<a href='index.php'>⬅ Retour à l'accueil</a>";
} else {
    echo "Méthode non autorisée.";
}

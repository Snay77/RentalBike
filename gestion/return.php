<?php
require_once __DIR__ . '/../database/database.php';
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

    $sql = "UPDATE bikes SET in_use = 0 WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $bike_id]);

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Rendu</title>
        <link rel="stylesheet" href="/../style.css">
    </head>

    <body>
        <main class='container'>
            <div class='success-message'>
                <h1>Vélo rendu ✅</h1>
                <p>Merci d'avoir utilisé RentalBike.</p>
            </div>
            <a class='back-link' href='../index.php'>⬅ Retour à l'accueil</a>
        </main>
    </body>

    </html>
<?php
} else {
    echo "Méthode non autorisée.";
}

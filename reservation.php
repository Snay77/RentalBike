<?php

require_once "database.php";

$db = getDB();

if (isset($_GET['bike_id'])) {
    $bike_id = (int) $_GET['bike_id'];

    $sql = "SELECT * FROM bikes WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $bike_id]);
    $bike = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$bike) {
        die("Vélo introuvable.");
    }
} else {
    die("Aucun vélo sélectionné.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Réservation du vélo <?= $bike->code ?></h1>

    <form action="add_reservation.php" method="post">
        <input type="hidden" name="bike_id" value="<?= $bike->id ?>">

        <label for="renter_name">Votre nom :</label>
        <input type="text" name="renter_name" id="renter_name" required>

        <br><br>
        <label for="expected_end_time">Heure de retour prévue :</label>
        <input type="datetime-local" name="expected_end_time" id="expected_end_time" required>

        <br><br>
        <button type="submit">Confirmer la réservation</button>
    </form>
</body>

</html>
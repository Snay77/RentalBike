<?php

require_once "database.php";
date_default_timezone_set('Europe/Paris');

$db = getDB();

$sql = "SELECT 
            stations.id, stations.name, stations.location,
            bikes.id AS bike_id, bikes.code, bikes.status, bikes.in_use, bikes.station_id,
            rentals.expected_end_time,
            IF(bikes.in_use = 1 AND rentals.end_time IS NULL AND rentals.expected_end_time < NOW(), 1, 0) AS is_late
        FROM stations
        LEFT JOIN bikes ON bikes.station_id = stations.id
        LEFT JOIN rentals ON rentals.bike_id = bikes.id AND rentals.end_time IS NULL
        ORDER BY stations.name,
            CASE
                WHEN bikes.in_use = 0 AND bikes.status = 'available' THEN 1  -- dispo
                WHEN bikes.in_use = 1 AND IF(rentals.expected_end_time < NOW(), 1, 0) = 0 THEN 2 -- loué dans les temps
                WHEN bikes.in_use = 1 AND rentals.expected_end_time < NOW() THEN 3 -- en retard
                ELSE 4
            END";

$query = $db->prepare($sql);
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_OBJ);

$stations = [];

foreach ($rows as $row) {
    if (!isset($stations[$row->id])) {
        $stations[$row->id] = [
            'name' => $row->name,
            'location' => $row->location,
            'bikes' => []
        ];
    }

    if ($row->code) {
        $stations[$row->id]['bikes'][] = $row;
    }
}

// echo '<pre>';
// print_r($stations);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalBike</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <img src="logo.png" alt="">
    <h1>Liste des stations</h1>
    <?php foreach ($stations as $station): ?>
        <div class="station">
            <h2><?= $station['name'] ?> (<?= $station['location'] ?>)</h2>
            <?php if (!empty($station['bikes'])): ?>
                <div class="grid">
                    <?php foreach ($station['bikes'] as $bike): ?>
                        <?php
                        $now = new DateTime();
                        $expected = $bike->expected_end_time ? new DateTime($bike->expected_end_time) : null;

                        // Changement de la couleur suivant l'état du vélo
                        if (!$bike->in_use && $bike->status === 'available') {
                            $cardClass = "card available";
                        } elseif ($bike->in_use && $expected && $now > $expected) {
                            $cardClass = "card late";
                        } elseif ($bike->in_use) {
                            $cardClass = "card inuse";
                        } else {
                            $cardClass = "card";
                        }
                        ?>
                        <div class="<?= $cardClass ?>">
                            <h3><?= $bike->code ?></h3>
                            <p>Statut: <?= $bike->status ?></p>
                            <p>En utilisation: <?= $bike->in_use ? "Oui" : "Non" ?></p>
                            <?php if (!$bike->in_use && $bike->status === 'available'): ?>
                                <form action="reservation.php" method="get">
                                    <input type="hidden" name="bike_id" value="<?= $bike->bike_id ?>">
                                    <button type="submit">Réserver</button>
                                </form>
                            <?php elseif ($bike->in_use): ?>
                                <form action="return.php" method="post">
                                    <input type="hidden" name="bike_id" value="<?= $bike->bike_id ?>">
                                    <button type="submit">Rendre</button>
                                </form>
                            <?php else: ?>
                                <p>Indisponible</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucun vélo dans cette station.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>

</html>
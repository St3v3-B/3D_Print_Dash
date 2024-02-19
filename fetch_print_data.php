<?php
// Databaseconfiguratie
$host = '';
$db = '';
$user = '';
$pass = '';
$charset = '';

// Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Databaseverbinding
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Hulpfunctie om minuten te converteren naar uren:min:sec
function convertMinutesToHMS($minutes) {
    $totalSeconds = intval($minutes) * 60;
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}

function vertaalStatusNaarNederlands($status) {
    switch ($status) {
        case 'Online':
            return 'Online';
        case 'Offline':
            return 'Offline';
        case 'Printing':
            return 'Afdrukken';
        case 'Paused':
            return 'Gepauzeerd';
        case 'Finished':
            return 'Voltooid';
        case 'Error':
            return 'Fout';
        case 'Free':
            return 'Vrij';    
        default:
            return $status; // Retourneer de originele status indien geen vertaling gevonden is
    }
}

// SQL-query om PrintData op te halen
$sql = "SELECT ID, `Printer name`, `Status code`, `Online status`, `Print status`, `Time remaining`, `Total time`, `Time percentage`, `Estimated material`, `Firmware status`, Timestamp, User, URL FROM PrintData";

// Query voorbereiden en uitvoeren
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Resultaten ophalen
$results = $stmt->fetchAll();
if ($results) {
    foreach ($results as $row) {
        $status = htmlspecialchars(vertaalStatusNaarNederlands($row['Print status']));
        $statusClass = '';
        switch ($row['Print status']) {
            case 'Free':
                $statusClass = 'status-vrij';
                break;
            case 'Finished':
                $statusClass = 'status-klaar';
                break;
            case 'Printing':
                $statusClass = 'status-bezig';
                break;
            case 'Error':
                $statusClass = 'status-error';
                break;
            // Voeg meer condities toe voor andere statussen
        }
        $randomDelay = rand(0, 9000) . 'ms';

        if ($row['Printer name'] !== 'Cookie') {
            // "Ultimaker-2C-" vervangen door "Printer" indien nodig
            $printerName = str_replace("Ultimaker-2C-", "Printer ", $row['Printer name']);

            echo '<div class="printer-status" style="animation-delay: ' . $randomDelay . ';">';

            // De aangepaste printernaam weergeven
            echo '<h2><span class="' . $statusClass . '">' . htmlspecialchars($printerName) . ' Status</span></h2>';

            // Printer afbeelding indien beschikbaar
            if (!empty($row['URL'])) {
                echo '<img src="' . htmlspecialchars($row['URL']) . '" alt="Printer Image" style="max-width:100%; height:auto;">';
            }

            // Voortgangsbalk voor printstatus
            echo '<div class="progress-container">';
            if ($row['Print status'] === 'Free') {
                $displayedPercentage = '';
                $progressWidth = 0;
            } else {
                // Formatteer het percentage om trailing nullen te verwijderen
                $progressWidth = rtrim(rtrim(sprintf('%.1f', $row['Time percentage']), '0'), '.');
                $displayedPercentage = $progressWidth . '%';
                $progressWidth = $row['Time percentage'];
            }
            echo '<div class="progress-bar" style="width:' . $progressWidth . '%">' . $displayedPercentage . '</div>';
            echo '</div>';

            // Overige printerinformatie
            echo '<div class="status-details">';
            echo '<p>Status: <span class="' . $statusClass . '">' . $status . '</span></p>';

            echo '<p>Tijd besteed: <span>' . convertMinutesToHMS(htmlspecialchars($row['Total time'])) . '</span></p>';
            // Het nummerformaat aanpassen naar Nederlands formaat met een komma
            $formattedMaterial = number_format(floatval(htmlspecialchars($row['Estimated material'])), 2, ',', '');
            echo '<p>Gebruikt materiaal: <span>' . $formattedMaterial . ' gram</span></p>';
            echo '<p>Resterende tijd: <span>' . convertMinutesToHMS(htmlspecialchars($row['Time remaining'])) . '</span></p>';
            echo '<p>Bestand: <span>' . htmlspecialchars($row['User']) . '</span></p>';
            echo '</div>'; // status-details sluiten
            echo '</div>'; // printer-status sluiten
            
        }
    }
} else {
    echo 'Geen data gevonden.';
}
?>
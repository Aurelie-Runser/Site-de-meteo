<?php
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ville = $_POST['ville'];
    $apiKey = "af0bed8924751e07bce0f22544b547e7";

    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($ville) . "&appid=" . $apiKey;

    // Initialisation de cURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    // Exécution de la requête
    $response = curl_exec($curl);
    
    // Vérification des erreurs de cURL
    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        die("Erreur cURL : " . $error);
    }
    
    // Fermeture de la requête cURL
    curl_close($curl);
    // Conversion des données en JSON
    $data = json_decode($response);

    // Vérification de la réussite de la conversion JSON
    if ($data === null) {
        die("Erreur de décodage JSON");
    }

    // Vérifier si la requête a retourné des données
    if ($data && isset($data->name)) {
        // Récupération des données de la réponse JSON
        $name = $data->name;
        $temps = $data->weather[0]->main;
        $description = $data->weather[0]->description;

        // Conversion des températures en degrés Celsius
        $temperatureC = $data->main->temp - 273.15;
        $temp_ressentitC = $data->main->feels_like - 273.15;
        $temperature_minC = $data->main->temp_min - 273.15;
        $temperature_maxC = $data->main->temp_max - 273.15;

        $pression = $data->main->pressure;
        $humidite = $data->main->humidity;

        $visibilite = $data->visibility;

        $vitesse_vent = $data->wind->speed;
        $direction_vent = $data->wind->deg;

        $pourcentage_nuage = $data->clouds->all;

        $pluie_1h = $data->rain->{"1h"} ?? 0;
        $pluie_3h = $data->rain->{"3h"} ?? 0;

        $neige_1h = $data->snow->{"1h"} ?? 0;
        $neige_3h = $data->snow->{"3h"} ?? 0;

        // Récupération du fuseau horaire pour la ville
        $timezoneOffset = $data->timezone;

        // Conversion de l'heure UTC en heure locale
        $currentTime = time() + $timezoneOffset;
        $heureLocale = date('H:i:s', $currentTime);

        // Récupération des heures de lever et coucher du soleil en heure locale
        $sunriseUTC = $data->sys->sunrise;
        $sunsetUTC = $data->sys->sunset;
        $sunriseLocale = date('H:i:s', $sunriseUTC + $timezoneOffset);
        $sunsetLocale = date('H:i:s', $sunsetUTC + $timezoneOffset);

        // Enregistrement des données dans la session
        $_SESSION['name'] = $name;
        $_SESSION['temps'] = $temps;
        $_SESSION['description'] = $description;
        $_SESSION['temperature'] = $temperatureC;
        $_SESSION['temp_ressentitC'] = $temp_ressentitC;
        $_SESSION['temperature_min'] = $temperature_minC;
        $_SESSION['temperature_max'] = $temperature_maxC;
        $_SESSION['pression'] = $pression;
        $_SESSION['humidite'] = $humidite;
        $_SESSION['visibilite'] = $visibilite;
        $_SESSION['vitesse_vent'] = $vitesse_vent;
        $_SESSION['direction_vent'] = $direction_vent;
        $_SESSION['pourcentage_nuage'] = $pourcentage_nuage;
        $_SESSION['pluie_1h'] = $pluie_1h;
        $_SESSION['pluie_3h'] = $pluie_3h;
        $_SESSION['neige_1h'] = $neige_1h;
        $_SESSION['neige_3h'] = $neige_3h;
        $_SESSION['heure'] = $heureLocale;
        $_SESSION['sunrise'] = $sunriseLocale;
        $_SESSION['sunset'] = $sunsetLocale;
    } else {
        $message = "Désolé, la ville que vous recherchez ne figure pas dans la base de données.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon site météo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,500;0,700;1,500;1,700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

    <form method="post" action="">
        <label for="villeInput" hidden>Ville :</label>
        <input type="text" name="ville" id="villeInput" required>
        <button type="submit">Afficher la météo</button>
    </form>

    <div id="resultat">
        <?php
        if (!empty($message)) {
            echo "<p>$message</p>";
        } elseif (isset($_SESSION['name'])) {
            echo "Ville : " . $_SESSION['name'] . "<br>";
            echo "Temps : " . $_SESSION['temps'] . "<br>";
            echo "Description : " . $_SESSION['description'] . "<br><br>";
            echo "Température : " . $_SESSION['temperature'] . " °C<br>";
            echo "Température ressentie : " . $_SESSION['temp_ressentitC'] . " °C<br>";
            echo "Température Minimum : " . $_SESSION['temperature_min'] . " °C<br>";
            echo "Température Maximum : " . $_SESSION['temperature_max'] . " °C<br><br>";
            echo "Pression Atmosphérique : " . $_SESSION['pression'] . " hPa<br>";
            echo "Humidité : " . $_SESSION['humidite'] . "<br>";
            echo "Visibilité : " . $_SESSION['visibilite'] . "<br><br>";
            echo "Vitesse du vent : " . $_SESSION['vitesse_vent'] . " m/s<br>";
            echo "Direction du vent : " . $_SESSION['direction_vent'] . " °<br><br>";
            echo "Pourcentage de nuages : " . $_SESSION['pourcentage_nuage'] . "% du ciel couvert<br><br>";
            echo "Pluie (1h) : " . $_SESSION['pluie_1h'] . " mm<br>";
            echo "Pluie (3h) : " . $_SESSION['pluie_3h'] . " mm<br><br>";
            echo "Neige (1h) : " . $_SESSION['neige_1h'] . " mm<br>";
            echo "Neige (3h) : " . $_SESSION['neige_3h'] . " mm<br><br>";
            echo "Heure de la dernière mesure : " . $_SESSION['heure'] . "<br>";
            echo "Heure du lever du soleil : " . $_SESSION['sunrise'] . "<br>";
            echo "Heure du coucher du soleil : " . $_SESSION['sunset'] . "<br>";

            // Supprimez les données de la session pour éviter les affichages indésirables lors des rechargements de la page
            session_unset();
            session_destroy();
        }
        ?>
    </div>
</body>
</html>

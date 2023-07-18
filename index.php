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

        // Conversion des températures en Kelvin en degrés Celsius
        $temperatureC = $data->main->temp - 273.15;
        $temp_ressentitC = $data->main->feels_like - 273.15;
        $temperature_minC = $data->main->temp_min - 273.15;
        $temperature_maxC = $data->main->temp_max - 273.15;

        // Récupération de la pression, humidité, visibilité du vent et des nuages
        $pression = $data->main->pressure;
        $humidite = $data->main->humidity;

        $visibilite = $data->visibility;

        $vitesse_vent = $data->wind->speed;
        $direction_vent = $data->wind->deg;

        $pourcentage_nuage = $data->clouds->all;

        // Récupération de la pluit et neige s'il n'y en a pas, mettre la valeure à 0
        $pluie_1h = $data->rain->{"1h"} ?? 0;
        $pluie_3h = $data->rain->{"3h"} ?? 0;

        $neige_1h = $data->snow->{"1h"} ?? 0;
        $neige_3h = $data->snow->{"3h"} ?? 0;

        // Récupération du fuseau horaire pour la ville
        $timezoneOffset = $data->timezone;

        // Conversion de l'heure UTC en heure locale
        $heureLocale = date('H:i:s');

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
        $message = "Désolé, la ville que vous recherchez ne figure pas dans la base de données. <br> Vérifiez son orthographe et ses tirets.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon site météo</title>

    <link href="style.css" rel="stylesheet">

</head>

<!-- La classe vérifie que les valeurs suivantes sont renseignées, si oui, récupère les valeurs et modifies la couleur du fond -->
<body class="<?php (isset($_SESSION['heure']) && isset($_SESSION['sunrise']) && isset($_SESSION['sunset'])) ?
            determineBackgroundColor($_SESSION['heure'], $_SESSION['sunrise'], $_SESSION['sunset']) : ''; ?>">

    <div class="info">

        <?php if (isset($_SESSION['name'])){
            echo "<h1>Météo de <span class='titre_ville'>" . $_SESSION['name'] . "</span></h1>";
        } else {
            echo "<h1>Ma Météo</h1>";
        }
        ?>

        <form method="post" action="">
            <label for="villeInput" hidden>Ville</label>
            <input type="text" name="ville" id="villeInput" required>
            
            <button type="submit" class="form_button">
                <img src="icons/loupe.svg" alt="icons de loupe pour rechercher">
            </button>
        </form>
    
        <div class="resultat">
            <?php
                if (!empty($message)) {
                    echo "<p>$message</p>";
                } elseif (isset($_SESSION['name'])) {
        
                    echo "<p>Ville : " . $_SESSION['name'] . "</p>";
                    echo "<p>Temps : " . $_SESSION['temps'] . "</p>";
                    echo "<p>Description : " . $_SESSION['description'] . "</p>";
                    echo "<br>";
        
                    echo "<p>Température : " . $_SESSION['temperature'] . " °C</p>";
                    echo "<p>Température ressentie : " . $_SESSION['temp_ressentitC'] . " °C</p>";
                    echo "<p>Température Minimum : " . $_SESSION['temperature_min'] . " °C</p>";
                    echo "<p>Température Maximum : " . $_SESSION['temperature_max'] . " °C</p>";
                    echo "<br>";
        
                    echo "<p>Pression Atmosphérique : " . $_SESSION['pression'] . " hPa</p>";
                    echo "<p>Humidité : " . $_SESSION['humidite'] . "</p>";
                    echo "<p>Visibilité : " . $_SESSION['visibilite'] . "</p>";
                    echo "<br>";
        
                    echo "<p>Vitesse du vent : " . $_SESSION['vitesse_vent'] . " m/s</p>";
                    echo "<p>Direction du vent : " . $_SESSION['direction_vent'] . " °</p>";
                    echo "<br>";
        
                    echo "<p>Pourcentage de nuages : " . $_SESSION['pourcentage_nuage'] . "% du ciel couvert</p>";
                    echo "<br>";
        
                    if ($_SESSION['pluie_1h'] != 0){
                        echo "<p>Pluie (1h) : " . $_SESSION['pluie_1h'] . " mm</p>";
                        echo "<p>Pluie (3h) : " . $_SESSION['pluie_3h'] . " mm</p>";
                        echo "<br>";
                    };
                    
                    if ($_SESSION['neige_1h'] != 0){
                        echo "<p>Neige (1h) : " . $_SESSION['neige_1h'] . " mm</p>";
                        echo "<p>Neige (3h) : " . $_SESSION['neige_3h'] . " mm</p>";
                        echo "<br>";
                    };
        
                    echo "<p>Heure de la dernière mesure : " . $_SESSION['heure'] . "<p>";
                    echo "<p>Heure du lever du soleil : " . $_SESSION['sunrise'] . "<p>";
                    echo "<p>Heure du coucher du soleil : " . $_SESSION['sunset'] . "<p>";
        
                    // Supprimez les données de la session pour éviter les affichages indésirables lors des rechargements de la page
                    session_unset();
                    session_destroy();
                }
                ?>
            </div>
        </div>

</body>
</html>

<?php

// modifie la couleur du fond en fonction de l'heure
function determineBackgroundColor($heureActuelle, $heureLeverSoleil, $heureCoucherSoleil) {
    $heureActuelle = DateTime::createFromFormat('H:i:s', $heureActuelle);
    $heureLeverSoleil = DateTime::createFromFormat('H:i:s', $heureLeverSoleil);
    $heureCoucherSoleil = DateTime::createFromFormat('H:i:s', $heureCoucherSoleil);
  
    $heureLeverSoleil->modify('+1 hour');
    $heureCoucherSoleil->modify('-1 hour');
  
    if ($heureActuelle > $heureLeverSoleil && $heureActuelle < $heureCoucherSoleil) {
        return 'daytime';
    } elseif ($heureActuelle < $heureLeverSoleil || $heureActuelle > $heureCoucherSoleil) {
        return 'nighttime';
    } else {
        return 'sunrise-sunset';
    }
}
  
  

?>
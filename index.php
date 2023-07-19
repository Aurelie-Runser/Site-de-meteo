<?php
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ville = $_POST['ville'];
    $apiKey = "af0bed8924751e07bce0f22544b547e7";

    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($ville) . "&lang=fr&appid=" . $apiKey;

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

        // Récupération de la pluie et neige s'il n'y en a pas, mettre la valeure à 0
        $pluie_1h = $data->rain->{"1h"} ?? 0;
        $pluie_3h = $data->rain->{"3h"} ?? 0;

        $neige_1h = $data->snow->{"1h"} ?? 0;
        $neige_3h = $data->snow->{"3h"} ?? 0;

        // Récupération du pays de la ville
        $pays = $data->sys->country;
        
        // Définition du fuseau horaire par défaut
        date_default_timezone_set('UTC');
        
        // Récupération du fuseau horaire pour la ville
        $timezoneOffset = $data->timezone;

        // Conversion de l'heure actuelle en heure locale
        $heureLocale = date('H:i:s', time() + $timezoneOffset);
        
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
        $_SESSION['sunrise'] = $sunriseLocale;
        $_SESSION['sunset'] = $sunsetLocale;
        $_SESSION['pays'] = $pays;
        $_SESSION['heure'] = $heureLocale;
    } else {
        $message = "Désolé, la ville que vous recherchez ne figure pas dans la base de données. <br> Vérifiez son orthographe et pensez aux tirets.";
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
<body class="<?php echo (isset($_SESSION['heure']) && isset($_SESSION['sunrise']) && isset($_SESSION['sunset'])) ?
            determineBackgroundColor($_SESSION['heure'], $_SESSION['sunrise'], $_SESSION['sunset']) : ''; ?>">
        
    <!-- barre pour rechercher une ville -->
    <form method="post" action="">
        <label for="villeInput" hidden>Ville</label>
        <input type="text" name="ville" id="villeInput" placeholder="Paris, FR / Londres, GB..." required>
        
        <button type="submit" hidden>
            Rechercher
        </button>
    </form>

    <!-- titre qui change en fonction de la ville -->
    <?php 
        if (isset($_SESSION['name'])){
            echo "<h1>Météo de <span class='titre_ville'>" . $_SESSION['name'] . "</span>, " .  $_SESSION['pays'] . "</h1>";
        } else {
            echo "<h1>Ma Météo</h1>";
        }
    ?>
    

    <div class="resultat">
        <?php
            if (!empty($message)) {
                echo "<div class='resultat_msg'>";
                echo "<img class='msg_icon' src='public/question.svg' alt='icon d'un point d'intérogation'/>";
                echo "<p class='msg_txt'>$message</p>";
                echo "</div>";

            } elseif (isset($_SESSION['name'])) {

                // 1er zone : température avec grand icon
                echo "<div class='temperature'>";
                    echo "<img src='public/sun.svg' alt='icon du vent'>";
                
                    echo "<div class='temp_txt'>";
                        echo "<p class='txt_temperature'>" . $_SESSION['temperature'] . "°C</p>";
                        echo "<p class='txt_ressentie'>ressentie " . $_SESSION['temp_ressentitC'] . "°C</p>";
                        echo "<p class='txt_minmax'>" . $_SESSION['temperature_min'] . "°C / " . $_SESSION['temperature_max'] . "°C</p>";
                        echo "<p class='txt_heure'>" . $_SESSION['heure'] . "<p>";
                        echo "<p class='txt_nuage'>" . $_SESSION['pourcentage_nuage'] . "% du ciel couvert</p>";
                    echo "</div>";  
                echo "</div>";  
                        
                echo "<div class='resultat_tableau'>";

                    // 2ème zone : pression + humidite + visibilité
                    echo "<div class='infos'>";
                        echo "<div class='info_pression'>";
                            echo "<p>pression</p>";

                            echo "<svg class='pression_icon'>";   
                                echo "<use xlink:href='public/pressure.svg#pressure' alt='icon de la pression atatmosphérique'/>";
                            echo "</svg>";

                            echo "<p>" . $_SESSION['pression'] . " hPa</p>";
                        echo "</div>";

                        echo "<span></span>";
                        
                        echo "<div class='info_humidity'>";
                            echo "<p>humidité</p>";

                            echo "<svg class='humidity_icon'>";   
                                echo "<use xlink:href='public/humidity.svg#humidity' alt='icon de l'humidité'/>";
                            echo "</svg>";  

                            echo "<p>" . $_SESSION['humidite'] . " g/m3</p>";
                        echo "</div>";

                        echo "<span></span>";

                        echo "<div class='info_visibility'>";
                            echo "<p>visibilité</p>";
                            
                            echo "<svg class='visibility_icon'>";   
                                echo "<use xlink:href='public/visibility.svg#visibility' alt='icon de la visibilité'/>";
                            echo "</svg>";  

                            echo "<p>" . $_SESSION['visibilite'] . " m</p>";
                        echo "</div>";    
                    echo "</div>";
                                                 
                    // 3ème zone : vent
                    echo "<div class='wind'>";
                        echo "<img src='public/wind.svg' alt='icon du vent'>";

                        echo "<div>";
                            echo "<p class='wind_speed'><span>vitesse du vent : </span>" . $_SESSION['vitesse_vent'] . " m/s</p>";
                            echo "<p class='wind_deg'><span>direction du vent : </span>" . $_SESSION['direction_vent'] . " °</p>";
                        echo "</div>";
                    echo "</div>";                        
                    
                    // 4ème zone : heure de lever et coucher du soleil
                    echo "<div class='sunrise-set'>";
                        echo "<div class='sunrise'>";
                            echo "<img src='public/sunrise.svg' alt='icon du levé du soleil'>";
                            echo "<p>heure du lever du soleil aujourd'hui : " . $_SESSION['sunrise'] . "<p>";
                        echo "</div>";
                        
                        echo "<div class='sunset'>";
                            echo "<img src='public/sunset.svg' alt='icon du couché du soleil'>";
                            echo "<p>heure du coucher du soleil aujourd'hui : " . $_SESSION['sunset'] . "<p>";
                        echo "</div>";
                    echo "</div>";
                    
                    // si != 0, afficher la pluie
                    // if ($_SESSION['pluie_1h'] != 0){
                        echo "<div class='rain'>";
                            echo "<p class='rain_1h'>pluie tombées en 1 heure : " . $_SESSION['pluie_1h'] . " mm</p>";
                            echo "<p class='rain_3h'>pluie tombées en 3 heures : " . $_SESSION['pluie_3h'] . " mm</p>";
                    //     echo "</div>";
                    // };
                    
                    // si != 0, afficher la neige
                    // if ($_SESSION['neige_1h'] != 0){
                        echo "<div class='snow'>";
                            echo "<p class='snow_1h'>neige tombées en 1 heure : " . $_SESSION['neige_1h'] . " mm</p>";
                            echo "<p class='snow_3h'>neige tombées en 3 heures : " . $_SESSION['neige_3h'] . " mm</p>";
                        echo "</div>";
                    // };

                echo "</div>";


                    // Supprimez les données de la session pour éviter les affichages indésirables lors des rechargements de la page
                    session_unset();
                    session_destroy();
                }
            ?>
        </div>
                
</body>
</html>

<?php

// modifie la couleur du fond en fonction de l'heure
function determineBackgroundColor($heureActuelle, $heureLeverSoleil, $heureCoucherSoleil) {
    // formatage des heures pour pouvoir les manipuler
    $heureActuelle = DateTime::createFromFormat('H:i:s', $heureActuelle);
    $heureLeverSoleil = DateTime::createFromFormat('H:i:s', $heureLeverSoleil);
    $heureCoucherSoleil = DateTime::createFromFormat('H:i:s', $heureCoucherSoleil);
  
    // création de la plage horaire pendant laquelle le soleil se lève
    $heureLeverSoleilStart = clone $heureLeverSoleil;
    $heureLeverSoleilStart->modify('-1 hour');
    $heureLeverSoleilEnd = clone $heureLeverSoleil;
    $heureLeverSoleilEnd->modify('+1 hour');
    
    // création de la plage horaire pendant laquelle le soleil se couche
    $heureCoucherSoleilStart = clone $heureCoucherSoleil;
    $heureCoucherSoleilStart->modify('-1 hour');
    $heureCoucherSoleilEnd = clone $heureCoucherSoleil;
    $heureCoucherSoleilEnd->modify('+1 hour');
  
    // affection de la couleur du fonc en fonction de l'heure
    if ( $heureLeverSoleilEnd < $heureActuelle && $heureActuelle < $heureCoucherSoleilStart) {
        return 'daytime';
    } elseif ($heureActuelle < $heureLeverSoleilStart || $heureCoucherSoleilEnd < $heureActuelle) {
        return 'nighttime';
    } else {
        return 'sunrise-sunset';
    }
}
  
  

?>
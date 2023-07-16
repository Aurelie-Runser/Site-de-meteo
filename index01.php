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
    $weatherData = json_decode($response);
    
    // Vérification de la réussite de la conversion JSON
    if ($weatherData === null) {
        die("Erreur de décodage JSON");
    }
    
    // Vérifier si la requête a retourné des données
    if ($weatherData && isset($weatherData->name)) {
        // Récupération des données de la réponse JSON
        $name = $weatherData->name;
        $temps = $weatherData->weather[0]->main;
        $description = $weatherData->weather[0]->description;
        
        // Récupération et conversion de la température
        $temperatureK = $weatherData->main->temp;
        $temperatureC = $temperatureK - 273.15;
        
        // Enregistrement des données dans la session
        $_SESSION['weatherData'] = $weatherData;
        $_SESSION['name'] = $name;
        $_SESSION['temps'] = $temps;
        $_SESSION['description'] = $description;
        $_SESSION['temperature'] = $temperatureC;
    } else {
        $message = "Désolé, la ville que vous recherchez ne figure pas dans la base de données.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon site météo</title>
    <link href="style.css" rel="stylesheet">

</head>
<body>
    <h1>Choisissez une ville :</h1>

    <hr/>

    <form method="post" action="">
        <label for="villeInput">Ville :</label>
        <input type="text" name="ville" id="villeInput" required>
        <button type="submit">Afficher la météo</button>
    </form>

    <div id="resultat">
        <?php
            if (!empty($message)) {
                echo "<p>$message</p>";
            } 
            elseif (isset($_SESSION['weatherData'])) {
                $weatherData = $_SESSION['weatherData'];
                $name = $_SESSION['name'];
                $temps = $_SESSION['temps'];
                $description = $_SESSION['description'];
                $temperatureC = $_SESSION['temperature'];

                // Affichez les données de la météo ici
                echo "Ville : $name<br>";
                echo "Temps : $temps<br>";
                echo "Description : $description<br>";
                echo "Température : $temperatureC °C<br>";

                // Supprimez les données de la session pour éviter les affichages indésirables lors des rechargements de la page
                unset($_SESSION['weatherData']);
                unset($_SESSION['name']);
                unset($_SESSION['temps']);
                unset($_SESSION['description']);
                unset($_SESSION['temperature']);
            }
        ?>
    </div>
</body>
</html>

<?php
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ville = $_POST['ville'];
    $apiKey = "af0bed8924751e07bce0f22544b547e7";

    $url = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($ville) . "&appid=" . $apiKey;

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
    if ($data && isset($data->city->name)) {
        // Récupération des données de la réponse JSON
        $name = $data->city->name;
        
        // Parcourir les prévisions météorologiques pour chaque période
        foreach ($data->list as $forecast) {
            // Récupérer les données spécifiques pour chaque période
            $temps = $forecast->weather[0]->main;
            $description = $forecast->weather[0]->description;
            
            // Récupération et conversion de la température
            $temperatureK = $forecast->main->temp;
            $temperatureC = $temperatureK - 273.15;

            $heure = $forecast->dt_txt;
            
            // Afficher les données de chaque périod
            echo "Temps : $temps<br>";
            echo "Description : $description<br>";
            echo "Température : $temperatureC °C<br>";
            echo "Date : $heure<br>";
            
            // Afficher une séparation entre chaque période
            echo "<hr>";
        }        

    } else {
        echo "Désolé, la ville que vous recherchez ne figure pas dans la base de données.";
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
    <h1>Choisissez une ville</h1>

    <hr/>

    <form method="post" action="">
        <label for="villeInput">Ville :</label>
        <input type="text" name="ville" id="villeInput" required>
        <button type="submit">Afficher la météo</button>
    </form>

    <div id="resultat">
    </div>
</body>
</html>

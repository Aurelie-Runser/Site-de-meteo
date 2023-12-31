<?php

// initialisation du message d'erreur
$message = "";

// lancement de la recherche de données dans l'api OpenWeatherMap si le champs est rempli
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation de l'entrée ville
    if (isset($_POST['ville']) && !empty($_POST['ville'])) {
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

            // Affichage du nom de la ville
            echo "Ville : $name<br>";

            // Parcourir les prévisions météorologiques pour chaque période
            foreach ($data->list as $forecast) {
                // Récupérer les données spécifiques pour chaque période
                $temps = $forecast->weather[0]->main;
                $description = $forecast->weather[0]->description;

                // Récupération et conversion de la température
                $temperatureK = $forecast->main->temp;
                $temperatureC = $temperatureK - 273.15;

                $heure = $forecast->dt_txt;

                // Affichage des données de chaque période
                echo "Temps : $temps<br>";
                echo "Description : $description<br>";
                echo "Température : $temperatureC °C<br>";
                echo "Date : $heure<br>";

                // Affichage d'une séparation entre chaque période
                echo "<hr>";
            }

        } else {
            echo "Désolé, la ville que vous recherchez ne figure pas dans la base de données.";
        }
    } else {
        echo "Veuillez entrer une ville valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon site météo</title>
    <!-- <link href="style.css" rel="stylesheet"> -->
    <link href="newstyle.css" rel="stylesheet">

</head>
<body>

    <div class="formulaire">
        <h1>Choisissez une ville</h1>
    
        <hr/>
    
        <form method="post" action="">
            <label for="villeInput">Ville :</label>
            <input type="text" name="ville" id="villeInput" required>
            <button type="submit">Afficher la météo</button>
        </form>
    </div>

</body>
</html>

<?php

namespace App\Service;

class ValidationService
{
    /**
     * Vérifie que toutes les valeurs du tableau fourni sont renseignées
     */
    public function checkArrayDoesNotContainsEmptyValues(array $arr)
    {
        foreach ($arr as $value) {
            if (empty(trim($value))) {
                return false;
            }
        }

        return true;
    }

    public function checkUploadedMusic(): array
    {
        $errors = [];

        //const, idéal pour modif la taille sans changer chaque ligne
        define('MAX_SIZE_FILE', 1000000);

        //----------------------------------------------------------------------------//
        //récup. le chemin du dossier pour y stocker les fichiers uploadés
        //puis crée un tableau avec les seuls formats autorisés
        //+ récup. l'extension du fichier pour test à venir.
        $arrExtensionsOK = ['jpg','webp','png'];
        $extension = strtolower(pathinfo($_FILES['image-musique']['name'], PATHINFO_EXTENSION));

        //premier test : voir si l'extension du fichier est correct
        if (!in_array($extension, $arrExtensionsOK)) {
            array_push($errors, 'Veuillez sélectionner un fichier au bon format(jpg, png, webp).');
        }

        //deuxième test: voir si la taille ne dépasse pas la taille max. autorisée
        if (file_exists($_FILES['image-musique']['tmp_name'])) {
            if (filesize($_FILES['image-musique']['tmp_name']) > MAX_SIZE_FILE) {
                array_push($errors, 'Votre fichier dépasse la taille maximale (1Mo).');
            }
        }

        //Récupère en chaîne de caractères le nom de l'host,
        //nécéssaire pour des test plus tard
        if (parse_url($_POST['source'], PHP_URL_HOST) !== 'www.youtube.com') {
            array_push($errors, "Ceci n'est pas un lien Youtube, veuillez réessayez.");
        }

        return $errors;
    }
}

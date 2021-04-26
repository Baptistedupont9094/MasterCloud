<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use App\Model\PlaylistManager;

class MyPlaylistController extends AbstractController
{
    /**
     * Affiche page Mon compte
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */

    public function create()
    {
        session_start();
        // if (!isset($_SESSION['user'])) {
            //     header('location: /');
            // }

        $errors = [];

        //si le formulaire est envoyé par post
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            //const, idéal pour modif la taille sans changer chaque ligne
            define('MAX_SIZE_FILE', 1000000);

        //----------------------------------------------------------------------------//

            //récup. le chemin du dossier pour y stocker les fichiers uploadés
            //puis crée un tableau avec les seuls formats autorisés
            //+ récup. l'extension du fichier pour test à venir.
            $dirPath = 'assets/upload/playlist/';

            $arrExtensionsOK = ['jpg','webp','png'];

            $extension = pathinfo($_FILES['image-playlist']['name'], PATHINFO_EXTENSION);

        //----------------------------------------------------------------------------//


            //var qui contient le futur chemin du fichier à uploader
            $filePath = $dirPath . uniqid() . ".$extension";

            //premier test : voir si l'extension du fichier est correct
            if (!in_array($extension, $arrExtensionsOK)) {
                array_push($errors, 'Veuillez sélectionner un fichier au bon format(jpg, png, webp).');
            }

            //deuxième test: voir si la taille ne dépasse pas la taille max. autorisée
            if (file_exists($_FILES['image-playlist']['tmp_name'])) {
                if (filesize($_FILES['image-playlist']['tmp_name']) > MAX_SIZE_FILE) {
                    array_push($errors, 'Votre fichier dépasse la taille maximale (1Mo).');
                }
            }


            //Si aucun message d'erreur, le fichier peut être uploadé
            if (empty($errors)) {
                //on récupère le chemin du fichier pour le garder en dehors du scope
                $_SESSION['file'] = $filePath;
                $playlistManager = new PlaylistManager();
                
                //Si la playlist est privée, renvoie true
                if ($_POST['est-privee'] === 'privee') {
                    $playlistManager->insert([
                        'nom' => trim($_POST['nom-playlist']),
                        'image' => trim($filePath),
                        'est_privee' => true,
                    ]);
                    move_uploaded_file($_FILES['image-playlist']['tmp_name'], $filePath);
                }
                //Si la playlist est publique, renvoie false
                else {
                    $playlistManager->insert([
                        'nom' => trim($_POST['nom-playlist']),
                        'image' => trim($filePath),
                        'est_privee' => false,
                    ]);

                    //Le fichier est uploadé dans le dossier /assets/upload/playlist
                    move_uploaded_file($_FILES['image-playlist']['tmp_name'], $filePath);
                }
                $playlists = $playlistManager->selectAll();
                header('Location: /myAccount/index');
            } else {
                return $this->twig->render('Playlist/create.html.twig', ['errors' => $errors]);
            }
        }
        return $this->twig->render('MyPlaylist/create.html.twig');
    }

    public function show($id)
    {
        $id = (int)$_GET['id'];
        $playlistManager = new PlaylistManager();
        $playlist = $playlistManager->selectOneById($id);

        return $this->twig->render('MyPlaylist/show.html.twig', ['playlist' => $playlist]);
    }
}


<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use App\Model\PlaylistManager;
use App\Model\MusicManager;

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

        if (!isset($_SESSION['user'])) {
                header('location: /');
            }

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

                if ($_POST['est-privee'] === 'privee') {
                    //Si la playlist est privée, renvoie true
                    $playlistManager->insert([
                        'nom' => trim($_POST['nom-playlist']),
                        'image' => trim($filePath),
                        'est_privee' => true,
                        'utilisateur_id' => $_SESSION['user']['id']
                    ]);
                    move_uploaded_file($_FILES['image-playlist']['tmp_name'], $filePath);
                } else {
                    //Si la playlist est publique, renvoie false
                    $playlistManager->insert([
                        'nom' => trim($_POST['nom-playlist']),
                        'image' => trim($filePath),
                        'est_privee' => false,
                        'utilisateur_id' => $_SESSION['user']['id']
                    ]);

                    //Le fichier est uploadé dans le dossier /assets/upload/playlist
                    move_uploaded_file($_FILES['image-playlist']['tmp_name'], $filePath);
                }
                header('Location: /myAccount/index');
            } else {
                return $this->twig->render('MyPlaylist/create.html.twig', ['errors' => $errors]);
            }
        }
        return $this->twig->render('MyPlaylist/create.html.twig');
    }

    public function show($id)
    {
        session_start();

        $id = (int)$_GET['id'];
        //conserve l'id, pour pouvoir revenir en arrière après
        //soumission de formulaire.
        $_SESSION['id-playlist'] = $id;

        $playlistManager = new PlaylistManager();
        $playlist = $playlistManager->selectOneById($id);

        $musicManager = new MusicManager();
        $musics = $musicManager->selectAllMusicsbyPlaylistID($id);


        return $this->twig->render('MyPlaylist/show.html.twig', ['playlist' => $playlist, 'listeMusiques' => $musics]);
    }

    public function addmusic()
    {
        session_start();

 
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //const, idéal pour modif la taille sans changer chaque ligne
            define('MAX_SIZE_FILE', 1000000);

        //----------------------------------------------------------------------------//

            //récup. le chemin du dossier pour y stocker les fichiers uploadés
            //puis crée un tableau avec les seuls formats autorisés
            //+ récup. l'extension du fichier pour test à venir.
            $dirPath = 'assets/upload/musique/';

            $arrExtensionsOK = ['jpg','webp','png'];

            $extension = pathinfo($_FILES['image-musique']['name'], PATHINFO_EXTENSION);

        //----------------------------------------------------------------------------//


            //var qui contient le futur chemin du fichier à uploader
            $filePath = $dirPath . uniqid() . ".$extension";

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
            $urlHost = parse_url($_POST['url'], PHP_URL_HOST);


            if ($urlHost !== 'www.youtube.com') {
                array_push($errors, "Ceci n'est pas un lien Youtube, veuillez réessayez.");
            }

            if (empty($errors)) {
                $queriesFromYT = [];
                //Récupère dans un tableau les query string de l'url Youtube
                parse_str(parse_url($_POST['url'], PHP_URL_QUERY), $queriesFromYT);

                $musicManager = new MusicManager();
                $musicManager->insert(
                    [
                        'nom' => trim($_POST['nom-musique']),
                        'artiste' => trim($_POST['artiste']),
                        'album' => trim($_POST['album']),
                        'genre' => trim($_POST['genre']),
                        'image' => trim($filePath),
                        'source' => trim($queriesFromYT['v']),
                        'playlist_id' => $_SESSION['id-playlist']
                    ]
                );
                //Le fichier est uploadé dans le dossier /assets/upload/playlist
                move_uploaded_file($_FILES['image-musique']['tmp_name'], $filePath);
                header('Location: /myPlaylist/show/?id=' . $_SESSION['id-playlist']);
            } else {
                return $this->twig->render('MyPlaylist/addmusic.html.twig', ['errors' => $errors]);
            }
        }
        return $this->twig->render('MyPlaylist/addmusic.html.twig');
    }

    public function deletePlaylist($id)
    {
        session_start();

        $playlistManager = new PlaylistManager();

        $id = $_SESSION['id-playlist'];

        $playlistManager->delete($id);

        header('Location: /myAccount/index');
    }

    public function deleteMusic($id)
    {
        session_start();

        $musicManager = new MusicManager();

        $id = $_GET['id'];

        $musicManager->delete($id);

        header('Location: /myPlaylist/show/?id=' . $_SESSION['id-playlist']);
    }
}

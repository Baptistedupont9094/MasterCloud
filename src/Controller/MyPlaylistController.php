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
                $playlistManager->insert([
                    'nom' => trim($_POST['nom-playlist']),
                    'image' => trim($filePath),
                    //Si la playlist est privée, renvoie true (1 en SQL), sinon false (0)
                    'est_privee' => ($_POST['est-privee'] === 'privee' ? true : false),
                    'utilisateur_id' => $_SESSION['user']['id']
                    ]);
                //Le fichier est uploadé dans le dossier /assets/upload/playlist
                move_uploaded_file($_FILES['image-playlist']['tmp_name'], $filePath);

                header('Location: /myAccount/index');
            } else {
                return $this->twig->render('MyPlaylist/create.html.twig', ['errors' => $errors]);
            }
        }
        return $this->twig->render('MyPlaylist/create.html.twig');
    }

    public function show($id)
    {
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
        $errors = [];
        $music = $_POST;
        $music['playlist_id'] = isset($_REQUEST['playlist_id']) ? $_REQUEST['playlist_id'] : "";

        if (empty($music['playlist_id'])) {
            header('Location: /home');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $queriesFromYT = [];
            parse_str(parse_url(isset($music['source']) ? $music['source'] : '', PHP_URL_QUERY), $queriesFromYT);
            $music['source'] = $queriesFromYT['v'];

            if (
                empty($music['nom']) ||
                empty($music['artiste']) ||
                empty($music['album']) ||
                empty($music['genre']) ||
                empty($music['source'])
            ) {
                $errors[] = 'Vous devez remplir tous les champs';
            } else {
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
                if (parse_url($_POST['source'], PHP_URL_HOST) !== 'www.youtube.com') {
                    array_push($errors, "Ceci n'est pas un lien Youtube, veuillez réessayez.");
                }

                if (empty($errors)) {
                    $music['image'] = $filePath;

                    $musicManager = new MusicManager();
                    $musicManager->insert($music);

                    //Le fichier est uploadé dans le dossier /assets/upload/playlist
                    move_uploaded_file($_FILES['image-musique']['tmp_name'], $filePath);

                    header('Location: /myPlaylist/show/?id=' . $music['playlist_id']);
                }
            }
        }

        return $this->twig->render('MyPlaylist/addmusic.html.twig', [
            'music' => $music,
            'errors' => $errors
        ]);
    }

    public function deletePlaylist($id)
    {
        $playlistManager = new PlaylistManager();

        $id = $_SESSION['id-playlist'];

        $playlistManager->delete($id);

        header('Location: /myAccount/index');
    }

    public function deleteMusic($id)
    {
        $musicManager = new MusicManager();

        $id = $_GET['id'];

        $musicManager->delete($id);

        header('Location: /myPlaylist/show/?id=' . $_SESSION['id-playlist']);
    }

    public function edit()
    {
        $id = $_GET['id'];

        $playlistManager = new PlaylistManager();

        $playlistToEdit = $playlistManager->selectForUpdateByPlaylistId($id);

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
                $playlistToEdit['nom'] = trim($_POST['nom-playlist']);
                $playlistToEdit['image'] = trim($filePath);
                //Si la playlist est privée, renvoie true (1 en SQL), sinon false (0)
                $playlistToEdit['est_privee'] = ($_POST['est-privee'] === 'privee' ? true : false);

                $playlistManager->update($playlistToEdit);
                //Le fichier est uploadé dans le dossier /assets/upload/playlist
                move_uploaded_file($_FILES['image-playlist']['tmp_name'], $filePath);
                header('Location: /myPlaylist/show/?id=' . $_SESSION['id-playlist']);
            } else {
                return $this->twig->render('MyPlaylist/edit.html.twig', ['errors' => $errors]);
            }
        }
        return $this->twig->render('MyPlaylist/edit.html.twig', ['playlist' => $playlistToEdit]);
    }
}

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
    public function index()
    {
        session_start();
        // if (!isset($_SESSION['user'])) {
            //     header('location: /');
            // }


        //var pour récup. nom du fichier
        $file = "";


        //si le formulaire est envoyé par post
        if($_SERVER['REQUEST_METHOD'] === "POST")
        {
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
            if(!in_array($extension, $arrExtensionsOK))
            {
                echo $errors[] = "<p style='color : #FF0000'> Veuillez sélectionner un fichier au bon format (jpg, png, webp).</p>.";
            }

            //deuxième test: voir si la taille ne dépasse pas la taille max. autorisée
            if(file_exists($_FILES['image-playlist']['tmp_name']) && filesize($_FILES['image-playlist']['tmp_name']) > MAX_SIZE_FILE)
            {
                echo $errors[] = "<p style='color : #FF0000'>Votre fichier dépasse la taille maximale (1Mo).</p>";
            }


            //Si aucun message d'erreur, le fichier peut être uploadé
            if(empty($errors))
            {
                //on récupère le chemin du fichier pour le garder en dehors du scope
                $_SESSION['file'] = $filePath;
                $playlistManager = new PlaylistManager();
                if($_POST['est-privee'] === 'privee')
                {
                    $playlistManager->insert([
                        'nom' => trim($_POST['nom-playlist']),
                        'image' => trim($filePath),
                        'est_privee' => true,
                    ]);
                    move_uploaded_file($_FILES['image-playlist']['tmp_name'], $filePath);
                }
                else
                {
                    $playlistManager->insert([
                        'nom' => trim($_POST['nom-playlist']),
                        'image' => trim($filePath),
                        'est_privee' => false,
                    ]);
                    move_uploaded_file($_FILES['image-playlist']['tmp_name'], $filePath);
                }
                $playlists = $playlistManager->selectAll();
                return $this->twig->render('MyPlaylist/index.html.twig', ['playlistsTwig' => $playlists]);
            }
        }
        else
            {
                $playlistManager = new PlaylistManager();
                $playlists = $playlistManager->selectAll();
                return $this->twig->render('MyPlaylist/index.html.twig', ['playlistsTwig' => $playlists]);
            }
    }

        public function createPlaylist()
    {
        session_start();

        return $this->twig->render('MyPlaylist/createPlaylist.html.twig');
    }

    /**
     * Show informations for a specific item
     */
    public function show(int $id): string
    {
        $itemManager = new ItemManager();
        $item = $itemManager->selectOneById($id);

        return $this->twig->render('Item/show.html.twig', ['item' => $item]);
    }


    /**
     * Edit a specific item
     */
    public function edit(int $id): string
    {
        $itemManager = new ItemManager();
        $item = $itemManager->selectOneById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // clean $_POST data
            $item = array_map('trim', $_POST);

            // TODO validations (length, format...)

            // if validation is ok, update and redirection
            $itemManager->update($item);
            header('Location: /item/show/' . $id);
        }

        return $this->twig->render('Item/edit.html.twig', [
            'item' => $item,
        ]);
    }


    /**
     * Add a new item
     */
    public function add(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // clean $_POST data
            $item = array_map('trim', $_POST);

            // TODO validations (length, format...)

            // if validation is ok, insert and redirection
            $itemManager = new ItemManager();
            $id = $itemManager->insert($item);
            header('Location:/item/show/' . $id);
        }

        return $this->twig->render('Item/add.html.twig');
    }


    /**
     * Delete a specific item
     */
    public function delete(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $itemManager = new ItemManager();
            $itemManager->delete($id);
            header('Location:/item/index');
        }
    }
}


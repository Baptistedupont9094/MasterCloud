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
        if (!empty($_POST)) 
        {
            $playlistManager = new PlaylistManager();
            $playlistManager->insert([
                'nom_playlist' => trim($_POST['user']),
                'image' => trim($_POST['email']),
                'est_privee' => trim($passwordHashed),
            ]);
            $emailArray = $userManager->selectOneByEmail($_POST['email']);
            $emailArray['est_connecte'] = true;
            $_SESSION['user'] = $emailArray;
            header('Location: /');
        } else {
            header('Location: /login/index');
        }

        $playlistManager = new PlaylistManager();
        $playlists = $playlistsManager->selectAll();
        return $this->twig->render('MyPlaylist/index.html.twig');['playlistsTwig' => $playlists];
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


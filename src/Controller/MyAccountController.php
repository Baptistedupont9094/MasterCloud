<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use App\Model\PlaylistManager;

class MyAccountController extends AbstractController
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
        if (!isset($_SESSION['user'])) {
            header('Location: /');
        } else {
            $playlistManager = new PlaylistManager();
            $playlists = $playlistManager->selectAllPlaylistsbyUserID($_SESSION['user']['id']);
            return $this->twig->render('MyAccount/index.html.twig', ['playlistsTwig' => $playlists]);
        }

        return $this->twig->render('MyAccount/index.html.twig');
    }
}

<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use App\Model\PlaylistManager;
use App\Model\UserManager;

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
        if (!isset($_SESSION['user'])) {
            header('Location: /');
        } else {
            $playlistManager = new PlaylistManager();

            return $this->twig->render('MyAccount/index.html.twig', [
                'playlistsTwig' => $playlistManager->selectAllPlaylistsbyUserID($_SESSION['user']['id']),
                'playlists' => $playlistManager->selectAll()
            ]);
        }

        return $this->twig->render('MyAccount/index.html.twig');
    }
}

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

class HomeController extends AbstractController
{
    /**
     * Affiche page Home
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        $playlistManager = new PlaylistManager();
        $playlistsTop3 = $playlistManager->selectTop3Playlists();
        $this->twig->addGlobal('top3Playlists', $playlistManager->selectTop3Playlists());
        return $this->twig->render('Home/index.html.twig', ['playlistsTop3' => $playlistsTop3]);
    }
}

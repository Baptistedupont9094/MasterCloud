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

class MediaplayerController extends AbstractController
{
    /**
     * Affiche page Mon compte
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function playing()
    {
        $id = (int)$_GET['id'];

        $musicManager = new MusicManager();
        $musics = $musicManager->selectAllMusicsbyPlaylistID($id);

        return $this->twig->render('Components/mediaplayer.html.twig', ['listeMusiques' => $musics]);
    }
}

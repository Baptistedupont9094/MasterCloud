<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use App\Model\SearchManager;
use App\Model\PlaylistManager;
use App\Model\VoteManager;
use App\Service\AuthService;

class ExplorerController extends AbstractController
{
    /**
     * @var AuthService Service d'authentification
     */
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();

        $this->authService = new AuthService();
    }

    /**
     * Affiche page Explorer
     */
    public function index()
    {
        $searchManager = new SearchManager();

        if (!empty($_POST)) {
            $searchItem = $_POST['search'];
            $searchItem = strtolower($searchItem);
            $result = $searchManager->search($searchItem);

            return $this->twig->render('Explorer/index.html.twig', ["resultArray" => $result]);
        }

        $manager = new PlaylistManager();

        return $this->twig->render('Explorer/index.html.twig', [
            'myPlaylists' => $manager->selectAllByUser($this->authService->getUser()['id']),
            'playlists' => $manager->selectAll(),
        ]);
    }

    public function searchAjax()
    {
        $result = [];

        if (!empty($_POST)) {
            $searchManager = new SearchManager();
            $searchItem = $_POST['search'];
            $searchItem = strtolower($searchItem);
            $result = $searchManager->search($searchItem);
        }

        return $this->twig->render('Components/search-result.html.twig', [
            'searchResults' => $result,
        ]);
    }

    public function likes()
    {
        $voteData = false;

        if (isset($_GET['playlist_id'])) {
            $voteData = $this->vote((int) $_GET['playlist_id'], true);
        }

        header('Content-Type: application/json');
        return json_encode($voteData);
    }

    public function dislikes()
    {
        $voteData = false;

        if (isset($_GET['playlist_id'])) {
            $voteData = $this->vote((int) $_GET['playlist_id'], false);
        }

        header('Content-Type: application/json');
        return json_encode($voteData);
    }

    private function vote(int $playlistId, bool $like)
    {
        if (!$this->authService->isLogged()) {
            return false;
        }

        return (new VoteManager())->vote([
            'utilisateur_id' => $this->authService->getUser()['id'],
            'playlist_id' => $playlistId,
            'like' => $like,
        ]);
    }

    public function top10()
    {
        $manager = new PlaylistManager();

        return $this->twig->render('Explorer/top10.html.twig', [
            'top10Playlists' => $manager->selectTop(10),
            'myPlaylists' => $manager->selectAllByUser($this->authService->getUser()['id']),
        ]);
    }

    public function top3()
    {
        $manager = new PlaylistManager();

        return $this->twig->render('Explorer/top3.html.twig', [
            'top3Playlists' => $manager->selectTop(),
            'myPlaylists' => $manager->selectAllByUser($this->authService->getUser()['id']),
        ]);
    }

    public function myPlaylists()
    {
        $manager = new PlaylistManager();

        return $this->twig->render('Explorer/myplaylists.html.twig', [
            'myPlaylists' => $manager->selectAllByUser($this->authService->getUser()['id']),
        ]);
    }
}

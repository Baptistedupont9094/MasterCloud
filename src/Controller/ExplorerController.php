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
use App\Service\ValidationService;

class ExplorerController extends AbstractController
{
    /**
     * @var ValidationService Service de validation
     */
    private ValidationService $validationService;

    public function __construct()
    {
        parent::__construct();

        $this->validationService = new ValidationService();
    }

    /**
     * Affiche page Explorer
     */
    public function index()
    {
        $searchManager = new SearchManager();

    // var_dump((new PlaylistManager)->selectAll());exit;
        if (!empty($_POST)) {
            $searchItem = $_POST['search'];
            $searchItem = strtolower($searchItem);
            $result = $searchManager->search($searchItem);

            return $this->twig->render('Explorer/index.html.twig', ["resultArray" => $result]);
        }

        if (!isset($_SESSION['user'])) {
            header('location: /login/index');
        }

        return $this->twig->render('Explorer/index.html.twig', [
            'playlists' => (new PlaylistManager())->selectAll()
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
        $authService = new AuthService();

        if (!$authService->isLogged()) {
            return false;
        }

        return (new VoteManager())->vote([
            'utilisateur_id' => $authService->getUser()['id'],
            'playlist_id' => $playlistId,
            'like' => $like,
        ]);
    }

    public function top10()
    {
        return $this->twig->render('Explorer/top10.html.twig', [
            'top10Playlists' => (new PlaylistManager())->selectTop10Playlists()
        ]);
    }

    public function top3()
    {
        return $this->twig->render('Explorer/top3.html.twig', [
            'top3Playlists' => (new PlaylistManager())->selectTop3Playlists()
        ]);
    }

    public function myPlaylists()
    {
        return $this->twig->render('Explorer/myplaylists.html.twig', [
            'myPlaylists' => (new PlaylistManager())->selectAllPlaylistsbyUserID($_SESSION['user']['id'])
        ]);
    }
}

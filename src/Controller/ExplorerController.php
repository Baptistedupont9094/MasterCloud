<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use App\Model\SearchManager;

class ExplorerController extends AbstractController
{
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


        if (!isset($_SESSION['user'])) {
            header('location: /login/index');
        }

        return $this->twig->render('Explorer/index.html.twig');
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
}

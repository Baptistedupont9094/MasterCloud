<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

class ExplorerController extends AbstractController
{
    /**
     * Affiche page Explorer
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
            header('location: /login/index');
        }

        return $this->twig->render('Explorer/index.html.twig');
    }
}

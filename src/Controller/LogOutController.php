<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

class LogOutController extends AbstractController
{
    /**
     * Affiche page Mon compte
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */

    // Fonction pour déconnecter un utilisateur

    public function index()
    {
        if(isset($_SESSION["name"]))
        {
            session_start();
            session_unset();
            session_destroy();
            header('Location: /');
        }
        return $this->twig->render('LogOut/index.html.twig');
    }
}
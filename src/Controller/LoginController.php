<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use App\Model\UserManager;

class LoginController extends AbstractController
{
    /**
     * Affiche page Login/Signup
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        session_start();

        if (isset($_SESSION["user"])) {
            header('Location: /explorer/index');
        }
        define('EMAIL', 'seif@gmail.com');
        define('PASSWORD', 'test');

        if (!empty($_POST)) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $userManager = new UserManager();
            $emailArray = $userManager->selectOneByEmail($email);

            if ($email === $emailArray[0]['email'] && password_verify($password, $emailArray[0]['mot_de_passe'])) {
                $emailArray[0]['is_logged'] = true;
                $_SESSION['user'] = $emailArray;
                header('location: /explorer/index');
            }
        }
        return $this->twig->render('Login/index.html.twig');
    }
    public function register()
    {
        if ($_POST['password1'] === $_POST['repeatpassword']) {
            $userManager = new UserManager();
            $passwordHashed = password_hash($_POST['password1'], PASSWORD_BCRYPT);
            $userManager->insert([
                'nom' => trim($_POST['user']),
                'email' => trim($_POST['email']),
                'mot_de_passe' => trim($passwordHashed),
            ]);
            $emailArray = $userManager->selectOneByEmail($_POST['email']);
            $emailArray[0]['is_logged'] = true;
            $_SESSION['user'] = $emailArray;
            header('Location: /');
        } else {
            header('Location: /login/index');
        }
    }
}

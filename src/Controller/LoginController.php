<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use League\OAuth2\Client\Provider\Google;
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
        if (isset($_SESSION["user"])) {
            header('Location: /explorer/index');
        }

        $error = '';

        if (!empty($_POST)) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $userManager = new UserManager();
            $emailArray = $userManager->selectOneByEmail($email);

            if (
                !empty($emailArray) &&
                $email === $emailArray['email'] &&
                password_verify($password, $emailArray['mot_de_passe'])
            ) {
                $emailArray['est_connecte'] = true;
                $_SESSION['user'] = $emailArray;
                header('Location: /explorer/index');
            } else {
                $error = 'Identifiants incorrects';
            }
        }

        return $this->twig->render('Login/index.html.twig', [
            'error' => $error,
        ]);
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
            $emailArray['est_connecte'] = true;
            $_SESSION['user'] = $emailArray;
            header('Location: /');
        } else {
            header('Location: /login/index');
        }
    }
    public function loginDiscord()
    {
        $provider = new \Wohali\OAuth2\Client\Provider\Discord([
            'clientId' => '839433803223007232',
            'clientSecret' => 'IdRlJX2CFOogZda0-sZ_s6K_kUHmohRD',
            'redirectUri' => 'http://localhost:8000/login/loginDiscord'
        ]);

        if (!isset($_GET['code'])) {
            // Step 1. Get authorization code
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl);

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        } else {
            // Step 2. Get an access token using the provided authorization code
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Récupération du user sur discord
            $discordUser = $provider->getResourceOwner($token);

            // On vérifie si le user existe déjà sur mastercloud
            $userManager = new UserManager();
            $masterCloudUser = $userManager->selectOneByEmail($discordUser->getEmail());

            // Si le user n'existe pas, on le créé
            if (empty($masterCloudUser)) {
                $masterCloudUser = [
                    'nom' => $discordUser->getUsername(),
                    'email' => $discordUser->getEmail(),
                    'mot_de_passe' => uniqid(),
                ];

                // On récupère l'id de l'utilisateur fraichement créé pour l'ajouté au user en session
                $masterCloudUser['id'] = $userManager->insert($masterCloudUser);

                // On supprime le mot de passe du user en session
                unset($masterCloudUser['mot_de_passe']);
            }

            $_SESSION['user'] = $masterCloudUser;

            header('Location: /Explorer');
        }
    }
    public function loginGoogle()
    {

        $provider = new Google([
        'clientId'     => '193879673157-spj2riskhlp0dpn62bapno76hd958sr6.apps.googleusercontent.com',
        'clientSecret' => 'Ry5ZukyqNsVpl57hQB_LaItp',
        'redirectUri'  => 'http://localhost:8000/login/loginGoogle',
        ]);

        if (!empty($_GET['error'])) {
            // Got an error, probably user denied access
            exit('Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'));
        } elseif (empty($_GET['code'])) {
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl);
            exit;
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            // State is invalid, possible CSRF attack in progress
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        } else {
            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Récupération du user sur google
            $discordUser = $provider->getResourceOwner($token);

            // On vérifie si le user existe déjà sur mastercloud
            $userManager = new UserManager();
            $masterCloudUser = $userManager->selectOneByEmail($discordUser->getEmail());

            // Si le user n'existe pas, on le créé
            if (empty($masterCloudUser)) {
                $masterCloudUser = [
                    'nom' => $discordUser->getUsername(),
                    'email' => $discordUser->getEmail(),
                    'mot_de_passe' => uniqid(),
                ];

                // On récupère l'id de l'utilisateur fraichement créé pour l'ajouté au user en session
                $masterCloudUser['id'] = $userManager->insert($masterCloudUser);

                // On supprime le mot de passe du user en session
                unset($masterCloudUser['mot_de_passe']);
            }

            $_SESSION['user'] = $masterCloudUser;

            header('Location: /Explorer');
        }
    }
}

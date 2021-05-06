<?php

/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use GuzzleHttp\Client as GuzzleHttpClient;
use League\OAuth2\Client\Provider\Google;
use Wohali\OAuth2\Client\Provider\Discord;
use App\Model\UserManager;

/**
 * @SuppressWarnings(PHPMD.ErrorControlOperator) Suppression des erreurs PHPMD sur l'utilisation du @
 */
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
        $provider = new Discord([
            'clientId' => '839433803223007232',
            'clientSecret' => 'IdRlJX2CFOogZda0-sZ_s6K_kUHmohRD',
            'redirectUri'  => $this->getHostFullUrl() . '/login/loginDiscord',
        ]);

        $provider->setHttpClient(
            @new GuzzleHttpClient(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ))
        );

        if (!isset($_GET['code'])) {
            // Step 1. Get authorization code
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl);
        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            header('Location: /');
        } else {
            // Step 2. Get an access token using the provided authorization code
            /** @var \League\OAuth2\Client\Token\AccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Récupération du user sur discord
            $discordUser = $provider->getResourceOwner($token);

            // On vérifie si le user existe déjà sur mastercloud
            $userManager = new UserManager();

            /** @phpstan-ignore-next-line */
            $masterCloudUser = $userManager->selectOneByEmail($discordUser->getEmail());

            // Si le user n'existe pas, on le créé
            if (empty($masterCloudUser)) {
                $masterCloudUser = [
                    'nom' => $discordUser->getUsername(), /** @phpstan-ignore-line */
                    'email' => $discordUser->getEmail(), /** @phpstan-ignore-line */
                    'mot_de_passe' => uniqid(),
                ];

                // On récupère l'id de l'utilisateur fraichement créé pour l'ajouté au user en session
                $masterCloudUser['id'] = $userManager->insert($masterCloudUser);

                // On supprime le mot de passe du user en session
                unset($masterCloudUser['mot_de_passe']);
            }

            // Mise à jour de l'url de l'avatar
            $avatarData = [
                'https://cdn.discordapp.com/avatars/',
                $discordUser->getId(),
                '/',
                $discordUser->getAvatarHash(), /** @phpstan-ignore-line */
                '.png',
            ];

            $masterCloudUser['avatar'] = implode('', $avatarData);

            $_SESSION['user'] = $masterCloudUser;
            header('Location: /');
        }
    }

    public function loginGoogle()
    {
        $provider = new Google([
            'clientId'     => '193879673157-spj2riskhlp0dpn62bapno76hd958sr6.apps.googleusercontent.com',
            'clientSecret' => 'Ry5ZukyqNsVpl57hQB_LaItp',
            'redirectUri'  => $this->getHostFullUrl() . '/login/loginGoogle',
        ]);

        $provider->setHttpClient(
            @new GuzzleHttpClient(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ))
        );

        if (!empty($_GET['error'])) {
            // Got an error, probably user denied access
            header('Location: /');
        } elseif (
            empty($_GET['code']) ||
            empty($_GET['state']) ||
            $_GET['state'] !== $_SESSION['oauth2state']
        ) {
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl);
        } else {
            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Récupération du user sur discord
            /** @phpstan-ignore-next-line */
            $discordUser = $provider->getResourceOwner($token);

            // On vérifie si le user existe déjà sur mastercloud
            $userManager = new UserManager();

            /** @phpstan-ignore-next-line */
            $masterCloudUser = $userManager->selectOneByEmail($discordUser->getEmail());

            // Si le user n'existe pas, on le créé
            if (empty($masterCloudUser)) {
                $masterCloudUser = [
                    'nom' => $discordUser->getName(), /** @phpstan-ignore-line */
                    'email' => $discordUser->getEmail(), /** @phpstan-ignore-line */
                    'mot_de_passe' => uniqid(),
                ];

                // On récupère l'id de l'utilisateur fraichement créé pour l'ajouté au user en session
                $masterCloudUser['id'] = $userManager->insert($masterCloudUser);

                // On supprime le mot de passe du user en session
                unset($masterCloudUser['mot_de_passe']);
            }

            // Mise à jour de l'url de l'avatar
            $masterCloudUser['avatar'] = $discordUser->getAvatar(); /** @phpstan-ignore-line */

            $_SESSION['user'] = $masterCloudUser;

            header('Location: /');
        }
    }

    /**
     * Renvoi l'URL complète du serveur.
     *
     * Ex : http://localhost:8000
     * Ex : https://monserveur.com
     */
    private function getHostFullUrl()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $url .=  "://" . $_SERVER['HTTP_HOST'];

        return $url;
    }
}

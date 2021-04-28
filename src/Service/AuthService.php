<?php

namespace App\Service;

class AuthService
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            session_regenerate_id();
        }
    }

    public function isLogged(): bool
    {
        return isset($_SESSION['user']);
    }

    public function getUser(): ?array
    {
        return $this->isLogged() ? $_SESSION['user'] : null;
    }
}

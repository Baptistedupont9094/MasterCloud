<?php

namespace App\Model;

class UserManager extends AbstractManager
{
    public const TABLE = 'utilisateur';

    public function insert(array $item): int
    {
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE .
        "(nom,mot_de_passe,email) VALUES (:nom,:mot_de_passe,:email)");
        $statement->bindValue(':nom', $item['nom'], \PDO::PARAM_STR);
        $statement->bindValue(':mot_de_passe', $item['mot_de_passe'], \PDO::PARAM_STR);
        $statement->bindValue(':email', $item['email'], \PDO::PARAM_STR);

        $statement->execute();
        return (int)$this->pdo->lastInsertId();
    }
    /**
     * Update item in database
     */
    public function update(array $item): bool
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE .
        " SET 'email' = :email WHERE id=:id");
        $statement->bindValue('email', $item['email']);
        $statement->bindValue('id', $item['id'], \PDO::PARAM_INT);

        return $statement->execute();
    }

    public function selectOneByEmail(string $email)
    {
        // prepared request
        $statement = $this->pdo->prepare("SELECT * FROM " . static::TABLE . " WHERE email=:email");
        $statement->bindValue('email', $email, \PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC) ;
    }
}

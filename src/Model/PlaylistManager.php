<?php

namespace App\Model;

class PlaylistManager extends AbstractManager
{
    public const TABLE = 'playlist';

    /**
     * Insert new item in database
     */
    public function insert(array $playlist): int
    {
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE .
        "(nom, image, est_privee, utilisateur_id) VALUES (:nom,:image,:est_privee, :utilisateur_id)");
        $statement->bindValue(':nom', $playlist['nom'], \PDO::PARAM_STR);
        $statement->bindValue(':image', $playlist['image'], \PDO::PARAM_STR);
        $statement->bindValue(':est_privee', $playlist['est_privee'], \PDO::PARAM_BOOL);
        $statement->bindValue(':utilisateur_id', $playlist['utilisateur_id'], \PDO::PARAM_INT);

        $statement->execute();
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update item in database
     */
    public function update(array $item): bool
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE . " SET `title` = :title WHERE id=:id");
        $statement->bindValue('id', $item['id'], \PDO::PARAM_INT);
        $statement->bindValue('title', $item['title'], \PDO::PARAM_STR);

        return $statement->execute();
    }

    //Récupère toutes les playlists d'un utilisateur dans la BDD
    public function selectAllPlaylistsbyUserID(int $utilisateurId): array
    {
        $query = 'SELECT * FROM ' . static::TABLE . ' WHERE utilisateur_id=' . $utilisateurId . ';';

        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }
}

<?php

namespace App\Model;

class PlaylistManager extends AbstractManager
{
    public const TABLE = 'playlist';

    /**
     * Get all row from database.
     */
    public function selectAll(string $orderBy = '', string $direction = 'ASC'): array
    {
        $query = '
            SELECT 
                t.*, 
                u.nom as username, 
                (SELECT COUNT(playlist_id) FROM votes WHERE playlist_id = t.id AND `like` = 1) as likes,
                (SELECT COUNT(playlist_id) FROM votes WHERE playlist_id = t.id AND `like` = 0) as dislikes 
            FROM ' . static::TABLE . ' t 
            LEFT JOIN utilisateur u ON t.utilisateur_id = u.id
        ';

        if ($orderBy) {
            $query .= ' ORDER BY ' . $orderBy . ' ' . $direction;
        }

        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

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

    public function selectForUpdateByPlaylistId(int $id)
    {
        $statement = $this->pdo->prepare("SELECT nom, image, est_privee, id FROM " . static::TABLE .
        " WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    /**
     * Update item in database
     */
    public function update(array $playlist): bool
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE . " SET `nom` = :nom, 
        `image` = :image, `est_privee` = :est_privee WHERE id=:id");
        $statement->bindValue('id', $playlist['id'], \PDO::PARAM_INT);
        $statement->bindValue(':nom', $playlist['nom'], \PDO::PARAM_STR);
        $statement->bindValue(':image', $playlist['image'], \PDO::PARAM_STR);
        $statement->bindValue(':est_privee', $playlist['est_privee'], \PDO::PARAM_BOOL);

        return $statement->execute();
    }

    //Récupère toutes les playlists d'un utilisateur dans la BDD
    public function selectAllPlaylistsbyUserID(int $utilisateurId): array
    {
        $query = '
            SELECT playlist.*, nombre_likes - nombre_dislikes as ratio, utilisateur.nom as username
            FROM ' . static::TABLE . ' 
            INNER JOIN utilisateur ON playlist.utilisateur_id = utilisateur.id
            WHERE playlist.utilisateur_id = :userId
            ORDER BY ratio DESC';
        ;

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userId', $utilisateurId, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    //Récupère le nombre de playlists d'un utilisateur dans la BDD
    public function selectNbPlaylistsbyUserID(int $utilisateurId): array
    {
        $query = 'SELECT count(id) as somme FROM ' . static::TABLE . ' WHERE utilisateur_id=' . $utilisateurId . ';';

        return $this->pdo->query($query)->fetch();
    }

    //Récupère le top 3 des playlists (publiques) de la communauté MasterCloud
    public function selectTop(int $count = 3): array
    {
        $query = '
            SELECT playlist.*, nombre_likes - nombre_dislikes as ratio, utilisateur.nom as username
            FROM ' . static::TABLE . ' 
            RIGHT JOIN utilisateur ON playlist.utilisateur_id = utilisateur.id
            WHERE est_privee IS FALSE
            HAVING ratio > 0 
            ORDER BY ratio DESC LIMIT ' . $count;
        ;

        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function likes(int $id)
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE .
        " SET nombre_likes = nombre_likes + 1 WHERE id = :id");

        $statement->bindValue(':id', $id, \PDO::PARAM_INT);

        return $statement->execute();
    }

    public function dislikes(int $id)
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE .
        " SET nombre_dislikes = nombre_dislikes + 1 WHERE id = :id");

        $statement->bindValue(':id', $id, \PDO::PARAM_INT);

        return $statement->execute();
    }
}

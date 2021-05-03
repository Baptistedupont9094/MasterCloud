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
        $query = 'SELECT * FROM ' . static::TABLE . ' WHERE utilisateur_id=' . $utilisateurId . ';';

        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // public function selectAllVotes(int $playlistId)
    // {
    //     if (isset($_SESSION['utilisateur_id'])) {
    //         $vote = false;

    //         $req = $pdo->prepare('SELECT * FROM ' . static::TABLE . ' WHERE ref = '
    //         . $ref . 'AND id= ' . $playlistId . 'AND utilisateur_id = ' . $utilisateurId);
    //         $req->execute(['playlist', $_GET['id'], $_SESSION['utilisateur_id']]);
    //         $vote = $req->fetch();
    //     }
    // }

    public function likes()
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE .
        " SET 'nombre_likes' = :nombre_likes+1 WHERE id=:id");
        return $statement->execute();
    }

    public function dislikes()
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE .
        " SET 'nombre_dislikes' = :nombre_dislikes+1 WHERE id=:id");
        return $statement->execute();
    }
}

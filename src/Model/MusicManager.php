<?php

namespace App\Model;

class MusicManager extends AbstractManager
{
    public const TABLE = 'musique';

    /**
     * Insert new item in database
     */
    public function insert(array $music): int
    {
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE .
        "(nom, artiste, album, genre, image, source) VALUES (:nom, :artiste, :album ,:genre ,:image, :source)");
        $statement->bindValue(':nom', $music['nom'], \PDO::PARAM_STR);
        $statement->bindValue(':artiste', $music['artiste'], \PDO::PARAM_STR);
        $statement->bindValue(':album', $music['album'], \PDO::PARAM_STR);
        $statement->bindValue(':genre', $music['genre'], \PDO::PARAM_STR);
        $statement->bindValue(':image', $music['image'], \PDO::PARAM_STR);
        $statement->bindValue(':source', $music['source'], \PDO::PARAM_STR);

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
}

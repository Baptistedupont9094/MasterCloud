<?php

namespace App\Model;

class VoteManager extends AbstractManager
{
    public const TABLE = 'votes';

    public function selectByUserAndPlaylist(int $userId, int $playlistId)
    {
        $statement = $this->pdo->prepare("
            SELECT * FROM " . self::TABLE . " WHERE utilisateur_id = :userId AND playlist_id = :playlistId
        ");
        $statement->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $statement->bindValue(':playlistId', $playlistId, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    public function vote(array $vote): array
    {
        $userVote = $this->selectByUserAndPlaylist($vote['utilisateur_id'], $vote['playlist_id']);
        $hasVoted = !empty($userVote);

        $query = "
            INSERT INTO " . self::TABLE . "
            (`like`, utilisateur_id, playlist_id) VALUES 
            (:like, :userId, :playlistId)
        ";

        if ($hasVoted) {
            $query = "
                UPDATE " . self::TABLE . " 
                SET `like` = :like 
                WHERE utilisateur_id = :userId AND playlist_id = :playlistId
            ";
        }

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':like', $vote['like'], \PDO::PARAM_BOOL);
        $statement->bindValue(':userId', $vote['utilisateur_id'], \PDO::PARAM_INT);
        $statement->bindValue(':playlistId', $vote['playlist_id'], \PDO::PARAM_INT);
        $statement->execute();

        return [
            'hasVoted' => $hasVoted,
            'voteHasChanged' => $hasVoted ? (bool) $userVote['like'] !== (bool) $vote['like'] : true,
        ];
    }
}

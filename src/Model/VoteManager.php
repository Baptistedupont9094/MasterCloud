<?php

namespace App\Model;

class VoteManager extends AbstractManager
{
    private $former_vote;

    private function recordExists($ref, $playlist_id)
    {
        $req = $this->$pdo->prepare("SELECT * FROM $ref WHERE id = ?");
        $req->execute(array($playlist_id));
        if ($req->rowCount() == 0) {
            throw new Exception("Impossible de voter pour une playlist qui n'existe pas");
        }
    }

    public function like($ref, $playlist_id, $utilisateur_id)
    {
        if ($this->vote($ref, $playlist_id, $utilisateur_id, 1){
            $sql_part = "";
            if ($this->former_vote) {
                $sql_part = ", dislike_count = dislike_count -1";
            }
            $this->pdo->query("UPDATE $ref SET like_count = like_count + 1 $sql_part WHERE id = $playlist_id");
        }
    }

    public function dislike($ref, $playlist_id, $utilisateur_id)
    {
        if ($this->vote($ref, $playlist_id, $utilisateur_id, -1){
            $sql_part = "";
            if ($this->former_vote) {
                $sql_part = ", like_count = like_count -1";
            }
            $this->pdo->query("UPDATE $ref SET dislike_count = dislike_count + 1 $sql_part WHERE id = $playlist_id");
        }
    }

    private function vote($ref, $playlist_id, $utilisateur_id, $vote)
    {
        $this->recordExists($ref, $playlist_id);
        $req = $this->pdo->prepare("SELECT id, vote FROM votes 
        WHERE ref = ? AND playlist_id = ? AND utilisateur_id = ?");
        $req->execute([$ref, $playlist_id, $utilisateur_id]);
        $vote_row = $req->fetch();
        if ($vote_row) {
            if ($vote_row->vote == $vote) {
                return false;
            }
            $this->$former_vote = $vote_row;
            $this->pdo->prepare("UPDATE votes SET vote = ?, created_at = ? 
            WHERE id = {$vote_row->id}")->execute([$vote, date('d-m-Y H:i')]);
            return true;
        }
        $req = $this->pdo->prepare("INSERT INTO votes 
        SET ref = ?, playlist_id = ?, utilisateur_id = ?, created_at = ?, vote = $vote");
        $req->execute([$ref, $playlist_id, $utilisateur_id, date('d-m-Y H:i')]);
    }

    public function updatecount($ref, $playlist_id)
    {
        $req = $this->pdo->prepare("SELECT COUNT(id) as count, vote FROM votes 
        WHERE ref = ? AND playlist_id = ? GROUP BY vote");
        $req->execute([$ref, $playlist_id]);
        $req->fetchAll();
        $counts = [
            '-1' => 0,
            '1' => 0
        ];
        foreach ($votes as $vote) {
            $counts[$vote->vote] = $vote->count;
        }
        $req = $this->pdo->query("UPDATE $ref 
        SET like_count = {count [1]}, dislikecount = {$counts[-1]} WHERE id = $ref_id");
        return true;
    }

/**
 * Permet d'ajouter une class is-liked ou is-disliked
 * @param $vote mixed false/PDORow
 */
    public static function getClass($vote)
    {
        if ($vote) {
            return $vote->vote == 1 ? 'is_liked' : 'is-disliked';
        }
        return null;
    }
}
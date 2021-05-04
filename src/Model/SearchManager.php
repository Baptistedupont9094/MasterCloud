<?php

namespace App\Model;

class SearchManager extends AbstractManager
{
    public const TABLE = 'playlist';

    public function search(string $item): array
    {
        $query = '
            SELECT t.*, u.nom as utilisateur FROM ' . static::TABLE . ' t 
            LEFT JOIN utilisateur u ON t.utilisateur_id = u.id 
            WHERE t.nom LIKE "%' . $item . '%"
        ';

        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }
}

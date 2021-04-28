<?php

namespace App\Model;

class SearchManager extends AbstractManager
{
    public const TABLE = 'playlist';

    public function search(string $item): array
    {
        $query= "SELECT * FROM playlist WHERE nom LIKE '%". $item. "%'";

        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}


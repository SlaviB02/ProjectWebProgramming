<?php
declare(strict_types=1);

class ArticleRepository
{
    public function __construct(private PDO $pdo) {}

    public function count(string $search = ''): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM articles a
            LEFT JOIN authors au ON a.author_id = au.id
            LEFT JOIN publications p ON a.publication_id = p.id
        ";

        $params = [];

        if ($search !== '') {
            $sql .= " WHERE a.title ILIKE :search OR au.name ILIKE :search OR p.title ILIKE :search";
            $params['search'] = "%$search%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public function getAll(string $search, string $orderBy, int $limit, int $offset): array
    {
        $sql = "
            SELECT 
                a.id,
                a.title,
                a.created_at,
                au.name AS author_name,
                p.title AS publication_title
            FROM articles a
            LEFT JOIN authors au ON a.author_id = au.id
            LEFT JOIN publications p ON a.publication_id = p.id
        ";

        $params = [];

        if ($search !== '') {
            $sql .= " WHERE a.title ILIKE :search OR au.name ILIKE :search OR p.title ILIKE :search";
            $params['search'] = "%$search%";
        }

        $sql .= " ORDER BY $orderBy LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        if ($search !== '') {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findFavorite(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id,
                a.title,
                au.name AS author_name,
                p.title AS publication_title
            FROM articles a
            LEFT JOIN authors au ON a.author_id = au.id
            LEFT JOIN publications p ON a.publication_id = p.id
            WHERE a.id = :id
            LIMIT 1
        ");

        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}
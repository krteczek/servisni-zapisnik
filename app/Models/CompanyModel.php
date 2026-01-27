<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CompanyModel extends BaseModel
{
    protected string $table = 'companies';

    public function existsBySlug(string $slug): ?array
    {
        return $this->findRowBy('slug', $slug);
    }

    public function existsById(int $id): bool
    {
        return (bool) $this->findRow($id);
    }

public function findBySlug(string $slug): ?array
{
    $pdo = Database::admin();

    $stmt = $pdo->prepare(
        "SELECT *
         FROM companies
         WHERE slug = :slug
           AND active = 1
         LIMIT 1"
    );

    $stmt->execute(['slug' => $slug]);

    return $stmt->fetch() ?: null;
}

    public function findAll(): array
    {
        $pdo = Database::pdo();

        $stmt = $pdo->query(
            'SELECT id, name, slug, created_at
             FROM ' . Database::table('companies') . '
             ORDER BY name'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}

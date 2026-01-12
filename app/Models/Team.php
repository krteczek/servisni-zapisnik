<?php
declare(strict_types=1);

namespace App\Models;

class Team extends BaseModel
{
    public static function all(): array
    {
        return self::db()
            ->query(
                'SELECT * FROM ' . self::table('teams') . ' ORDER BY name'
            )
            ->fetchAll();
    }

    public static function find(int $id): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM ' . self::table('teams') . ' WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: [];
    }

    public static function create(string $name, string $color): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO ' . self::table('teams') . ' (name, color) VALUES (?, ?)'
        );
        $stmt->execute([$name, $color]);
    }

    public static function update(int $id, string $name, string $color): void
    {
        $stmt = self::db()->prepare(
            'UPDATE ' . self::table('teams') . ' SET name = ?, color = ? WHERE id = ?'
        );
        $stmt->execute([$name, $color, $id]);
    }
}
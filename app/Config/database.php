public static function table(string $name): string
{
    static $prefix = null;

    if ($prefix === null) {
        $config = require BASE_PATH . '/../config/database.php';
        $prefix = $config['table_prefix'] ?? '';
    }

    return $prefix . $name;
}
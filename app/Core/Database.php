<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Správce databázových připojení s podporou multi-tenancy.
 * Poskytuje oddělená připojení pro admin databázi (globální) a work databáze (tenant-specific).
 * Implementuje connection pooling na úrovni requestu.
 */
final class Database
{
    /**
     * @var array<string, PDO> Cache otevřených PDO připojení [connection_key => PDO]
     */
    private static array $connections = [];

    /**
     * @var string|null Název aktuální tenant databáze pro work kontext
     */
    private static ?string $currentWorkDb = null;

    // TODO: [PERFORMANCE] Přidat connection pooling pro vysokou zátěž
    // TODO: [OBSERVABILITY] Přidat metriky pro počet připojení a dobu života

    /* ==========================================================
     * ADMIN DB – vždy jedna
     * ========================================================== */

    /**
     * Vrátí připojení ke globální admin databázi.
     * Admin DB obsahuje systémové tabulky (uživatelé, tenanty, audit logy).
     *
     * Očekává:
     * - Konfigurační klíč 'database.admin' s host, dbname, user, password
     *
     * TODO: [SECURITY] Zvážit použití read-only repliky pro admin dotazy
     * TODO: [BACKUP] Přidat automatické backupování admin DB
     *
     * @return PDO PDO připojení k admin databázi
     * @throws RuntimeException Pokud se připojení nepovede
     */
    public static function admin(): PDO
    {
        return self::getConnection('admin');
    }

    /* ==========================================================
     * WORK DB – explicitně nebo přes kontext
     * ========================================================== */

    /**
     * Vrátí připojení k tenant-specific work databázi.
     * Pokud není zadán název DB, použije se aktuálně nastavená work DB.
     *
     * Očekává:
     * - Konfigurační klíč 'database.work' s host, user, password
     * - Název databáze musí existovat na serveru
     *
     * TODO: [SECURITY] Validovat název databáze proti whitelistu nebo patternu
     * TODO: [PERFORMANCE] Přidat připojení k replikám pro read-only operace
     *
     * @param string|null $dbName Název tenant databáze (volitelné)
     * @return PDO PDO připojení k work databázi
     * @throws RuntimeException Pokud work DB není nastavena nebo připojení selže
     */
    public static function work(?string $dbName = null): PDO
    {
        $dbName ??= self::$currentWorkDb;

        if ($dbName === null) {
            throw new RuntimeException(
                'WORK database is not selected (missing db name or useWorkDatabase()).'
            );
        }

        return self::getConnection('work:' . $dbName);
    }

    /**
     * Nastaví výchozí WORK DB pro aktuální request.
     * Používá se pro automatické přepínání do tenant databáze při přihlášení.
     *
     * Vedlejší efekty:
     * - Mění statický stav třídy
     * - Ovlivňuje následná volání Database::work() bez parametru
     *
     * TODO: [MAINTENANCE] Přidat validaci, že zadaná databáze existuje
     * TODO: [FEATURE] Přidat možnost nastavit work DB pouze pro scope (callback)
     *
     * @param string $dbName Název tenant databáze
     * @return void
     */
		public static function useWorkDatabase(string $dbName): void
		{
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
			    throw new RuntimeException('Invalid database name.');
			}
		
		
		    if (!self::workDatabaseExists($dbName)) {
		        throw new RuntimeException(
		            "Tenant database '{$dbName}' does not exist."
		        );
		    }
		
		    self::$currentWorkDb = $dbName;
		}

    /**
     * Legacy kompatibilita – vrátí aktuální work DB nebo admin DB.
     * Používá se tam, kde není jasné, zda jde o tenant nebo admin data.
     *
     * @deprecated Použijte explicitně admin() nebo work()
     * @return PDO PDO připojení k aktuální databázi
     */
    public static function pdo(): PDO
    {
        return self::$currentWorkDb !== null
            ? self::work(self::$currentWorkDb)
            : self::admin();
    }

    /* ==========================================================
     * Interní factory
     * ========================================================== */

    /**
     * Vytvoří nebo vrátí cached PDO připojení podle klíče.
     * Implementuje lazy loading a connection pooling na úrovni requestu.
     *
     * TODO: [PERFORMANCE] Přidat TTL pro připojení a automatické uzavírání starých
     * TODO: [SECURITY] Přidat SSL/TLS podporu pro připojení k externí DB
     *
     * @param string $key Identifikátor připojení ('admin' nebo 'work:dbname')
     * @return PDO PDO připojení
     * @throws RuntimeException Pokud připojení selže nebo klíč je neplatný
     */
    private static function getConnection(string $key): PDO
    {
        if (isset(self::$connections[$key])) {
            return self::$connections[$key];
        }

        try {
            if ($key === 'admin') {
                $cfg = Config::get('database.admin');
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    $cfg['host'],
                    $cfg['dbname']
                );
                $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], self::options());

            } elseif (str_starts_with($key, 'work:')) {
                $dbName = substr($key, 5);
                $cfg = Config::get('database.work');
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    $cfg['host'],
                    $dbName
                );
                $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], self::options());

            } else {
                throw new RuntimeException('Unknown database key: ' . $key);
            }

        } catch (PDOException $e) {
            // TODO: [OBSERVABILITY] Integrovat s externím monitoringem (Sentry, NewRelic)
            Logger::instance()->error('DB connection failed', [
					'key' => $key,
					'exception' => $e->getMessage(),
				]);


            throw new RuntimeException('Database connection failed.');
        }

        self::$connections[$key] = $pdo;
        return $pdo;
    }

    /**
     * Vrátí společné PDO options pro všechna připojení.
     * Zajišťuje konzistentní chování napříč aplikací.
     *
     * TODO: [PERFORMANCE] Přidat PDO::MYSQL_ATTR_USE_BUFFERED_QUERY pro velké výsledky
     * TODO: [RELIABILITY] Přidat PDO::ATTR_TIMEOUT pro prevenci nekonečného čekání
     *
     * @return array<int, mixed>
     */
    private static function options(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
    
    public static function workDatabaseExists(string $dbName): bool
    {
        $cfg = Config::get('database.work');

        $dsn = sprintf(
            'mysql:host=%s;charset=utf8mb4',
            $cfg['host']
        );

        try {
            $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], self::options());

            $stmt = $pdo->prepare(
                "SELECT SCHEMA_NAME
                FROM INFORMATION_SCHEMA.SCHEMATA
                WHERE SCHEMA_NAME = :name"
            );

            $stmt->execute(['name' => $dbName]);

            return (bool) $stmt->fetch();

        } catch (PDOException) {
            return false;
        }
    }

    public static function getCurrentDatabase(): ?string
    {
        return self::$currentWorkDb;
    }

    public static function useAdminDatabase(): void
    {
        self::$currentWorkDb = null;
    }

    public static function setDatabase(?string $dbName): void
    {
        self::$currentWorkDb = $dbName;
    }


    public static function connection(string $type): \PDO
    {
        if ($type === 'admin') {
            return self::admin();
        }

        if ($type === 'work') {
            return self::work();
        }

        if (str_starts_with($type, 'work:')) {
            return self::work(substr($type, 5));
        }

        throw new \RuntimeException("Unknown connection type: {$type}");
    }
    
}
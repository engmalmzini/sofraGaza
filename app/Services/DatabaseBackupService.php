<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseBackupService
{
    private const SKIP_TABLES = [
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'sqlite_sequence',
    ];

    public function filename(): string
    {
        $database = (string) (config('database.connections.'.config('database.default').'.database') ?: 'sofragaza');
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', basename($database)) ?: 'sofragaza';

        return $safe.'-backup-'.now()->format('Y-m-d-His').'.sql';
    }

    /**
     * @param  callable(string): void  $write
     */
    public function streamDump(callable $write): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            throw new RuntimeException('نوع قاعدة البيانات غير مدعوم للنسخ الاحتياطي.');
        }

        $write("-- Sofra Gaza database backup\n");
        $write('-- Generated at '.now()->toDateTimeString()."\n");
        $write("-- Driver: {$driver}\n\n");
        $write("SET NAMES utf8mb4;\n");
        $write("SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($this->dumpableTables() as $table) {
            if ($driver === 'sqlite') {
                $this->dumpSqliteTable($table, $write);
            } else {
                $this->dumpMysqlTable($table, $write);
            }
        }

        $write("SET FOREIGN_KEY_CHECKS=1;\n");
    }

    /**
     * @return list<string>
     */
    private function dumpableTables(): array
    {
        return array_values(array_filter(
            $this->tableNames(),
            fn (string $table) => $table !== '' && ! in_array($table, self::SKIP_TABLES, true)
        ));
    }

    /**
     * @return list<string>
     */
    private function tableNames(): array
    {
        $listing = Schema::getTableListing(null, false);

        return array_values(array_unique(array_filter(array_map(
            [$this, 'unqualifiedTableName'],
            is_array($listing) ? $listing : []
        ))));
    }

    private function unqualifiedTableName(string $table): string
    {
        return str_contains($table, '.')
            ? substr($table, strrpos($table, '.') + 1)
            : $table;
    }

    /**
     * @param  callable(string): void  $write
     */
    private function dumpMysqlTable(string $table, callable $write): void
    {
        $quoted = $this->quoteId($table);
        $row = DB::selectOne('SHOW CREATE TABLE '.$quoted);
        $create = $row->{'Create Table'} ?? $row->{'Create View'} ?? null;
        if (! is_string($create) || $create === '') {
            return;
        }

        $write("DROP TABLE IF EXISTS {$quoted};\n");
        $write($create.";\n\n");
        $this->dumpRows($table, $write);
    }

    /**
     * @param  callable(string): void  $write
     */
    private function dumpSqliteTable(string $table, callable $write): void
    {
        $quoted = $this->quoteId($table);
        $create = DB::table('sqlite_master')
            ->where('type', 'table')
            ->where('name', $table)
            ->value('sql');

        if (! is_string($create) || $create === '') {
            return;
        }

        $write("DROP TABLE IF EXISTS {$quoted};\n");
        $write($create.";\n\n");
        $this->dumpRows($table, $write);

        $indexes = DB::table('sqlite_master')
            ->where('type', 'index')
            ->where('tbl_name', $table)
            ->whereNotNull('sql')
            ->pluck('sql');

        foreach ($indexes as $indexSql) {
            $write(rtrim((string) $indexSql, ';').";\n");
        }

        if ($indexes->isNotEmpty()) {
            $write("\n");
        }
    }

    /**
     * @param  callable(string): void  $write
     */
    private function dumpRows(string $table, callable $write): void
    {
        $columns = Schema::getColumnListing($table);
        if ($columns === []) {
            return;
        }

        $quotedTable = $this->quoteId($table);
        $quotedColumns = implode(', ', array_map(fn (string $column) => $this->quoteId($column), $columns));
        $primary = $this->firstPrimaryColumn($table, $columns);

        $flush = function ($rows) use ($write, $quotedTable, $quotedColumns, $columns): void {
            if ($rows->isEmpty()) {
                return;
            }

            $values = $rows->map(function ($row) use ($columns): string {
                $data = (array) $row;
                $sqlValues = array_map(
                    fn (string $column) => $this->sqlValue($data[$column] ?? null),
                    $columns
                );

                return '('.implode(', ', $sqlValues).')';
            })->implode(",\n");

            $write("INSERT INTO {$quotedTable} ({$quotedColumns}) VALUES\n{$values};\n\n");
        };

        $query = DB::table($table);
        if ($primary) {
            $query->orderBy($primary)->chunkById(100, $flush, $primary);
        } else {
            $offset = 0;
            do {
                $rows = (clone $query)->offset($offset)->limit(100)->get();
                $flush($rows);
                $offset += 100;
            } while ($rows->isNotEmpty());
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function firstPrimaryColumn(string $table, array $columns): ?string
    {
        try {
            foreach (Schema::getIndexes($table) as $index) {
                if (! empty($index['primary']) && ! empty($index['columns'][0])) {
                    return $index['columns'][0];
                }
            }
        } catch (\Throwable) {
            // Fall back to a conventional id column.
        }

        return in_array('id', $columns, true) ? 'id' : null;
    }

    private function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif (is_resource($value)) {
            $value = stream_get_contents($value);
        } elseif (! is_string($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_string($value) && str_contains($value, "\0")) {
            return "X'".bin2hex($value)."'";
        }

        $quoted = Schema::getConnection()->getPdo()->quote((string) $value);

        return $quoted === false ? 'NULL' : $quoted;
    }

    private function quoteId(string $name): string
    {
        return '`'.str_replace('`', '``', $name).'`';
    }
}

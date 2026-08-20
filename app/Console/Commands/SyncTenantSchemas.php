<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncTenantSchemas extends Command
{
    protected $signature = 'tenants:sync-schema
        {--template=consultorio_online : Base de datos que sirve de plantilla/fuente de verdad}
        {--apply : Si se omite, solo se muestra el diff sin ejecutar nada (dry-run)}';

    protected $description = 'Compara el esquema de la BD plantilla contra cada BD de tenant '
        . '(registradas en central.tenants) y aplica o reporta las diferencias de tablas/columnas. '
        . 'Nunca elimina columnas existentes, solo agrega/corrige.';

    public function handle()
    {
        $template = $this->option('template');
        $apply = $this->option('apply');

        $tenants = DB::connection('central')->table('tenants')->pluck('db_name');

        if ($tenants->isEmpty()) {
            $this->warn('No hay tenants registrados en la BD central.');
            return;
        }

        $this->info("Comparando contra plantilla: {$template}");
        $this->info($apply ? '>>> MODO APLICAR (se ejecutarán los cambios)' : '>>> MODO DRY-RUN (solo se muestra el diff, no se aplica nada)');
        $this->newLine();

        foreach ($tenants as $dbName) {
            if ($dbName === $template) {
                continue;
            }

            $this->comment("── Tenant: {$dbName} ──");
            $statements = $this->compararEsquema($template, $dbName);

            if (empty($statements)) {
                $this->info('  Sin diferencias.');
                $this->newLine();
                continue;
            }

            foreach ($statements as $sql) {
                $this->line("  {$sql}");
                if ($apply) {
                    try {
                        DB::connection('mysql')->statement($sql);
                        $this->info('    Aplicado correctamente');
                    } catch (\Throwable $e) {
                        $this->error('    Error: ' . $e->getMessage());
                    }
                }
            }
            $this->newLine();
        }

        if (!$apply) {
            $this->newLine();
            $this->comment('Esto fue solo un dry-run. Revisa los statements de arriba.');
            $this->comment('Si se ven bien, vuelve a correr con --apply para ejecutarlos de verdad.');
        }
    }

    /**
     * Compara tablas y columnas entre la plantilla y un tenant, y devuelve
     * la lista de sentencias SQL necesarias para poner al tenant al día.
     * No genera DROP de nada: solo CREATE TABLE / ADD COLUMN / MODIFY COLUMN.
     */
    private function compararEsquema(string $template, string $tenant): array
    {
        $statements = [];

        $tablasTemplate = collect(DB::connection('mysql')->select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?',
            [$template]
        ))->pluck('TABLE_NAME');

        $tablasTenant = collect(DB::connection('mysql')->select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?',
            [$tenant]
        ))->pluck('TABLE_NAME');

        // Tablas que faltan por completo en el tenant
        foreach ($tablasTemplate->diff($tablasTenant) as $tabla) {
            $statements[] = "CREATE TABLE `{$tenant}`.`{$tabla}` LIKE `{$template}`.`{$tabla}`;";
        }

        // Para las tablas que ya existen en ambos, comparamos columna por columna
        foreach ($tablasTemplate->intersect($tablasTenant) as $tabla) {
            $statements = array_merge($statements, $this->compararColumnas($template, $tenant, $tabla));
        }

        return $statements;
    }

    private function compararColumnas(string $template, string $tenant, string $tabla): array
    {
        $statements = [];

        $colsTemplate = collect(DB::connection('mysql')->select(
            'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, ORDINAL_POSITION
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
             ORDER BY ORDINAL_POSITION',
            [$template, $tabla]
        ))->values();

        $colsTenant = collect(DB::connection('mysql')->select(
            'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$tenant, $tabla]
        ))->keyBy('COLUMN_NAME');

        foreach ($colsTemplate as $i => $col) {
            $nombre = $col->COLUMN_NAME;

            if (!$colsTenant->has($nombre)) {
                // Columna faltante: la agregamos en la misma posición relativa
                $anterior = $i > 0 ? $colsTemplate[$i - 1]->COLUMN_NAME : null;
                $nullable = $col->IS_NULLABLE === 'YES' ? 'NULL' : 'NOT NULL';
                $default = $col->COLUMN_DEFAULT !== null ? "DEFAULT '{$col->COLUMN_DEFAULT}'" : '';
                $posicion = $anterior ? "AFTER `{$anterior}`" : 'FIRST';

                $statements[] = trim(
                    "ALTER TABLE `{$tenant}`.`{$tabla}` ADD COLUMN `{$nombre}` {$col->COLUMN_TYPE} {$nullable} {$default} {$posicion};"
                );
                continue;
            }

            // Columna existe en ambos: comparamos tipo/nulabilidad
            $colTenant = $colsTenant->get($nombre);
            if ($colTenant->COLUMN_TYPE !== $col->COLUMN_TYPE || $colTenant->IS_NULLABLE !== $col->IS_NULLABLE) {
                $nullable = $col->IS_NULLABLE === 'YES' ? 'NULL' : 'NOT NULL';
                $statements[] = "ALTER TABLE `{$tenant}`.`{$tabla}` MODIFY COLUMN `{$nombre}` {$col->COLUMN_TYPE} {$nullable};";
            }
        }

        return $statements;
    }
}
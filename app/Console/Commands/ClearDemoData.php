<?php

namespace App\Console\Commands;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;

class ClearDemoData extends Command
{
    protected $signature = 'demo:clear {--force : Ejecutar sin solicitar confirmación}';
    protected $description = 'Elimina únicamente los usuarios, PQR y auditorías de demostración';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('¿Deseas retirar todos los datos de demostración?')) {
            return self::SUCCESS;
        }

        DemoDataSeeder::clear();
        $this->info('Datos de demostración eliminados. Los registros reales se conservaron.');

        return self::SUCCESS;
    }
}

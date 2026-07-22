<?php

namespace App\Console\Commands;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;

class SeedDemoData extends Command
{
    protected $signature = 'demo:seed {--force : Ejecutar sin solicitar confirmación}';
    protected $description = 'Carga datos académicos de demostración sin alterar los registros reales';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('¿Deseas cargar o reemplazar los datos de demostración?')) {
            return self::SUCCESS;
        }

        $this->call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);
        $this->info('Datos de demostración cargados: 14 usuarios y 40 PQRS.');
        $this->line('Contraseña común: DemoPQRS2026!');

        return self::SUCCESS;
    }
}

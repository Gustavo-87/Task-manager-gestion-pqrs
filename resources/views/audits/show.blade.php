<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Detalle de auditoría #{{ $audit->id }}</h2>
            <a href="{{ route('audits.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Volver a auditoría</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 rounded-lg bg-white p-6 shadow sm:grid-cols-2">
                @foreach ([
                    'Fecha y hora' => $audit->created_at->format('d/m/Y H:i:s'),
                    'Usuario' => $audit->user?->name ?? 'Usuario eliminado',
                    'Correo' => $audit->user?->email ?? '—',
                    'Módulo' => $audit->module,
                    'Acción' => ucfirst(str_replace('_', ' ', $audit->action)),
                    'Dirección IP' => $audit->ip_address ?? '—',
                ] as $label => $value)
                    <div><div class="text-xs font-semibold uppercase text-gray-500">{{ $label }}</div><div class="mt-1 text-sm text-gray-900">{{ $value }}</div></div>
                @endforeach
                <div class="sm:col-span-2"><div class="text-xs font-semibold uppercase text-gray-500">Descripción</div><div class="mt-1 text-sm text-gray-900">{{ $audit->description }}</div></div>
                <div class="sm:col-span-2"><div class="text-xs font-semibold uppercase text-gray-500">Navegador / dispositivo</div><div class="mt-1 break-all text-sm text-gray-700">{{ $audit->user_agent ?? '—' }}</div></div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 font-semibold text-gray-900">Valores anteriores</h3>
                    <pre class="overflow-x-auto whitespace-pre-wrap rounded-md bg-gray-50 p-4 text-xs text-gray-700">{{ $audit->old_values ? json_encode($audit->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Sin valores anteriores' }}</pre>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 font-semibold text-gray-900">Valores nuevos</h3>
                    <pre class="overflow-x-auto whitespace-pre-wrap rounded-md bg-gray-50 p-4 text-xs text-gray-700">{{ $audit->new_values ? json_encode($audit->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Sin valores nuevos' }}</pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

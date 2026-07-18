<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Invitados - Boda</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #4f46e5;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            margin-top: 20px;
        }
        .status-pendiente {
            color: #d97706;
        }
        .status-confirmado {
            color: #059669;
        }
        .status-no_asiste {
            color: #dc2626;
        }
        .empty {
            text-align: center;
            color: #9ca3af;
            padding: 40px;
        }
    </style>
</head>
<body>
    <h1>Lista de Invitados - Boda</h1>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Estado RSVP</th>
                <th>Mesa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($guests as $guest)
                <tr>
                    <td>{{ $guest->name }}</td>
                    <td>{{ $guest->email ?? '—' }}</td>
                    <td>{{ $guest->phone ?? '—' }}</td>
                    <td class="status-{{ $guest->rsvp_status->value }}">
                        @switch($guest->rsvp_status->value)
                            @case('confirmado')
                                Confirmado
                                @break
                            @case('pendiente')
                                Pendiente
                                @break
                            @case('no_asiste')
                                No Asiste
                                @break
                            @default
                                {{ $guest->rsvp_status->value }}
                        @endswitch
                    </td>
                    <td>{{ $guest->table->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">No hay invitados registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ $generatedAt }}
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Jawaban Formulir</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
    </style>
</head>
<body>
    <h2>Jawaban Formulir</h2>
    <table>
        <thead>
            <tr>
                <th>ID Respon</th>
                <th>Formulir</th>
                <th>Total Jawaban</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($responses as $response)
            <tr>
                <td>{{ $response->id }}</td>
                <td>{{ $response->form->title ?? '-' }}</td>
                <td>{{ $response->answers->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $blogTitle }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #000000; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 2px; text-transform: uppercase; }
        .cover { display: block; width: 100%; height: auto; max-height: 320px; object-fit: cover; }
        .content { padding: 40px; color: #333333; line-height: 1.6; }
        .content h2 { color: #1a1a1a; font-size: 22px; margin: 0 0 16px; }
        .excerpt { color: #555555; font-size: 15px; margin-bottom: 28px; }
        .btn-container { margin: 28px 0; text-align: center; }
        .btn { background-color: #be9b7b; color: #ffffff !important; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; color: #888888; font-size: 12px; }
        .footer a { color: #be9b7b; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ALMHA</h1>
            <p style="color: #be9b7b; margin: 5px 0 0 0; font-size: 12px; letter-spacing: 1px;">PLASTIC SURGERY</p>
        </div>

        @if(!empty($blogImage))
            <img src="{{ $blogImage }}" alt="{{ $blogTitle }}" class="cover" />
        @endif

        <div class="content">
            <p style="text-transform: uppercase; letter-spacing: 0.12em; font-size: 12px; color: #be9b7b; margin: 0 0 8px;">Nuevo artículo</p>
            <h2>{{ $blogTitle }}</h2>
            <p class="excerpt">{{ $blogExcerpt }}</p>

            <div class="btn-container">
                <a href="{{ $blogUrl }}" class="btn">LEER EL ARTÍCULO</a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Almha Plastic Surgery. Todos los derechos reservados.</p>
            <p>Recibes este correo porque estás suscrito a nuestras novedades.</p>
            <p><a href="{{ $unsubscribeUrl }}">Cancelar suscripción</a></p>
        </div>
    </div>
</body>
</html>

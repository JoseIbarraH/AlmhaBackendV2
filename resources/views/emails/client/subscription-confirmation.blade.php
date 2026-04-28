<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirma tu suscripción - Almha</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #000000; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 2px; text-transform: uppercase; }
        .content { padding: 40px; text-align: center; color: #333333; line-height: 1.6; }
        .content h2 { color: #1a1a1a; font-size: 22px; margin-bottom: 20px; }
        .btn-container { margin: 35px 0; }
        .btn { background-color: #be9b7b; color: #ffffff !important; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block; transition: background 0.3s; }
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

        <div class="content">
            <h2>¡Hola!</h2>
            <p>Gracias por tu interés en unirte a nuestra comunidad. Para completar tu suscripción y empezar a recibir noticias, consejos de especialistas y actualizaciones exclusivas, solo necesitas confirmar tu dirección de correo electrónico.</p>

            <div class="btn-container">
                <a href="{{ $confirmationUrl }}" class="btn">
                    CONFIRMAR MI SUSCRIPCIÓN
                </a>
            </div>

            <p style="font-size: 14px;">Si no solicitaste esta suscripción, puedes ignorar este mensaje de forma segura.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Almha Plastic Surgery. Todos los derechos reservados.</p>
            <p>Estás recibiendo este correo porque te registraste en nuestro sitio web.</p>
        </div>
    </div>
</body>
</html>

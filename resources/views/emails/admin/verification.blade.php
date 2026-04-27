<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu cuenta - Almha</title>
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
            <h2>¡Hola, {{ $name }}!</h2>
            <p>Se ha creado una cuenta administrativa para ti en el panel de Almha. Antes de que puedas acceder y gestionar el contenido, necesitamos confirmar tu identidad verificando esta dirección de correo electrónico.</p>

            <div class="btn-container">
                <a href="{{ $verificationUrl }}" class="btn">
                    VERIFICAR MI CUENTA
                </a>
            </div>

            <p style="font-size: 14px;">Este enlace de verificación es personal y único. Una vez verificado, podrás iniciar sesión en el panel administrativo.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Almha Plastic Surgery. Todos los derechos reservados.</p>
            <p>Recibes este correo porque se ha solicitado el registro de tu cuenta en Almha Admin.</p>
        </div>
    </div>
</body>
</html>

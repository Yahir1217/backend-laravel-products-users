<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Código de Verificación - Agencia TRES R</title>
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #1e40af;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        .content {
            padding: 30px;
        }

        .content h2 {
            color: #1e40af;
            text-align: center; 

        }

        .codigo {
            display: inline-block;
            background-color: #e0e7ff;
            color: #1e3a8a;
            font-size: 28px;
            font-weight: bold;
            padding: 12px 25px;
            margin: 20px auto;
            border-radius: 8px;
            letter-spacing: 3px;
            text-align: center;
        }


        .footer {
            background-color: #f1f5f9;
            color: #6b7280;
            text-align: center;
            font-size: 13px;
            padding: 15px;
        }

        .logo {
            max-height: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Agencia TRES R</h1>
            <p>Innovación para tu negocio</p>
        </div>

        <div class="content">
            <h2>Hola, {{ $nombre }}</h2>
            <p>Hemos recibido una solicitud para verificar tu cuenta en nuestra plataforma <strong>distema productos usuarios</strong>.</p>

            <p>Tu código de verificación es:</p>

            <div class="codigo">
                {{ $codigo }}
            </div>

            <p>Por favor, ingresa este código en la aplicación para completar tu verificación.</p>

            <p style="margin-top: 20px;">Si no solicitaste este código, puedes ignorar este mensaje.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Yahir Nava Gandara. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

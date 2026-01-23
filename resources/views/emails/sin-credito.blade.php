<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Email</title>
    </head>
    <body style="margin: 0; padding: 0">
        <div class="container" style="padding: 20px">
            <table
                align="center"
                border="0"
                cellpadding="0"
                cellspacing="0"
                width="600"
                style="
                    border-collapse: collapse;
                    width: 100%;
                    font-family: Arial, sans-serif;
                "
            >
                <tr>
                    <td
                        style="
                            background-image: url('{{ asset('logo/bg-black.jpg') }}');
                            background-size: cover;
                            background-position: center;
                            color: #fff;
                            padding: 10px;
                            text-align: center;
                        "
                    >
                        <img
                            src="{{ asset('logo/logo2white.png') }}"
                            alt="Logo"
                            style="width: 100px"
                        />
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                            text-align: center;
                            padding: 10px;
                            background-image: url('{{ asset('logo/image-8.png') }}');
                            background-size: cover;
                            background-position: center;
                        "
                    >
                        <img
                            src="{{ asset('logo/image-7.png') }}"
                            alt="Alerta"
                            style="width: 150px"
                        />
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                            text-align: center;
                            font-size: 2rem;
                            padding: 10px;
                            background-image: url('{{ asset('logo/image-8.png') }}');
                            background-size: cover;
                            background-position: center;
                        "
                    >
                        <strong>Notificación</strong>
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                            text-align: center;
                            font-size: 1.5rem;
                            padding: 10px;
                            background-image: url('{{ asset('logo/image-8.png') }}');
                            background-size: cover;
                            background-position: center;
                        "
                    >
                        Actualización sobre tus Créditos Restantes
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                            font-size: 1rem;
                            padding: 20px 40px;
                            text-align: center;
                            background-image: url('{{ asset('logo/image-8.png') }}');
                            background-size: cover;
                            background-position: center;
                        "
                    >
                        {!! $message !!}
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                            padding: 20px;
                            text-align: left;
                            background-image: url('{{ asset('logo/image-8.png') }}');
                            background-size: cover;
                            background-position: center;
                        "
                    >
                        Saludos, El equipo de Beebus.<br />
                        contacto@beebuscr.com
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>

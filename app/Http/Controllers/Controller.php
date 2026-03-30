<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Almha API",
    version: "1.0.0",
    description: "API para el sistema administrativo de Almha Plastic Surgery",
    contact: new OA\Contact(email: "admin@almhaplasticsurgery.com")
)]
#[OA\Server(
    url: "/api/v1",
    description: "Servidor de API v1"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Get(
    path: "/ping",
    summary: "Prueba de Swagger",
    responses: [
        new OA\Response(response: 200, description: "OK")
    ]
)]
abstract class Controller
{
    //
}

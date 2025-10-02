<?php

namespace GatePass\Core;

use Firebase\JWT\JWT;
use firebase\JWT\Key;
use stdClass;

class AuthService
{
    private string $secretKey;

    public function __construct(string $jwtSecretKey)
    {
        $this->secretKey = $jwtSecretKey;
    }

    // métodos para gerar token, requisitos de aceitação listado no notion

}
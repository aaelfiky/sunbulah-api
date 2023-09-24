<?php

namespace App\Helpers;
use JWTFactory;

class AuthHelper {
    public static function getAuthTokenData(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTFactory::getTTL() * 60
        ];
    }
}
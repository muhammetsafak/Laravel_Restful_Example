<?php
/**
 * JWT.php
 *
 * This file is part of restapi.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace App\Classes;

class JWT
{

    private static array $data = [];

    public static function encode(array $data): string
    {
        $payload = [
            'iss'   => 'http://localhost',
            'aud'   => 'http://localhost',
            'data'  => $data,
        ];
        return \Firebase\JWT\JWT::encode($payload, env('JWT_SECRET_KEY'), env('JWT_ALGO'));
    }

    public static function decode(string $token): bool
    {
        $decode = (array)\Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(env('JWT_SECRET_KEY'), env('JWT_ALGO')));
        if(isset($decode['data'])){
            self::$data = (array)$decode['data'];
            return true;
        }
        return false;
    }

    /**
     * @return array|false
     */
    public static function get()
    {
        return empty(self::$data) ? false : self::$data;
    }

}

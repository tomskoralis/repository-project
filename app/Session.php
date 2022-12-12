<?php

namespace App;

class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function get(...$keys)
    {
        if (empty($keys)) {
            return null;
        }
        return array_reduce(
            $keys,
            function ($result, $keys) {
                if (array_key_exists($keys, $result)) {
                    return $result[$keys];
                }
                return null;
            },
            $_SESSION
        );
    }

    public static function add($value, ...$keys): void
    {
        if (empty($keys)) {
            return;
        }
        $_SESSION = array_replace_recursive($_SESSION, self::nestElement($value, $keys));
    }

    public static function remove(...$keys): void
    {
        if (empty($keys)) {
            return;
        }
        self::deleteElement($_SESSION, $keys);
    }

    public static function has(...$keys): bool
    {
        if (empty($keys)) {
            return false;
        }
        $value = array_reduce(
            $keys,
            function ($result, $keys) {
                if (array_key_exists($keys, $result)) {
                    return $result[$keys];
                }
                return null;
            },
            $_SESSION
        );
        return isset($value);
    }

    private static function nestElement(string $value, array $keys)
    {
        if (empty($keys)) {
            return $value;
        }
        $key = array_shift($keys);
        return [$key => self::nestElement($value, $keys)];
    }

    private static function deleteElement(&$array, array $keys): void
    {
        $key = array_shift($keys);
        if (count($keys) === 0) {
            unset($array[$key]);
        } else {
            if (array_key_exists($key, $array)) {
                self::deleteElement($array[$key], $keys);
            }
        }
    }
}
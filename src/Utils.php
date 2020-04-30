<?php

namespace App\Utils;

use Exception;
use Ramsey\Uuid\Uuid;

class Utils
{
    /**
     * @param string $message
     * @param $payload
     * @return string
     */
    public static function toLog(string $message, $payload): string
    {
        if ($payload instanceof \JsonSerializable) {
            $str = json_encode($payload);
        } elseif (is_object($payload) && method_exists($payload, "__toString")) {
            $str = $payload->__toString();
        } else {
            $str = json_encode($payload);
        }

        return json_encode(
            [
                "message" => $message,
                "object" => $str
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param string $documentType
     * @param string $id
     * @return string
     */
    public static function makeKey(string $documentType, string $id): string
    {
        return $documentType . "::" . $id;
    }

    public static function ArrayReplaceRecursive(array $original, array $new): array
    {
        $result = array_replace_recursive($original, $new);
        foreach ($new as $key => $value) {
            if (is_array($new[$key])) {
                if (!self::isAssocArray($new[$key])) {
                    $result[$key] = $new[$key];
                }
            }
        }
        return $result;
    }

    public static function isAssocArray(array $data): bool
    {
        if (array() === $data) return false;
        return array_keys($data) !== range(0, count($data) - 1);
    }

    public static function emptyClass(): object
    {
        return new class() {};
    }

    public static function genId(): string
    {
        try {
            return Uuid::uuid4()->toString();
        } catch (Exception $e) {
            return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                random_int( 0, 0xffff ), random_int( 0, 0xffff ),
                random_int( 0, 0xffff ),
                random_int( 0, 0x0fff ) | 0x4000,
                random_int( 0, 0x3fff ) | 0x8000,
                random_int( 0, 0xffff ), random_int( 0, 0xffff ), random_int( 0, 0xffff )
            );
        }
    }

    public static function makeTranslit(string $string): string
    {
        $string = mb_strtolower($string);
        $replace = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'е', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];
        $string = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        $string =  implode('', array_map(function($char) use ($replace) {
            if (isset($replace[$char])) return $replace[$char];
            if (preg_match('/[a-z0-9]+/', $char)) return $char;
            return '_';
        }, $string));
        $string = preg_replace('/_{2,}/', '_', $string);
        return $string;
    }
}
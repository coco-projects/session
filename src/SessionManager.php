<?php

    namespace Coco\session;

    use Coco\base64\Base64;
    use Coco\session\storages\StorageAbstract;

class SessionManager
{
    /**
     * @var SessionContainer[] $containers
     */
    protected static array $containers = [];

    protected ?StorageAbstract $storage = null;

    protected static ?SessionManager $ins = null;

    public static int $expire = 86400 * 7;

    public static array $base64Factor = [
        "factor"  => "_-.BCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",
        "padding" => "A",
    ];

    protected function __construct(StorageAbstract $storage)
    {
        $this->storage = $storage;
    }

    public function getStorage(): ?StorageAbstract
    {
        return $this->storage;
    }

    public static function InitStorage(StorageAbstract $storage): ?SessionManager
    {
        if (is_null(static::$ins)) {
            static::$ins = new static($storage);
        }

        return static::$ins;
    }

    public static function setBase64Factor(string $factor, string $padding): void
    {
        self::$base64Factor['factor']  = $factor;
        self::$base64Factor['padding'] = $padding;
    }

    public static function setExpire(int $expire): void
    {
        static::$expire = $expire;
    }

    public static function getSessionContainer($namespace, $token): SessionContainer
    {
        if (!static::validateToken($token)) {
            throw  new \Exception('invalidate token');
        }

        $key = $namespace . $token;

        if (!isset(static::$containers[$key])) {
            static::$containers[$key] = new SessionContainer($namespace, $token, static::$ins);
        }

        return static::$containers[$key];
    }

    public static function validateToken($token): bool
    {
        $total = 10000000;

        $arr = static::parseToken($token);

        if (is_array($arr)) {
            return ($arr[0] + $arr[1]) == $total;
        }

        return false;
    }

    public static function parseTokenTime($token): bool|int
    {
        $arr = static::parseToken($token);

        if (is_array($arr)) {
            return $arr[2];
        }

        return false;
    }

    public static function generateToken(): string
    {
        $total = 10000000;

        $f1 = rand(2000000, 8000000);
        $f2 = $total - $f1;

        $f1Arr   = str_split((string)$f1);
        $f2Arr   = str_split((string)$f2);
        $timeArr = str_split((string)time());

        $result = [
            0  => $f1Arr[1],
            1  => $f2Arr[5],
            2  => $timeArr[3],
            3  => $f2Arr[4],
            4  => $f2Arr[6],
            5  => $f1Arr[4],
            6  => $f1Arr[6],
            7  => $timeArr[0],
            8  => $f2Arr[1],
            9  => $f2Arr[3],
            10 => $timeArr[1],
            11 => $f1Arr[0],
            12 => $timeArr[2],
            13 => $f1Arr[5],
            14 => $timeArr[6],
            15 => $f1Arr[3],
            16 => $timeArr[4],
            17 => $timeArr[8],
            18 => $f1Arr[2],
            19 => $timeArr[5],
            20 => $timeArr[7],
            21 => $f2Arr[0],
            22 => $timeArr[9],
            23 => $f2Arr[2],
        ];

        $instance = Base64::getInstance(static::$base64Factor['factor'], static::$base64Factor['padding']);

        return $instance->encode(implode('', $result));
    }

    public static function generateFactor(): array
    {
        return Base64::makeRandomKey();
    }

    protected static function parseToken($token): bool|array
    {
        $instance = Base64::getInstance(static::$base64Factor['factor'], static::$base64Factor['padding']);

        try {
            $token = $instance->decode($token);
        } catch (\Exception) {
            return false;
        }

        $tokenArr = str_split($token);

        $f1Arr = [
            0 => $tokenArr[11],
            1 => $tokenArr[0],
            2 => $tokenArr[18],
            3 => $tokenArr[15],
            4 => $tokenArr[5],
            5 => $tokenArr[13],
            6 => $tokenArr[6],
        ];

        $f2Arr = [
            0 => $tokenArr[21],
            1 => $tokenArr[8],
            2 => $tokenArr[23],
            3 => $tokenArr[9],
            4 => $tokenArr[3],
            5 => $tokenArr[1],
            6 => $tokenArr[4],

        ];

        $timeArr = [
            0 => $tokenArr[7],
            1 => $tokenArr[10],
            2 => $tokenArr[12],
            3 => $tokenArr[2],
            4 => $tokenArr[16],
            5 => $tokenArr[19],
            6 => $tokenArr[14],
            7 => $tokenArr[20],
            8 => $tokenArr[17],
            9 => $tokenArr[22],
        ];

        $f1   = (int)implode('', $f1Arr);
        $f2   = (int)implode('', $f2Arr);
        $time = (int)implode('', $timeArr);

        return [
            $f1,
            $f2,
            $time,
        ];
    }
}

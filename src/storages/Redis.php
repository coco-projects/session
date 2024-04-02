<?php

    namespace Coco\session\storages;

class Redis extends StorageAbstract
{
    private ?\Redis $redisClient = null;

    /**
     * @param string $redisHost
     * @param int    $redisPort
     * @param string $redisPassword
     * @param int    $redisDb
     * @param string $prefix
     *
     * @throws \RedisException
     */
    public function __construct(private string $redisHost = '127.0.0.1', private int $redisPort = 6379, private string $redisPassword = '', private int $redisDb = 9, private string $prefix = 'default_db')
    {
        $this->redisClient = new \Redis();
        $this->redisClient->connect($this->redisHost, $this->redisPort);
        $this->redisPassword and $this->redisClient->auth($this->redisPassword);
        $this->redisClient->select($this->redisDb);
    }

    public function set($namespace, $key, $value): static
    {
        $this->redisClient->hSet($namespace, $key, $value);

        return $this;
    }

    public function get($namespace, $key, $default = null): mixed
    {
        $value = $this->redisClient->hGet($namespace, $key);

        if (is_null($value)) {
            $value = $default;
        }

        return $value;
    }

    public function del($namespace, $key): static
    {
        $this->redisClient->hDel($namespace, $key);

        return $this;
    }

    public function flush($namespace): static
    {
        $this->redisClient->del($namespace);

        return $this;
    }

    public function getAll($namespace): \Redis|array|bool
    {
        return $this->redisClient->hGetAll($namespace);
    }
}

<?php

    namespace Coco\session\storages;

abstract class StorageAbstract
{
    abstract public function set($namespace, $key, $value): static;

    abstract public function get($namespace, $key, $default = null): mixed;

    abstract public function del($namespace, $key): static;

    abstract public function flush($namespace): static;

    abstract public function getAll($namespace): \Redis|array|bool;
}

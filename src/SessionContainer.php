<?php

    namespace Coco\session;

class SessionContainer
{
    protected string         $name;
    protected string         $domain;
    protected string         $domainData;
    protected string         $domainAnalysis;
    protected ?string        $token = null;
    protected SessionManager $sessionManager;

    protected const key_init_time         = 'init_time';
    protected const key_last_read_time    = 'last_read_time';
    protected const key_last_write_time   = 'last_write_time';
    protected const key_session_available = 'is_session_available';

    public function __construct($name, $token, $sessionManager)
    {
        $this->name           = $name;
        $this->token          = $token;
        $this->sessionManager = $sessionManager;

        $this->domain = implode(':', [
            $this->name,
            $this->token,
        ]);

        $this->domainData = implode(':', [
            $this->domain,
            'data',
        ]);

        $this->domainAnalysis = implode(':', [
            $this->domain,
            'analysis',
        ]);

        if (!$this->isSessionInited()) {
            $this->init();
        }
    }

    protected function init(): void
    {
        $this->sessionManager->getStorage()->set($this->domainAnalysis, static::key_init_time, time());
        $this->sessionManager->getStorage()->set($this->domainAnalysis, static::key_session_available, true);
    }

    public function set($key, $value): static
    {
        $this->sessionManager->getStorage()->set($this->domainData, $key, $value);
        $this->sessionManager->getStorage()->set($this->domainAnalysis, static::key_last_write_time, time());

        return $this;
    }

    public function get($key, $default = null): mixed
    {
        $this->sessionManager->getStorage()->set($this->domainAnalysis, static::key_last_read_time, time());

        return $this->sessionManager->getStorage()->get($this->domainData, $key, $default);
    }

    public function del($key): static
    {
        $this->sessionManager->getStorage()->set($this->domainAnalysis, static::key_last_write_time, time());
        $this->sessionManager->getStorage()->del($this->domainData, $key);

        return $this;
    }

    public function flushCurrentSession(): static
    {
        $this->sessionManager->getStorage()->flush($this->domainData);
        $this->sessionManager->getStorage()->flush($this->domainAnalysis);

        return $this;
    }

    public function getAllData(): \Redis|array|bool
    {
        return $this->sessionManager->getStorage()->getAll($this->domainData);
    }

    public function getAnalysis(): \Redis|array|bool
    {
        return $this->sessionManager->getStorage()->getAll($this->domainAnalysis);
    }

    public function isSessionInited(): bool
    {
        return !!$this->sessionManager->getStorage()->get($this->domainAnalysis, static::key_init_time);
    }

    public function isSessionAvailable(): bool
    {
        return !!$this->sessionManager->getStorage()->get($this->domainAnalysis, static::key_session_available);
    }

    public function isSessionExpired(): bool
    {
        $tokenTime = $this->sessionManager::parseTokenTime($this->token);

        return time() > ($tokenTime + $this->sessionManager::$expire);
    }

    public function disable(): static
    {
        $this->sessionManager->getStorage()->set($this->domainAnalysis, static::key_session_available, false);

        return $this;
    }
}

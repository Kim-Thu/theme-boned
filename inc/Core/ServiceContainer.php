<?php
declare(strict_types=1);

namespace ModernArchitect\Core;

use Exception;
use Closure;

/**
 * Service Container
 * 
 * Implements a simple dependency injection container.
 * Follows Singleton pattern for global access.
 * 
 * @package ModernArchitect\Core
 * @author Your Name
 * @license GPL-2.0-or-later
 */
class ServiceContainer
{
    /**
     * @var array<string, callable> Registered services
     */
    private array $services = [];

    /**
     * @var array<string, mixed> Instantiated service instances
     */
    private array $instances = [];

    /**
     * @var self|null Singleton instance
     */
    private static ?self $instance = null;

    /**
     * Get singleton instance
     * 
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a service
     * 
     * @param string $name Service identifier
     * @param callable $resolver Closure that returns service instance
     * @return void
     */
    public function register(string $name, callable $resolver): void
    {
        $this->services[$name] = $resolver;
    }

    /**
     * Get a service instance
     * 
     * Resolves and caches the service on first request.
     * 
     * @param string $name Service identifier
     * @return mixed Service instance
     * @throws Exception If service is not registered
     */
    public function get(string $name): mixed
    {
        if (!isset($this->services[$name])) {
            throw new Exception(
                sprintf('Service "%s" is not registered.', esc_html($name))
            );
        }

        if (!isset($this->instances[$name])) {
            $resolver = $this->services[$name];
            $this->instances[$name] = $resolver($this);
        }

        return $this->instances[$name];
    }

    /**
     * Check if a service is registered
     * 
     * @param string $name Service identifier
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * Remove a service registration
     * 
     * @param string $name Service identifier
     * @return void
     */
    public function remove(string $name): void
    {
        unset($this->services[$name], $this->instances[$name]);
    }

    /**
     * Get all registered service names
     * 
     * @return array<string>
     */
    public function getRegisteredServices(): array
    {
        return array_keys($this->services);
    }

    /**
     * Prevent cloning
     * 
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     * 
     * @return void
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
}

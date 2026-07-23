<?php
/**
 * Hooks Manager
 *
 * @package DShop\Core
 */

namespace DShop\Core;

/**
 * Class Hooks
 *
 * Manages WordPress hooks (actions and filters)
 */
class Hooks
{
    /**
     * Registered hooks
     *
     * @var array
     */
    private $hooks = [];

    /**
     * Add action
     *
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @param int $accepted_args Number of accepted arguments
     * @return void
     */
    public function addAction(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        add_action($hook, $callback, $priority, $accepted_args);
        $this->registerHook('action', $hook, $callback, $priority);
    }

    /**
     * Add filter
     *
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @param int $accepted_args Number of accepted arguments
     * @return void
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        add_filter($hook, $callback, $priority, $accepted_args);
        $this->registerHook('filter', $hook, $callback, $priority);
    }

    /**
     * Remove action
     *
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @return void
     */
    public function removeAction(string $hook, callable $callback, int $priority = 10): void
    {
        remove_action($hook, $callback, $priority);
        $this->unregisterHook('action', $hook, $callback, $priority);
    }

    /**
     * Remove filter
     *
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @return void
     */
    public function removeFilter(string $hook, callable $callback, int $priority = 10): void
    {
        remove_filter($hook, $callback, $priority);
        $this->unregisterHook('filter', $hook, $callback, $priority);
    }

    /**
     * Do action
     *
     * @param string $hook Hook name
     * @param mixed ...$args Arguments
     * @return void
     */
    public function doAction(string $hook, ...$args): void
    {
        do_action($hook, ...$args);
    }

    /**
     * Apply filter
     *
     * @param string $hook Hook name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed
     */
    public function applyFilter(string $hook, $value, ...$args)
    {
        return apply_filters($hook, $value, ...$args);
    }

    /**
     * Register hook internally
     *
     * @param string $type Hook type (action/filter)
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @return void
     */
    private function registerHook(string $type, string $hook, callable $callback, int $priority): void
    {
        $this->hooks[$type][$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];
    }

    /**
     * Unregister hook internally
     *
     * @param string $type Hook type (action/filter)
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @return void
     */
    private function unregisterHook(string $type, string $hook, callable $callback, int $priority): void
    {
        if (!isset($this->hooks[$type][$hook])) {
            return;
        }

        $this->hooks[$type][$hook] = array_filter(
            $this->hooks[$type][$hook],
            function($registered) use ($callback, $priority) {
                return $registered['callback'] !== $callback ||
                       $registered['priority'] !== $priority;
            }
        );
    }

    /**
     * Get all registered hooks
     *
     * @return array
     */
    public function getRegisteredHooks(): array
    {
        return $this->hooks;
    }

    /**
     * Check if hook is registered
     *
     * @param string $type Hook type (action/filter)
     * @param string $hook Hook name
     * @return bool
     */
    public function isRegistered(string $type, string $hook): bool
    {
        return isset($this->hooks[$type][$hook]) && !empty($this->hooks[$type][$hook]);
    }
}

<?php interface Controller
{
    /**
     * Dispatches a request to the appropriate controller
     *
     * @param string $method The HTTP method
     * @param string $path The path of the request
     * @return void
     */
    public function dispatch($method, array $path);
}

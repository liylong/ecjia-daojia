<?php namespace Royalcms\Component\Stack;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StackedHttpKernel implements HttpKernelInterface, TerminableInterface
{
    private $royalcms;
    private $middlewares = array();

    public function __construct(HttpKernelInterface $royalcms, array $middlewares)
    {
        $this->royalcms = $royalcms;
        $this->middlewares = $middlewares;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        return $this->royalcms->handle($request, $type, $catch);
    }

    public function terminate(Request $request, Response $response)
    {
        $prevKernel = null;
        foreach ($this->middlewares as $kernel) {
            // if prev kernel was terminable we can assume this middleware has already been called
            if (!$prevKernel instanceof TerminableInterface && $kernel instanceof TerminableInterface) {
                $kernel->terminate($request, $response);
            }
            $prevKernel = $kernel;
        }
    }
}

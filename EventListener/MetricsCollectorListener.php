<?php

declare(strict_types=1);

namespace Artprima\PrometheusMetricsBundle\EventListener;

use Artprima\PrometheusMetricsBundle\Metrics\ConsoleCommandMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\ConsoleErrorMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\ConsoleTerminateMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\ExceptionMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorRegistry;
use Artprima\PrometheusMetricsBundle\Metrics\PreExceptionMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\PreRequestMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\RequestMetricsCollectorInterface;
use Artprima\PrometheusMetricsBundle\Metrics\ResponseMetricsCollectorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class MetricsCollectorListener is an event listener that calls the registered metric handlers.
 */
class MetricsCollectorListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private MetricsCollectorRegistry $metricsCollectors, private array $ignoredRoutes = ['prometheus_bundle_prometheus'])
    {
    }

    public function onKernelRequestPre(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof PreRequestMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectStart($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'request_collector', 'class' => $collector::class]
                    );
                }
            }
        }
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof RequestMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectRequest($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'request_collector', 'class' => $collector::class]
                    );
                }
            }
        }
    }

    public function onKernelExceptionPre(ExceptionEvent $event): void
    {
        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof PreExceptionMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectPreException($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => $collector::class]
                    );
                }
            }
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof ExceptionMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectException($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => $collector::class]
                    );
                }
            }
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $requestRoute = $event->getRequest()->attributes->get('_route');
        if (in_array($requestRoute, $this->ignoredRoutes, true)) {
            return;
        }

        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof ResponseMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectResponse($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => $collector::class]
                    );
                }
            }
        }
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof ConsoleCommandMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectConsoleCommand($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => $collector::class]
                    );
                }
            }
        }
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof ConsoleTerminateMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectConsoleTerminate($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => $collector::class]
                    );
                }
            }
        }
    }

    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        foreach ($this->metricsCollectors->getMetricsCollectors() as $collector) {
            if (!$collector instanceof ConsoleErrorMetricsCollectorInterface) {
                continue;
            }

            try {
                $collector->collectConsoleError($event);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error(
                        $e->getMessage(),
                        ['from' => 'response_collector', 'class' => $collector::class]
                    );
                }
            }
        }
    }
}

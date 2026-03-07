<?php

declare(strict_types=1);

namespace Tests\Artprima\PrometheusMetricsBundle\Fixtures\App;

use Artprima\PrometheusMetricsBundle\Metrics\MetricsCollectorInterface;

interface FunctionNameCollectorInterface extends MetricsCollectorInterface
{
    public function collectStart($event);

    public function collectRequest($event);

    public function collectPreException($event);

    public function collectException($event);

    public function collectResponse($event);

    public function collectConsoleCommand($event);

    public function collectConsoleTerminate($event);

    public function collectConsoleError($event);
}

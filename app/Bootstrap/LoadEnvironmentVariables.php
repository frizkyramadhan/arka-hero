<?php

namespace App\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as FrameworkLoadEnvironmentVariables;

class LoadEnvironmentVariables extends FrameworkLoadEnvironmentVariables
{
    /**
     * Reload this app's .env over process environment left by another app
     * in the same Apache worker.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return \Dotenv\Dotenv
     */
    protected function createDotenv($app)
    {
        $builder = RepositoryBuilder::createWithDefaultAdapters()
            ->addAdapter(PutenvAdapter::class);

        return Dotenv::create(
            $builder->make(),
            $app->environmentPath(),
            $app->environmentFile()
        );
    }
}

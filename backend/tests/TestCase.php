<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Forca a conexao 'mysql' a apontar para o banco de teste via mutacao direta de
     * config (nao via variavel de ambiente).
     *
     * Quando o container do backend ja recebe DB_DATABASE=incident_hub via
     * docker-compose.yml, esse valor fica gravado em $_SERVER na inicializacao do
     * processo PHP. O override <env> do phpunit.xml so atualiza $_ENV/putenv(), e o
     * helper env() do Laravel le $_SERVER primeiro - ou seja, o override do phpunit.xml
     * e silenciosamente ignorado e os testes rodavam (e apagavam, via RefreshDatabase)
     * o banco de desenvolvimento/demo em vez do banco de teste. Mutar o config
     * diretamente aqui, antes do RefreshDatabase migrar o schema, contorna esse
     * problema por completo.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.connections.mysql.database', 'incident_hub_test');
        $app['db']->purge('mysql');

        return $app;
    }
}

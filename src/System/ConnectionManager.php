<?php
declare(strict_types = 1);
namespace GPRS\System;

use BCL\System\AbstractObject;
use BCL\System\AbstractException;
use BCL\System\Streams\Network\ClientStream;

/**
 * Gerenciador de conexões ativas.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System
 */
final class ConnectionManager extends AbstractObject
{

    /**
     * Tempo limite para estabelecer uma conexão.
     *
     * @var int
     */
    const CONNECTION_TIME = 10;

    /**
     * Tempo limite para enviar ou receber uma resposta.
     *
     * @var int
     */
    const COMMUNICATION_TIME = 600;

    /**
     * Instância do gerenciador de registros.
     *
     * @var LogManager
     */
    private $logger;

    /**
     * Lista de clientes de conexão.
     *
     * @var array
     */
    private $connections;

    /**
     * Lista de clientes de conexão pendentes.
     *
     * @var array
     */
    private $pending;

    /**
     * Cria uma nova conexão.
     *
     * @param string $host
     *            Endereço de conexão.
     * @param int $port
     *            Porta de conexão.
     * @return ClientStream Instância da nova conexão.
     */
    private function create(string $host, int $port): ClientStream
    {
        $mode = ClientStream::PROTOCOL_TCP | ClientStream::ASYNC_MODE | ClientStream::ACCESS_ALL;
        $connection = new ClientStream($mode, $host, $port);

        $connection->setConnectTimeout(self::CONNECTION_TIME);
        $connection->setWriteTimeout(self::COMMUNICATION_TIME);
        $connection->setReadTimeout(self::COMMUNICATION_TIME);

        $connection->connect();

        return $connection;
    }

    /**
     * Tenta restabelecer uma conexão.
     *
     * @param ClientStream $connection
     *            Instância da conexão.
     * @return void
     */
    private function reconnect(ClientStream $connection)
    {
        if ($connection->hasTimedout()) {
            $this->logger->logConnection('communication timeout');
        }

        $this->logger->logNotice('trying new connection');
        $connection->connect();
    }

    /**
     * Verifica se a conexão esta pronta para uso.
     *
     * @param ClientStream $connection
     *            Instância da conexão.
     * @param bool $pending
     *            Espeifica se a conexão esta pendente (Atualizado por referência).
     * @return bool
     */
    private function isReady(ClientStream $connection, bool &$pending): bool
    {
        if ($connection->hasConnection()) {

            if ($pending) {

                $pending = false;
                $this->logger->logConnection('established');
            }

            return true;
        }

        if (! $pending) {

            $pending = true;
            $this->reconnect($connection);
        }

        return false;
    }

    /**
     * Construtor.
     *
     * @param LogManager $logger
     *            Instância do gerenciador de registros.
     */
    public function __construct(LogManager $logger)
    {
        $this->logger = $logger;
        $this->connections = [];
        $this->pending = [];
    }

    /**
     * Obtém uma conexão.
     *
     * Se a conexão não existir, um stream cliente de conexão será criado.
     * Se a conexão já existir, será verificado se o stream cliente possui uma conexão ativa.
     *
     * @param string $host
     *            Endereço de conexão.
     * @param int $port
     *            Porta de conexão.
     * @return ClientStream|NULL Cliente de conexão ou Null quando o cliente de não puder conectar-se ao endereço.
     */
    public function get(string $host, int $port)
    {
        $address = sprintf('%s:%d', $host, $port);

        try {

            if (! isset($this->connections[$address])) {

                $this->connections[$address] = $this->create($host, $port);
                $this->pending[$address] = true;
            }

            $connection = $this->connections[$address];
            $this->logger->setConnection($connection);

            if (! $this->isReady($connection, $this->pending[$address])) {
                $connection = NULL;
            }
        } catch (AbstractException $exception) {

            $this->logger->logException($exception);
        }

        $this->logger->setConnection(NULL);
        return $connection;
    }
}
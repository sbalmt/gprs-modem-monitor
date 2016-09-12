<?php
declare(strict_types = 1);
namespace GPRS\System;

use BCL\System\Streams\Network\ClientStream;
use GPRS\System\Entities\ModemEntity;

/**
 * Gerênciador de objetos para manipulação de registros de atividades.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System
 */
final class LogManager extends \BCL\System\Logger\LogManager
{

    /**
     * Comando do registro.
     *
     * @var string
     */
    private $command;

    /**
     * Modem do registro.
     *
     * @var ModemEntity
     */
    private $modem;

    /**
     * Conexão do registro.
     *
     * @var ClientStream
     */
    private $connection;

    /**
     * Mensagem do registro.
     *
     * @var string
     */
    private $message;

    /**
     * Define a instância do modem relacionado ao registro.
     *
     * @param ModemEntity|NULL $modem
     *            Instância do modem ou Null para desassociar o modem atual.
     * @return void
     */
    public function setModem($modem)
    {
        $this->modem = $modem;
    }

    /**
     * Define a instância da conexão relacionada ao registro.
     *
     * @param ClientStream|NULL $connection
     *            Instância da conexão ou Null para desassociar a conexão atual.
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Envia um registro para os objetos de manipulação de registros.
     *
     * @param mixed ...$params
     *            Parâmetros informativos do registro.
     * @return void
     */
    public function log(...$params)
    {
        $stage = (isset($this->modem) ? $this->modem->getStage() : - 1);
        $address = isset($this->connection) ? $this->connection->getAddress() : 'none';
        $message = (isset($this->message) ? vsprintf('\'' . $this->message . '\'', $params) : '');

        parent::log($this->command, $stage, $address, $message);
    }

    /**
     * Registra uma atividade de notificação.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logNotice(string $message = NULL, ...$params)
    {
        $this->command = 'NOTICE';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade informativa.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logInfo(string $message = NULL, ...$params)
    {
        $this->command = 'INFO';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade para depuração de dados.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @return void
     */
    public function logData(string $message = NULL)
    {
        $this->command = 'DATA';
        $this->message = strtoupper(implode('', unpack('H*', $message)));

        $this->log();
    }

    /**
     * Registra uma atividade de conexão.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logConnection(string $message = NULL, ...$params)
    {
        $this->command = 'CONENCTION';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade de escrita.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logWrite(string $message = NULL, ...$params)
    {
        $this->command = 'WRITE';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade de leitura.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logRead(string $message = NULL, ...$params)
    {
        $this->command = 'READ';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade que originou uma exceção.
     *
     * @param \Exception $exception
     *            Instância da exceção.
     * @return void
     */
    public function logException(\Exception $exception)
    {
        $this->command = 'EXCEPTION';
        $this->message = 'class: %s, code: %d, message: "%s"';

        $this->log(get_class($exception), $exception->getCode(), $exception->getMessage());
    }
}
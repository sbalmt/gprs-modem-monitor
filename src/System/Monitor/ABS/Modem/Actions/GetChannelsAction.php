<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Modem\Actions;

/**
 * Obtém as informações sobre os canais do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Modem\Actions
 */
final class GetChannelsAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractGetChannelsAction
{

    /**
     * Endereço de leitura das informações.
     *
     * @var int
     */
    const R_CHANNEL_ADDRRES = 64360;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetChannelsAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        if ((bool) $this->modem->getData('modem.signal')) {
            return parent::writeCommand();
        }

        return false;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetChannelsAction::getReadAddress()
     */
    protected function getReadAddress(): int
    {
        return self::R_CHANNEL_ADDRRES;
    }
}
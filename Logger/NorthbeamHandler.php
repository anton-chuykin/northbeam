<?php

namespace KozakGroup\Northbeam\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class NorthbeamHandler extends Base
{

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
    /**
     * @var string
     */
    protected $fileName = '/var/log/Northbeam.log';
}

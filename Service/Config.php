<?php

declare(strict_types=1);

namespace KozakGroup\Northbeam\Service;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Store\Model\ScopeInterface as StoreScope;

class Config
{
    const CONFIG_PATH = 'north_beam/general/';

    private ScopeConfig $scopeConfig;

    public function __construct(
        ScopeConfig $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH . 'enabled', StoreScope::SCOPE_STORE);
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public function getConfigData(string $field): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH . $field, StoreScope::SCOPE_STORE);
    }
}

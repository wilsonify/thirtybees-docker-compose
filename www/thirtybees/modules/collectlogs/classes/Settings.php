<?php

namespace CollectLogsModule;

use Configuration;
use PrestaShopException;
use ReflectionClass;
use Throwable;
use Tools;

class Settings
{
    const SETTINGS_SECRET = 'COLLECTLOGS_CRON_SECRET';
    const SETTINGS_LAST_CRON_EXECUTION = 'COLLECTLOGS_CRON_TS';
    const SETTINGS_SEND_NEW_ERRORS_EMAIL = 'COLLECTLOGS_SEND_NEW_ERRORS_EMAIL';
    const SETTINGS_NEW_ERRORS_EMAIL_ADDRESSES = 'COLLECTLOGS_NEW_ERRORS_EMAIL';
    const SETTINGS_LOG_TO_FILE = 'COLLECTLOGS_LOG_TO_FILE';
    const SETTINGS_LOG_TO_FILE_NEW_ONLY = 'COLLECTLOGS_LOG_TO_FILE_NEW_ONLY';
    const SETTINGS_LOG_TO_FILE_SEVERITY = 'COLLECTLOGS_LOG_TO_FILE_SEVERITY';
    const SETTINGS_LAST_SYNC = 'COLLECTLOGS_SYNC_TS';

    /**
     * @return bool
     */
    public function cleanup()
    {
        try {
            // delete everything that starts with SETTINGS_*
            $reflection = new ReflectionClass(static::class);
            foreach ($reflection->getConstants() as $key => $configKey) {
                if (strpos($key, "SETTINGS_") === 0) {
                    Configuration::deleteByName($configKey);
                }
            }
        } catch (Throwable $ignored) {
        }

        return true;
    }

    /**
     * @return string
     * @throws PrestaShopException
     */
    public function getSecret()
    {
        $value = Configuration::getGlobalValue(static::SETTINGS_SECRET);
        if (!$value) {
            $value = Tools::passwdGen(32);
            Configuration::updateGlobalValue(static::SETTINGS_SECRET, $value);
        }
        return $value;
    }

    /**
     * @return int
     * @throws PrestaShopException
     */
    public function getCronLastExec()
    {
        return (int)Configuration::getGlobalValue(static::SETTINGS_LAST_CRON_EXECUTION);
    }

    /**
     * @return void
     * @throws PrestaShopException
     */
    public function updateCronLastExec()
    {
        Configuration::updateGlobalValue(static::SETTINGS_LAST_CRON_EXECUTION, time() - 1);
    }

    /**
     * @return int
     * @throws PrestaShopException
     */
    public function getLastSync()
    {
        return (int)Configuration::getGlobalValue(static::SETTINGS_LAST_SYNC);
    }

    /**
     * @return void
     * @throws PrestaShopException
     */
    public function updateLastSync(int $ts)
    {
        Configuration::updateGlobalValue(static::SETTINGS_LAST_SYNC, $ts);
    }

    /**
     * @return int
     *
     * @throws PrestaShopException
     */
    public function getLogToFileMinSeverity()
    {
        $value = (int)Configuration::getGlobalValue(static::SETTINGS_LOG_TO_FILE_SEVERITY);
        if (! Severity::isSeverityLevel($value)) {
            return $this->setLogToFileMinSeverity(Severity::SEVERITY_DEPRECATION);
        }
        return $value;
    }

    /**
     * @param int $value
     *
     * @return int
     * @throws PrestaShopException
     */
    public function setLogToFileMinSeverity($value)
    {
        $value = (int)$value;
        if (! Severity::isSeverityLevel($value)) {
            $value = Severity::SEVERITY_DEPRECATION;
        }
        Configuration::updateGlobalValue(static::SETTINGS_LOG_TO_FILE_SEVERITY, $value);
        return $value;
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function getLogToFile()
    {
        return $this->getBoolValue(static::SETTINGS_LOG_TO_FILE, false);
    }

    /**
     * @param bool $value
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function setLogToFile($value)
    {
        return $this->setBoolValue(static::SETTINGS_LOG_TO_FILE, $value);
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function getLogToFileNewOnly()
    {
        return $this->getBoolValue(static::SETTINGS_LOG_TO_FILE_NEW_ONLY, true);
    }

    /**
     * @param bool $value
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function setLogToFileNewOnly($value)
    {
        return $this->setBoolValue(static::SETTINGS_LOG_TO_FILE_NEW_ONLY, $value);
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function getSendNewErrorsEmail()
    {
        return $this->getBoolValue(static::SETTINGS_SEND_NEW_ERRORS_EMAIL, false);
    }

    /**
     * @param bool $value
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function setSendNewErrorsEmail($value)
    {
        return $this->setBoolValue(static::SETTINGS_SEND_NEW_ERRORS_EMAIL, $value);
    }

    /**
     * @return string[]
     * @throws PrestaShopException
     */
    public function getEmailAddresses()
    {
        $strValue = Configuration::getGlobalValue(static::SETTINGS_NEW_ERRORS_EMAIL_ADDRESSES);
        if (is_null($strValue) || $strValue === false) {
            return [];
        }
        return explode("\n", $strValue);
    }

    /**
     * @param string[] $emails
     *
     * @return string[]
     * @throws PrestaShopException
     */
    public function setEmailAddresses(array $emails)
    {
        if ($emails) {
            $strValue = implode("\n", $emails);
            Configuration::updateGlobalValue(static::SETTINGS_NEW_ERRORS_EMAIL_ADDRESSES, $strValue);
        } else {
            Configuration::deleteByName(static::SETTINGS_NEW_ERRORS_EMAIL_ADDRESSES);
        }
        return $emails;
    }

    /**
     * @param string $key
     * @param bool $default
     *
     * @return bool
     * @throws PrestaShopException
     */
    protected function getBoolValue($key, $default)
    {
        $value = Configuration::getGlobalValue($key);
        if (is_null($value) || $value === false) {
            return $this->setBoolValue($key, $default);
        }
        return (bool)$value;
    }

    /**
     * @param string $key
     * @param bool $value
     *
     * @return bool
     * @throws PrestaShopException
     */
    protected function setBoolValue($key, $value)
    {
        $value = (bool)$value;
        Configuration::updateGlobalValue($key, (int)$value);
        return $value;
    }

    /**
     * @param string $email
     *
     * @return string
     * @throws PrestaShopException
     */
    public function getUnsubscribeSecret($email)
    {
        return md5($email . $this->getSecret());
    }


}
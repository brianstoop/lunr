<?php

/**
 * This file contains the abstract definition for the
 * Gettext Localization Provider.
 *
 * PHP Version 5.3
 *
 * @category   Libraries
 * @package    L10n
 * @subpackage Libraries
 * @author     M2Mobi <info@m2mobi.com>
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 */

namespace Lunr\Libraries\L10n;

/**
 * Gettext Localization Provider class
 *
 * @category   Libraries
 * @package    L10n
 * @subpackage Libraries
 * @author     M2Mobi <info@m2mobi.com>
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 */
class GettextL10nProvider extends L10nProvider
{

    /**
     * Reference to the Configuration class.
     * @var Configuration
     */
    private $configuration;

    /**
     * Reference to the Logger class.
     * @var Logger
     */
    private $logger;

    /**
     * Define gettext msgid size limit
     * @var Integer
     */
    const GETTEXT_MAX_MSGID_LENGTH = 4096;

    /**
     * Constructor.
     *
     * @param String        $language       POSIX locale definition
     * @param Configuration &$configuration Reference to the Configuration class
     * @param Logger        &$logger        Reference to the Logger class
     */
    public function __construct($language, &$configuration, &$logger)
    {
        parent::__construct($language);

        $this->configuration =& $configuration;
        $this->logger =& $logger;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Initialization method for setting up the provider.
     *
     * @param String $language POSIX locale definition
     *
     * @return void
     */
    protected function init($language)
    {
        setlocale(LC_MESSAGES, $language);
        bindtextdomain($this->configuration['l10n']['domain'], $this->configuration['l10n']['locales']);
        textdomain($this->configuration['l10n']['domain']);
    }

    /**
     * Return a translated string.
     *
     * @param String $identifier Identifier for the requested string
     * @param String $context    Context information fot the requested string
     *
     * @return String $string Translated string, identifier by default
     */
    public function lang($identifier, $context = '')
    {
        if (strlen($identifier) + strlen($context) + 1 > self::GETTEXT_MAX_MSGID_LENGTH)
        {
            $this->logger->log_error('Identifier too long: ' . $identifier);
            return $identifier;
        }

        $this->init($this->language);

        if ($context == '')
        {
            return gettext($identifier);
        }

        // Glue msgctxt and msgid together, with ASCII character 4
        // (EOT, End Of Text)
        $composed = "{$context}\004{$identifier}";
        $output = dcgettext($this->configuration['l10n']['domain'], $composed, LC_MESSAGES);

        if (($output == $composed) && ($this->language != $this->configuration['l10n']['default_language']))
        {
            return $identifier;
        }
        else
        {
            return $output;
        }
    }

    /**
     * Return a translated string, with proper singular/plural form.
     *
     * @param String  $singular Identifier for the singular version of
     *                          the string
     * @param String  $plural   Identifier for the plural version of
     *                          the string
     * @param Integer $amount   The amount the translation should be based on
     * @param String  $context  Context information fot the requested string
     *
     * @return String $string Translated string, identifier by default
     */
    public function nlang($singular, $plural, $amount, $context = '')
    {
        if (strlen($singular) + strlen($context) + 1 > self::GETTEXT_MAX_MSGID_LENGTH)
        {
            $this->logger->log_error('Identifier too long: ' . $singular);
            return $singular;
        }

        $this->init($this->language);

        if ($context == '')
        {
            return ngettext($singular, $plural, $amount);
        }

        // Glue msgctxt and msgid together, with ASCII character 4
        // (EOT, End Of Text)
        $composed = "{$context}\004{$singular}";
        $output = dcngettext($this->configuration['l10n']['domain'], $composed, $plural, $amount, LC_MESSAGES);

        if ((($output == $composed) || ($output == $plural))
            && ($this->language != $this->configuration['l10n']['default_language']))
        {
            return ($amount == 1 ? $singular : $plural);
        }
        else
        {
            return $output;
        }
    }

}

?>

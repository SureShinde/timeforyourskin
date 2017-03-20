<?php

/**
 * Model for the languages
 */
class eKomiLanguage
{
    // Default language
    private $language;

    // Default language
    private $defaultLanguage = 'de';

    // Available languages
    private $ekomiLanguages = array(
        'de' => array(
            'code' => 'de',
            'name' => 'Deutsch',
            'website' => 'http://www.ekomi.de/de/',
        ),
        'es' => array(
            'code' => 'es',
            'name' => 'Español',
            'website' => 'http://www.ekomi.es/es/',
        ),
        'fr' => array(
            'code' => 'fr',
            'name' => 'Français',
            'website' => 'http://www.ekomi.fr/fr/',
        ),
        'it' => array(
            'code' => 'it',
            'name' => 'Italiano',
            'website' => 'http://www.ekomi.it/it/',
        ),
        'nl' => array(
            'code' => 'nl',
            'name' => 'Nederlands',
            'website' => 'http://www.ekomi.nl/nl/',
        ),
        'pl' => array(
            'code' => 'pl',
            'name' => 'Polski',
            'website' => 'http://www.ekomi-pl.com/',
        ),
        'pt' => array(
            'code' => 'pt',
            'name' => 'Português',
            'website' => 'http://www.ekomi.pt/pt/',
        ),
        'uk' => array(
            'code' => 'en',
            'name' => 'English UK',
            'website' => 'http://www.ekomi.co.uk/uk/',
        ),
        'us' => array(
            'code' => 'en',
            'name' => 'English US',
            'website' => 'http://www.ekomi-us.com/us/',
        )
    );

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get the language attributes
     *
     * @return array
     */
    public function getLanguageAtts()
    {
        return array_key_exists($this->language, $this->ekomiLanguages) ? $this->ekomiLanguages[$this->language] : $this->ekomiLanguages[$this->defaultLanguage];
    }

    public function getEkomiLanguages()
    {
        // Remove uk and us
        $languages = $this->ekomiLanguages;
        unset($languages['us']);
        unset($languages['uk']);

        // Add en language
        $languages['en'] = array(
            'code' => 'en',
            'name' => 'English',
            'website' => 'http://www.ekomi.de/',
        );

        return $languages;
    }

}
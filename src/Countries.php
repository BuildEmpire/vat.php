<?php

namespace DvK\Vat;

/**
 * Class Countries
 *
 * This class contains a few helpers methods for dealing with ISO-3166-1-alpha2 countries (and EC exceptions)
 *
 * @package DvK\Vat
 */
class Countries {

    /**
     * Country objects
     * 
     * @var $countries
     */
    private $countries = [];

    /**
     * Countries class constructor
     *
     */
    public function __construct()
    {
        global $DB;

        $results = $DB->get_records('cscore_countries');

        if ($results) {
            $this->countries = $results;
        }
    }

    /**
     * Get all countries with all info
     *
     * @return array
     */
    public function getAllInfo()
    {
        $countries = [];

        foreach ($this->countries as $country) {
            $country->currencyInfo   = $DB->get_record('cscore_country_currency_info', ['id' => $country->id]) ?: null;
            $country->region         = $DB->get_record('cscore_regions', ['id' => $country->region_id]) ?: null;
            $country->subRegion      = $DB->get_record('cscore_subregions', ['id' => $country->subregion_id]) ?: null;
            $country->specificRegion = $DB->get_record('cscore_specificregions', ['id' => $country->specific_region_id]) ?: null;

            $countries[] = $country;
        }

        return $countries;
    }

    /**
     * Get all countries in code => name format
     *
     * @return array
     */
    public function all() 
    {
        $countries = [];

        foreach ($this->countries as $countryObj) {
            $countries[$countryObj->alpha_two_code] = $countryObj->name;
        }

        return $countries;
    }

    /**
     * Get all EU countries in code => name format
     *
     * @return array
     */
    public function europe() 
    {
        $countries = [];

        foreach ($this->countries as $countryObj){
            if ($countryObj->eu) {
                $countries[$countryObj->alpha_two_code] = $countryObj->name;
            }
        }

        return $countries;
    }

    /**
     * Get full country name for a given country code
     *
     * @param string $code
     *
     * @return string
     */
    public function name($code) 
    {
        foreach ($this->countries as $countryObj) {
            if ($countryObj->alpha_two_code == $code) {
                return $countryObj->name;
            }
        }
    }

    /**
     * Checks whether the given country is in the EU
     *
     * @param string $code
     *
     * @return bool
     */
    public function inEurope($code) 
    {
        foreach ($this->countries as $countryObj) {
            if ($countryObj->alpha_two_code == $code) {
                return $countryObj->eu;
            }
        }
    }

    /**
     * Gets the country code by IP address
     *
     * @link http://about.ip2c.org/
     *
     * @param string $ip
     *
     * @return string
     */
    public function ip($ip) 
    {
        $url = 'http://ip2c.org/' . $ip;

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        $response_body = curl_exec($curl_handle);
        curl_close($curl_handle);

        if(!empty($response_body)) {
            $parts = explode( ';', $response_body );
            return $parts[1] === 'ZZ' ? '' : $parts[1];
        }

        return '';
    }

    /**
     * Get country codes which are used by European Commissions (exceptions to ISO-3166-1-alpha2)
     *
     * @link
     *
     * @param string $code
     * @return string
     */
    public function fixCode($code) 
    {
        static $exceptions = [
            'GR' => 'EL',
            'UK' => 'GB',
        ];

        if (isset($exceptions[$code])) {
            return $exceptions[$code];
        }

        return $code;
    }

}

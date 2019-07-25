<?php
namespace DvK\Vat;

/**
 * Class Validator
 *
 * @package DvK\Vat
 */
class Validator {

    /**
     * Regular expression patterns per country code
     *
     * @var array
     * @link http://ec.europa.eu/taxation_customs/vies/faq.html?locale=lt#item_11
     */
    protected static $patterns = array(
        'EU' => '\[0-9a-zA-Z]{8,}',
        'GB' => '\d{9}|\d{12}|(GD|HA)\d{3}',
    );

    /**
     * VatValidator constructor.
     *
     * @param Vies\Client $client        (optional)
     */
    public function __construct( Vies\Client $client = null ) {
        $this->client = $client;

        if( ! $this->client ) {
            $this->client = new Vies\Client();
        }
    }

    /**
     * Validate a VAT number format. This does not check whether the VAT number was really issued.
     *
     * @param string $vatNumber
     *
     * @return boolean
     */
    public function validateFormat($vatNumber, $countryCode) {
        $countryCode = strtoupper($countryCode);
        $country     = strtoupper(substr($vatNumber, 0, 2));

        if ($countryCode === 'GB') {
            if ($country === 'GB') {
                return preg_match( '/^' . self::$patterns['GB'] . '$/', substr($vatNumber, 2) ) > 0;
            }

            return false;
        }

        return preg_match( '/^' . self::$patterns['EU'] . '$/', $vatNumber ) > 0;
    }

    /**
     *
     * @param string $vatNumber
     *
     * @return boolean
     *
     * @throws Vies\ViesException
     */
    public function validateExistence($vatNumber) {
        $vatNumber = strtoupper( $vatNumber );
        $country = substr( $vatNumber, 0, 2 );
        $number = substr( $vatNumber, 2 );
        return $this->client->checkVat($country, $number);
    }

    /**
     * Validates a VAT number using format + existence check.
     *
     * @param string $vatNumber Either the full VAT number (incl. country) or just the part after the country code.
     *
     * @return boolean
     *
     * @throws Vies\ViesException
     */
    public function validate( $vatNumber ) {
        return $this->validateFormat( $vatNumber ) && $this->validateExistence( $vatNumber );
    }


}

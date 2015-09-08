<?php namespace Mpociot\VatCalculator\Http;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use Mpociot\VatCalculator\VatCalculator;

class Controller extends BaseController
{

    /**
     * @var VatCalculator
     */
    private $calculator;

    public function __construct(VatCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Returns the tax rate for the given country
     *
     * @return \Illuminate\Http\Response
     */
    public function getTaxRateForCountry($country = null)
    {
        return [
            "tax_rate" => $this->calculator->getTaxRateForCountry($country)
        ];
    }

    /**
     * Returns the tax rate for the given country
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function calculateGrossPrice(Request $request)
    {
        if (!$request->has('netPrice')) {
            return Response::json( [
                "error" => "The 'netPrice' parameter is missing"
            ], 422 );
        }

        $valid_company = false;
        if( $request->has('vat_number') )
        {
            $valid_company = $this->validateVATID( $request->get('vat_number') );
            $valid_company = $valid_company["is_valid"];
        }

        return [
            "gross_price" => $this->calculator->calculate($request->get('netPrice'), $request->get('country'), $valid_company),
            "net_price"   => $this->calculator->getNetPrice(),
            "tax_rate"    => $this->calculator->getTaxRate(),
            "tax_value"   => $this->calculator->getTaxValue(),
        ];
    }

    /**
     * Returns the tax rate for the given country
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountryCode()
    {
        return [
            "country_code" => $this->calculator->getIPBasedCountry(),
        ];
    }

    /**
     * Returns the tax rate for the given country
     *
     * @param string $vat_id
     * @return \Illuminate\Http\Response
     * @throws \Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException
     */
    public function validateVATID($vat_id)
    {
        try {
            $isValid = $this->calculator->isValidVATNumber($vat_id);
            $message = "";
        } catch (VATCheckUnavailableException $e) {
            $isValid = false;
            $message = $e->getMessage();
        }
        return [
            "vat_id"   => $vat_id,
            "is_valid" => $isValid,
            "message"  => $message,
        ];
    }

}
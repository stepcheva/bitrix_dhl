<?php


class ParamsDto
{
    public $date;
    public $zip_from;
    public $zip_to;
    public $weight;

    public $siteId;
    public $password;
    public $account;

    public function __construct($siteId, $password, $account)
    {
        $this->siteId = $siteId;
        $this->password = $password;
        $this->account = $account;
    }
}

interface XMLgenerator
{
    public function __construct(ParamsDto $param);

    public function generate();
}

class DomDocumentGenerator implements XMLgenerator
{
    private $params;
    private $xml;

    public function __construct(ParamsDto $param)
    {
        $this->params = $param;
    }

    public function generate()
    {
        $this->xml = new \DOMDocument('1.0', 'UTF-8');

        $p = $this->xml->createElement("p:DCTRequest");
        $p = $this->xml->appendChild($p);

        $p->setAttribute( "xmlns:p", "http://www.dhl.com" );
        $p->setAttribute( "xmlns:p1", "http://www.dhl.com/datatypes" );
        $p->setAttribute( "xmlns:p2", "http://www.dhl.com/DCTRequestdatatypes" );
        $p->setAttribute( "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance" );

        $getQuote = $this->xml->createElement("GetQuote");
        $getQuote = $p->appendChild($getQuote);

        $Request = $this->xml->createElement("Request");
        $Request = $getQuote->appendChild($Request);

        $ServiceHeader = $this->xml->createElement("ServiceHeader");
        $ServiceHeader = $Request->appendChild($ServiceHeader);

        $ServiceHeader->appendChild($this->xml->createElement('MessageTime', date('Y-m-d').'T'.date('H:i:sP')));
        $ServiceHeader->appendChild($this->xml->createElement('MessageReference','1234567890123456789012345678901'));
        $ServiceHeader->appendChild($this->xml->createElement('SiteID', $this->params->siteId));
        $ServiceHeader->appendChild($this->xml->createElement('Password', $this->params->password));


        $From = $this->xml->createElement("From");
        $From = $getQuote->appendChild($From);

        $From->appendChild($this->xml->createElement('CountryCode','RU'));
        $From->appendChild($this->xml->createElement('Postalcode',$this->params->zip_from));

        $BkgDetails = $this->xml->createElement("BkgDetails");
        $BkgDetails = $getQuote->appendChild($BkgDetails);

        $BkgDetails->appendChild($this->xml->createElement('PaymentCountryCode','RU'));
        $BkgDetails->appendChild($this->xml->createElement('Date', $this->params->date));
        $BkgDetails->appendChild($this->xml->createElement('ReadyTime','PT10H00M'));
        $BkgDetails->appendChild($this->xml->createElement('DimensionUnit','CM'));
        $BkgDetails->appendChild($this->xml->createElement('WeightUnit','KG'));
        $BkgDetails->appendChild($this->xml->createElement('ShipmentWeight', $this->params->weight));
        $BkgDetails->appendChild($this->xml->createElement('PaymentAccountNumber', $this->params->account));
        $BkgDetails->appendChild($this->xml->createElement('IsDutiable','N'));
        $BkgDetails->appendChild($this->xml->createElement('NetworkTypeCode','TD'));

        $QtdShp = $this->xml->createElement("QtdShp");
        $QtdShp = $BkgDetails->appendChild($QtdShp);

        $QtdShp->appendChild($this->xml->createElement('GlobalProductCode','N'));
        $QtdShp->appendChild($this->xml->createElement('LocalProductCode','N'));

        $To = $this->xml->createElement("To");
        $To = $getQuote->appendChild($To);

        $To->appendChild($this->xml->createElement('CountryCode','RU'));
        $To->appendChild($this->xml->createElement('Postalcode', $this->params->zip_to));

        return $this->xml->saveXML();
    }

}
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
    public function generate(ParamsDto $param);
}

class DomDocumentGenerator implements XMLgenerator
{
    private $xml;

    public function generate(ParamsDto $param)
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
        $ServiceHeader->appendChild($this->xml->createElement('SiteID', $param->siteId));
        $ServiceHeader->appendChild($this->xml->createElement('Password', $param->password));


        $From = $this->xml->createElement("From");
        $From = $getQuote->appendChild($From);

        $From->appendChild($this->xml->createElement('CountryCode','RU'));
        $From->appendChild($this->xml->createElement('Postalcode', $param->zip_from));

        $BkgDetails = $this->xml->createElement("BkgDetails");
        $BkgDetails = $getQuote->appendChild($BkgDetails);

        $BkgDetails->appendChild($this->xml->createElement('PaymentCountryCode','RU'));
        $BkgDetails->appendChild($this->xml->createElement('Date', $param->date));
        $BkgDetails->appendChild($this->xml->createElement('ReadyTime','PT10H00M'));
        $BkgDetails->appendChild($this->xml->createElement('DimensionUnit','CM'));
        $BkgDetails->appendChild($this->xml->createElement('WeightUnit','KG'));
        $BkgDetails->appendChild($this->xml->createElement('ShipmentWeight', $param->weight));
        $BkgDetails->appendChild($this->xml->createElement('PaymentAccountNumber', $param->account));
        $BkgDetails->appendChild($this->xml->createElement('IsDutiable','N'));
        $BkgDetails->appendChild($this->xml->createElement('NetworkTypeCode','TD'));

        $QtdShp = $this->xml->createElement("QtdShp");
        $QtdShp = $BkgDetails->appendChild($QtdShp);

        $QtdShp->appendChild($this->xml->createElement('GlobalProductCode','N'));
        $QtdShp->appendChild($this->xml->createElement('LocalProductCode','N'));

        $To = $this->xml->createElement("To");
        $To = $getQuote->appendChild($To);

        $To->appendChild($this->xml->createElement('CountryCode','RU'));
        $To->appendChild($this->xml->createElement('Postalcode', $param->zip_to));

        return $this->xml->saveXML();
    }

}

class StringXMLGenerator implements XMLgenerator
{
    public function generate(ParamsDto $param)
    {
        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xmlStr .= '<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $xmlStr .= '<GetQuote><Request><ServiceHeader><MessageTime>'.date('Y-m-d').'T'.date('H:i:sP').'</MessageTime><MessageReference>1234567890123456789012345678901</MessageReference><SiteID>';
        $xmlStr .= $param->siteId .'</SiteID><Password>'.$param->password.'</Password></ServiceHeader></Request><From><CountryCode>RU</CountryCode><Postalcode>';
        $xmlStr .= $param->zip_from .'</Postalcode></From><BkgDetails><PaymentCountryCode>RU</PaymentCountryCode><Date>'.$param->date.'</Date><ReadyTime>PT10H00M</ReadyTime><DimensionUnit>CM</DimensionUnit><WeightUnit>KG</WeightUnit><ShipmentWeight>';
        $xmlStr .= $param->weight .'</ShipmentWeight><PaymentAccountNumber>'.$param->account.'</PaymentAccountNumber><IsDutiable>N</IsDutiable><NetworkTypeCode>TD</NetworkTypeCode><QtdShp><GlobalProductCode>N</GlobalProductCode><LocalProductCode>N</LocalProductCode>';
        $xmlStr .= '</QtdShp></BkgDetails><To><CountryCode>RU</CountryCode><Postalcode>'.$param->zip_to.'</Postalcode></To></GetQuote></p:DCTRequest>';

        return $xmlStr;
    }
}
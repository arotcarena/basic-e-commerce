<?php
namespace App\Convertor;

use App\Entity\Cart;
use App\Entity\Address;
use App\Entity\CartLine;
use App\Convertor\ConvertorTrait;
use App\Convertor\ShopConvertorTrait;

class AddressToArrayConvertor
{
    use ConvertorTrait;
    use ShopConvertorTrait;

    /**
     * @param Address[]|Address $data
     * @return array
     */
    public function convert($data): array 
    {
        return $this->convertOneOrMore($data);
    }

    private function convertOne(Address $address): array 
    {
        return [
            'id' => $address->getId(),
            'civility' => $address->getCivility(),
            'firstName' => $address->getFirstName(),
            'lastName' => $address->getLastName(),
            'lineOne' => $address->getLineOne(),
            'lineTwo' => $address->getLineTwo(),
            'postcode' => $address->getPostcode(),
            'city' => $address->getCity(),
            'country' => $address->getCountry()
        ];
    }  
    
}
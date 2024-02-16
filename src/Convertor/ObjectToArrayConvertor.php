<?php
namespace App\Convertor;

use App\Convertor\ConvertorTrait;

class ObjectToArrayConvertor 
{
    use ConvertorTrait;
    
    /**
     * Undocumented function
     *
     * @param array|Object $data
     * @return array 
     */
    public function convert($data, array $propertyNames): array
    {
        return $this->convertOneOrMore($data, $propertyNames);
    }

    private function convertOne(Object $object, $propertyNames): array 
    {
        $array = [];
        foreach($propertyNames as $propertyName)
        {
            $getter = 'get' . ucfirst($propertyName);
            $array[$propertyName] = $object->$getter();
        }
        return $array;
    }
}
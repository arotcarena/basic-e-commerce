<?php
namespace App\Convertor;


trait ConvertorTrait 
{

    /**
     * @param Object[]|Object $data
     * @return array
     */
    private function convertOneOrMore($data, $propertyNames = null): array 
    {
        if(is_object($data))
        {
            return $this->convertOne($data, $propertyNames);
        }
        $array = [];
        /** @var Object $object */
        foreach($data as $object)
        {
            $array[] = $this->convertOne($object, $propertyNames);
        }
        return $array;
    }



    
}
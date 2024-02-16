<?php
namespace App\Persister;

use App\Config\SiteConfig;
use App\Convertor\PurchaseLineProductConvertor;
use stdClass;
use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Purchase;
use App\Entity\PostalDetail;
use App\Entity\PurchaseLine;
use App\Helper\FrDateTimeGenerator;
use App\Helper\PurchaseRefGenerator;
use App\Repository\AddressRepository;
use App\Repository\PurchaseRepository;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// const checkoutInitialData = {
//     civilState: {
//         civility: '',
//         firstName: '',
//         lastName: ''
//     },
//     deliveryAddress: {
//         civility: '',
//         firstName: '',
//         lastName: '',
//         lineOne: '',
//         lineTwo: '',
//         postcode: '',
//         city: '',
//         country: ''
//     },
//     invoiceAddress: {
//         lineOne: '',
//         lineTwo: '',
//         postcode: '',
//         city: '',
//         country: ''
//     }
// }
// }


class PurchasePersister 
{
    public function __construct(
        private EntityManagerInterface $em,
        private AddressRepository $addressRepository,
        private PurchaseRefGenerator $purchaseRefGenerator,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private ValidatorInterface $validator,
        private PurchaseRepository $purchaseRepository,
        private PurchaseLineProductConvertor $purchaselineProductConvertor
    )
    {

    }

    public function persist(Purchase $purchase, Cart $cart, stdClass $checkoutData, User $user = null): bool
    {
        //basics
        $purchase
                ->setRef($this->purchaseRefGenerator->generate())
                ->setTotalPrice($cart->getTotalPrice())
                ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                ;
        if($user)
        {
            $purchase->setUser($user);
        }
        
        //cart
        foreach($cart->getCartLines() as $cartLine)
        {
            $product = $cartLine->getProduct();
            $purchase->addPurchaseLine(
                (new PurchaseLine)
                ->setProduct(
                    $this->purchaselineProductConvertor->convert($product)
                )
                ->setQuantity($cartLine->getQuantity())
                ->setTotalPrice($cartLine->getTotalPrice())
            );
        }
        //checkoutData
        $purchase
            ->setDeliveryDetail(
                    $this->createDeliveryDetail($checkoutData->deliveryAddress)
                )
            ->setInvoiceDetail(
                $this->createInvoiceDetail($checkoutData->invoiceAddress, $checkoutData->civilState)
            )
            ;
                

        //validation
        if($this->validate($purchase))
        {
            $this->em->flush();
            return true;
        }
        return false;
    }

    private function validate(Purchase $purchase): bool 
    {
        if($purchase->getDeliveryDetail() === null || $purchase->getInvoiceDetail() === null)
        {
            return false;
        }

        $errors = $this->validator->validate($purchase);
        $deliveryErrors = $this->validator->validate($purchase->getDeliveryDetail());
        $invoiceErrors = $this->validator->validate($purchase->getInvoiceDetail());
        $totalErrors = count($errors) + count($deliveryErrors) + count($invoiceErrors);
        if($totalErrors !== 0)
        {
            return false;
        }
        return true;
    }


    private function createDeliveryDetail(stdClass $address): PostalDetail
    {
        return (new PostalDetail)
                ->setCivility($address->civility)
                ->setFirstName($address->firstName)
                ->setLastName($address->lastName)
                ->setLineOne($address->lineOne)
                ->setLineTwo($address->lineTwo)
                ->setPostcode($address->postcode)
                ->setCity($address->city)
                ->setCountry($address->country)
                ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                ;
    }
    private function createInvoiceDetail(stdClass $address, stdClass $civilState): PostalDetail 
    {
        return (new PostalDetail)
                ->setCivility($civilState->civility)
                ->setFirstName($civilState->firstName)
                ->setLastName($civilState->lastName)
                ->setLineOne($address->lineOne)
                ->setLineTwo($address->lineTwo)
                ->setPostcode($address->postcode)
                ->setCity($address->city)
                ->setCountry($address->country)
                ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                ;
    }

}
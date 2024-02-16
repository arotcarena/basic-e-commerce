<?php
namespace App\Controller\Api\Account;

use App\Convertor\AddressToArrayConvertor;
use App\Entity\Address;
use App\Helper\FrDateTimeGenerator;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
class ApiAddressController extends AbstractController
{
    public function __construct(
        private AddressRepository $addressRepository,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private EntityManagerInterface $em,
        private AddressToArrayConvertor $addressConvertor
    )
    {

    }

    #[Route('/api/address/index', name: 'api_address_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $addresses = $this->addressRepository->findBy(['user' => $this->getUser()]);

        return $this->json(
            $this->addressConvertor->convert($addresses)
        );
    }

    #[Route('/api/address/create', name: 'api_address_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try 
        {
            $data = json_decode($request->getContent());
            $address = (new Address)
                        ->setUser($this->getUser())
                        ->setCivility($data->civility)
                        ->setFirstName($data->firstName)
                        ->setLastName($data->lastName)
                        ->setLineOne($data->lineOne)
                        ->setLineTwo($data->lineTwo)
                        ->setPostcode($data->postcode)
                        ->setCity($data->city)
                        ->setCountry($data->country)
                        ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                    ;
            $this->em->persist($address);
            $this->em->flush();
            return $this->json($address->getId());
        }
        catch(Exception $e)
        {
            return $this->json([
                'errors' => ['L\'adresse n\'a pas pu être ajoutée, les données soumises sont invalides']
            ], 500);
        }
    }

    #[Route('/api/address/{id}/update', name: 'api_address_update', methods: ['POST'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $address = $this->addressRepository->find($id);
        if(!$address)
        {
            return $this->json([
                'errors' => ['Aucune Adresse ne possède l\'id "'.$id.'"']
            ], 500);
        }

        try 
        {
            $data = json_decode($request->getContent());
            $address 
                    ->setUser($this->getUser())
                    ->setCivility($data->civility)
                    ->setFirstName($data->firstName)
                    ->setLastName($data->lastName)
                    ->setLineOne($data->lineOne)
                    ->setLineTwo($data->lineTwo)
                    ->setPostcode($data->postcode)
                    ->setCity($data->city)
                    ->setCountry($data->country)
                    ;
            $this->em->flush();
            return $this->json($address->getId());
        }
        catch(Exception $e)
        {
            return $this->json([
                'errors' => ['L\'adresse n\'a pas pu être modifiée, les données soumises sont invalides']
            ], 500);
        }
    }

    #[Route('/api/address/delete', name: 'api_address_delete', methods: ['POST'])]
    public function delete(Request $request): JsonResponse
    {
        $id = json_decode($request->getContent());
        $address = $this->addressRepository->find($id);
        if(!$address)
        {
            return $this->json([
                'errors' => ['Aucune Adresse ne possède l\'id "'.$id.'"']
            ], 500);
        }

        $this->em->remove($address);
        $this->em->flush();

        return $this->json('ok');
    }
}
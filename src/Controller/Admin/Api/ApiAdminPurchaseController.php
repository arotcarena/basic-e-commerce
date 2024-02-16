<?php
namespace App\Controller\Admin\Api;

use Exception;
use App\Config\SiteConfig;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Email\Customer\PurchaseStatusEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[IsGranted('ROLE_ADMIN')]
class ApiAdminPurchaseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PurchaseRepository $purchaseRepository,
        private PurchaseStatusEmail $purchaseStatusEmail
    )
    {

    }

    #[Route('/admin/api/purchase/{id}/updateStatus', name: 'admin_api_purchase_updateStatus', methods: 'POST')]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $purchase = $this->purchaseRepository->find($id);
        if(!$purchase)
        {
            return new JsonResponse([
                'errors' => ['La commande avec l\'id "'.$id.'" que vous essayez de modifier n\'existe pas']
            ], 500);
        }
        
        $newStatus = json_decode($request->getContent());
        if(!in_array($newStatus, [SiteConfig::STATUS_PENDING, SiteConfig::STATUS_PAID, SiteConfig::STATUS_SENT, SiteConfig::STATUS_DELIVERED, SiteConfig::STATUS_CANCELED]))
        {
            return new JsonResponse([
                'errors' => ['Bad POST content data (field Purchase.status)']
            ], 500);
        }

        if($purchase->getStatus() !== SiteConfig::STATUS_PENDING && $newStatus === SiteConfig::STATUS_PENDING)
        {
            return new JsonResponse([
                'errors' => ['Impossible de repasser une commande au statut "en attente"']
            ], 500);
        }

        if($newStatus !== $purchase->getStatus())
        {
            try
            {
                $this->purchaseStatusEmail->send($purchase, $newStatus);
                $purchase->setStatus($newStatus);
                $this->em->flush();
            }
            catch(Exception $e)
            {
                return new JsonResponse([
                    'errors' => ['Pour une raison inconnue, l\'email de notification n\'a pas pu être envoyé au client. Veuillez réessayer ultérieurement']
                ], 500);
            }
        }
        return new JsonResponse('Le statut a bien été mis à jour, et le client notifié par email !');   // on utilise new JsonResponse plutôt que $this->json() pour permettre aux tests unitaires de fonctionner
    }
}
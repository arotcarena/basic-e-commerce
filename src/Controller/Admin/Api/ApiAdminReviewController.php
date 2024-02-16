<?php
namespace App\Controller\Admin\Api;

use App\Config\SiteConfig;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

#[IsGranted('ROLE_ADMIN')]
class ApiAdminReviewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReviewRepository $reviewRepository,
    )
    {

    }


    #[Route('/admin/api/review/{id}/updateModerationStatus', name: 'admin_api_review_updateModerationStatus', methods: ['GET'])]
    public function updateModerationStatus(int $id, Request $request): JsonResponse
    {
        $review = $this->reviewRepository->find($id);
        if(!$review)
        {
            return $this->json([
                'errors' => ['Aucune Review avec l\'id '.$id]
            ], 500);
        }

        $review->setModerationStatus($request->query->get('status', null));
        $this->em->flush();

        $info = null;
        if($review->getModerationStatus() === SiteConfig::MODERATION_STATUS_ACCEPTED)
        {
            $info = 'L\'avis est désormais visible !';
        }
        if($review->getModerationStatus() === SiteConfig::MODERATION_STATUS_REFUSED)
        {
            $info = 'L\'avis ne sera pas visible !';
        }
        return $this->json($info);
    }
}
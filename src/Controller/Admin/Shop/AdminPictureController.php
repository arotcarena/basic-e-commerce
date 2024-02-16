<?php
namespace App\Controller\Admin\Shop;

use Exception;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;


#[IsGranted('ROLE_ADMIN')]
class AdminPictureController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PictureRepository $pictureRepository
    )
    {

    }

    #[Route('/admin/picture/{id}/delete', name: 'admin_picture_delete', methods: ['GET'])]
    public function delete(int $id): Response 
    {
        $picture = $this->pictureRepository->find($id);
        $product = $picture->getProduct();
        if(!$picture || !$product)
        {
            throw new NotFoundResourceException();
        }
        if($picture->getListPosition() === 1)
        {
            throw new Exception('Impossible de supprimer la photo principale !');
        }

        $picturePosition = $picture->getListPosition();
        $this->em->remove($picture);
        $this->em->flush();

        $this->addFlash('success', 'La photo n° '.$picturePosition.' a bien été supprimée !');
        return $this->redirectToRoute('admin_product_update', [
            'id' => $product->getId()
        ]);
    }
}
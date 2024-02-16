<?php
namespace App\Controller\Shop;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Helper\FrDateTimeGenerator;
use App\Repository\ProductRepository;
use App\Service\ProductShowUrlResolver;
use App\Service\UserBoughtProductVerificator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ReviewController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private EntityManagerInterface $em,
        private UserBoughtProductVerificator $userBoughtProductVerificator,
        private ProductShowUrlResolver $productShowUrlResolver
    )
    {

    }


    #[IsGranted('ROLE_USER')]
    #[Route('/laisser-un-avis/{productSlug}_{publicRef}.html', name: 'review_create', methods: ['GET', 'POST'], priority: 1)]
    public function create(Request $request, string $publicRef): Response
    {
        $product = $this->productRepository->findOneByPublicRef($publicRef);
        if(!$product)
        {
            throw new NotFoundResourceException('Aucun produit correspondant');
        }
        if(!$this->userBoughtProductVerificator->verify($this->getUser(), $product))
        {
            throw new UnauthorizedHttpException('Vous ne pouvez pas laisser un avis pour ce produit, car vous ne l\'avez pas encore acheté');
        }

        $review = new Review;
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $review->setProduct($product)
                    ->setUser($this->getUser())
                    ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                    ;
            $this->em->persist($review);
            $this->em->flush();
            $this->addFlash('success', 'Merci pour votre avis. Il a bien été enregistré');
            return $this->redirectToRoute('home');
        }

        return $this->render('shop/review/create.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'product_show_url' => $this->productShowUrlResolver->getUrl($product)
        ]);
    }
}
<?php
namespace App\Controller\Admin\Shop;

use App\Entity\Product;
use App\Form\Admin\ProductType;
use App\Helper\FrDateTimeGenerator;
use App\Form\Admin\ProductFilterType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Admin\DataModel\ProductFilter;
use App\Service\PictureUploadHelper;
use App\Service\ProductHasFirstPictureVerificator;
use App\Service\UniqueSlugVerificator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

#[IsGranted('ROLE_ADMIN')]
class AdminProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private PictureUploadHelper $pictureUploadHelper,
        private UniqueSlugVerificator $uniqueSlugVerificator,
        private ProductHasFirstPictureVerificator $productHasFirstPictureVerificator
    )
    {

    }


    #[Route('/admin/product/index', name: 'admin_product_index')]
    public function index(Request $request): Response
    {
        $productFilter = new ProductFilter;
        $filterForm = $this->createForm(ProductFilterType::class, $productFilter);
        $filterForm->handleRequest($request);

        $pagination = $this->productRepository->adminFilter($request, $productFilter);
        
        return $this->render('admin/shop/product/index.html.twig', [
            'filter_form' => $filterForm->createView(),
            'pagination' => $pagination,
            'count_products' => $this->productRepository->count([])
        ]);
    }

    #[Route('/admin/product/show/{id}', name: 'admin_product_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $product = $this->productRepository->find($id);
        if(!$product)
        {
            throw new NotFoundResourceException('pas de product avec l\'id '.$id);
        }
        return $this->render('admin/shop/product/show.html.twig', [
            'product' => $product,
            'count_products' => $this->productRepository->count([])
        ]);
    }

    #[Route('/admin/product/update/{id}', name: 'admin_product_update', requirements: ['id' => '\d+'])]
    public function update(Request $request, int $id): Response
    {
        $product = $this->productRepository->find($id);
        if(!$product)
        {
            throw new NotFoundResourceException('pas de product avec l\'id '.$id);
        }
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            //AJOUT D UNE VALIDATION : on vérifie que le slug n'est pas déjà utilisé par un product de la même Category/SubCategory
            if(!$this->uniqueSlugVerificator->verify($product))
            {
                $form->get('slug')->addError(new FormError('Ce slug est déjà utilisé par un produit de la même catégorie/sous-catégorie'));
            }
            if(!$this->productHasFirstPictureVerificator->verify($product) && !$form->get('pictureOne')->getData())
            {
                $form->get('pictureOne')->addError(new FormError('La photo principale est obligatoire'));
            }
            if($form->isValid())
            {
                $product->setCreatedAt($this->frDateTimeGenerator->generateImmutable());
                // on précise au pictureUploadHelper la position de chaque picture
                $this->pictureUploadHelper->uploadProductPictures([
                    1 => [
                        'file' => $form->get('pictureOne')->getData(),
                        'alt' => $form->get('altOne')->getData()
                    ],
                    2 => [
                        'file' => $form->get('pictureTwo')->getData(),
                        'alt' => $form->get('altTwo')->getData()
                    ],
                    3 => [
                        'file' => $form->get('pictureThree')->getData(),
                        'alt' => $form->get('altThree')->getData()
                    ]
                ], $product);

                $this->em->flush();
                $this->addFlash('success', 'Le produit '.$product->getDesignation().' a bien été modifié !');
                return $this->redirectToRoute('admin_product_show', [
                    'id' => $product->getId()
                ]);
            }
        }

        return $this->render('admin/shop/product/update.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'count_products' => $this->productRepository->count([])
        ]);
    }

    #[Route('/admin/product/create', name: 'admin_product_create')]
    public function create(Request $request): Response
    {
        $product = new Product;
        $form = $this->createForm(ProductType::class, $product, [
            'validation_groups' => ['Default', 'create']
        ]);
        $form->handleRequest($request);
        
        if($form->isSubmitted())
        {
            //AJOUT D UNE VALIDATION : on vérifie que le slug n'est pas déjà utilisé par un product de la même Category/SubCategory
            if(!$this->uniqueSlugVerificator->verify($product))
            {
                $form->get('slug')->addError(new FormError('Ce slug est déjà utilisé par un produit de la même catégorie/sous-catégorie'));
            }
            if($form->isValid())
            {
                $product->setCreatedAt($this->frDateTimeGenerator->generateImmutable());
                // on précise au pictureUploadHelper la position de chaque picture
                $this->pictureUploadHelper->uploadProductPictures([
                    1 => [
                        'file' => $form->get('pictureOne')->getData(),
                        'alt' => $form->get('altOne')->getData()
                    ],
                    2 => [
                        'file' => $form->get('pictureTwo')->getData(),
                        'alt' => $form->get('altTwo')->getData()
                    ],
                    3 => [
                        'file' => $form->get('pictureThree')->getData(),
                        'alt' => $form->get('altThree')->getData()
                    ]
                ], $product);

                $this->em->persist($product);
                $this->em->flush();
                $this->addFlash('success', 'Le produit '.$product->getDesignation().' a bien été ajouté !');
                return $this->redirectToRoute('admin_product_show', [
                    'id' => $product->getId()
                ]);
            }
        }
        

        return $this->render('admin/shop/product/create.html.twig', [
            'form' => $form->createView(),
            'count_products' => $this->productRepository->count([])
        ]);
    }

    #[Route('/admin/product/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Request $request): Response 
    {
        $product = $this->productRepository->find($request->request->get('id'));
        if(!$product)
        {
            throw new NotFoundResourceException();
        }
        $productDesignation = $product->getDesignation();

        $this->em->remove($product);
        $this->em->flush();
        $this->addFlash('success', 'Le produit '.$productDesignation.' a bien été supprimé !');
        return $this->redirectToRoute('admin_product_index');
    }

}
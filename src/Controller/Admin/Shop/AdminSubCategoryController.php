<?php
namespace App\Controller\Admin\Shop;

use App\Entity\SubCategory;
use App\Form\Admin\SubCategoryType;
use App\Helper\FrDateTimeGenerator;
use App\Service\PictureUploadHelper;
use Symfony\Component\Form\FormError;
use App\Repository\CategoryRepository;
use App\Service\UniqueSlugVerificator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UniqueListPositionVerificator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;


#[IsGranted('ROLE_ADMIN')]
class AdminSubCategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private SubCategoryRepository $subCategoryRepository,
        private EntityManagerInterface $em,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private PictureUploadHelper $pictureUploadHelper,
        private UniqueSlugVerificator $uniqueSlugVerificator,
        private UniqueListPositionVerificator $uniqueListPositionVerificator
    )
    {

    }


    #[Route('/admin/subcategory/show/{id}', name: 'admin_subCategory_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $subCategory = $this->subCategoryRepository->find($id);
        if(!$subCategory)
        {
            throw new NotFoundResourceException('pas de Sous-catégorie avec l\'id '.$id);
        }
        return $this->render('admin/shop/subCategory/show.html.twig', [
            'subCategory' => $subCategory,
            'category' => $this->categoryRepository->findOneOrdered($subCategory->getParentCategory()->getId()),  // pour que les sous-catégories existantes soient dans le bon ordre
            'count_categories' => $this->categoryRepository->count([])
        ]);
    }

    #[Route('/admin/subcategory/update/{id}', name: 'admin_subCategory_update', requirements: ['id' => '\d+'])]
    public function update(Request $request, int $id): Response
    {
        $subCategory = $this->subCategoryRepository->find($id);
        if(!$subCategory)
        {
            throw new NotFoundResourceException('pas de Sous-catégorie avec l\'id '.$id);
        }
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);
        
        if($form->isSubmitted())
        {
            if(!$this->uniqueSlugVerificator->verify($subCategory))
            {
                $form->get('slug')->addError(new FormError('Ce slug est déjà utilisé par une sous-catégorie de la même catégorie'));
            }
            if(!$this->uniqueListPositionVerificator->verifySubCategory($subCategory))
            {
                $form->get('listPosition')->addError(new FormError('Cette position est déjà utilisée pour une sous-catégorie de la même catégorie'));
            }
            
            if($form->isValid())
            {
                if($form->get('picture')->getData())
                {
                    $this->pictureUploadHelper->uploadSubCategoryPicture(
                        [
                            'file' => $form->get('picture')->getData(),
                            'alt' => $form->get('alt')->getData()
                        ], 
                        $subCategory
                    );
                }
                
                $this->em->flush();
                $this->addFlash('success', 'La Sous-catégorie '.$subCategory->getName().' a bien été modifiée !');
                return $this->redirectToRoute('admin_category_index');
            }
        }

        return $this->render('admin/shop/subCategory/update.html.twig', [
            'form' => $form->createView(),
            'subCategory' => $subCategory,
            'category' => $subCategory->getParentCategory(),
            'count_categories' => $this->categoryRepository->count([])
        ]);
    }

    #[Route('/admin/subcategory/create', name: 'admin_subCategory_create')]
    public function create(Request $request): Response
    {
        $subCategory = new SubCategory;
        $form = $this->createForm(SubCategoryType::class, $subCategory, [
            'validation_groups' => ['Default', 'create']
        ]);
        $form->handleRequest($request);
        
        if($form->isSubmitted())
        {
            if(!$this->uniqueSlugVerificator->verify($subCategory))
            {
                $form->get('slug')->addError(new FormError('Ce slug est déjà utilisé par une sous-catégorie de la même catégorie'));
            }
            if(!$this->uniqueListPositionVerificator->verifySubCategory($subCategory))
            {
                $form->get('listPosition')->addError(new FormError('Cette position est déjà utilisée pour une sous-catégorie de la même catégorie (voir liste ci-dessus)'));
            }
            
            if($form->isValid())
            {
                $subCategory->setCreatedAt($this->frDateTimeGenerator->generateImmutable());
                $this->pictureUploadHelper->uploadSubCategoryPicture(
                    [
                        'file' => $form->get('picture')->getData(),
                        'alt' => $form->get('alt')->getData()
                    ], 
                    $subCategory
                );
                $this->em->persist($subCategory);
                $this->em->flush();
                $this->addFlash('success', 'La Sous-catégorie '.$subCategory->getName().' a bien été ajoutée !');
                return $this->redirectToRoute('admin_category_index');
            }
        }

        return $this->render('admin/shop/subCategory/create.html.twig', [
            'form' => $form->createView(),
            'count_categories' => $this->categoryRepository->count([]),
            'count_subCategories' => $this->subCategoryRepository->count([])
        ]);
    }

    #[Route('/admin/subCategory/delete', name: 'admin_subCategory_delete', methods: ['POST'])]
    public function delete(Request $request): Response 
    {
        $subCategory = $this->subCategoryRepository->find($request->request->get('id'));
        if(!$subCategory)
        {
            throw new NotFoundResourceException();
        }
        $subCategoryName = $subCategory->getName();

        $this->em->remove($subCategory);
        $this->em->flush();
        $this->addFlash('success', 'La Sous-catégorie '.$subCategoryName.' a bien été supprimée !');
        return $this->redirectToRoute('admin_category_index');
    }

}
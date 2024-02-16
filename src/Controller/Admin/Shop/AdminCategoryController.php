<?php
namespace App\Controller\Admin\Shop;

use App\Entity\Category;
use App\Form\Admin\CategoryType;
use App\Helper\FrDateTimeGenerator;
use App\Service\PictureUploadHelper;
use App\Repository\CategoryRepository;
use App\Service\UniqueSlugVerificator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;


#[IsGranted('ROLE_ADMIN')]
class AdminCategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $em,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private PictureUploadHelper $pictureUploadHelper,
        private UniqueSlugVerificator $uniqueSlugVerificator
    )
    {

    }


    #[Route('/admin/category/index', name: 'admin_category_index')]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAllOrderedForMenuList();
        
        return $this->render('admin/shop/category/index.html.twig', [
            'categories' => $categories,
            'count_categories' => $this->categoryRepository->count([])
        ]);
    }

    #[Route('/admin/category/show/{id}', name: 'admin_category_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $category = $this->categoryRepository->findOneOrdered($id);
        if(!$category)
        {
            throw new NotFoundResourceException('pas de catégorie avec l\'id '.$id);
        }
        return $this->render('admin/shop/category/show.html.twig', [
            'category' => $category,
            'count_categories' => $this->categoryRepository->count([])
        ]);
    }

    #[Route('/admin/category/update/{id}', name: 'admin_category_update', requirements: ['id' => '\d+'])]
    public function update(Request $request, int $id): Response
    {
        $category = $this->categoryRepository->findOneOrdered($id);
        if(!$category)
        {
            throw new NotFoundResourceException('pas de catégorie avec l\'id '.$id);
        }
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            if($form->get('picture')->getData())
            {
                $this->pictureUploadHelper->uploadCategoryPicture(
                    [
                        'file' => $form->get('picture')->getData(),
                        'alt' => $form->get('alt')->getData()
                    ], 
                    $category
                );
            }
            $this->em->flush();
            $this->addFlash('success', 'La Catégorie '.$category->getName().' a bien été modifiée !');
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/shop/category/update.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'count_categories' => $this->categoryRepository->count([]),
            'existingCategories' => $this->categoryRepository->findAllOrderedForMenuList()
        ]);
    }

    #[Route('/admin/category/create', name: 'admin_category_create')]
    public function create(Request $request): Response
    {
        $category = new Category;
        $form = $this->createForm(CategoryType::class, $category, [
            'validation_groups' => ['Default', 'create']
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $category->setCreatedAt($this->frDateTimeGenerator->generateImmutable());
            $this->pictureUploadHelper->uploadCategoryPicture(
                [
                    'file' => $form->get('picture')->getData(),
                    'alt' => $form->get('alt')->getData()
                ], 
                $category
            );

            $this->em->persist($category);
            $this->em->flush();
            $this->addFlash('success', 'La Catégorie '.$category->getName().' a bien été ajoutée !');
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/shop/category/create.html.twig', [
            'form' => $form->createView(),
            'count_categories' => $this->categoryRepository->count([]),
            'existingCategories' => $this->categoryRepository->findAllOrderedForMenuList()
        ]);
    }

    #[Route('/admin/category/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Request $request): Response 
    {
        $category = $this->categoryRepository->find($request->request->get('id'));
        if(!$category)
        {
            throw new NotFoundResourceException();
        }
        $categoryName = $category->getName();

        $this->em->remove($category);
        $this->em->flush();
        $this->addFlash('success', 'La Catégorie '.$categoryName.' a bien été supprimée !');
        return $this->redirectToRoute('admin_category_index');
    }

}
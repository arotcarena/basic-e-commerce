<?php
namespace App\Tests\EndToEnd\Admin\Shop;

use Facebook\WebDriver\WebDriverBy;
use App\DataFixtures\Tests\UserTestFixtures;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\Tests\EndToEnd\Admin\AdminEndToEndTest;

/**
 * On ne teste ici que les comportements en js : le persist est déjà testé dans les functional tests
 */
class AdminProductEndToEndTest extends AdminEndToEndTest
{
    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class, ProductTestFixtures::class]);
        $this->loginAdmin();
    }

    //suggestedProducts
    public function testCreateSuggestProductsSuggestListOpen()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->client->findElement(WebDriverBy::cssSelector('#product_suggestedProducts'))->click();
        $this->client->getKeyboard()->sendKeys('pr');
        $this->client->waitFor('.admin-suggest-item', 5);
        $this->assertSelectorExists('.admin-suggest-item:nth-child(2)');
    }
    public function testCreateAddOnlyCorrectSelectedProductOnVisibleList()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->client->findElement(WebDriverBy::cssSelector('#product_suggestedProducts'))->click();
        $this->client->getKeyboard()->sendKeys('pr');
        $this->client->waitFor('.admin-suggest-item', 5);
        $suggestItem = $this->client->findElement(WebDriverBy::cssSelector('.admin-suggest-item'));
        $selectedText = $suggestItem->getText();
        $suggestItem->click();
        $this->client->waitFor('.admin-suggestedProducts-item', 5);
        $resultText = $this->client->findElement(WebDriverBy::cssSelector('.admin-suggestedProducts-item:first-child'))->getText();
        $this->assertEquals(
            str_replace('É', 'é', strtolower($selectedText)),  
            strtolower($resultText)
        );
        $this->assertSelectorNotExists('.admin-suggestedProducts-item:nth-child(2)');
    }
    public function testCreateAddSuggestedProductOnHiddenSelect()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->client->findElement(WebDriverBy::cssSelector('#product_suggestedProducts'))->click();
        $this->client->getKeyboard()->sendKeys('pr');
        $this->client->waitFor('.admin-suggest-item', 5);
        $suggestItem = $this->client->findElement(WebDriverBy::cssSelector('.admin-suggest-item'));
        $selectedText = $suggestItem->getText();
        $suggestItem->click();

        // on résoud la productDesignation à partir du texte présent dans le suggestItem ("DESIGNATION" DANS "CATEGORY" > "SUBCATEGORY")
        $productDesignation = str_replace('É', 'é', strtolower(trim(explode('DANS', $selectedText)[0])));
        // on vérifie que le product placé dans le hidden select a bien le même designation que notre product sélectionné
        $this->client->waitFor('.suggestedProducts-hidden-option', 5);
        $dataDesignation = $this->client->findElement(WebDriverBy::cssSelector('.suggestedProducts-hidden-option'))->getAttribute('data-designation');
        $this->assertTrue(strtolower($dataDesignation) === $productDesignation);
        $this->assertSelectorNotExists('.suggestedProducts-hidden-option:nth-child(2)');
    }
    
    public function testCreateDeleteSuggestProduct()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->client->findElement(WebDriverBy::cssSelector('#product_suggestedProducts'))->click();
        $this->client->getKeyboard()->sendKeys('pr');
        $this->client->waitFor('.admin-suggest-item', 5);
        $this->client->findElement(WebDriverBy::cssSelector('.admin-suggest-item'))->click();
       
        $this->client->waitFor('.admin-suggestedProducts-item', 5);
        $this->assertSelectorExists('.admin-suggestedProducts-item');
        $this->client->getMouse()->clickTo('.admin-suggestedProducts-closer');
        // on vérifie que la liste est vide
        $this->assertSelectorNotExists('.admin-suggestedProducts-item');
        // on vérifie que le select est vide
        $this->assertSelectorNotExists('.suggestedProducts-hidden-option');
    }

    public function testUpdateSuggestedProductsInDatabaseAreRendered()
    {
        //on va sur la page update d'un produit  (le seul produit à éviter est celui qui contient 2 suggestedProducts et qui s'appelle 'product with suggestedProducts') en principe il ne ressort pas en premier sur la page index donc c'est bon
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->client->findElement(WebDriverBy::cssSelector('.admin-table-button.success:nth-child(2)'))->click();
        $this->assertSelectorTextContains('h1', 'Modifier');
        //on vérifie que 1 suggestedProduct est bien affiché (tous les products sauf 1 contiennent 1 seul suggestedProduct)
        $this->client->waitFor('.admin-suggestedProducts-item', 5);
        $this->assertSelectorExists('.admin-suggestedProducts-item:nth-child(1)');
        $this->assertSelectorNotExists('.admin-suggestedProducts-item:nth-child(2)');
        //on vérifie que 1 suggestedProduct est bien entré dans le select hidden
        $this->client->waitFor('.suggestedProducts-hidden-option', 5);
        $this->assertSelectorExists('.suggestedProducts-hidden-option:nth-child(1)');
        $this->assertSelectorNotExists('.suggestedProducts-hidden-option:nth-child(2)');
    }
    public function testUpdateAddSuggestProductOnVisibleList()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->client->findElement(WebDriverBy::cssSelector('.admin-table-button.success:nth-child(2)'))->click();
        $this->assertSelectorTextContains('h1', 'Modifier');
        //on vérifie que 1 suggestedProduct est bien affiché (tous les products sauf 1 contiennent 1 seul suggestedProduct)
        $this->client->waitFor('.admin-suggestedProducts-item', 5);
        $this->assertSelectorExists('.admin-suggestedProducts-item:nth-child(1)');
        $this->assertSelectorNotExists('.admin-suggestedProducts-item:nth-child(2)');
        //on ajoute un suggestedProduct
        $this->client->findElement(WebDriverBy::cssSelector('#product_suggestedProducts'))->click();
        $this->client->getKeyboard()->sendKeys('bij'); // bijoux
        $this->client->waitFor('.admin-suggest-item', 5);
        $suggestItem = $this->client->findElement(WebDriverBy::cssSelector('.admin-suggest-item'));
        $selectedText = $suggestItem->getText();
        $suggestItem->click();
        // on vérifie qu'il s'est bien ajouté et que c'est bien le bon
        $this->client->waitFor('.admin-suggestedProducts-item:nth-child(2)', 5);
        $resultText = $this->client->findElement(WebDriverBy::cssSelector('.admin-suggestedProducts-item:nth-child(2)'))->getText();
        $this->assertEquals(
            str_replace('É', 'é', strtolower($selectedText)),  
            strtolower($resultText)
        );
        $this->assertSelectorNotExists('.admin-suggestedProducts-item:nth-child(3)');
    }
    public function testUpdateAddSuggestedProductOnHiddenSelect()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->client->findElement(WebDriverBy::cssSelector('.admin-table-button.success:nth-child(2)'))->click();
        $this->assertSelectorTextContains('h1', 'Modifier');
        //on vérifie que 1 suggestedProduct est bien affiché (tous les products sauf 1 contiennent 1 seul suggestedProduct)
        $this->client->waitFor('.admin-suggestedProducts-item', 5);
        $this->assertSelectorExists('.admin-suggestedProducts-item:nth-child(1)');
        $this->assertSelectorNotExists('.admin-suggestedProducts-item:nth-child(2)');
        //on ajoute un suggestedProduct
        $this->client->findElement(WebDriverBy::cssSelector('#product_suggestedProducts'))->click();
        $this->client->getKeyboard()->sendKeys('bij'); // bijoux
        $this->client->waitFor('.admin-suggest-item', 5);
        $suggestItem = $this->client->findElement(WebDriverBy::cssSelector('.admin-suggest-item'));
        $selectedText = $suggestItem->getText();
        $suggestItem->click();
        // on vérifie qu'il s'est bien ajouté dans le select et que c'est bien le bon, et qu'il n'y a que 2 options pas plus
        // on résoud la productDesignation à partir du texte présent dans le suggestItem ("DESIGNATION" DANS "CATEGORY" > "SUBCATEGORY")
        $productDesignation = str_replace('É', 'é', strtolower(trim(explode('DANS', $selectedText)[0])));
        $this->client->waitFor('.suggestedProducts-hidden-option:nth-child(2)', 5);
        $dataDesignation = $this->client->findElement(WebDriverBy::cssSelector('.suggestedProducts-hidden-option:nth-child(2)'))->getAttribute('data-designation');
        $this->assertTrue(strtolower($dataDesignation) === $productDesignation);
        $this->assertSelectorNotExists('.suggestedProducts-hidden-option:nth-child(3)');
    }
    public function testUpdateDeleteSuggestProduct()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->client->findElement(WebDriverBy::cssSelector('.admin-table-button.success:nth-child(2)'))->click();
        $this->assertSelectorTextContains('h1', 'Modifier');

        $this->client->waitFor('.admin-suggestedProducts-item', 5);
        $this->assertSelectorExists('.admin-suggestedProducts-item');
        $this->client->getMouse()->clickTo('.admin-suggestedProducts-closer');
        // on vérifie que la liste est vide
        $this->assertSelectorNotExists('.admin-suggestedProducts-item');
        // on vérifie que le select est vide
        $this->assertSelectorNotExists('.suggestedProducts-hidden-option');
    }

    public function testDeleteOnlyOneItemIsDeleted()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->client->findElement(WebDriverBy::cssSelector('.admin-table-button.success:nth-child(2)'))->click();
        $this->assertSelectorTextContains('h1', 'Modifier');
        //on ajoute un suggestedProduct 
        $this->client->findElement(WebDriverBy::cssSelector('#product_suggestedProducts'))->click();
        $this->client->getKeyboard()->sendKeys('bij'); // bijoux
        $this->client->waitFor('.admin-suggest-item', 5);
        $suggestItem = $this->client->findElement(WebDriverBy::cssSelector('.admin-suggest-item'));
        $selectedText = $suggestItem->getText();
        $suggestItem->click();
         // on vérifie qu'il y en a bien 2 maintenant
        $this->client->waitFor('.admin-suggestedProducts-item:nth-child(2)', 5);
        $this->assertSelectorExists('.admin-suggestedProducts-item:nth-child(2)');
        //on supprime le premier suggestedProduct
        $this->client->getMouse()->clickTo('.admin-suggestedProducts-item:nth-child(1) .admin-suggestedProducts-closer');
        //on vérifie que tout est correct
        //on résoud d'abord le productDesignation
        $productDesignation = str_replace('É', 'é', strtolower(trim(explode('DANS', $selectedText)[0])));
        // on vérifie que la liste ne comporte plus qu'un seul item, et que c'est bien celui qu'on vient d'ajouter
        $itemText = $this->client->findElement(WebDriverBy::cssSelector('.admin-suggestedProducts-item'))->getText();
        $this->assertStringContainsString($productDesignation, strtolower($itemText));
        $this->assertSelectorNotExists('.admin-suggestedProducts-item:nth-child(2)');
        // on vérifie que le select ne comporte plus qu'une seule option et que c'est bien la bonne
        $this->client->waitFor('.suggestedProducts-hidden-option', 5);
        $dataDesignation = $this->client->findElement(WebDriverBy::cssSelector('.suggestedProducts-hidden-option'))->getAttribute('data-designation');
        $this->assertTrue(strtolower($dataDesignation) === $productDesignation);
        $this->assertSelectorNotExists('.suggestedProducts-hidden-option:nth-child(2)');
    }

    //category and subcategory select
    public function testCreateSubCategorySelectIsCorrectlyUpdatedWhenCategoryChange()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        //on vérifie que le select subCategory est masqué
        $subCategorySelect = $this->client->findElement(WebDriverBy::cssSelector('#product_subCategory'));
        $this->assertEquals('', $subCategorySelect->getText());
        //on selectionne une category
        $categorySelect = $this->client->findElement(WebDriverBy::cssSelector('#product_category'));
        $categorySelect->click();
        $this->client->findElement(WebDriverBy::cssSelector('option:nth-child(2)'))->click(); // on choisit bijoux femme
        //on attent que le subCategory select soit visible
        $this->client->waitForVisibility('#product_subCategory', 5);
        // on vérifie que subCategory select comporte les bonnes options
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(1)', '');  // la première option est vide (aucune subCategory)
        $this->assertSelectorAttributeContains('#product_subCategory option:nth-child(1)', 'value', ''); // la première option est vide (aucune subCategory)
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(2)', 'Bracelets'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(3)', 'Boucles d\'oreilles'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(4)', 'Colliers'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(5)', 'Pendentifs');
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(6)', ''); 
        $this->assertSelectorTextContains('#product_subCategory option:last-child', '');

        //on refait le test en sélectionnant une autre category
        //on selectionne une category 
        $categorySelect->click();
        $this->client->findElement(WebDriverBy::cssSelector('option:nth-child(5)'))->click();  // on choisit décoration & détente
        //on attent que le subCategory select devienne invisible puis de nouveau visible (pour que la modification ait eu lieu)
        $this->client->waitForInvisibility('#product_subCategory', 5);
        $this->client->waitForVisibility('#product_subCategory', 5);
        // on vérifie que subCategory select comporte les bonnes options
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(1)', '');  // la première option est vide (aucune subCategory)
        $this->assertSelectorAttributeContains('#product_subCategory option:nth-child(1)', 'value', ''); // la première option est vide (aucune subCategory)
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(2)', ''); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(14)', 'Décoration'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(15)', 'Détente'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(16)', ''); 
        $this->assertSelectorTextContains('#product_subCategory option:last-child', '');
    }

    public function testUpdateSubCategorySelectIsCorrectlyUpdatedWhenCategoryChange()
    {
        //on va sur la page update
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->client->findElement(WebDriverBy::cssSelector('.admin-table-button.success:nth-child(2)'))->click();
        $this->assertSelectorTextContains('h1', 'Modifier');
        //on selectionne une category
        $categorySelect = $this->client->findElement(WebDriverBy::cssSelector('#product_category'));
        $categorySelect->click();
        $this->client->findElement(WebDriverBy::cssSelector('option:nth-child(2)'))->click(); // on choisit bijoux femme
        //on attent que le subCategory select soit visible (ou caché puis visible si une category était déjà sélectionnée)
        $this->client->waitForInvisibility('#product_subCategory', 5);
        $this->client->waitForVisibility('#product_subCategory', 5);
        // on vérifie que subCategory select comporte les bonnes options
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(1)', '');  // la première option est vide (aucune subCategory)
        $this->assertSelectorAttributeContains('#product_subCategory option:nth-child(1)', 'value', ''); // la première option est vide (aucune subCategory)
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(2)', 'Bracelets'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(3)', 'Boucles d\'oreilles'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(4)', 'Colliers'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(5)', 'Pendentifs');
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(6)', ''); 
        $this->assertSelectorTextContains('#product_subCategory option:last-child', '');

        //on refait le test en sélectionnant une autre category
        //on selectionne une category 
        $categorySelect->click();
        $this->client->findElement(WebDriverBy::cssSelector('option:nth-child(5)'))->click();  // on choisit décoration & détente
        //on attent que le subCategory select devienne invisible puis de nouveau visible (pour que la modification ait eu lieu)
        $this->client->waitForInvisibility('#product_subCategory', 5);
        $this->client->waitForVisibility('#product_subCategory', 5);
        // on vérifie que subCategory select comporte les bonnes options
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(1)', '');  // la première option est vide (aucune subCategory)
        $this->assertSelectorAttributeContains('#product_subCategory option:nth-child(1)', 'value', ''); // la première option est vide (aucune subCategory)
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(2)', ''); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(14)', 'Décoration'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(15)', 'Détente'); 
        $this->assertSelectorTextContains('#product_subCategory option:nth-child(16)', ''); 
        $this->assertSelectorTextContains('#product_subCategory option:last-child', '');
    }

}
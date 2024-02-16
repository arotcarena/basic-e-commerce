<?php
namespace App\Tests\EndToEnd\Utils;

use Facebook\WebDriver\WebDriverBy;

trait CartTrait 
{
    protected function openCartMenu()
    {
        $this->client->request('GET', $this->urlGenerator->generate('home'));
        $this->client->waitFor('.cart-opener', 5);
        $this->client->findElement(WebDriverBy::cssSelector('.cart-opener'))->click();
        $this->client->waitFor('.cart-line', 5);
    }
    protected function addProduct(string $q = '')
    {
        $this->client->request('GET', $this->urlGenerator->generate('product_index', [
            'q' => $q
        ]));
        $this->client->waitFor('.product-card', 5);
        $this->client->getMouse()->mouseMoveTo('.product-card:first-child');
        $this->client->waitFor('.product-card:first-child button', 5);
        $this->client->findElement(WebDriverBy::cssSelector('.product-card:first-child button'))->click();
    }
    protected function assertCartCount($count)
    {
        $this->client->waitForElementToContain('.cart-count-chip', $count, 5);
        $this->assertSelectorTextContains('.cart-count-chip', $count);
    }
    protected function assertCartTotalPrice($totalPrice)
    {
        $this->client->waitForElementToContain('.cart-price-chip', $totalPrice, 5);
        $this->assertSelectorTextContains('.cart-price-chip', $totalPrice);
    }
    protected function getCartCount()
    {
        $this->client->waitFor('.cart-count-chip', 5);
        return $this->client->findElement(WebDriverBy::cssSelector('.cart-count-chip'))->getText();
    }
    protected function getCartTotalPrice()
    {
        $this->client->waitFor('.cart-price-chip', 5);
        return $this->client->findElement(WebDriverBy::cssSelector('.cart-price-chip'))->getText();
    }
}
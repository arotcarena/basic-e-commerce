<?php
namespace App\Tests\UnitAndIntegration\Entity;

use App\Entity\User;
use App\Entity\Review;
use DateTimeImmutable;
use App\Entity\Product;
use App\Tests\UnitAndIntegration\Entity\EntityTest;

/**
 * @group Entity
 */
class ReviewTest extends EntityTest 
{
    public function testValidReview()
    {
        $this->assertHasErrors(0, $this->createValidReview());
    }
    public function testInvalidRate()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidReview()->setRate(0)
        );
        $this->assertHasErrors(
            1, 
            $this->createValidReview()->setRate(-2)
        );
        $this->assertHasErrors(
            1, 
            $this->createValidReview()->setRate(6)
        );
    }
    public function testInvalidNullRate()
    {
        $review = (new Review)
                    ->setUser(new User)
                    ->setProduct(new Product)
                    ->setFullName('Jean Michel')
                    ->setComment('Voici mon commentaire valide')
                    ->setCreatedAt(new DateTimeImmutable())
                    ;
        $this->assertHasErrors(1, $review);
    }
    public function testInvalidBlankFullName()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidReview()->setFullName('')
        );
    }
    public function testInvalidTooLongFullName()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidReview()->setFullName($this->moreThan200Caracters)
        );
    }
    public function testInvalidTooLongComment()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidReview()->setComment($this->moreThan2000Caracters)
        );
    }
    public function createValidReview(): Review
    {
        return (new Review)
                ->setFullName('Jean Michel')
                ->setRate(4)
                ->setComment('Voici mon commentaire valide')
                ;
    }
}
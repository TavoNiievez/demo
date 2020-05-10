<?php

namespace Fixture;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\Security\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Fixture\Security\UserFixture;

class CartFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            UserFixture::class
        ];
    }

    public function load(ObjectManager $manager)
    {
        $carts = [
            [
                'customer' => 'john',
                'items'    => [
                    [
                        'product'  => 'Staff',
                        'quantity' => 1
                    ]
                ]
            ],
            [
                'customer' => 'jane',
                'items'    => [
                    [
                        'product'  => 'Sword',
                        'quantity' => 10
                    ]
                ]
            ]
        ];

        foreach ($carts as $prop) {
            /** @var User $customer */
            $customer = $this->getReference($prop['customer']);
            $cart = new Cart($customer);

            foreach ($prop['items'] as $item) {
                /** @var Product $product */
                $product = $this->getReference($item['product']);
                $cart->addItem(
                    new CartItem($product, $item['quantity'])
                );
            }

            $manager->persist($cart);
        }

        $manager->flush();
    }
}
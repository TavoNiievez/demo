<?php

namespace App\Controller\Cart;

use App\DTO\Purchase;
use App\DTO\Response\BadRequest\InvalidFormResponse;
use App\Form\Type\PurchaseType;
use App\Pricing\CartPricingStrategy;
use App\Service\Cart\GetActiveCart;
use App\Service\Cart\PutProductToCart;
use App\Traits\EntityManagerTrait;
use App\Traits\FormFactoryTrait;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\DTO\Receipt;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation as SWG;

/**
 * @Route("/cart", name="cart.put", methods={"POST"})
 *
 * @OA\RequestBody(request=PurchaseType::class, required=true)
 * @OA\Response(
 *     response=200,
 *     description="Put Product To The Cart",
 *     @SWG\Model(type=Receipt::class)
 * )
 * @OA\Tag(name="Cart")
 * @SWG\Security(name="Bearer")
 *
 */
class PostAction
{
    use EntityManagerTrait;
    use FormFactoryTrait;

    private GetActiveCart $getActiveCart;
    private CartPricingStrategy $pricing;
    private PutProductToCart $put;

    public function __construct(GetActiveCart $getActiveCart, CartPricingStrategy $pricing, PutProductToCart $put)
    {
        $this->getActiveCart = $getActiveCart;
        $this->pricing = $pricing;
        $this->put = $put;
    }

    public function __invoke(Request $request)
    {
        $cart = $this->getActiveCart->execute();

        $purchase = new Purchase();
        $form = $this->formFactory->create(PurchaseType::class, $purchase);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $form->submit([]);
        }

        if (!$form->isValid()) {
            return View::create(new InvalidFormResponse($form), Response::HTTP_BAD_REQUEST);
        }

        $this->put->execute($cart, $purchase);

        $this->em->flush();
        $this->em->refresh($cart);

        return $this->pricing->execute($cart);
    }
}

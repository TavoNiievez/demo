<?php

namespace App\Controller\Product;

use App\DTO\Product\ProductCriteria;
use App\DTO\Response\BadRequest\InvalidFormResponse;
use App\DTO\Response\BadRequest\InvalidPageResponse;
use App\DTO\Response\PaginationResponse;
use App\Form\Type\Product\ProductCriteriaType;
use App\Repository\ProductRepositoryInterface;
use App\Traits\FormFactoryTrait;
use FOS\RestBundle\View\View;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation as SWG;

/**
 * @Route("/products/{page}", name="products", methods={"GET"}, requirements={"page"="^[1-9]\d*$"}, defaults={"page"=1})
 *
 * @OA\RequestBody(request=ProductCriteriaType::class, required=true)
 * @OA\Response(
 *     response=200,
 *     description="Returns list of the Products",
 *     @SWG\Model(type=PaginationResponse::class)
 * )
 * @OA\Tag(name="Product")
 * @SWG\Security(name="Bearer")
 */
class ReadAction
{
    use FormFactoryTrait;

    private const PRODUCTS_PER_PAGE = 20;

    public function __invoke(ProductRepositoryInterface $repository, Request $request, int $page = 1)
    {
        $criteria = new ProductCriteria();
        $form = $this->formFactory->create(ProductCriteriaType::class, $criteria);

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return View::create(new InvalidFormResponse($form), Response::HTTP_BAD_REQUEST);
        }

        try {
            return new PaginationResponse(
                $repository->paginate($criteria, $page, self::PRODUCTS_PER_PAGE)
            );
        } catch (OutOfRangeCurrentPageException $e) {
            return View::create(new InvalidPageResponse($page), Response::HTTP_BAD_REQUEST);
        }
    }
}

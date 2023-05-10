<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\AddProductType;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class ProductController extends AbstractController
{
    /**
     * @Route("/product/{id}", name="product.detail")
     */
    public function detail($id, Request $request, CartManager $cartManager, ProductRepository $productRepository) {
        $product = $productRepository->find($id);
        $form = $this->createForm(AddToCartType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $item->setProduct($product);

            $cart = $cartManager->getCurrentCart();
            $cart
                ->addItem($item)
                ->setUpdatedAt(new \DateTimeImmutable());

            $cartManager->save($cart);

            return $this->redirectToRoute('product.detail', ['id' => $product->getId()]);
        }

        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/add/product", name="product.add")
     */
    public function add(Request $request, EntityManagerInterface $entityManager) {
        $product = new Product();
        $form = $this->createForm(AddProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setName($product->getName());
            $product->setDescription($product->getDescription());
            $product->setPrice($product->getPrice());
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'The product is addded');
            return $this->redirect($this->generateUrl('home'));
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/product/edit/{id}", name="product.edit")
     */
    public function edit($id, Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager) {
        $product = $productRepository->find($id);
        $form = $this->createForm(AddProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setName($product->getName());
            $product->setDescription($product->getDescription());
            $product->setPrice($product->getPrice());
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'The product is edited');
            return $this->redirect($this->generateUrl('home'));
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/product/remove/{id}", name="product.delete")
     */
    public function delete($id, ProductRepository $productRepository, EntityManagerInterface $entityManager) {
        $product = $productRepository->find($id);
        $productRepository->remove($product);
        $entityManager->flush();
        return $this->redirect($this->generateUrl('home'));
    }
}

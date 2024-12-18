<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }



    #[Route('/products', name: 'product_list')]
    public function list(ProductRepository $productRepository): Response
    {
        //Получаем все продукты
        $products = $productRepository->findAll();

        //Рендерим в шаблон
        return $this->render('product\list.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/products/create', name: 'product_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        #Создаём новый объект Product
        $product = new Product();

        #Создаём форму
        $form = $this->createFormBuilder($product)
        ->add('name', TextType::class, [
            'label' => 'Product Name',
        ])
        ->add('description', TextareaType::class,[
            'label' => 'Description',
        ])
        ->add('price', MoneyType::class, [
            'label' => 'Product Price',
            'currency' => 'UAH',
        ])
        ->add('image_url', UrlType::class, [
            'label' => 'Image URL',
        ])
        ->add('save', SubmitType::class, ['label' => 'Create Product'])
        ->getForm();

    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
        //Сохраняем продукт в базу данных
        $entityManager->persist($product);
        $entityManager->flush();

        //Редирект на список продуктов
        return $this->redirectToRoute('product_list');
    }

    //Ренедерим форму
    return $this->render('product/create.html.twig', [
        'form' => $form->createView(),
    ]);
    }

    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function edit(Request $request, Product $product): Response
    {
        // Создаем форму для редактирования
        $form = $this->createForm(ProductType::class, $product);

        // Обрабатываем запрос
        $form->handleRequest($request);

        // Если форма отправлена и валидна
        if ($form->isSubmitted() && $form->isValid()) {
            // Сохраняем изменения
            // $entityManager = $this->getDoctrine()->getManager();
            $this->entityManager->flush();

            // Перенаправляем на страницу с продуктами
            return $this->redirectToRoute('product_list');
        }

        // Рендерим шаблон с формой
        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }        

    #[Route('/products/delete/{id}', name: 'product_delete')]
    public function delete(Product $product): Response
    {
        // Удаляем продукт из базы данных
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        // Перенаправляем на страницу с продуктами
        return $this->redirectToRoute('product_list');
    }

    //Сюда дополнительные методы для создания, редактирования и удаления продуктов.
}

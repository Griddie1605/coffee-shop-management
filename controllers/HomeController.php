<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $productModel = new ProductModel();
        $products = $productModel->getFeaturedProducts(6);

        $this->view('home/index', [
            'pageTitle' => 'Trang chủ - Quán Cà Phê',
            'products' => $products,
        ]);
    }
}
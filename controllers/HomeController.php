<?php

require_once __DIR__ . '/../models/ProductModel.php';

function homeIndex(): void
{
    $products = getFeaturedProducts(6);

    render('home/index', [
        'pageTitle' => 'Trang chủ - Quán Cà Phê',
        'products' => $products,
    ]);
}

function homeMenu(): void
{
    $categoryId = isset($_GET['category']) && $_GET['category'] !== ''
        ? (int) $_GET['category']
        : null;
    $keyword = (string) ($_GET['q'] ?? '');

    $categories = getAllCategories();
    $products = getFilteredProducts($categoryId, $keyword);

    render('home/menu', [
        'pageTitle' => 'Thực đơn - Quán Cà Phê',
        'categories' => $categories,
        'products' => $products,
        'selectedCategory' => $categoryId,
        'keyword' => $keyword,
    ]);
}
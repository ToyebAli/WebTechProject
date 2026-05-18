<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../Model/4_ReviewModel.php';

// TASK 4 PART START: Product review controller
class Task4ReviewController
{
    private Task4ReviewModel $model;

    public function __construct()
    {
        $this->model = new Task4ReviewModel();
    }

    public function create(): void
    {
        session_guard();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            json_response(['ok' => false, 'message' => 'POST method required.'], 405);
        }

        $body = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($body['product_id'] ?? 0);
        $rating = (int)($body['rating'] ?? 0);
        $reviewText = trim((string)($body['review_text'] ?? ''));
        $userId = (int)$_SESSION['user_id'];

        if ($productId <= 0) {
            json_response(['ok' => false, 'message' => 'Invalid product.'], 422);
        }

        if ($rating < 1 || $rating > 5) {
            json_response(['ok' => false, 'message' => 'Rating must be between 1 and 5.'], 422);
        }

        if (!$this->model->canUserReviewDeliveredProduct($userId, $productId)) {
            json_response(['ok' => false, 'message' => 'You can review only delivered products you bought.'], 403);
        }

        if ($this->model->findUserReview($userId, $productId)) {
            json_response(['ok' => false, 'message' => 'You already reviewed this product.'], 409);
        }

        $review = $this->model->addReview($productId, $userId, $rating, $reviewText);
        if (!$review) {
            json_response(['ok' => false, 'message' => 'Could not add review.'], 500);
        }

        json_response([
            'ok' => true,
            'message' => 'Review added successfully',
            'review' => $review,
        ]);
    }

    public function productReviews(int $productId): void
    {
        session_guard();

        if ($productId <= 0) {
            json_response(['ok' => false, 'message' => 'Invalid product.'], 422);
        }

        json_response([
            'ok' => true,
            'average' => $this->model->getProductAverage($productId),
            'reviews' => $this->model->getReviewsForProduct($productId),
        ]);
    }
}
// TASK 4 PART END


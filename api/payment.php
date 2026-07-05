<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/services/PaymentService.php';

Middleware::api();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        Middleware::auth();
        $data = Middleware::getJsonInput();
        $action = $_GET['action'] ?? '';

        if ($action === 'create-order') {
            $validator = new Validator($data);
            $validator->required('amount')->numeric('amount')->min('amount', 1);
            if (!$validator->passes()) Response::error('Valid amount required.', 422, $validator->errors());

            try {
                $order = PaymentService::createOrder(
                    (float)$data->amount,
                    $data->currency ?? 'INR',
                    $data->receipt ?? null,
                    ['user_id' => $_SESSION['user_id'] ?? '', 'purpose' => $data->purpose ?? 'general']
                );
                $order['key_id'] = PaymentService::getKeyId();
                Response::success($order, 'Order created.');
            } catch (RuntimeException $e) {
                Response::error($e->getMessage(), 503);
            } catch (Exception $e) {
                Response::error('Payment service error.', 500);
            }
        }

        if ($action === 'verify') {
            $validator = new Validator($data);
            $validator->required('order_id')->required('payment_id')->required('signature');
            if (!$validator->passes()) Response::error('Missing payment verification fields.', 422, $validator->errors());

            $verified = PaymentService::verifyPayment($data->order_id, $data->payment_id, $data->signature);
            if ($verified) {
                AuditLogger::log('payment_success', 'payment', null, "Payment {$data->payment_id} for order {$data->order_id} verified.");
                Response::success(null, 'Payment verified successfully.');
            } else {
                AuditLogger::log('payment_failed', 'payment', null, "Payment verification failed for order {$data->order_id}.");
                Response::error('Payment verification failed.', 400);
            }
        }
        break;

    default:
        Response::error('Method not allowed.', 405);
}

<?php
require_once __DIR__ . '/../src/core/Middleware.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/services/PDFService.php';

Middleware::api();
Middleware::auth();

$data = Middleware::getJsonInput();
$type = $_GET['type'] ?? '';

switch ($type) {
    case 'lease':
        $validator = new Validator($data);
        $validator->required('tenant_name')->required('property_title')->required('rent')->required('start_date')->required('end_date');
        if (!$validator->passes()) Response::error('All lease fields required.', 422, $validator->errors());
        PDFService::generateLeaseAgreement($data->tenant_name, $data->property_title, $data->rent, $data->start_date, $data->end_date);
        break;

    case 'invoice':
        $validator = new Validator($data);
        $validator->required('to')->required('items')->required('total')->required('invoice_no');
        if (!$validator->passes()) Response::error('All invoice fields required.', 422, $validator->errors());
        PDFService::generateInvoice($data->to, $data->items, $data->total, $data->invoice_no);
        break;

    default:
        Response::error('Invalid type. Use ?type=lease or ?type=invoice', 400);
}

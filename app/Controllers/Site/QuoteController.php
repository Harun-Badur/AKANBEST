<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Contact;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Throttle;
use App\Core\Validator;
use App\Models\Product;
use App\Models\Quote;
use DateTimeImmutable;
use Throwable;

final class QuoteController
{
    public static function store(Request $request, array $params): Response
    {
        Session::start();
        if (!Csrf::verify($request)) {
            Session::flash('quote_errors', ['form' => 'Oturum doğrulaması geçersiz. Lütfen formu yeniden gönderin.']);
            return Response::redirect('/#teklif', 303);
        }

        $trap = $request->post('website', '');
        if (is_string($trap) && trim($trap) !== '') {
            Session::flash('quote_success', 'Teklif talebiniz başarıyla alındı.');
            return Response::redirect('/#teklif', 303);
        }

        $data = [];
        foreach (['full_name', 'company', 'phone', 'email', 'product_id', 'production_type', 'quantity', 'request_date', 'message'] as $field) {
            $value = $request->post($field, '');
            $data[$field] = is_string($value) ? trim($value) : '';
        }
        $rules = [
            'full_name' => 'required|max:201', 'company' => 'required|max:200',
            'phone' => 'required|phone|max:50', 'email' => 'required|email|max:255',
            'product_id' => 'required|max:10', 'production_type' => 'max:200',
            'quantity' => 'required|max:10', 'request_date' => 'max:10', 'message' => 'max:3000',
        ];
        $labels = ['full_name' => 'Ad Soyad', 'company' => 'Firma Adı', 'phone' => 'Telefon', 'email' => 'E-posta', 'product_id' => 'İlgilendiğiniz Ürün', 'production_type' => 'Üretim Türü', 'quantity' => 'Tahmini Miktar', 'request_date' => 'Talep Zamanı', 'message' => 'Ek Bilgi'];
        $errors = [];
        foreach (Validator::make($data, $rules) as $field => $issues) {
            $errors[$field] = match ($issues[0]) {
                'required' => $labels[$field] . ' alanını doldurun.',
                'email' => 'Geçerli bir e-posta adresi girin.',
                'phone' => 'Geçerli bir cep telefonu numarası girin (05xx xxx xx xx).',
                default => $labels[$field] . ' alanındaki bilgiyi kısaltın.',
            };
        }
        $names = preg_split('/\s+/u', $data['full_name'], -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lastName = count($names) > 1 ? array_pop($names) : '';
        $firstName = implode(' ', $names);
        if ($lastName === '' || mb_strlen($firstName) > 100 || mb_strlen($lastName) > 100) {
            $errors['full_name'] = 'Adınızı ve soyadınızı birlikte girin; her biri en fazla 100 karakter olmalıdır.';
        }
        $productId = filter_var($data['product_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
        $product = $productId !== false ? Product::find($productId) : [];
        if ($product === [] || (int) $product['is_active'] !== 1) {
            $errors['product_id'] = 'Listeden geçerli bir ürün seçin.';
        }
        $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
        if ($quantity === false) {
            $errors['quantity'] = 'Miktarı 1 ile 2147483647 arasında bir tam sayı olarak girin.';
        }
        if ($data['request_date'] !== '') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $data['request_date']);
            if ($date === false || $date->format('Y-m-d') !== $data['request_date']) {
                $errors['request_date'] = 'Geçerli bir tarih seçin.';
            }
        }
        if ($errors !== []) {
            return self::retry($data, $errors);
        }

        $throttleKey = 'quote:' . ($request->ip() ?? 'unknown');
        if (Throttle::blocked($throttleKey)) {
            return self::retry($data, ['form' => 'Çok sayıda talep gönderdiniz. Lütfen 15 dakika sonra tekrar deneyin.']);
        }
        Throttle::hit($throttleKey);
        $message = ['Firma Adı: ' . $data['company']];
        foreach (['production_type', 'request_date', 'message'] as $field) {
            if ($data[$field] !== '') {
                $message[] = $labels[$field] . ': ' . $data[$field];
            }
        }
        try {
            Database::transaction(static fn (): int => Quote::createWithItem([
                'first_name' => $firstName, 'last_name' => $lastName,
                'phone' => $data['phone'], 'email' => $data['email'],
                'message' => implode("\n\n", $message), 'ip_address' => $request->ip(), 'status' => 'new',
            ], $product, $quantity));
        } catch (Throwable $exception) {
            error_log('Quote submission failed: ' . get_class($exception));
            return self::retry($data, ['form' => 'Talebiniz kaydedilemedi. Lütfen daha sonra tekrar deneyin.']);
        }
        // QuoteMailer::send() çağrılmıyor; e-posta gönderimi kapalı. Sınıf duruyor.
        $summary = self::whatsappText(
            $data,
            trim($firstName . ' ' . $lastName),
            (string) ($product['name'] ?? ''),
            (string) $quantity,
        );
        $whatsapp = Contact::whatsappUrl($summary);
        if ($whatsapp !== '') {
            return Response::redirect($whatsapp, 303);
        }
        Session::flash('quote_success', 'Teklif talebiniz başarıyla alındı.');
        Session::flash('quote_whatsapp', $whatsapp);
        return Response::redirect('/#teklif', 303);
    }

    /** @param array<string, string> $data */
    private static function whatsappText(array $data, string $name, string $product, string $quantity): string
    {
        $lines = [
            'Ad Soyad: ' . $name,
            'Firma: ' . $data['company'],
            'Telefon: ' . $data['phone'],
            'E-posta: ' . $data['email'],
            'İlgilendiğim Ürün: ' . $product,
        ];
        if ($data['production_type'] !== '') {
            $lines[] = 'Üretim Türü: ' . $data['production_type'];
        }
        $lines[] = 'Tahmini Miktar: ' . $quantity;
        if ($data['request_date'] !== '') {
            $lines[] = 'Talep Zamanı: ' . $data['request_date'];
        }
        if ($data['message'] !== '') {
            $note = $data['message'];
            if (mb_strlen($note) > 1000) {
                $note = mb_substr($note, 0, 1000) . '...';
            }
            $lines[] = 'Ek Bilgi: ' . $note;
        }

        return "Merhaba, web sitenizden teklif talebi gönderiyorum.\n\n" . implode("\n", $lines);
    }

    private static function retry(array $data, array $errors): Response
    {
        Session::flash('quote_old', array_map(static fn (string $value): string => mb_substr($value, 0, 3000), $data));
        Session::flash('quote_errors', $errors);
        return Response::redirect('/#teklif', 303);
    }
}

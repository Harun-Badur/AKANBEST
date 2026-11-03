<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use RuntimeException;
use Throwable;

/** Sends a quote summary. Delivery failure is logged and never rethrown. */
final class QuoteMailer
{
    /** @param array{company: string, name: string, phone: string, email: string, product: string, quantity: string, note: string} $summary */
    public static function send(array $summary): void
    {
        try {
            self::deliver($summary);
        } catch (Throwable $exception) {
            self::log($exception->getMessage());
        }
    }

    /** @param array{company: string, name: string, phone: string, email: string, product: string, quantity: string, note: string} $summary */
    public static function text(array $summary): string
    {
        $lines = [
            'Firma: ' . self::single($summary['company']),
            'Ad: ' . self::single($summary['name']),
            'Telefon: ' . self::single($summary['phone']),
            'E-posta: ' . self::single($summary['email']),
            'Ürün: ' . self::single($summary['product']),
            'Miktar: ' . self::single($summary['quantity']),
        ];
        $note = self::body($summary['note']);
        if (trim($note) !== '') {
            $lines[] = 'Not: ' . $note;
        }

        return implode("\n", $lines);
    }

    /** @param array{company: string, name: string, phone: string, email: string, product: string, quantity: string, note: string} $summary */
    private static function deliver(array $summary): void
    {
        $to = self::single((string) Config::get('mail.to', ''));
        if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            self::log('quote mail skipped: recipient missing');

            return;
        }
        $from = self::single((string) Config::get('mail.from', ''));
        if (filter_var($from, FILTER_VALIDATE_EMAIL) === false) {
            $from = $to;
        }
        $subject = 'Yeni teklif talebi';
        $body = self::text($summary);
        $reply = self::single($summary['email']);
        if (filter_var($reply, FILTER_VALIDATE_EMAIL) === false) {
            $reply = '';
        }

        $host = self::single((string) Config::get('mail.smtp_host', ''));
        $pass = self::single((string) Config::get('mail.smtp_pass', ''));
        if ($host !== '' && $pass !== '') {
            self::smtp(
                $host,
                self::port(),
                self::single((string) Config::get('mail.smtp_user', '')),
                $pass,
                $from,
                $to,
                $reply,
                $subject,
                $body,
            );

            return;
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'From: ' . $from,
        ];
        if ($reply !== '') {
            $headers[] = 'Reply-To: ' . $reply;
        }
        $previous = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', '5');
        $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
        if ($previous !== false) {
            ini_set('default_socket_timeout', (string) $previous);
        }
        if ($sent !== true) {
            self::log('mail() returned false');
        }
    }

    private static function smtp(
        string $host,
        int $port,
        string $user,
        string $pass,
        string $from,
        string $to,
        string $reply,
        string $subject,
        string $body,
    ): void {
        if (preg_match('/\A[A-Za-z0-9](?:[A-Za-z0-9.-]{0,253}[A-Za-z0-9])?\z/', $host) !== 1) {
            throw new RuntimeException('SMTP host rejected');
        }
        if ($user === '') {
            throw new RuntimeException('SMTP user missing');
        }
        $socket = @stream_socket_client(
            'ssl://' . $host . ':' . $port,
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]),
        );
        if ($socket === false) {
            throw new RuntimeException('SMTP connection failed');
        }
        stream_set_timeout($socket, 10);
        try {
            self::expect($socket, [220]);
            self::command($socket, 'EHLO akanbest', [250]);
            self::command($socket, 'AUTH LOGIN', [334]);
            self::command($socket, base64_encode($user), [334]);
            self::command($socket, base64_encode($pass), [235]);
            self::command($socket, 'MAIL FROM:<' . $from . '>', [250]);
            self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            self::command($socket, 'DATA', [354]);
            $lines = explode("\n", str_replace("\r", '', $body));
            foreach ($lines as $index => $line) {
                if (str_starts_with($line, '.')) {
                    $lines[$index] = '.' . $line;
                }
            }
            $headers = [
                'From: ' . $from,
                'To: ' . $to,
                'Subject: ' . $subject,
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];
            if ($reply !== '') {
                $headers[] = 'Reply-To: ' . $reply;
            }
            $payload = implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $lines) . "\r\n.";
            if (fwrite($socket, $payload . "\r\n") === false) {
                throw new RuntimeException('SMTP write failed');
            }
            self::expect($socket, [250]);
            self::command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    /** @param resource $socket */
    private static function command($socket, string $command, array $ok): void
    {
        if (fwrite($socket, $command . "\r\n") === false) {
            throw new RuntimeException('SMTP write failed');
        }
        self::expect($socket, $ok);
    }

    /** @param resource $socket @param array<int, int> $ok */
    private static function expect($socket, array $ok): void
    {
        $line = '';
        do {
            $chunk = fgets($socket, 512);
            if (!is_string($chunk)) {
                throw new RuntimeException('SMTP connection closed');
            }
            $line = $chunk;
        } while (isset($chunk[3]) && $chunk[3] === '-');
        $code = (int) substr($line, 0, 3);
        if (!in_array($code, $ok, true)) {
            throw new RuntimeException('SMTP reply ' . $code);
        }
    }

    private static function port(): int
    {
        $port = (int) Config::get('mail.smtp_port', 465);
        if ($port < 1 || $port > 65535) {
            return 465;
        }

        return $port;
    }

    private static function single(string $value): string
    {
        return trim(str_replace(["\0", "\r", "\n", '%0a', '%0d', '%0A', '%0D'], '', $value));
    }

    private static function body(string $value): string
    {
        return str_replace(["\0", "\r"], '', $value);
    }

    private static function log(string $message): void
    {
        $pass = self::single((string) Config::get('mail.smtp_pass', ''));
        if ($pass !== '') {
            $message = str_replace($pass, '[redacted]', $message);
        }
        $message = str_replace(["\r", "\n"], ' ', $message);
        error_log('[quote-mail] ' . $message);
    }
}

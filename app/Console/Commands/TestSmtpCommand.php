<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\AdminVerificationEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSmtpCommand extends Command
{
    protected $signature = 'mail:test
        {--to= : Si se especifica, manda un correo real a esta dirección}
        {--host= : Override del host SMTP (default: config mail)}
        {--port= : Override del puerto SMTP}
        {--encryption= : tls (STARTTLS) | ssl (TLS implícito) | null (sin cifrado)}
        {--username= : Override del usuario}
        {--password= : Override del password}
        {--insecure : Desactiva verificación TLS (solo para diagnóstico)}';

    protected $description = 'Valida la conexión SMTP paso a paso (DNS, TCP, TLS, AUTH, envío)';

    public function handle(): int
    {
        $smtp = (array) config('mail.mailers.smtp', []);

        $host = (string) ($this->option('host') ?: $smtp['host'] ?? '');
        $port = (int) ($this->option('port') ?: $smtp['port'] ?? 587);
        $encryption = $this->option('encryption');
        if ($encryption === null) {
            $scheme = $smtp['scheme'] ?? null;
            $encryption = $scheme ?: ($port === 465 ? 'ssl' : 'tls');
        }
        if ($encryption === 'null' || $encryption === '') {
            $encryption = null;
        }
        $username = (string) ($this->option('username') ?: $smtp['username'] ?? '');
        $password = (string) ($this->option('password') ?: $smtp['password'] ?? '');
        $insecure = (bool) $this->option('insecure');
        $to = $this->option('to');

        if ($host === '') {
            $this->error('Falta MAIL_HOST. Configura tu .env o pasa --host=...');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('=== Configuración a probar ===');
        $this->line('  Host       : ' . $host);
        $this->line('  Puerto     : ' . $port);
        $this->line('  Cifrado    : ' . ($encryption ?: 'ninguno'));
        $this->line('  Usuario    : ' . ($username !== '' ? $username : '(sin auth)'));
        $this->line('  TLS verify : ' . ($insecure ? 'NO (--insecure)' : 'SÍ'));
        $this->newLine();

        if (!$this->checkDns($host)) {
            return self::FAILURE;
        }
        if (!$this->checkTcp($host, $port)) {
            return self::FAILURE;
        }

        if ($encryption !== null) {
            $this->inspectCertificate($host, $port, $encryption, $insecure);
        } else {
            $this->warn('3. Inspección de certificado omitida (sin cifrado).');
        }

        if (!$this->checkSmtpAuth($host, $port, $encryption, $username, $password, $insecure)) {
            return self::FAILURE;
        }

        if ($to !== null) {
            if (!$this->sendTestEmail($host, $port, $encryption, $username, $password, $insecure, (string) $to)) {
                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('Listo. SMTP OK.');
        return self::SUCCESS;
    }

    private function checkDns(string $host): bool
    {
        $this->info('1. Resolviendo DNS...');
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->line('   OK  ' . $host . ' (es una IP literal)');
            return true;
        }
        $ip = gethostbyname($host);
        if ($ip === $host) {
            $this->error('   FAIL  No se pudo resolver ' . $host);
            return false;
        }
        $this->line('   OK  ' . $host . ' -> ' . $ip);
        return true;
    }

    private function checkTcp(string $host, int $port): bool
    {
        $this->info('2. Abriendo conexión TCP...');
        $errno = 0;
        $errstr = '';
        $sock = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$sock) {
            $this->error(sprintf('   FAIL  No conecta a %s:%d — %s (errno %d)', $host, $port, $errstr, $errno));
            return false;
        }
        stream_set_timeout($sock, 5);
        $banner = fgets($sock, 1024);
        fclose($sock);
        $this->line('   OK  Banner: ' . trim((string) $banner));
        return true;
    }

    private function inspectCertificate(string $host, int $port, string $encryption, bool $insecure): void
    {
        $this->info('3. Inspeccionando certificado TLS...');

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => !$insecure,
                'verify_peer_name' => !$insecure,
                'allow_self_signed' => $insecure,
                'SNI_enabled' => true,
                'peer_name' => $host,
            ],
        ]);

        $errno = 0;
        $errstr = '';

        if ($encryption === 'ssl') {
            $stream = @stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );
            if (!$stream) {
                $this->error('   FAIL  Handshake SSL: ' . $errstr);
                if (!$insecure) {
                    $this->warn('   Sugerencia: vuelve a correr con --insecure para confirmar si solo es el cert.');
                }
                return;
            }
        } else {
            $stream = @stream_socket_client(
                "tcp://{$host}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );
            if (!$stream) {
                $this->error('   FAIL  No conecta plano: ' . $errstr);
                return;
            }
            stream_set_timeout($stream, 10);
            fgets($stream, 1024);
            fwrite($stream, "EHLO localhost\r\n");
            while (($line = fgets($stream, 1024)) !== false) {
                if (strlen($line) >= 4 && substr($line, 3, 1) === ' ') {
                    break;
                }
            }
            fwrite($stream, "STARTTLS\r\n");
            $resp = (string) fgets($stream, 1024);
            if (strpos($resp, '220') !== 0) {
                fclose($stream);
                $this->error('   FAIL  STARTTLS rechazado: ' . trim($resp));
                return;
            }
            $tlsError = '';
            set_error_handler(function ($_errno, $errstr) use (&$tlsError): bool {
                $tlsError .= ($tlsError === '' ? '' : ' | ') . $errstr;
                return true;
            });
            $ok = stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            restore_error_handler();
            if (!$ok) {
                fclose($stream);
                $this->error('   FAIL  Handshake TLS: ' . ($tlsError !== '' ? $tlsError : 'falló sin mensaje'));
                if (!$insecure) {
                    $this->warn('   Sugerencia: vuelve a correr con --insecure para confirmar si solo es el cert.');
                }
                return;
            }
        }

        $params = stream_context_get_params($stream);
        fclose($stream);

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        if (!$cert) {
            $this->warn('   No se pudo capturar el certificado.');
            return;
        }

        $parsed = (array) openssl_x509_parse($cert);
        $now = time();
        $validFrom = $parsed['validFrom_time_t'] ?? null;
        $validTo = $parsed['validTo_time_t'] ?? null;
        $cn = $parsed['subject']['CN'] ?? '?';
        $issuerCn = $parsed['issuer']['CN'] ?? '?';
        $issuerO = $parsed['issuer']['O'] ?? '?';

        $this->line('   OK  Handshake TLS');
        $this->line('       Subject : CN=' . $cn);
        $this->line('       Issuer  : CN=' . $issuerCn . ', O=' . $issuerO);
        $this->line('       Vigencia: ' . ($validFrom ? date('Y-m-d', $validFrom) : '?')
            . ' -> ' . ($validTo ? date('Y-m-d', $validTo) : '?'));

        if ($validTo) {
            $daysLeft = (int) (($validTo - $now) / 86400);
            if ($daysLeft < 0) {
                $this->error('       EXPIRADO hace ' . abs($daysLeft) . ' días');
            } elseif ($daysLeft < 14) {
                $this->warn('       Expira en ' . $daysLeft . ' días');
            } else {
                $this->line('       Días restantes: ' . $daysLeft);
            }
        }

        if (strcasecmp($issuerO, 'Poste.io') === 0
            || strcasecmp($issuerCn, $cn) === 0
        ) {
            $this->warn('       Cert autofirmado o emitido por el propio servidor.');
        }
    }

    private function checkSmtpAuth(
        string $host,
        int $port,
        ?string $encryption,
        string $username,
        string $password,
        bool $insecure
    ): bool {
        $this->info('4. Probando handshake SMTP + AUTH...');

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => !$insecure,
                'verify_peer_name' => !$insecure,
                'allow_self_signed' => $insecure,
                'SNI_enabled' => true,
                'peer_name' => $host,
            ],
        ]);

        $errno = 0;
        $errstr = '';

        if ($encryption === 'ssl') {
            $stream = @stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );
        } else {
            $stream = @stream_socket_client(
                "tcp://{$host}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );
        }

        if (!$stream) {
            $this->error('   FAIL  Conexión: ' . $errstr);
            return false;
        }
        stream_set_timeout($stream, 10);

        $read = function () use ($stream): string {
            $out = '';
            while (($line = fgets($stream, 1024)) !== false) {
                $out .= $line;
                if (strlen($line) >= 4 && substr($line, 3, 1) === ' ') {
                    break;
                }
            }
            return $out;
        };

        $send = function (string $cmd) use ($stream): void {
            fwrite($stream, $cmd . "\r\n");
        };

        $read();
        $send('EHLO localhost');
        $read();

        if ($encryption === 'tls') {
            $send('STARTTLS');
            $resp = $read();
            if (strpos($resp, '220') !== 0) {
                fclose($stream);
                $this->error('   FAIL  STARTTLS: ' . trim($resp));
                return false;
            }
            $tlsError = '';
            set_error_handler(function ($_errno, $errstr) use (&$tlsError): bool {
                $tlsError .= ($tlsError === '' ? '' : ' | ') . $errstr;
                return true;
            });
            $ok = stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            restore_error_handler();
            if (!$ok) {
                fclose($stream);
                $this->error('   FAIL  Crypto TLS: ' . ($tlsError !== '' ? $tlsError : 'falló sin mensaje'));
                return false;
            }
            $send('EHLO localhost');
            $read();
        }

        if ($username !== '' && $password !== '') {
            $send('AUTH LOGIN');
            $resp = $read();
            if (strpos($resp, '334') !== 0) {
                fclose($stream);
                $this->error('   FAIL  AUTH LOGIN no aceptado: ' . trim($resp));
                return false;
            }
            $send(base64_encode($username));
            $resp = $read();
            if (strpos($resp, '334') !== 0) {
                fclose($stream);
                $this->error('   FAIL  Usuario rechazado: ' . trim($resp));
                return false;
            }
            $send(base64_encode($password));
            $resp = $read();
            if (strpos($resp, '235') !== 0) {
                fclose($stream);
                $this->error('   FAIL  Password rechazado: ' . trim($resp));
                return false;
            }
            $this->line('   OK  AUTH LOGIN exitosa');
        } else {
            $this->line('   OK  Handshake SMTP exitoso (sin AUTH)');
        }

        $send('QUIT');
        @fclose($stream);
        return true;
    }

    private function sendTestEmail(
        string $host,
        int $port,
        ?string $encryption,
        string $username,
        string $password,
        bool $insecure,
        string $to
    ): bool {
        $this->info('5. Enviando correo de prueba a ' . $to . '...');

        config(['mail.mailers.smtp_test' => [
            'transport' => 'smtp',
            'scheme' => $encryption,
            'host' => $host,
            'port' => $port,
            'username' => $username !== '' ? $username : null,
            'password' => $password !== '' ? $password : null,
            'timeout' => 10,
            'local_domain' => null,
            'stream' => $insecure ? [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ] : [],
        ]]);

        try {
            $verificationUrl = rtrim((string) config('app.admin_url'), '/') . '/verify?token=test-token';
            Mail::mailer('smtp_test')->to($to)->send(new AdminVerificationEmail('Test', $verificationUrl));
            $this->line('   OK  Correo enviado (revisa inbox y carpeta de spam).');
            return true;
        } catch (\Throwable $e) {
            $this->error('   FAIL  ' . $e->getMessage());
            return false;
        }
    }
}

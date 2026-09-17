<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Database\Connectors\SqlServerConnector;

/**
 * El SQL Server del ERP Contiflex requiere un handshake TLS legado (certificado/
 * protocolo viejo) que OpenSSL 3 bloquea por defecto. En vez de rebajar la
 * seguridad de OpenSSL para todo el proceso PHP-FPM, este conector activa una
 * config OpenSSL aislada (docker/Dockerfile la genera en /etc/ssl/openssl-erp-legacy.cnf)
 * únicamente durante la conexión SQL Server, y restaura el valor previo después.
 */
class ScopedTlsSqlServerConnector extends SqlServerConnector
{
    public function connect(array $config)
    {
        $previous = getenv('OPENSSL_CONF');

        putenv('OPENSSL_CONF=/etc/ssl/openssl-erp-legacy.cnf');

        try {
            return parent::connect($config);
        } finally {
            putenv($previous === false ? 'OPENSSL_CONF' : "OPENSSL_CONF={$previous}");
        }
    }
}

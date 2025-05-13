<?php
namespace App\Config;

class Redis {
    private static $client;
    
    /**
     * Obtient l'instance du client Redis
     * @return \Predis\Client
     */
    public static function getClient() {
        if (!self::$client) {
            // Récupérer les paramètres de configuration depuis des variables d'environnement
            // ou utiliser des valeurs par défaut pour le développement
            $host = getenv('REDIS_HOST') ?: '127.0.0.1';
            $port = getenv('REDIS_PORT') ?: 6379;
            $password = getenv('REDIS_PASSWORD') ?: null;
            
            $options = [];
            if ($password) {
                $options['parameters']['password'] = $password;
            }
            
            try {
                self::$client = new \Predis\Client([
                    'scheme' => 'tcp',
                    'host'   => $host,
                    'port'   => $port,
                ], $options);
                
                // Vérifier la connexion
                self::$client->ping();
            } catch (\Exception $e) {
                // En cas d'erreur, logger mais ne pas interrompre l'application
                error_log("Erreur de connexion Redis: " . $e->getMessage());
                // Retourner un client factice qui ne fait rien
                self::$client = new RedisNoopClient();
            }
        }
        
        return self::$client;
    }
}

/**
 * Client Redis factice utilisé en cas d'échec de connexion
 * Permet à l'application de continuer à fonctionner sans Redis
 */
class RedisNoopClient {
    public function __call($name, $arguments) {
        return null;
    }
} 
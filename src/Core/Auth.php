<?php
// src/Core/Auth.php

namespace GatePass\Core;

/**
 * Classe para gerenciar a autenticação e autorização em um ambiente de API RESTful.
 * Utiliza o conceito de tokens (JWT) para um modelo sem estado (stateless).
 */
class Auth
{
    /**
     * @var string A chave secreta usada para assinar e validar tokens.
     * Em produção, deve ser uma string longa, complexa e armazenada em um local seguro (ex: variável de ambiente).
     */
    private const JWT_SECRET_KEY = 'sua_chave_secreta_aqui_muito_complexa';

    /**
     * Gera um token JWT para um usuário ou cliente autenticado.
     * @param int $id ID do usuário ou cliente.
     * @param string $email Email da conta.
     * @param string $tipo Tipo da conta ('usuario' para vendedor, 'cliente' para comprador).
     * @return string O token JWT gerado.
     */
    public static function gerarToken(int $id, string $email, string $tipo): string
    {
        // O payload contém as informações não sensíveis que identificam o usuário.
        $payload = [
            'iss' => 'GatePassAPI', // Emissor do token
            'aud' => 'GatePassFrontend', // Audiência (quem pode usar o token)
            'iat' => time(), // Hora em que o token foi emitido
            'exp' => time() + (60 * 60 * 2), // Expira em 2 horas (tempo de vida do token)
            'data' => [
                'id' => $id,
                'email' => $email,
                'tipo' => $tipo
            ]
        ];

        // Em um ambiente de produção, este é o ponto onde uma biblioteca JWT real
        // assinaria o token com a chave secreta. Aqui, apenas codificamos para demonstração.
        return base64_encode(json_encode($payload));
    }

    /**
     * Extrai e valida um token JWT do cabeçalho de autorização da requisição.
     * @return object|null Um objeto com os dados do usuário se o token for válido, ou null.
     */
    public static function validarToken(): ?object
    {
        $headers = getallheaders();

        // Verifica se o cabeçalho 'Authorization' existe
        if (!isset($headers['Authorization'])) {
            return null;
        }

        // Formato padrão: Bearer [token]
        if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return null;
        }

        $token = $matches[1];

        try {
            // Decodifica o payload do token
            $payloadString = base64_decode($token);
            $payload = json_decode($payloadString, false); // Retorna um objeto

            if (!$payload) {
                return null;
            }

            // Verifica se o token expirou
            if ($payload->exp < time()) {
                // Token expirado. Em produção, retorne um erro HTTP 401 (Unauthorized)
                return null;
            }

            // Verifica o emissor e a audiência
            if ($payload->iss !== 'GatePassAPI' || $payload->aud !== 'GatePassFrontend') {
                return null;
            }

            // A lógica de validação de assinatura (criptográfica) de uma biblioteca
            // profissional seria executada aqui.

            return $payload->data; // Retorna os dados do usuário contidos no token
        } catch (\Exception $e) {
            // Captura qualquer erro de decodificação ou validação
            return null;
        }
    }
}   
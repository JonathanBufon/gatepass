<?php
// src/Utils/FileUpload.php

namespace GatePass\Utils;

class FileUpload
{
    private static $diretorioBase = __DIR__ . '/../../public/uploads';
    private static $diretoriosPermitidos = ['produtos']; 
    private static $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
    private static $tamanhoMaximoBytes = 5242880; // 5MB (5 * 1024 * 1024)

    /*
    Realiza o Upload das imagens
     */
    public static function upload(array $fileData, string $subDiretorio): string
    {
        if (!in_array($subDiretorio, self::$diretoriosPermitidos)) {
            throw new \Exception("Subdiretório de upload não permitido.");
        }

        if (!isset($fileData['error']) || is_array($fileData['error'])) {
            throw new \Exception("Parâmetros de upload inválidos.");
        }

        switch ($fileData['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \Exception("Nenhum arquivo enviado.");
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \Exception("Arquivo excede o tamanho máximo permitido.");
            default:
                throw new \Exception("Erro desconhecido no upload.");
        }

        // Valida tamanho do arquivo
        if ($fileData['size'] > self::$tamanhoMaximoBytes) {
            throw new \Exception("Arquivo muito grande.");
        }

        // Valida tipo MIME e extensão
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileData['tmp_name']);
        $extensao = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));

        $tiposMimePermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
        ];

        if (!isset($tiposMimePermitidos[$mimeType]) || $extensao !== $tiposMimePermitidos[$mimeType]) {
            throw new \Exception("Tipo de arquivo inválido. Apenas JPG, PNG, GIF são permitidos.");
        }

        // Garante que o diretório de destino exista
        $diretorioDestino = self::$diretorioBase . '/' . $subDiretorio;
        if (!is_dir($diretorioDestino)) {
            if (!mkdir($diretorioDestino, 0777, true)) { // Permissão 0777 é para testes
                throw new \Exception("Falha ao criar diretório de upload.");
            }
        }

        // Gera um nome de arquivo único para evitar colisões
        $nomeArquivoGerado = uniqid() . 'Utils' . $extensao;
        $caminhoCompletoDestino = $diretorioDestino . '/' . $nomeArquivoGerado;

        if (!move_uploaded_file($fileData['tmp_name'], $caminhoCompletoDestino)) {
            throw new \Exception("Falha ao mover o arquivo enviado.");
        }

        // Retorna apenas o nome do arquivo gerado para ser salvo no DB
        // O caminho completo no DB será 'uploads/subpasta/nome_do_arquivo'
        return 'uploads/' . $subDiretorio . '/' . $nomeArquivoGerado;
    }
}
<?php

namespace Recommendation\System\Model\Core;

interface ExporterInterface
{
    /**
     * Inicia a exportação
     */
    public function exportInit();
    /**
     * Obtem e processa os registros para o
     * export
     */
    public function process();
    /**
     * Obtem a collection paginada
     * e retorna apenas os attributos
     * necessários
     */
    public function getData();
    /**
     * Função que envia os registros
     * para a API do Sistema de Recomendação
     */
    public function send();
    /**
     * Função que grava os registros
     * no arquivo JSON caso for solicitado
     */
    public function write();

}


<?php

/**
 * Execução mais elaborada compreendendo o conceito do Parser e do Renderer como elementos separados
 * Testes com HTML e XML renderer.
 *
 * Utilizei como exemplo uma saída de conteúdo da API de IA da Anthropic.
 * Mas é apenas uma referência, na prática, é um markdown comum.
 */

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;
use League\CommonMark\Xml\XmlRenderer;
use League\CommonMark\Exception\CommonMarkException;

require "vendor/autoload.php";

/* Pego o conteúdo que quero processar */
$content = file_get_contents(__DIR__ . '/input/anthropic_response_with_html.md');
if($content === false) {
    exit('Conteúdo do arquivo não encontrado');
}

/* Configuro o ambiente com a referência principal de markdown */
$env = new Environment();
$env->addExtension(new CommonMarkCoreExtension());

/* Preparo o parser e executo para obter o documento completo da árvore de nós do markdown. Trabalharemos aqui no exemplo 3 */
$parser = new MarkdownParser($env);
try {
    $documentAST = $parser->parse($content);
} catch (CommonMarkException $e) {
    echo $e;
    exit('Erro ao interpretar o documento markdown');
}

/* Cria HTML Renderer, adiciona uma correção pequena de UTF-8 no HTML para abrir com a codificação correta e salva o resultado */
$htmlRenderer = new HtmlRenderer($env);
$html = $htmlRenderer->renderDocument($documentAST)->getContent();
$html = '<meta charset="UTF-8">' . $html;
file_put_contents(__DIR__ . '/output/anthropic_response_with_html.html', $html);

/* Gera o XML também para compararmos o resultado */
$xmlRenderer = new XmlRenderer($env);
$xml = $xmlRenderer->renderDocument($documentAST)->getContent();
file_put_contents(__DIR__ . '/output/anthropic_response_with_html.xml', $xml);

/* Fim */
echo 'Processamento finalizado' . "\n";
<?php

/**
 * Neste exemplo vamos percorrer o documento gerado com o parser
 * com o objetivo de encontrar os trechos de código para transformá-los em links para arquivos externos.
 *
 * Apenas para conhecimento do processo, usaremos um nó LinkDatapedia ao invés de usarmos diretamente os nós "Link"
 * já disponíveis na biblioteca
 */

use Kaleu\CommonMarkTests\LinkDatapedia;
use Kaleu\CommonMarkTests\LinkDatapediaRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;
use League\CommonMark\Xml\XmlRenderer;
use League\CommonMark\Exception\CommonMarkException;

require "vendor/autoload.php";

/* Nosso conteúdo agora foi modificado para ter ao menos 2 snippets de código de linguagens diferentes */
$content = file_get_contents(__DIR__ . '/input/example-3.md');

if($content === false) {
    exit('Conteúdo do arquivo não encontrado');
}

$env = new Environment();
$env->addExtension(new CommonMarkCoreExtension());

/* Dizemos ao ambiente que devemos renderizar nós do tipo LinkDatapedia com o LinkDatapediaRenderer */
$env->addRenderer(LinkDatapedia::class, new LinkDatapediaRenderer());

$parser = new MarkdownParser($env);

try {
    $documentAST = $parser->parse($content);
} catch (CommonMarkException $e) {
    echo $e;
    exit('Erro ao interpretar o documento markdown');
}

/* Agora que temos a árvore, pesquisamos nela os trechos de código */
$codeBlocksNodes = [];
foreach ($documentAST->iterator() as $node) {
    if($node instanceof FencedCode) {
        $codeBlocksNodes[] = $node;
    }
}

/* Vamos então substituir os trechos de código por links para os arquivos gerados com o conteúdo dos snippets */
foreach ($codeBlocksNodes as $node) {
    // Define extensão do arquivo pelo nome do conteúdo
    $extension = match($node->getInfo()) {
        'html' => "html",
        'python' => 'py',
        default => 'txt'
    };

    // Usa estratégia de hash para gerar um nome de arquivo, apenas para este exemplo
    $content = $node->getLiteral();
    $name = 'exemplo-3-'. substr(md5($content), 0, 10) . '.' . $extension;

    // Salva o conteúdo do arquivo
    file_put_contents(__DIR__ . '/output/' . $name, $content);

    // Cria link para o arquivo
    $url = "./{$name}";
    $label = "Arquivo {$node->getInfo()} gerado pela IA";
    $title = "Clique para ver o conteúdo completo do arquivo";
    $link = new LinkDatapedia($url, $label, $title);

    // Substitui o nó com o bloco inteiro pelo link
    $node->replaceWith($link);
}

/* Cria renderizadores finais e salva o html final */
$xmlRenderer = new XmlRenderer($env);
$xml = $xmlRenderer->renderDocument($documentAST)->getContent();
file_put_contents(__DIR__ . '/output/example-3.xml', $xml);

$htmlRenderer = new HtmlRenderer($env);
$html = $htmlRenderer->renderDocument($documentAST)->getContent();
$html = '<meta charset="UTF-8">' . $html;
file_put_contents(__DIR__ . '/output/example-3.html', $html);

echo 'Abra e teste: ' . __DIR__ . '/output/example-3.html' . "\n";
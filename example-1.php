<?php

/**
 * Exemplo mais básico de funcionamento.
 * Somente para ver funcionando e confirmar a instalação.
 */

require "vendor/autoload.php";

$content = file_get_contents(__DIR__ . '/input/basic.md');

$converter = new \League\CommonMark\CommonMarkConverter();
echo $converter->convert($content);
<?php

/**
 * Вкарваме файловете необходими за работа с програмата.
 */
require_once 'phpsass/SassParser.php';


/**
 * Конвертира sass файлове в css
 *
 * @category  vendors
 * @package   sass
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2013 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class sass_Converter
{
    
    
    /**
     * Конвертира sass в css файл
     * 
     * @param string $file - Линк към файла или стринг от стилове
     * @param string $syntax - Синтаксиса sass или scss
     * @param string $style - nested, expanded, compact, compressed
     * 
     * @return string - Конвертиран css стринг
     */
    static function convert($file, $syntax=FALSE, $style = 'nested')
    {
        // Опциите
        $options = array(
            'style' => $style,
            'cache' => FALSE,
            'syntax' => $syntax,
            'debug' => FALSE,
            'callbacks' => array(
            	'warn' => FALSE,
            	'debug' => FALSE,
            ),
        );
        
        // Инстанция на класа
        $parser = new SassParser($options);
        
        // Парсираме и връщаме резултата
        return $parser->toCss($file);
    }
}
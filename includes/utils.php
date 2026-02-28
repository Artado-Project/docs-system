<?php
/**
 * Utility functions for Artado Docs
 */

/**
 * Creates a URL-friendly slug from a string, handling Turkish characters.
 * 
 * @param string $text
 * @return string
 */
function createSlug($text) {
    if (empty($text)) return '';
    
    $turkce = array('ş','Ş','ı','İ','ğ','Ğ','ü','Ü','ö','Ö','ç','Ç');
    $duzgun = array('s','s','i','i','g','g','u','u','o','o','c','c');
    $text = str_replace($turkce, $duzgun, $text);
    
    // Convert to lowercase and replace non-alphanumeric characters with -
    $text = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($text)));
    
    // Remove multiple hyphens
    $text = preg_replace('/-+/', '-', $text);
    
    return trim($text, '-');
}

/**
 * Renders Markdown content with table support.
 */
function renderMarkdown($markdown) {
    require_once dirname(__FILE__) . '/Parsedown.php';
    $pd = new Parsedown();
    $pd->setSafeMode(false); // Enable if you want to allow some HTML, but tables are standard
    return $pd->text($markdown);
}

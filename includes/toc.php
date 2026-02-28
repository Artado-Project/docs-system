<?php
/**
 * TOC Generator for Artado Docs
 */
require_once 'utils.php';

class TOCGenerator {
    /**
     * Generates a TOC and injects IDs into headers of the HTML content.
     * 
     * @param string $html The rendered HTML content.
     * @return array ['html' => Modified HTML, 'toc' => Array of TOC items]
     */
    public static function generate($html) {
        $toc = [];
        $ids = []; // To track used IDs for uniqueness
        
        // Regex to find headers h1-h6
        $pattern = '/<(h[1-6])(.*?)>(.*?)<\/h[1-6]>/is';
        
        $modified_html = preg_replace_callback($pattern, function($matches) use (&$toc, &$ids) {
            $tag = $matches[1];
            $attrs = $matches[2];
            $content = $matches[3];
            $level = (int)substr($tag, 1);
            
            // Strip HTML tags for the ID generation
            $clean_text = strip_tags($content);
            $id = createSlug($clean_text);
            
            // Handle duplicate IDs
            $base_id = $id;
            $counter = 1;
            while (isset($ids[$id])) {
                $id = $base_id . '-' . $counter++;
            }
            $ids[$id] = true;
            
            $toc[] = [
                'level' => $level,
                'id' => $id,
                'title' => $clean_text
            ];
            
            // Inject ID into the tag
            // Check if ID already exists (unlikely from Parsedown but possible)
            if (strpos($attrs, 'id=') === false) {
                return "<$tag$attrs id=\"$id\"><a href=\"#$id\" class=\"header-anchor\">$content</a></$tag>";
            }
            
            return $matches[0];
        }, $html);
        
        return [
            'html' => $modified_html,
            'toc' => $toc
        ];
    }
    
    /**
     * Renders the TOC as a nested HTML list.
     * 
     * @param array $toc Items from generate()
     * @return string HTML list
     */
    public static function renderList($toc) {
        if (empty($toc)) return '';
        
        $html = '<div class="toc-container">';
        $html .= '<div class="toc-title">' . t('on_this_page', 'Bu Sayfada') . '</div>';
        $html .= '<ul class="toc-list">';
        
        foreach ($toc as $item) {
            $indent = ($item['level'] - 1) * 15;
            $html .= '<li class="toc-item level-' . $item['level'] . '" style="padding-left: ' . $indent . 'px;">';
            $html .= '<a href="#' . $item['id'] . '">' . htmlspecialchars($item['title']) . '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
}

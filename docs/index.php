<?php
require 'libs/Parsedown.php'; // Include the Parsedown library
require 'libs/JsonHighlighter.php'; // Include the JsonHighlighter library

// Function to get the base URL without /docs
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/docs');
    return $protocol . $host . $uri;
}

function getBaseUrlPart() {
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST']; // e.g., localhost:8080
    return $scheme . '://' . $host . '/';
}

// Function to parse URL parameters and wrap them in spans
function parseUrlParams($url) {
    $parts = explode('/', $url);
    $lastPart = end($parts);

    $lastPart = str_replace("?", '<span class="codeblock_api_urlparam">?</span>', $lastPart);
    $lastPart = str_replace("&", '<span class="codeblock_api_urlparam">&</span>', $lastPart);

    $parts[count($parts) - 1] = $lastPart;
    
    return implode('/', $parts);
}


// Function to parse special API markdown blocks before markdown conversion
function parseSpecialMarkdown($content) {
    // Find triple ``` code blocks of type api.request, api.response, notice.red, notice.yellow, notice.green and notice.gray
    preg_match_all('/```(api\.request|api\.response|notice\.red|notice\.yellow|notice\.green|notice\.gray|json)([\s\S]*?)```/', $content, $matches);
    // Iterate the matches
    foreach ($matches[0] as $key => $match) {
        $type = $matches[1][$key];
        $block = $matches[2][$key];

        // Remove first ```
        $first_pos = strpos($block, "```");
        if ($first_pos !== false) {
            $block = substr_replace($block, '', $first_pos, 3);
        }
        // Remove last ```
        $last_pos = strrpos($block, "```");
        if ($last_pos !== false) {
            $block = substr_replace($block, '', $last_pos, 3);
        }

        // Api
        if (substr($type, 0, strlen("api.")) === "api.") {
            // api.requests
            if ($type === 'api.request') {
                $baseUrlRepl = str_replace(getBaseUrlPart(), '<span class="codeblock_api_url">' . getBaseUrlPart() . '</span>', getBaseUrl());
                $block = str_replace('{url}', $baseUrlRepl, $block);
                $block = parseUrlParams($block);
            }

            // api.response
            else if ($type === 'api.response') {
                // Color the JSON
                $block = JsonHighlighter::highlight($block);
            }

            // Add blocks
            $typename = str_replace('api.', '', $type);
            $block = '<pre class="codeblock_api_' . $typename . '">' . $block . '</pre>';
        }
        
        // Notices
        else if (substr($type, 0, strlen("notice.")) === "notice.") {
            // Wrapp in div
            $block = '<div class="' . str_replace("notice.","notice_",$type) . '">' . $block . '</div>';
        }

        // json
        else if ($type === 'json') {
            // Color the JSON
            $block = JsonHighlighter::highlight($block);
            // trim the block
            $block = trim($block);
            $block = '<pre class="codeblock_json"><code class="language-json">' . $block . '</code></pre>';
        }

        $content = str_replace($match, $block, $content);
    }

    return $content;
}

// Function to wrap API blocks with <div class="codeblock_api"> before Parsedown
function wrapApiCodeBlocks($content) {
    // Match when codeblock_api_request is directly followed by codeblock_api_response
    $content = preg_replace_callback('/<pre class="codeblock_api_request">([\s\S]*?)<\/pre>\s*<pre class="codeblock_api_response">([\s\S]*?)<\/pre>/', function ($matches) {
        // Wrap the pair of <pre> blocks inside a <div class="codeblock_api">
        $wrappedBlock = '<div class="codeblock_api">' . $matches[0] . '</div>';
        return $wrappedBlock;
    }, $content);

    return $content;
}

// Determine which markdown file to load based on the URL parameter 'ver'
$defaultVersion = 'v0';
$version = isset($_GET['ver']) ? $_GET['ver'] : $defaultVersion; // Default to 'v0' if 'ver' is not set
$markdownFile = $version . '.md';
$originalQueriedFileFound = TRUE;

// Check if the markdown file exists
if (!file_exists($markdownFile)) {
    $markdownFile = $defaultVersion . '.md';
    $originalQueriedFileFound = FALSE;
}

// Read the chosen markdown file
$markdown = file_get_contents($markdownFile);

// Append warning if the original queried file was not found
if (!$originalQueriedFileFound) {
    $warning = <<<EOT
    \n\n```notice.red
    The requested version "%version%" was not found, loaded "%defaultVersion%" instead!
    ```\n\n
    EOT;
    $warning = str_replace('%version%', $version, $warning);
    $warning = str_replace('%defaultVersion%', $defaultVersion, $warning);
    $markdown = $warning . $markdown;
}

// Parse the special markdown
$markdown = parseSpecialMarkdown($markdown);

// Wrap API code blocks with <div class="codeblock_api">
$markdown = wrapApiCodeBlocks($markdown);

// Convert markdown to HTML using Parsedown
$Parsedown = new Parsedown();
$parsedContent = $Parsedown->text($markdown);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodMenu API - Group1</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="json.css">
</head>
<body>
    <main>
        <?php echo $parsedContent; ?>
    </main>
</body>
</html>

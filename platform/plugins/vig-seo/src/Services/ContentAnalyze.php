<?php

namespace VigStudio\VigSeo\Services;

include __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;

class ContentAnalyze
{
    private $client = null;

    private $baseUrl;

    private $domainUrl;

    private $domainname;

    public function analyze(string $url, string $content = '', string $keywords = ''): array
    {
        $this->baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/' . ltrim(parse_url($url, PHP_URL_PATH), '/');
        $this->domainUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
        $this->domainname = parse_url($url, PHP_URL_HOST);

        $document = $this->parseHtml($content);

        $result = [
            'url' => $url,
            'baseUrl' => $this->baseUrl,
            'domainUrl' => $this->domainUrl,
            'domainname' => $this->domainname,
            'title' => $this->getTitle($document),
            'description' => $this->getDescription($document),
            'language' => $this->getLanguage($document),
            'mainText' => $this->getMainText($document),
            'headers' => $this->getHeadings($document),
            'links' => $this->getLinks($document),
            'images' => $this->getImages($document),
            'keywords' => $this->getKeywords($document),
            'getKeywords' => $this->extractKeywordsFromBody($document, explode(',', $keywords)),
            'content' => $this->getContentToArray($document),
        ];

        return $result;
    }

    private function parseHtml($body): Crawler
    {
        $crawler = new Crawler($body);

        return $crawler;
    }

    private function getContentToArray(Crawler $document): array
    {
        $nodes = $document->filter('body > *')->each(function (Crawler $node, $i) {
            return $node->text();
        });

        return $nodes;
    }

    private function getTitle(Crawler $document): string|null
    {
        $titleNode = $document->filter('head > title');
        if ($titleNode->count() > 0) {
            $title = $titleNode->text();
        } else {
            $title = null;
        }

        return $title;
    }

    private function getLanguage(Crawler $document): string|null
    {
        $langNode = $document->filter('html')->attr('lang');
        if ($langNode) {
            $lang = $langNode;
        } else {
            $lang = null;
        }

        return $lang;
    }

    private function getDescription(Crawler $document): string|null
    {
        $descriptionNode = $document->filter('head > meta[name="description"]');
        if ($descriptionNode->count() > 0) {
            $description = $descriptionNode->attr('content');
        } else {
            $description = null;
        }

        return $description;
    }

    private function getLinks(Crawler $document): array
    {
        $follow = [];
        $nofollow = [];
        $internal = [];
        $external = [];

        $document->filter('a')->each(function (Crawler $node) use (&$follow, &$nofollow, &$internal, &$external) {
            if ($node->attr('rel') === 'nofollow') {
                $nofollow[] = $node->attr('href');
            } else {
                $follow[] = $node->attr('href');
            }

            $url = parse_url($node->attr('href'));
            if (isset($url['host']) && $url['host'] === $this->domainname) {
                $internal[] = $node->attr('href');
            } else {
                $external[] = $node->attr('href');
            }
        });

        return [
            'follow' => $follow,
            'nofollow' => $nofollow,
            'internal' => $internal,
            'external' => $external,
        ];
    }

    private function getImages(Crawler $document): array
    {
        $imagesWithAlt = $document->filter('img[alt]')->each(function (Crawler $node, $i) {
            return [
                'src' => $node->attr('src'),
                'alt' => $node->attr('alt'),
            ];
        });

        $imagesWithoutAlt = $document->filter('img:not([alt])')->each(function (Crawler $node, $i) {
            return [
                'src' => $node->attr('src'),
            ];
        });

        $images = [
            'withAlt' => $imagesWithAlt,
            'withoutAlt' => $imagesWithoutAlt,
        ];

        return $images;
    }

    private function getMainText(Crawler $document): array
    {
        $nodes = [];
        $document->filter('body')->children()->each(function (Crawler $node) use (&$nodes) {
            $nodes[] = $node->text();
        });

        $text = $document->filter('body')->text();
        $word_count = str_word_count($text);

        return [
            'content' => $nodes,
            'code_to_text_ratio' => $this->getCodeToTextRatio($document),
            'word_count' => $word_count,
        ];
    }

    private function getCodeToTextRatio(Crawler $document): float
    {
        $text = $document->filter('body')->text();
        $code = $document->filter('body')->html();

        $text_length = strlen($text);
        $code_length = strlen($code);

        return ($text_length / $code_length) * 100;
    }

    private function getHeadings(Crawler $document): array
    {
        $headers = [];
        $document->filter('body h1, body h2, body h3, body h4, body h5, body h6')->each(function (Crawler $node, $i) use (&$headers) {
            $tag = $node->nodeName();
            $text = trim($node->text());

            if (! isset($headers[$tag])) {
                $headers[$tag] = [];
            }

            $headers[$tag][] = ['text' => $text];
        });

        return $headers;
    }

    private function getKeywords(Crawler $document): array
    {
        $contents = $this->getContentToArray($document);
        $body = $document->filter('body')->text();

        $result = [];
        foreach ($contents as $text) {
            $keywords = $this->extractkeywords($text);
            $result = array_merge($result, $keywords);
        }
        $result = array_unique($result);

        $matches = $this->extractKeywordsFromBody($document, $result);

        return $matches;
    }

    private function extractkeywords(string $text): array
    {
        $delimiters = ['.', ';', '?', '!', ':', ',', '(', ')', '[', ']', '{', '}'];
        $stop_words = [];
        $text = trim($text);
        $delimiters = implode('|', $delimiters);
        $sentences = preg_split("/[$delimiters]/", $text);
        $result = [];
        foreach ($sentences as $sentence) {
            if (trim($sentence) === '') {
                continue;
            }
            $words = explode(' ', $sentence);
            $valid_words = [];
            foreach ($words as $word) {
                if (! is_numeric($word)) {
                    $valid_words[] = $word;
                }
            }
            if (count($valid_words) > 0 && count($valid_words) > 3 && count($valid_words) < 20) {
                $result[] = trim(implode(' ', $valid_words));
            }
        }

        return $result;
    }

    private function extractKeywordsFromBody(Crawler $document, array $keywords)
    {
        $body = $document->filter('body')->text();
        $matches = [];
        foreach ($keywords as $keyword) {
            if (! empty($keyword)) {
                preg_match_all('/\b' . preg_quote($keyword, '/') . '\b/i', $body, $match);
                if (! empty($match[0])) {
                    $matches[$keyword] = $match[0];
                }
            }
        }

        return $matches;
    }
}

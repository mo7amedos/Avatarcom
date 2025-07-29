<?php

namespace VigStudio\VigSeo\Providers;

use Illuminate\Support\ServiceProvider;
use MetaBox;
use VigStudio\VigSeo\Facades\ContentAnalyze;

class HookServiceProvider extends ServiceProvider
{
    public function boot()
    {
        add_action(BASE_ACTION_META_BOXES, [$this, 'addSeoScore'], 99, 2);
        add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'setKeywords'], 127, 3);
        add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'setKeywords'], 127, 3);
    }

    public function addSeoScore(string $priority, $data)
    {
        if ($priority == 'advanced'
            && ! empty($data)
            && in_array(get_class($data), [
                'Botble\Blog\Models\Post',
                'Botble\Page\Models\Page',
            ])
        ) {
            MetaBox::addMetaBox(
                'seo_score_wrap',
                'SEO Scanner',
                [$this, 'addPublishFields'],
                get_class($data),
                'advanced',
                'low'
            );

            return true;
        }

        return false;
    }

    public function addPublishFields()
    {
        $args = func_get_args();

        $meta = [];

        if (! empty($args[0]) && $args[0]->id) {
            $metadata = MetaBox::getMetaData($args[0], 'vig_seo_keywords', true);
        }

        if (! empty($metadata) && is_array($metadata)) {
            $meta = array_merge($meta, $metadata);
            $meta = implode(',', $meta['keywords']);
        } else {
            $meta = '';
        }

        $object = $args[0];

        $title = $meta['seo_title'] ?? (! empty($object->id) ? $object->name ?? $object->title : null);
        $description = $meta['seo_description'] ?? (! empty($object->id) ? $object->description : null);

        $content = '';
        $content .= '<html>';
        $content .= '<head>';
        $content .= '<title>' . $title . '</title>';
        $content .= '<meta name="description" content="' . $description . '">';
        $content .= '</head>';
        $content .= '<body>';
        $content .= '<h1>' . $title . '</h1>';
        $content .= '<p>' . $description . '</p>';
        $content .= $object->content;
        $content .= '</html>';

        $data = ContentAnalyze::analyze($args[0]->url, $content, $meta);

        return view('plugins/vig-seo::score-box', compact('data', 'meta'))->render();
    }

    public function setKeywords($screen, $request, $object): bool
    {
        $keywords = explode(',', $request->input('vig_seo_keywords'));
        $data = MetaBox::saveMetaBoxData($object, 'vig_seo_keywords', ['keywords' => $keywords]);

        return true;
    }

    public function highlightContent($string, $value)
    {
        $keyword = $value['originalText'];
        $start = $value['startIndex'];
        $length = ($value['endIndex']) - ($value['startIndex']);

        $start = mb_strpos($string, $keyword, 0, 'UTF-8');
        $length = mb_strlen($keyword, 'UTF-8');
        $highlighted = mb_substr($string, $start, $length, 'UTF-8');
        $before = mb_substr($string, 0, $start, 'UTF-8');
        $after = mb_substr($string, $start + $length, mb_strlen($string, 'UTF-8'), 'UTF-8');

        return $before . ' <b style="background:red">' . $highlighted . '</b>&nbsp; <b style="background:green">' . $value['suggestion'] . '</b> ' . $after;
    }
}
